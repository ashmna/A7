<?php

namespace A7;


interface ReflectionUtilsInterface
{

    /**
     * Get class reflection
     *
     * @param string $className
     * @return \ReflectionClass
     */
    function getClassReflection($className);

    /**
     * Get public properties reflection
     *
     * @param string $className
     * @return \ReflectionProperty[]
     */
    function getPropertiesReflection($className);

    /**
     * Get property reflection
     *
     * @param string $className
     * @param string $propertyName
     * @return \ReflectionProperty
     */
    function getPropertyReflection($className, $propertyName);

    /**
     * Get methods reflection
     *
     * @param string $className
     * @return \ReflectionMethod[]
     */
    function getMethodsReflection($className);

    /**
     * Get method reflection
     *
     * @param string $className
     * @param string $methodName
     * @return \ReflectionMethod
     */
    function getMethodReflection($className, $methodName);

    /**
     * Get parameters reflection
     *
     * @param string $className
     * @param string $methodName
     * @return \ReflectionParameter[]
     */
    function getParametersReflection($className, $methodName);

}
