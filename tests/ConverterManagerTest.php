<?php

use FetchLeo\LaravelXml\Contracts\Converter;
use FetchLeo\LaravelXml\Facades\Xml;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class ConverterManagerTest extends Orchestra\Testbench\TestCase
{
    /**
     * Get package providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return ['FetchLeo\LaravelXml\XmlServiceProvider'];
    }

    /** @test */
    public function it_sees_the_configuration_file()
    {
        $this->assertEquals('laravelxml.converters.model', $this->app['config']->get('laravel-xml.converters.defaults.models'));
    }

    /** @test */
    public function it_retrieves_the_proper_converter_manager_instance()
    {
        /* @var \FetchLeo\LaravelXml\ConverterManager $converterManager */
        $converterManager = app('laravelxml.converters.manager');

        $this->assertArrayHasKey('laravelxml.converters.model', $converterManager->getConverters());
    }

    /** @test */
    public function it_properly_converts_objects()
    {
        /* @var SimpleXMLElement $result */
        $result = Xml::convert(new TestObject);

        $this->assertCount(0, $result->xpath('xxxx'));
        $this->assertCount(0, $result->xpath('privateProp'));
        $this->assertCount(1, $result->xpath('array/testing'));
        $this->assertCount(1, $result->xpath('array/const_test'));
        $this->assertCount(1, $result->xpath('key'));
    }

    /** @test */
    public function it_properly_converts_collections_and_arrays()
    {
        /* @var SimpleXMLElement $result1 */
        $result1 = Xml::convert(collect([
            'testing' => [
                'nested' => [
                    'value' => 'test',
                    'testing',
                    50,
                    [
                        'value' => 'other',
                        'key' => 5,
                        'array' => [
                            'value1' => 1,
                            'value2' => 'test'
                        ]
                    ]
                ]
            ]
        ]));

        $this->assertTrue($result1->xpath('testing/nested/int') !== false);

        /* @var SimpleXMLElement $result2 */
        $result2 = Xml::convert([
            'testing' => [
                'nested' => [
                    'value' => 'test',
                    'testing',
                    50,
                    [
                        'value' => 'other',
                        'key' => 5,
                        'array' => [
                            'value1' => 1,
                            'value2' => 'test'
                        ]
                    ]
                ]
            ]
        ]);

        $this->assertTrue($result2->xpath('testing/nested/int') !== false);
    }

    /** @test */
    public function it_properly_converts_models()
    {
        $model = new TestModel;
        $model->complexStructure = new Collection;
        $model->complexStructure->put('test1', [
            'test' => [
                'whatever' => 'test',
                'nested' => [
                    'value' => 1,
                    50000,
                    'other' => 'testing123',
                    'array' => [],

                    [
                        'test' => 'testing'
                    ]
                ]
            ]
        ]);

        $model->otherProperty = 'testing';

        /* @var SimpleXMLElement $result */
        $result = Xml::convert($model);
        $this->assertCount(1, $result->xpath('complexStructure/test1'));
    }

    /** @test */
    public function it_allows_the_registration_of_converters()
    {
        app('laravelxml.converters.manager')->register('my.custom.converter', new TestConverter);

        $this->assertArrayHasKey('my.custom.converter', app('laravelxml.converters.manager')->getConverters());
    }

    /** @test */
    public function it_correctly_handles_converter_priorities()
    {
        $this->app['config']->set('laravel-xml.converters.custom.models', [
            'priority' => 1000,
            'value' => CustomConverter::class,
        ]);

        $this->app['config']->set('laravel-xml.converters.custom.objects', [
            'priority' => 1001,
            'value' => CustomConverter::class,
        ]);

        $this->app['config']->set('laravel-xml.converters.custom.collections', [
            'priority' => 1001,
            'value' => CustomConverter::class,
        ]);

        $this->app['config']->set('laravel-xml.converters.custom.Illuminate\Database\Eloquent\Collection', [
            'priority' => 1001,
            'value' => CustomCollectionConverter::class,
        ]);

        $this->app['config']->set('laravel-xml.converters.custom.TestObject', [
            'priority' => 1001,
            'value' => CustomObjectConverter::class,
        ]);

        $this->assertEquals(CustomConverter::class, Xml::getConverterFor(new Collection));
        $this->assertInstanceOf(CustomConverter::class, app(Xml::getConverterFor(new Collection)));

        $this->assertEquals(CustomCollectionConverter::class, Xml::getConverterFor(new \Illuminate\Database\Eloquent\Collection));
        $this->assertInstanceOf(CustomCollectionConverter::class, app(Xml::getConverterFor(new \Illuminate\Database\Eloquent\Collection)));

        $this->assertEquals(CustomConverter::class, Xml::getConverterFor(new TestModel));
        $this->assertInstanceOf(CustomConverter::class, app(Xml::getConverterFor(new TestModel)));

        $this->assertEquals(CustomObjectConverter::class, Xml::getConverterFor(new TestObject));
        $this->assertInstanceOf(CustomObjectConverter::class, app(Xml::getConverterFor(new TestObject)));
    }
}

class TestObject {
    const TEST_CONSTANT = 'testing';

    private $privateProp = 'private';
    public $key = 'value';
    public $array = [
        'testing' => 'test1',
        'const_test' => self::TEST_CONSTANT
    ];
}

class TestModel extends Model {
    protected $attributes = [
        'test1' => 'value',
        'test2' => 5,
    ];
}

class CustomConverter implements Converter {
    /**
     * Convert a value to XML.
     *
     * @param mixed $value
     * @param SimpleXMLElement $element
     * @return SimpleXMLElement
     */
    public function convert($value, SimpleXMLElement $element) : SimpleXMLElement
    {
        $element->addChild('testing', 'testing 123');

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
        return true;
    }
}

class CustomCollectionConverter implements Converter {
    /**
     * Convert a value to XML.
     *
     * @param mixed $value
     * @param SimpleXMLElement $element
     * @return SimpleXMLElement
     */
    public function convert($value, SimpleXMLElement $element) : SimpleXMLElement
    {
        $element->addChild('value1', 'testing');
        $element->addChild('value2', 5);
        $element->addChild('value3', 'other');

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
        return true;
    }
}

class TestConverter implements Converter {
    /**
     * Convert a value to XML.
     *
     * @param mixed $value
     * @param SimpleXMLElement $element
     * @return SimpleXMLElement
     */
    public function convert($value, SimpleXMLElement $element) : SimpleXMLElement
    {
        $element->addChild('value1', 'testing');
        $element->addChild('value2', 5);
        $element->addChild('value3', 2.5);

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
        return true;
    }
}

class CustomObjectConverter implements Converter {
    /**
     * Convert a value to XML.
     *
     * @param mixed $value
     * @param SimpleXMLElement $element
     * @return SimpleXMLElement
     */
    public function convert($value, SimpleXMLElement $element) : SimpleXMLElement
    {
        $element->addChild('value1', 'testing');
        $element->addChild('value2', 5);

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
        return true;
    }
}