<?php


namespace A7;


class A7 implements A7Interface
{

    /** @var PostProcessInterface[] */
    protected $postProcessors = [];
    /** @var array */
    protected $singletonList  = [];
    /** @var PostProcessManagerInterface */
    protected $postProcessManager;

    public function __construct() {
        $this->postProcessManager = new PostProcessManager();
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
        $this->postProcessors[$postProcessor] = $this->postProcessManager->getPostProcessInstance($postProcessor);
    }

    public function disablePostProcessor($postProcessor)
    {
        if(isset($this->postProcessors[$postProcessor])) {
            unset($this->postProcessors[$postProcessor]);
        }
    }

    protected function initClass($class) {
        $instance = new \stdClass();
        foreach($this->postProcessors as $postProcessor) {
            $instance = $postProcessor->postProcessAfterInitialization($instance, $class);
        }
        foreach($this->postProcessors as $postProcessor) {
            $instance = $postProcessor->postProcessBeforeInitialization($instance, $class);
        }
        return $instance;
    }

}