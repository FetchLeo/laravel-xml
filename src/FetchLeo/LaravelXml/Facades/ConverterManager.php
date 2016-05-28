<?php

namespace FetchLeo\LaravelXml\Facades;

use Illuminate\Support\Facades\Facade;

class ConverterManager extends Facade
{
    protected static function getFacadeAccessor() { return 'laravelxml.converters.manager'; }
}