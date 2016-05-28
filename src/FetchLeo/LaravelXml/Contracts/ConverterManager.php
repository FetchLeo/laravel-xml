<?php

namespace FetchLeo\LaravelXml\Contracts;

interface ConverterManager
{
    /**
     * Get all of the registered converters.
     *
     * @return array
     */
    public function getConverters() : array;

    /**
     * Get all of the casted converters.
     *
     * @return array
     */
    public function getCastedConverters() : array;

    /**
     * Get the default converters.
     *
     * @return array
     */
    public function getDefaultConverters() : array;

    /**
     * Register a new converter.
     *
     * @param string $name
     * @param Converter $converter
     * @return void
     */
    public function register(string $name, Converter $converter);

    /**
     * Get a converter by its name.
     *
     * @param string $name
     * @return Converter
     */
    public function getByName(string $name);
}