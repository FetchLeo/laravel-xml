<?php

namespace FetchLeo\LaravelXml\Contracts;

use SimpleXMLElement;

interface Converter
{
    const TYPE_MODEL = 'model';
    const TYPE_ARRAY = 'array';
    const TYPE_COLLECTION = 'collection';
    const TYPE_OBJECT = 'object';

    /**
     * Convert a value to XML.
     *
     * @param mixed $value
     * @param SimpleXMLElement $element
     * @return SimpleXMLElement
     */
    public function convert($value, SimpleXMLElement $element) : SimpleXMLElement;

    /**
     * Determine if this converter can convert the given value.
     *
     * @param mixed $value
     * @param $type
     * @return bool
     */
    public function canConvert($value, $type) : bool;
}