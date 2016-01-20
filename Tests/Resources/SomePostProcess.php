<?php

namespace A7\PostProcessors;


use A7\PostProcessInterface;

class SomePostProcess implements PostProcessInterface
{

    public function postProcessBeforeInitialization($instance, $className)
    {
        return $instance;
    }

    public function postProcessAfterInitialization($instance, $className)
    {
        return $instance;
    }

}
