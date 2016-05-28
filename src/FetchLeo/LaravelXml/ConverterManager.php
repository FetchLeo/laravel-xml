<?php

namespace FetchLeo\LaravelXml;

use FetchLeo\LaravelXml\Contracts\Converter;
use FetchLeo\LaravelXml\Contracts\ConverterManager as Contract;
use FetchLeo\LaravelXml\Converters\ArrayConverter;
use FetchLeo\LaravelXml\Converters\CollectionConverter;
use FetchLeo\LaravelXml\Converters\ModelConverter;
use FetchLeo\LaravelXml\Converters\ObjectConverter;
use FetchLeo\LaravelXml\Exceptions\NoConverterFoundException;
use Illuminate\Container\Container;

class ConverterManager implements Contract
{
    /**
     * The registered converters.
     *
     * @var array
     */
    protected $converters = [];

    /**
     * The casted converters.
     *
     * @var array
     */
    protected $castedConverters = [];

    /**
     * The default converters.
     *
     * @var array
     */
    protected $defaultConverters = [
        'models' => 'laravelxml.converters.model',
        'objects' => 'laravelxml.converters.object',
        'arrays' => 'laravelxml.converters.array',
        'collections' => 'laravelxml.converters.collection',
    ];

    /**
     * ConverterManager constructor.
     */
    public function __construct()
    {
        $this->registerBaseConverters();
    }

    /**
     * @inheritdoc
     */
    public function getConverters() : array
    {
        return $this->converters;
    }

    /**
     * @inheritdoc
     */
    public function getCastedConverters() : array
    {
        return $this->castedConverters;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultConverters() : array
    {
        return $this->defaultConverters;
    }

    /**
     * @inheritdoc
     */
    public function register(string $name, Converter $converter)
    {
        $this->converters[$name] = $converter;
        Container::getInstance()->singleton($name, $converter);
    }

    /**
     * @inheritdoc
     */
    public function getByName(string $name)
    {
        if (isset($this->castedConverters[$name])) return $this->castedConverters[$name];
        if (!isset($this->converters[$name])) throw new NoConverterFoundException("Couldn't find that converter.");

        return $this->converters[$name];
    }

    /**
     * Cast an alias to a class.
     *
     * @param string $name
     * @param string $class
     */
    protected function cast(string $name, string $class)
    {
        $this->castedConverters[$name] = app($class);
    }

    /**
     * Register the base converters.
     */
    protected function registerBaseConverters()
    {
        $converters = [
            'laravelxml.converters.model' => ModelConverter::class,
            'laravelxml.converters.array' => ArrayConverter::class,
            'laravelxml.converters.object' => ObjectConverter::class,
            'laravelxml.converters.collection' => CollectionConverter::class,
        ];

        foreach($converters as $key => $value) {
            $this->register($key, app($value));
            $this->cast($key, $value);
        }
    }
}