<?php

namespace FetchLeo\LaravelXml\Facades;

use Illuminate\Support\Facades\Facade;

class Xml extends Facade
{
    protected static function getFacadeAccessor() { return 'xml'; }
}