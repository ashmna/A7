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
    /** @var CacheInterface */
    protected static $cache;

    /**
     * @return CacheInterface
     */
    public static function getCache()
    {
        return self::$cache;
    }


    public function __construct(CacheInterface $cache = null)
    {
        if(isset($cache)) {
            self::$cache = $cache;
        } else {
            self::$cache = new ArrayCache();
        }
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
        if(!is_object($class)) {
            $class = $this->get($class);
        }
        $callParams = [];
        $className = $class instanceof Proxy ? $class->a7getClass() : get_class($class);
        foreach(ReflectionUtils::getInstance()->getParametersReflection($className, $method) as $parameter) {
            $parameterName = $parameter->getName();
            if(array_key_exists($parameterName, $arguments)) {
                if($parameter->isArray()) {
                    $arguments[$parameterName] = (array)$arguments[$parameterName];
                }
                $callParams[] =& $arguments[$parameterName];
            } else {
                $val = null;
                if($parameter->isDefaultValueAvailable()) {
                    $val = $parameter->getDefaultValue();
                } elseif($parameter->isArray()) {
                    $val = [];
                }
                $callParams[] = $val;
            }
        }
        return call_user_func_array([$class, $method], $callParams);
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
            $instance = $this->doPostProcessors($instance, $class, $this->postProcessors);
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
     * @param null|Proxy $proxyInstance
     * @return mixed
     */
    public function doPostProcessors($instance, $class, array $postProcessors, $proxyInstance = null)
    {
        foreach($postProcessors as $postProcessor) {
            $instance = $postProcessor->postProcessBeforeInitialization($instance, $class);
        }

        $className = get_class($instance);
        $methodsAnnotations = $this->annotationManager->getMethodsAnnotations($className);
        foreach($methodsAnnotations as $method => $annotations) {
            if(isset($annotations['Init'])) {
                $methodReflection = ReflectionUtils::getInstance()->getMethodReflection($className, $method);
                $methodReflection->setAccessible(true);
                $methodReflection->invoke($instance);
            }
        }

        foreach($postProcessors as $postProcessor) {
            if(isset($proxyInstance)) {
                $postProcessor->postProcessAfterInitialization($proxyInstance, $class);
            } else {
                $instance = $postProcessor->postProcessAfterInitialization($instance, $class);
            }
        }

        return $instance;
    }

    public static function methodExists($class, $methodName) {
        if($class instanceof Proxy) {
            return $class->a7methodExists($methodName);
        } else {
            return method_exists($class, $methodName);
        }
    }

    protected function getRealClassName($class)
    {
        $arr = explode('\\', trim($class, '\\'));
        $name = $arr[count($arr)-1];
        $class = implode($arr, '\\');
        if(!class_exists($class)) {
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