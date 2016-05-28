<?php

namespace FetchLeo\LaravelXml;

use Exception;
use FetchLeo\LaravelXml\Contracts\ConverterManager as ConverterManagerContract;
use FetchLeo\LaravelXml\Exceptions\CantConvertValueException;
use FetchLeo\LaravelXml\Exceptions\NoConverterFoundException;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Application;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use SimpleXMLElement;

use FetchLeo\LaravelXml\Contracts\Xml as Contract;

class Xml implements Contract
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @var ConverterManagerContract
     */
    private $converterManager;
    
    /**
     * @var Application
     */
    private $application;

    /**
     * Xml constructor.
     * @param Container $container
     * @param Application $application
     * @param ConverterManagerContract $converterManager
     */
    public function __construct(Container $container, Application $application, ConverterManagerContract $converterManager)
    {
        $this->container = $container;
        $this->converterManager = $converterManager;
        $this->application = $application;
    }

    /**
     * @inheritdoc
     */
    public function convert($value, SimpleXMLElement $element = null) : SimpleXMLElement
    {
        if (!$this->isConvertable($this->getType($value))) {
            throw new CantConvertValueException("The value you passed can not be converted.");
        }

        $converter = $this->getConverterFor($value);
        $element = $element ?: new SimpleXMLElement($this->application['config']->get('laravel-xml.xml.templateString'));

        if (isset($this->converterManager->getConverters()[$converter])) {
            return $this->converterManager->getConverters()[$converter]
                ->convert($value, $element);
        }

        if (!class_exists($converter) && !($this->container->bound($converter))) {
            return $element;
        }

        return $this->container->make($converter)
            ->convert($value, $element);
    }

    /**
     * Get the appropriate converter name for the given value.
     * TODO: Clean this up. A lot.
     *
     * @param mixed $value
     * @param bool $debug
     * @return string
     */
    public function getConverterFor($value, bool $debug = false)
    {
        $type = $this->getType($value);
        $config = app('Illuminate\Contracts\Config\Repository');
        $defaults = collect($this->converterManager->getDefaultConverters())
            ->merge(
                $this->application['config']->get('laravel-xml.converters.defaults')
            )->filter(function($item) {
                $item = is_array($item) ? $item['value'] : $item;
                return class_exists($item) OR $this->container->bound($item);
            });

        $custom = collect($this->application['config']
            ->get('laravel-xml.converters.custom'))
            ->filter(function($item) {
                $item = is_array($item) ? $item['value'] : $item;
                return class_exists($item) OR $this->container->bound($item);
            });
//        if ($debug) dd($custom);

        // Step one: Try to find the CLASS or TYPE in $custom
        $class =  $custom->get(
            is_object($value) ? get_class($value) : str_plural($type),
            function() use ($custom, $defaults, $value, $type) {
                // Step two: try to find the TYPE in $custom
                return $custom->get(
                    str_plural($type),
                    function() use ($defaults, $value, $type) {
                        // Step three: Try to find the CLASS or TYPE in $defaults
                        return $defaults->get(
                            is_object($value) ? get_class($value) : str_plural($type),
                            function() use ($defaults, $value, $type) {
                                // Step four: Try to find the TYPE in $defaults
                                return $defaults->get(
                                    str_plural($type),
                                    function() {
                                        // If nothing works, throw an error.
                                        throw new NoConverterFoundException("Could not find an appropriate converter.");
                                    }
                                );
                            }
                        );
                    }
                );
            });

        return is_array($class) ?
            isset($class['value']) ? $class['value'] : ''
            : $class;
    }

    /**
     * Get a string that can be used to find the appropriate converter.
     * It simply states what type of value the passed value is.
     *
     * @param $value
     * @return string
     */
    public function getType($value)
    {
        if ($value instanceof Model) return 'model';
        if ($value instanceof Collection) return 'collection';
        if (is_array($value)) return 'array';
        if (is_object($value)) return 'object';
        if (is_string($value)) return 'string';
        if (is_int($value)) return 'int';

        return 'other';
    }

    private function isConvertable($type)
    {
        return in_array($type, [
            'model',
            'collection',
            'array',
            'object'
        ]);
    }

    private function isType($value)
    {
        return in_array($value, [
            'model',
            'collection',
            'array',
            'object',
            'string',
            'int'
        ]);
    }

    private function converterWorks($name, $value)
    {
        return $this->converterManager->getByName($name)
            ->canConvert($value, $this->isType($value) ? $value : $this->getType($value));
    }
}