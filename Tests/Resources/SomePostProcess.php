<?php

namespace A7\PostProcessors;


use A7\AbstractPostProcess;

class SomePostProcess extends AbstractPostProcess
{
    public static $counter = 0;
    public $isInitCalled = false;

    public function init()
    {
        $this->isInitCalled = true;
    }

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
