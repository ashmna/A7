## A7
[![Build Status](https://travis-ci.org/ashmna/A7.svg?branch=master)](https://travis-ci.org/ashmna/A7)

A7 is a simple php library, that implements AOP principles with annotations (doc block comments),
that support post processors and natively supported

 - dependency injection with scope (singleton and prototype) and lazy loading (default enabled)
 - call init method
 - transactional
 - logging

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
    class SomeNameImpl implements Some\SomeClass {
        public function methode() {
        }
    }
}

$container = new A7();

$someClassObjetc = $container->get("Some\\SomeClass");

```

##### scoping 
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
If lazy loading has turn on for class then class initializing when //TODO 

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

