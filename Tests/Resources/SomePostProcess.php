<?php

namespace A7\PostProcessors;


use A7\PostProcessInterface;

class SomePostProcess implements PostProcessInterface
{
    private $a7;
    private $annotationManager;
    private $parameters;

    public static $counter = 0;

    public function postProcessBeforeInitialization($instance, $className)
    {
        ++static::$counter;
        return $instance;
    }

    public function postProcessAfterInitialization($instance, $className)
    {
        ++static::$counter;
        return $instance;
    }

}
