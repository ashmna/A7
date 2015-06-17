<?php


namespace A7;


class A7 implements A7Interface {

    protected $postProcessors = [];
    protected $singletonList  = [];
    protected $postProcessManager;

    public function __construct() {
        $this->postProcessManager = new PostProcessorManger();
    }

    public function get($class)
    {
        $class = trim($class, '\\');

        if(isset($this->singletonList[$class])) {
            return $this->singletonList[$class];
        }

        if(class_exists($class)) {
            $object = $this->initClass($class);
            $this->singletonList[$class] = $object;
            return $object;
        } else {
            throw new \Exception($class.' class not found');
        }
    }

    public function call($class, $method, array $arguments)
    {
        // TODO: Implement call() method.
    }

    public function enablePostProcessor($postProcessor)
    {
        $this->postProcessors[$postProcessor] = $this->postProcessManager->getPostProcessorInstance($postProcessor);
    }

    public function disablePostProcessor($postProcessor)
    {
        if(isset($this->postProcessors[$postProcessor])) {
            unset($this->postProcessors[$postProcessor]);
        }
    }

    protected function initClass($class) {

    }

}