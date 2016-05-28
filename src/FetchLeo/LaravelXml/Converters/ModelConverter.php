<?php

namespace FetchLeo\LaravelXml\Converters;

use FetchLeo\LaravelXml\Contracts\Converter;
use FetchLeo\LaravelXml\Exceptions\CantConvertValueException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use SimpleXMLElement;

class ModelConverter implements Converter
{
    /**
     * Convert a value to XML.
     *
     * @param Model $value
     * @param SimpleXMLElement $element
     * @return SimpleXMLElement
     * @throws CantConvertValueException
     */
    public function convert($value, SimpleXMLElement $element) : SimpleXMLElement
    {
        if (!($value instanceof Model)) throw new CantConvertValueException("Value is not a model.");

        return $this->prepareElement(
            collect($value->attributesToArray()),
            $element
        );
    }

    /**
     * Mutate an XML element based on the given data.
     *
     * @param Collection $data
     * @param SimpleXMLElement $element
     * @param mixed $providedKey
     * @return SimpleXMLElement The new element.
     */
    protected function prepareElement(Collection $data, SimpleXMLElement $element, $providedKey = null) : SimpleXMLElement
    {
        foreach($data->toArray() as $key => $value) {
            if (is_array($value)) {
                $this->prepareElement(
                    collect($value),
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
        return $value instanceof Model && $type === self::TYPE_MODEL;
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