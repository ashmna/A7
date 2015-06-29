<?php


namespace A7\PostProcessors;


use A7\PostProcessInterface;

class DependencyInjection implements PostProcessInterface
{
    /** @var \A7\A7Interface  */
    protected $a7;
    /** @var \A7\AnnotationManagerInterface  */
    protected $annotationManager;
    /** @var array */
    protected $parameters;

    function postProcessBeforeInitialization($instance, $className) {
        $propertiesAnnotations = $this->annotationManager->getPropertiesAnnotations($className);
        foreach($propertiesAnnotations as $propertyName => $annotations) {
            if(isset($annotations['Inject'])) {
                /** @var \A7\Annotations\Inject $inject */
                $inject = $annotations['Inject'];
                $reflectionProperty = new \ReflectionProperty($instance, $propertyName);
                $reflectionProperty->setAccessible(true);
                if($inject->isInjectObject()) {
                    $reflectionProperty->setValue($instance, $this->a7->get($inject->getName()));
                } else {
                    $name = $inject->getName();
                    if(isset($parameters[$name])) {
                        $reflectionProperty->setValue($instance, $parameters[$name]);
                    }
                }
            }
        }
        return $instance;
    }

    function postProcessAfterInitialization($instance, $className) {
        return $instance;
    }

}