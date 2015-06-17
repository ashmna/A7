<?php


namespace A7;


interface AnnotationManagerInterface
{
    function getClassAnnotations($className);
    function getClassAnnotation($className, $annotationName);
    function getPropertiesAnnotations($className);
    function getPropertyAnnotations($className, $propertyName);
    function getPropertyAnnotation($className, $propertyName, $annotationName);
    function getMethodsAnnotations($className);
    function getMethodAnnotations($className, $methodName);
    function getMethodAnnotation($className, $methodName, $annotationName);

    function scan($directory);
}