<?php

namespace FetchLeo\LaravelXml\Contracts;

use SimpleXMLElement;

interface Xml
{
    /**
     * Convert a value to XML.
     *
     * @param mixed $value
     * @param SimpleXMLElement $element
     * @return SimpleXMLElement
     */
    public function convert($value, SimpleXMLElement $element = null) : SimpleXMLElement;
}