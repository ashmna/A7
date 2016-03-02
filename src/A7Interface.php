<?php

namespace A7;


interface A7Interface
{

    /**
     * Get a class instance
     * If given interface name then return a implementation class
     *
     * @param string $class
     * @return Proxy|object
     * @throws \Exception When class not found
     */
    function get($class);

    /**
     * Call a object method with assoc arguments
     *
     * @param object|string $object
     * @param string $method
     * @param array $arguments
     * @return mixed
     * @throws \Exception
     */
    function call($object, $method, array $arguments);

    /**
     * Enable post processor
     *
     * @param string $postProcessor
     * @param array $parameters
     */
    function enablePostProcessor($postProcessor, array $parameters = []);

    /**
     * Disable post processor
     *
     * @param string $postProcessor
     */
    function disablePostProcessor($postProcessor);

    /**
     * Initialization class
     *
     * @param string $class
     * @param bool $instanceOnly
     * @return Proxy|object
     */
    function initClass($class, $instanceOnly = false);

    /**
     * Do post processors
     *
     * @param object $instance
     * @param string $class
     * @param PostProcessInterface[] $postProcessors
     * @param null|Proxy $proxyInstance
     * @return object
     */
    function doPostProcessors($instance, $class, array $postProcessors, $proxyInstance = null);

}
