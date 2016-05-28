<?php

namespace FetchLeo\LaravelXml\Converters;

use FetchLeo\LaravelXml\Contracts\Converter;
use FetchLeo\LaravelXml\Exceptions\CantConvertValueException;
use FetchLeo\LaravelXml\Facades\Xml;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use ReflectionObject;
use ReflectionProperty;
use SimpleXMLElement;

/**
 * Class ObjectConverter
 * @package FetchLeo\LaravelXml\Converters
 *
 * Quite a convoluted class, unfortunately.
 */
class ObjectConverter implements Converter
{
    /**
     * Convert a value to XML.
     *
     * @param object $value
     * @param SimpleXMLElement $element
     * @return SimpleXMLElement
     * @throws CantConvertValueException
     */
    public function convert($value, SimpleXMLElement $element) : SimpleXMLElement
    {
        if (is_array($value)) throw new CantConvertValueException(
            "Arrays can not be used with ObjectConverter. Use ArrayConverter instead."
        );

        if ($value instanceof Model) throw new CantConvertValueException(
            "Models can not be used with ObjectConverter. Use ModelConverter instead."
        );

        if ($value instanceof Collection) throw new CantConvertValueException(
            "Collections can not be used with ObjectConverter. Use CollectionConverter instead."
        );

        return $this->prepareElement(
            collect($this->getObjectProperties($value)),
            $element,
            $value
        );
    }

    /**
     * Get all of the public properties of the object.
     *
     * @param object $value
     * @return array
     */
    protected function getObjectProperties($value) : array
    {
        return (new ReflectionObject($value))
            ->getProperties(ReflectionProperty::IS_PUBLIC);
    }

    /**
     * Mutate an XML element based on the given data.
     *
     * @param Collection $data
     * @param SimpleXMLElement $element
     * @param object $object The object.
     * @param mixed $providedKey
     * @return SimpleXMLElement The new element.
     */
    protected function prepareElement(Collection $data, SimpleXMLElement $element, $object, $providedKey = null) : SimpleXMLElement
    {
        foreach($data->all() as $value) {
            $this->prepareWithReflection($value, $element, $object);
        }

        return $element;
    }

    /**
     * Using the given ReflectionProperty object, mutate the XML element.
     *
     * @param ReflectionProperty $property
     * @param SimpleXMLElement $element
     * @param object $object
     * @param mixed $providedKey
     */
    protected function prepareWithReflection(ReflectionProperty $property, SimpleXMLElement $element, $object, $providedKey = null)
    {
        $value = $property->getValue($object);
        $element = $element->addChild($property->getName());

        $this->prepareFromValue(collect($value), $element);
    }

    /**
     * This is what actually handles manipulating the XML element.
     *
     * @param Collection $data
     * @param SimpleXMLElement $element
     * @param mixed $providedKey
     */
    protected function prepareFromValue(Collection $data, SimpleXMLElement &$element, $providedKey = null)
    {
        foreach($data->toArray() as $key => $value) {
            if (is_array($value)) {
                $this->prepareFromValue(
                    collect($value),
                    $element->addChild(is_numeric($key) ? ($providedKey ?: $this->intelligent_key($value)) : $key),
                    str_singular(is_numeric($key) ? ($providedKey ?: $this->intelligent_key($value)) : $key)
                );
            } else {
                $element->addChild(is_numeric($key) ? ($providedKey ?: $this->intelligent_key($value)) : $key, $value);
            }
        }
    }

    /**
     * Intelligent key technology... such a boring name
     * Only use if absolutely necessary!!!
     *
     * This is really quite intelligent *sarcasm*
     *
     * @param $value
     */
    protected function intelligent_key($value)
    {
        return Xml::getType($value);
    }

    /**
     * Determine if this converter can convert the given value.
     *
     * @param mixed $value
     * @param $type
     * @return bool
     */
    public function canConvert($value, $type) : bool
    {
        return is_object($value) && !($value instanceof Model) && $type == self::TYPE_OBJECT;
    }
}