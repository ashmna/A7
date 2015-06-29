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
            if(isset($annotations['A7\Annotations\Inject'])) {
                echo $propertyName."\n";
                $reflectionProperty = new \ReflectionProperty($instance, $propertyName);
                $reflectionProperty->setAccessible(true);
                $reflectionProperty->setValue($instance, 'gugo');
            }
        }
        return $instance;
    }

    function postProcessAfterInitialization($instance, $className) {
        // TODO: Implement postProcessAfterInitialization() method.
        return $instance;
    }

}