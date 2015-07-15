<?php


namespace A7\PostProcessors;


use A7\PostProcessInterface;
use A7\Proxy;

class DependencyInjection implements PostProcessInterface
{
    /** @var \A7\A7Interface  */
    protected $a7;
    /** @var \A7\AnnotationManagerInterface  */
    protected $annotationManager;
    /** @var array */
    protected $parameters;

    function postProcessBeforeInitialization($instance, $className)
    {
        if($instance instanceof Proxy) {
            $instance->a7AddPostProcessor($this);
        } else {
            $instance = $this->doInjection($instance, $className);
        }

        return $instance;
    }

    function postProcessAfterInitialization($instance, $className)
    {
        return $instance;
    }

    protected function doInjection($instance, $className)
    {
        $propertiesAnnotations = $this->annotationManager->getPropertiesAnnotations($className);

        foreach($propertiesAnnotations as $propertyName => $annotations) {
            if(isset($annotations['Inject'])) {
                /** @var \A7\Annotations\Inject $inject */
                $inject = $annotations['Inject'];
                $reflectionProperty = new \ReflectionProperty($instance, $propertyName);
                $reflectionProperty->setAccessible(true);
                if(isset($annotations['var'])) {
                    $inject->setVar($annotations['var']);
                }
                $inject->isInjectObject();
                if($inject->isInjectObject()) {
                    $reflectionProperty->setValue($instance, $this->a7->get($inject->getName()));
                } else {
                    $name = $inject->getName();
                    if(isset($name) && isset($this->parameters[$name])) {
                        $reflectionProperty->setValue($instance, $this->parameters[$name]);
                    }
                }
            }
        }
        return $instance;
    }

}