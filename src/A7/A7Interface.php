<?php

namespace A7;


interface A7Interface
{
    function get($class);
    function call($class, $method, array $arguments);
    function enablePostProcessor($postProcessor, array $parameters = []);
    function disablePostProcessor($postProcessor);
    function initClass($class, $checkLazy = true);
    function doPostProcessors($instance, $class, array $postProcessors, $proxyInstance = null);
}