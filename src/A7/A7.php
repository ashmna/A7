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
        $class = $this->getRealClassName($class);

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
        if ($instanceOnly) {
            $instance = new $class();
        } else {
            $instance = $this->isLazy($class) ? new Proxy($this, $class) : new $class();
        }

        if(!$instanceOnly) {
            $this->doPostProcessors($instance, $class, $this->postProcessors);
        }

        if($instanceOnly && $this->isSingleton($class)) {
            $this->singletonList[$class] = $instance;
        }

        return $instance;
    }

    /**
     * @param $instance
     * @param $class
     * @param PostProcessInterface[] $postProcessors
     */
    public function doPostProcessors($instance, $class, array $postProcessors)
    {
        foreach($postProcessors as $postProcessor) {
            $instance = $postProcessor->postProcessBeforeInitialization($instance, $class);
        }

        $methodsAnnotations = $this->annotationManager->getMethodsAnnotations(get_class($instance));
        foreach($methodsAnnotations as $method => $annotations) {
            if(isset($annotations['Init'])) {
                call_user_func_array([$instance, $method], []);
            }
        }

        foreach($postProcessors as $postProcessor) {
            $instance = $postProcessor->postProcessAfterInitialization($instance, $class);
        }
    }

    protected function getRealClassName($class)
    {
        $arr = explode('\\', trim($class, '\\'));
        if(empty($arr)) {
            $arr = [$class];
        }
        $name = $arr[count($arr)-1];
        $class = $name;
        array_pop($arr);
        if(!empty($arr)) {
            $namespace = implode($arr, '\\');
            $newClassName = $namespace."\\Impl\\".$name."Impl";
            if(class_exists($newClassName)) {
                $class = $newClassName;
            } else {
                $newClassName = $namespace."\\Impl\\".$name;
                if(class_exists($newClassName)) {
                    $class = $newClassName;
                } else {
                    $newClassName = $namespace."\\".$name."Impl";
                    if(class_exists($newClassName)) {
                        $class = $newClassName;
                    }
                }
            }
        }
        return $class;
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