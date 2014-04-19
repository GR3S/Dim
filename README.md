# Dim – PHP Dependency Injection Manager

Dim is a small, simple and powerful Dependency Injection Container for PHP:
```php
class One { /* ... */ }

class Two { /* ... */ }

class Foo
{
    public function __construct(One $one, Two $two, $three)
    {
        // ...
    }
}

// Instantiates the container
$container = new Container;

// Puts service that creates an instance of "One" to the container
$container->set(new Service('One'));

// Puts instance of "Two" to the container
$container->set(new Two);

// Puts service that creates an instance of "Foo" to the container
$container->set(new Service('Foo'));

// ...

// Instantiates "Foo" passing dependencies "One", "Two" and third argument "3" to the constructor
$three = $container->get('Foo', array('three' => 3));
```
Dim works with PHP 5.3 or later.

## Installation
You may install the Dim with [Composer](https://getcomposer.org).

1. Create a `composer.json` file in your project root
and run the `php composer.phar install` command to install it:
    ```php
    {
        "require": {
            "GR3S/Dim": "1.*"
        }
    }
    ```

2. Add this line to your application’s index.php file:
    ```php
    <?php
    require_once __DIR__ . '/vendor/autoload.php';
    ```

3. Instantiate the `Container` class:
    ```php
    $container = new Container;
    ```

> Alternatively, you can download the [archive](https://github.com/GR3S/Dim/archive/master.zip) and extract it.

## Defining parameters
```php
$container->set('value' , 'name');
// or
$container->set('value' , array('name1', 'name2'));
// or
$container->name = 'value';
// or
$container['name'] = 'value';
```

## Defining services
```php
$container->set(new Service('Foo'));
// or
$container->set(new Service('Foo') , array('name1', 'name2'));
// or
$container->Foo = new Service('Foo');
// or
$container['Foo'] = new Service('Foo');
```
> In first case for names will be used class name and names of extended classes, interfaces and traits.

You can define arguments that will be passed to constructor of service class:
```php
$container->set(
    new Service('Foo', array(new One, new Two, 3))
);
// or
$container->set(
    new Service('Foo', array('one' => new One, 'two' => new Two, 'three' => 3))
);
// or
$container->set(
    new Service('Foo', array(0 => new One, 'two' => new Two, 2 => 3))
);
// ...
```
> Keys should be identical to parameter names or their positions in constructor definition.

## Retrieving data
```php
$foo = $container->get('Foo');
// or
$foo = $container->Foo;
// or
$foo = $container['Foo'];
// or
$foo = $container('Foo');
```
Also you can pass additional arguments to constructor of service class, they overwrite arguments with same keys passed
to `Service` constructor:
```php
$foo = $container->get('Foo', array('three' => 'three'));
// or
$foo = $container('Foo', array('three' => 'three'));
```

## Tests
To run the test suite, you need [PHPUnit](http://phpunit.de):
```bash
$ php composer.phar install --dev
$ vendor/bin/phpunit
```

## License
Dim is licensed under the MIT license.