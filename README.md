## A7
[![Build Status](https://travis-ci.org/ashmna/A7.svg?branch=master)](https://travis-ci.org/ashmna/A7)

A7 is a simple php library, that implements AOP principles with annotations (doc block comments),
that support post processors and natively supported

 - [Getting object into container](#getting-object-into-container)
    - [get by class name](#get-by-class-name)
    - [get by interface name](#get-by-interface-name)
    - [scopes](#scopes)
    - [lazy loading](#lazy-loading)
    - call init method
 - [Dependency Injection](#dependency-injection)
    - [How to enable](#how-to-enable)
    - [Inject static values](#inject-static-values)
    - [Inject object](#inject-object)
 - Transactional
 - Logging

and supported custom post processors

### Getting object into container

##### get by Class name
```php

class SomeClass {
}

$container = new A7();

$someClassObjetc = $container->get("SomeClass");

```

##### get by Interface name
class name and interface name must be to follow 
one of this naming conventions

| Interface | Class             |
|-----------|-------------------|
| SomeName  | SomeNameImpl      |
| SomeName  | Impl\SomeName     |
| SomeName  | Impl\SomeNameImpl |

Best practice using last variant.

```php

namespace Some {
    interface SomeClass {
        function methode();
    }    
}

namespace Some\Impl {
    class SomeClassImpl implements Some\SomeClass {
        public function methode() {
        }
    }
}

$container = new A7();

$someClassObjetc = $container->get("Some\\SomeClass");

```

##### scopes 
Default all class that getting form a7 container is a singleton.
Exist two scoping type *singleton* and *prototype*
 - _singleton_ : When from all php life cycle exist only one instance from that class.
 - _prototype_ : Every time when getting class from container creating new object.
```php

/**
 * @Injectable(scope="singleton")
 */
class IsSingltoneClass {
}

/**
 * @Injectable(scope="prototype")
 */
class IsPrototypeClass {
}

```

##### lazy loading
If lazy loading has turn on for class then class initializing when anybody took this class
call method or get or set any property.
Lazy loading default is turn on for all classes in container. 
You can turn off

```php

/**
 * @Injectable(lazy=false)
 */
class NoteLazyLoadingClass {
}

// you can not write, this is a default values
/**
 * @Injectable(lazy=true, scope="singleton")
 */
class SomeClass {
}

```
### Dependency Injection

##### How to enable
```php
$container = new A7();
$container->enablePostProcessor("DependencyInjection");
```

##### Inject static values
```php

class SomeClass {
    
    /**
     * @Inject("property_name")
     * @var int
     */
    private $somePropety;
    
    public function getSomeProperty() {
        return $this->someProperty;
    }
}

```

```php
$parameters = [
    "property_name" => 2016
];

$container = new A7();
$container->enablePostProcessor("DependencyInjection", $parameters);

$someClassObject = $container->get("SomeClass");

echo $someClassObject->getSomeProperty(); // result is a 2016

```

##### Inject object
```php
class SomeClass {
    
    /**
     * @Inject
     * @var OtherClass
     */
    private $otherClass;
    
    function hello() {
        echo $this->otherClass->sayHello();
    }
}

```

```php
class OtherClass {
    
    function sayHello() {
        return "Hello, World!";
    }
}

```

```php
$container = new A7();
$container->enablePostProcessor("DependencyInjection");

$someObject = $container->get("SomeClass");

$someObject->hello(); // result is a "Hello, World!"

```

