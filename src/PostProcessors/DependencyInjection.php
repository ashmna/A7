<?php


namespace A7\PostProcessors;


use A7\AbstractPostProcess;

class DependencyInjection extends AbstractPostProcess
{

    public function postProcessBeforeInitialization($instance, $className)
    {
        return $this->doInjection($instance, $className);
    }

    public function postProcessAfterInitialization($instance, $className)
    {
        return $instance;
    }

    private function doInjection($instance, $className)
    {
        $propertiesAnnotations = $this->annotationManager->getPropertiesAnnotations($className);

        foreach ($propertiesAnnotations as $propertyName => $annotations) {
            if (isset($annotations['Inject'])) {
                $this->injectProperty($instance, $propertyName, $annotations);
            }
        }
        return $instance;
    }

    private function injectProperty($instance, $propertyName, $annotations)
    {
        /** @var \A7\Annotations\Inject $inject */
        $inject = $annotations['Inject'];
        $reflectionProperty = new \ReflectionProperty($instance, $propertyName);
        $reflectionProperty->setAccessible(true);
        if (isset($annotations['var'])) {
            $inject->setVar($annotations['var']);
        }

        if ($inject->isInjectObject()) {
            $reflectionProperty->setValue($instance, $this->a7->get($inject->getName()));
        } else {
            $name = $inject->getName();
            if (isset($name) && isset($this->parameters[$name])) {
                $reflectionProperty->setValue($instance, $this->parameters[$name]);
            }
        }
    }

}
