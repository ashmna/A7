<?php


namespace A7;


use A7\Annotations\Injectable;

class A7 implements A7Interface
{

    /** @var PostProcessInterface[] */
    protected $postProcessors = [];
    /** @var array */
    protected $singletonList  = [];
    /** @var PostProcessManagerInterface */
    protected $postProcessManager;
    /** @var AnnotationManagerInterface */
    protected $annotationManager;

    public function __construct()
    {
        $this->annotationManager  = new AnnotationManager();
        $this->postProcessManager = new PostProcessManager($this, $this->annotationManager);
    }

    public function get($class)
    {
        $class = trim($class, '\\');

        if(isset($this->singletonList[$class])) {
            return $this->singletonList[$class];
        }

        if(class_exists($class)) {
            $object = $this->initClass($class);
            if($this->isSingleton($class)) {
                $this->singletonList[$class] = $object;
            }
            return $object;
        } else {
            throw new \Exception($class.' class not found');
        }
    }

    public function call($class, $method, array $arguments)
    {
        // TODO: Implement call() method.
    }

    public function enablePostProcessor($postProcessor, array $parameters = [])
    {
        $this->postProcessors[$postProcessor] = $this->postProcessManager->getPostProcessInstance($postProcessor, $parameters);
    }

    public function disablePostProcessor($postProcessor)
    {
        if(isset($this->postProcessors[$postProcessor])) {
            unset($this->postProcessors[$postProcessor]);
        }
    }

    protected function initClass($class)
    {
        $instance = new $class();
        foreach($this->postProcessors as $postProcessor) {
            $instance = $postProcessor->postProcessBeforeInitialization($instance, $class);
        }
        foreach($this->postProcessors as $postProcessor) {
            $instance = $postProcessor->postProcessAfterInitialization($instance, $class);
        }
        return $instance;
    }

    protected function isSingleton($class)
    {
        $injectable = $this->annotationManager->getClassAnnotation($class, 'Injectable');
        /** @var Injectable $injectable */
        $injectable = !isset($injectable) ? new Injectable() : $injectable;

        return $injectable->isSingleton();
    }

}