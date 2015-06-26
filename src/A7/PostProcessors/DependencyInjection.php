<?php


namespace A7\PostProcessors;


use A7\AnnotationManager;
use A7\PostProcessInterface;

class DependencyInjection implements PostProcessInterface
{

    function postProcessBeforeInitialization($instance, $className) {
        $annotationManager = new AnnotationManager();
        $propertiesAnnotations = $annotationManager->getPropertiesAnnotations($className);
        foreach($propertiesAnnotations as $propertyName => $annotations) {
            var_dump($annotations);
            if(isset($annotations['Inject'])) {
                $reflectionProperty = new \ReflectionProperty($instance, $propertyName);
                $reflectionProperty->setAccessible(true);
                $reflectionProperty->setValue('gugo');
            }
        }
        return $instance;
    }

    function postProcessAfterInitialization($instance, $className) {
        // TODO: Implement postProcessAfterInitialization() method.
        return $instance;
    }

}