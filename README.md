# LaravelXML
> Never manipulate XML again! (Hopefully.)

**_Please note_: LaravelXML may have some issues. Feel free to open pull requests!**

_This package is aimed at the [Laravel Framework](https://laravel.com). A standalone version may be created at some point._

Ever tried to convert various types of values to XML? Perhaps you need to make an "export as XML" feature.
If you have, then you probably know that XML can be an absolute _pain in the butt_.

**LaravelXML** aims to solve this problem. Easily modify _how_ and _when_ it will convert objects.
You can even write your own code to extend it! How cool is that?

If you use XML in your app, LaravelXML is a good choice for you!

## Prerequisites

To use LaravelXML, there are not really any specific prerequisites. Just make sure you have the `xml` PHP extension installed!
You can check to see if you have it by running ```php -i | grep xml``` and seeing if anything comes back.
If nothing shows up after running that command, you need to install the XML extension.

You also must have PHP 5.5.9 or above.
## Installation

Installation is quick and easy. All you have to do is run
```
composer require fetchleo/laravel-xml dev-master
```
_Currently, there is not a stable release. I'm going to work on the tests some more and avoid tagging a release until I'm sure everything works._

Once that is complete, be sure to register the package's service provider in your `config/app.php` by adding this line:

```
FetchLeo\LaravelXml\XmlServiceProvider::class
```

There's nothing else you really need to do! Of course, you can always modify the configuration, which will be covered below.

## Configuration

If you would like to publish the LaravelXML configuration file, simply run

```
php artisan vendor:publish --provider=FetchLeo\LaravelXml\XmlServiceProvider
```

## Usage

Out of the box, LaravelXML should work just fine! No need to set any weird settings right away. Unless, of course, you like doing that sort of thing, in which case, read on.

### Converting Values

LaravelXML comes with a quick way to convert values to XML.
Please note that due to certain limitations, there are certain types of values that can **NOT** be converted by themselves.
However, they can be inside of other values that CAN be converted.

These non-self-convertable types are:
- Integers (or doubles/floats)
- Strings

That means that unless they are inside a different value, one that CAN be converted (an array, etc), they can not be converted.
So, calling `Xml::convert(1)` would throw an exception.
**This will be changed soon! You will be able to specify your _OWN_ converters for these types. Default ones may be provided as well.**

Anyway, moving on from that...

To convert a value to XML, simply call `Xml::convert($value)`, being sure to import `FetchLeo\LaravelXml\Facades\Xml`.
Under the hood, LaravelXML is attempting to locate an appropriate converter for the value. If no suitable one is found, an exception will be thrown.
You can specify and create your own converters; read on to find out how!

### Custom Converters

In addition to the converters that come out of the box, you can create your own converters.

First, you should create a new class that implements the `FetchLeo\LaravelXml\Contracts\Converter` interface.

It should look something like this:

```php
<?php

namespace My\App\Namespace;

use FetchLeo\LaravelXml\Contracts\Converter;    
use SimpleXMLElement;
use FetchLeo\LaravelXml\Exceptions\CantConvertValueException;
    
class MyCustomConverter implements Converter 
{
    /**
     * Convert a value to XML.
     *
     * @param Model $value
     * @param SimpleXMLElement $element
     * @return SimpleXMLElement
     * @throws CantConvertValueException
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
```

Then, you can configure the new converter in your `config/laravel-xml.php` file.
_Please note: If you don't have this file, run_ ```php artisan vendor:publish --provider=FetchLeo\LaravelXml\XmlServiceProvider```.

What you'll want to do is go to the `custom` array, inside `converters`.
Then, you want to add a new entry. The key should be something like `models` or `objects` or `arrays` or `collections` or whatever!
**Note: Currently, a converter can only handle one type of value (whatever you choose, except for strings and numbers.) This may change in the future.**

The value should be the name of your new class. If you used Laravel's IOC container to register an alias, you can use the alias instead, if you want.
For example:

```
'custom' => [
    // ...
    'models' => 'My\App\Namespace\MyCustomConverter'
]
```

In addition to specifying one converter for all values of the same generic type, you can have certain converters for certain classes!
So, if you only wanted values of the type `My\App\Namespace\CustomThing` to have the converter applied, you could have:

```
'custom' => [
    // ...
    'My\App\Namespace\CustomThing' => 'My\App\Namespace\MyCustomConverter'
]
```

This will tell LaravelXML: "Hey, only use this converter if I give you a value of this type!"
LaravelXML is smart enough to fallback to the default converters if it can't find an appropriate custom one.

### Registering your Converter
If you want to use a custom name for your converter without manually registering it in the IOC container, you can do something like this:
```
app('laravelxml.converters.manager')->register('name-here', $converterInstance)
```

LaravelXML will do all of the work for you; it takes care of binding everything into the container, so you don't have to worry about that stuff.

### Intelligent Keys
LaravelXML is intelligent enough to be able to dynamically assume what keys should be used if there isn't an appropriate one.
The reason that this feature is included is because XML doesn't accept numbers as keys. And if you're working with arrays of objects, you're definitely going to have numbers as keys.
Instead of throwing a fit because it can't use a numeric key, LaravelXML will try to intelligently determine a key to use. It works 99.99% of the time. (Possibly 100%)

So, say you had an array (or collection) with a key called, let's say, `projects`. And inside of that array, you had many elements that looked something like this:
```
[
    'id' => 42,
    'name' => 'Testing event',
    'description' => '...',
    'user' => [
        'id' => 56,
        'name' => 'John Doe',
        'email' => 'doe@some-email-provider.com'
    ]
]
```

Now, this array has no top-level keys _at all._ It's just a bunch of arrays (inside an array!) Now, normally, if you even tried to convert this to XML, your browser would inform you that there's a syntax error.
Why? Because when there are no keys set, PHP uses numbers as the keys. Remember, numbers aren't accepted as XML tag names!

So, instead of freaking out, LaravelXML has a "provided key" system. When one of the base converters detects an array, it passes a third parameter to the conversion function.
The value of the third parameter will be used by that converter as the key. If that isn't set for some reason, it'll use the value's type as a "fallback key".

**Note: This functionality is not included by default! It is your responsibility to implement it!**
**To get a feeling for how it works, take a look at any of the following files**: `src/FetchLeo/LaravelXml/Converters/ArrayConverter`, `src/FetchLeo/LaravelXml/Converters/ModelConverter`, `src/FetchLeo/LaravelXml/Converters/ObjectConverter`

### List of Converters
Here is a table of the different converters that come by default, and what value types they can handle.

| Name        | Class           | Handles Type  |
| ------------- |:-------------:| -----:|
| laravelxml.converters.model      | FetchLeo\LaravelXml\Converters\ModelConverter | Models |
| laravelxml.converters.array      | FetchLeo\LaravelXml\Converters\ArrayConverter | Arrays |
| laravelxml.converters.collection      | FetchLeo\LaravelXml\Converters\CollectionConverter | Collections |
| laravelxml.converters.object      | FetchLeo\LaravelXml\Converters\ObjectConverter | Objects |

## Conclusion
I hope LaravelXML works well for your project! I've spent quite a bit of time making sure that it does what it should.
Of course, if you find any bugs, _open an issue_ and maybe even create a pull request if you have a fix!

New features will be coming soon!
## To-Dos
- [ ] Add partial support for converting non-object types to XML (strings, numbers, etc)
     _This will probably be done by adding a single child element to the XML element, where the key is the value type and the value is... the value._
- [ ] Add new settings to the configuration file
- [ ] Make facade binding optional.
- [ ] Expose more private API methods (for some of them, there's no reason for them to be private)

**P.S.** All of the tests pass!