<?php


namespace A7;

interface PostProcessInterface
{
    /**
     * This method called after members initialization
     */
    function init();

    /**
     * Post process before initialization
     *
     * @param object $instance
     * @param string $className
     * @return object
     */
    function postProcessBeforeInitialization($instance, $className);

    /**
     * Post process after initialization
     *
     * @param object $instance
     * @param string $className
     * @return object
     */
    function postProcessAfterInitialization($instance, $className);

}
