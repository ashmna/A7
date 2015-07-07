<?php

namespace A7;


interface ReflectionUtilsInterface {
    /**
     * @param $className
     * @return \ReflectionClass
     */
    function getClassReflection($className);
    /**
     * @param $className
     * @return \ReflectionProperty[]
     */
    function getPropertiesReflection($className);
    /**
     * @param $className
     * @return \ReflectionMethod[]
     */
    function getMethodsReflection($className);
    /**
     * @param $className
     * @param $methodName
     * @return \ReflectionMethod
     */
    function getMethodReflection($className, $methodName);
    /**
     * @param $className
     * @param $methodName
     * @return \ReflectionParameter[]
     */
    function getParametersReflection($className, $methodName);
}