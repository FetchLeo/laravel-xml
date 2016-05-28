<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Converters
    |--------------------------------------------------------------------------
    |
    | This array allows you to define converters to be used for different object types.
    | Converters are the basic framework of this package; they handle the actual conversion
    | of models and objects to XML.
    |
    | In addition to setting default converters, you can change what converters are used
    | for specific models or objects. An example has been provided for you.
    |
    | The default identifiers that you can use for different converters are:
    |    - "laravelxml.converters.model" for a ModelConverter
    |    - "laravelxml.converters.object" for an ObjectConverter
    |
    | If you would like to use your own, you can either specify a full classname as the identifier, or your own
    | identifier that you registered in your code. Make sure to call "$this->app->bind('name-here', 'class-here')" in your
    | service provider, assuming that you want to use a custom identifier. Otherwise, you MUST use the full classname.
    */
    'converters' => [
        'custom' => [
            'My\Custom\Model' => 'laravelxml.converters.model',
            'My\Custom\Object' => 'laravelxml.converters.object',
            'My\Custom\Other\Object' => 'my.own.object_converter',
            'My\Custom\Other\Model' => 'my.own.model_converter',
            'models' => 'my.custom.default_model_converter',
        ],

        'defaults' => [
            'models' => 'laravelxml.converters.model',
            'objects' => 'laravelxml.converters.object',
            'arrays' => 'laravelxml.converters.array',
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Generic XML Settings
    |--------------------------------------------------------------------------
    |
    | Here, you can set some generic settings for the package. Right now,
    | you can only set the base template string to be used. The template string
    | will be used as the base of the XML structure.
    |
    | Of course, you can always replace this to use a env() call if you would like.
     */
    'xml' => [
        'templateString' => '<?xml version="1.0" encoding="UTF-8"?><response/>'
    ]
];