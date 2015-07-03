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

    public function initClass($class, $instanceOnly = false)
    {
        $instance = $instanceOnly ? new $class() : $this->isLazy($class) ? new Proxy($this, $class) : new $class();

        foreach($this->postProcessors as $postProcessor) {
            if(self::isCallPostProcessors($postProcessor, $instanceOnly))
                $instance = $postProcessor->postProcessBeforeInitialization($instance, $class);
        }

        foreach($this->postProcessors as $postProcessor) {
            if(self::isCallPostProcessors($postProcessor, $instanceOnly))
                $instance = $postProcessor->postProcessAfterInitialization($instance, $class);
        }

        if($instanceOnly && $this->isSingleton($class)) {
            $this->singletonList[$class] = $instance;
        }
        return $instance;
    }

    protected static function isCallPostProcessors(PostProcessInterface $postProcessor, $instanceOnly) {
        $res = false;
        if(isset($postProcessor->processMode)) {
            $res = $postProcessor->processMode == 0;
            $res = $res ? $res : $instanceOnly ? $postProcessor->processMode == 1 : $postProcessor->processMode == 2;
        }
        return $res;
    }

    protected function isSingleton($class)
    {
        $injectable = $this->annotationManager->getClassAnnotation($class, 'Injectable');
        /** @var Injectable $injectable */
        $injectable = !isset($injectable) ? new Injectable() : $injectable;

        return $injectable->isSingleton();
    }

    protected function isLazy($class)
    {
        $injectable = $this->annotationManager->getClassAnnotation($class, 'Injectable');
        /** @var Injectable $injectable */
        $injectable = !isset($injectable) ? new Injectable() : $injectable;
        return $injectable->lazy;
    }

}