<?php

namespace FetchLeo\LaravelXml\Converters;

use FetchLeo\LaravelXml\Contracts\Converter;
use FetchLeo\LaravelXml\Exceptions\CantConvertValueException;
use FetchLeo\LaravelXml\Facades\Xml;
use Illuminate\Support\Collection;
use SimpleXMLElement;

class CollectionConverter implements Converter
{
    /**
     * Convert a value to XML.
     *
     * @param Collection $value
     * @param SimpleXMLElement $element
     * @return SimpleXMLElement
     * @throws CantConvertValueException
     */
    public function convert($value, SimpleXMLElement $element) : SimpleXMLElement
    {
        if (!($value instanceof Collection)) throw new CantConvertValueException("Value is not a collection.");

        // Hand off an array form of the collection to another converter.
        return Xml::convert($value->toArray(), $element);
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
        return $value instanceof Collection && $type === self::TYPE_COLLECTION;
    }
}