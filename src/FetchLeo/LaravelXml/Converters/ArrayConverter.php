<?php

namespace FetchLeo\LaravelXml\Converters;

use FetchLeo\LaravelXml\Contracts\Converter;
use FetchLeo\LaravelXml\Exceptions\CantConvertValueException;
use SimpleXMLElement;

class ArrayConverter implements Converter
{
    /**
     * Convert a value to XML.
     *
     * @param array $value
     * @param SimpleXMLElement $element
     * @return SimpleXMLElement
     * @throws CantConvertValueException
     */
    public function convert($value, SimpleXMLElement $element) : SimpleXMLElement
    {
        if (!is_array($value)) throw new CantConvertValueException("Value is not an array.");

        return $this->prepareElement(
            $value,
            $element
        );
    }

    /**
     * Mutate an XML element based on the given data.
     *
     * @param array $data
     * @param SimpleXMLElement $element
     * @param mixed $providedKey
     * @return SimpleXMLElement The new element.
     */
    protected function prepareElement(array $data, SimpleXMLElement $element, $providedKey = null) : SimpleXMLElement
    {
        foreach($data as $key => $value) {
            if (is_array($value)) {
                $this->prepareElement(
                    collect($value)->toArray(),
                    $element->addChild(is_numeric($key) ? ($providedKey ?: $this->intelligent_key($value)) : $key),
                    str_singular(is_numeric($key) ? ($providedKey ?: $this->intelligent_key($value)) : $key)
                );
            } else {
                $element->addChild(is_numeric($key) ? ($providedKey ?: $this->intelligent_key($value)) : $key, $value);
            }
        }

        return $element;
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
        return is_array($value) && $type === self::TYPE_ARRAY;
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
}