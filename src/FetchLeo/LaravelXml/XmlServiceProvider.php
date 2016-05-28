<?php

namespace FetchLeo\LaravelXml;

use FetchLeo\LaravelXml\ConverterManager;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

class XmlServiceProvider extends ServiceProvider
{
    const DEFAULT_CONVERTERS = [
        'laravelxml.converters.model'
    ];

    /**
     * Bootstrap.
     */
    public function boot()
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/laravel-xml.php', 'laravel-xml');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerBindings();
        AliasLoader::getInstance()->alias('Xml', 'FetchLeo\LaravelXml\Facades\Xml');
    }

    private function registerBindings()
    {
        $this->app->bind('xml', Xml::class);
        $this->app->singleton('laravelxml.converters.manager', function() {
            return app(ConverterManager::class);
        });
        $this->app->singleton('FetchLeo\LaravelXml\Contracts\ConverterManager', 'laravelxml.converters.manager');
    }
}