<?php


namespace A7;


use A7\Annotations\Injectable;

class A7 implements A7Interface
{

    /** @var PostProcessInterface[] */
    private $postProcessors = [];
    /** @var array */
    private $singletonList  = [];
    /** @var PostProcessManagerInterface */
    private $postProcessManager;
    /** @var AnnotationManagerInterface */
    private $annotationManager;
    /** @var CacheInterface */
    private static $cache;

    /**
     * A7 constructor
     *
     * @param CacheInterface|null $cache
     */
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

    /**
     * Get cache
     *
     * @return CacheInterface
     */
    public static function getCache()
    {
        return self::$cache;
    }

    /**
     * Checks if the class method exists
     *
     * @param object $object
     * @param string $methodName
     * @return bool
     */
    public static function methodExists($object, $methodName)
    {
        if($object instanceof Proxy) {
            return $object->a7methodExists($methodName);
        } else {
            return method_exists($object, $methodName);
        }
    }

    /**
     * @inheritdoc
     */
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
            throw new \Exception($class." class not found");
        }
    }

    /**
     * @inheritdoc
     */
    public function call($object, $method, array $arguments)
    {
        if(!is_object($object)) {
            $object = $this->get($object);
        }
        if ($object instanceof Proxy) {
            $className = $object->a7getClass();
        } else {
            $className = get_class($object);
        }
        $callParams = self::getCallParams($className, $method, $arguments);
        return call_user_func_array([$object, $method], $callParams);
    }

    /**
     * @inheritdoc
     */
    public function enablePostProcessor($postProcessor, array $parameters = [])
    {
        $this->postProcessors[$postProcessor] = $this->postProcessManager->getPostProcessInstance($postProcessor, $parameters);
    }

    /**
     * @inheritdoc
     */
    public function disablePostProcessor($postProcessor)
    {
        if(isset($this->postProcessors[$postProcessor])) {
            unset($this->postProcessors[$postProcessor]);
        }
    }

    /**
     * @inheritdoc
     */
    public function initClass($class, $instanceOnly = false)
    {
        if ($instanceOnly) {
            $instance = new $class();
        } else {
            if ($this->isLazy($class)) {
                $instance = new Proxy($this, $class);
                $instanceOnly = true;
            } else {
                $instance = new $class();
            }
        }

        if(!$instanceOnly) {
            $instance = $this->doPostProcessors($instance, $class, $this->postProcessors);
        }

        return $instance;
    }

    /**
     * @inheritdoc
     *
     * @param object $instance
     * @param string $class
     * @param PostProcessInterface[] $postProcessors
     * @param null|Proxy $proxyInstance
     * @return object
     */
    public function doPostProcessors($instance, $class, array $postProcessors, $proxyInstance = null)
    {
        foreach($postProcessors as $postProcessor) {
            $instance = $postProcessor->postProcessBeforeInitialization($instance, $class);
        }

        $className = get_class($instance);
        $methodsAnnotations = $this->annotationManager->getMethodsAnnotations($className);
        foreach($methodsAnnotations as $method => $annotations) {
            if(isset($annotations["Init"])) {
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

    /**
     * Synchronization called method and given arguments
     *
     * @param string $className
     * @param string $method
     * @param array $arguments
     * @return array
     */
    private static function getCallParams($className, $method, array $arguments)
    {
        $callParams = [];
        foreach(ReflectionUtils::getInstance()->getParametersReflection($className, $method) as $parameter) {
            $parameterName = $parameter->name;
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
        return $callParams;
    }

    /**
     * Get real class name
     *
     * @param string $class
     * @return string
     */
    private function getRealClassName($class)
    {
        $arr = explode("\\", trim($class, "\\"));
        $name = $arr[count($arr)-1];
        $class = implode($arr, "\\");
        if(!class_exists($class)) {
            $class = $name;
            array_pop($arr);
            if(!empty($arr)) {
                $namespace = implode($arr, "\\");
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

    /**
     * Get injectable annotation from class annotation
     *
     * @param string $class
     * @return Injectable
     */
    private function getInjectableAnnotation($class)
    {
        $injectable = $this->annotationManager->getClassAnnotation($class, "Injectable");
        if (!isset($injectable)) {
            $injectable = new Injectable();
        }
        return $injectable;
    }

    /**
     * Checks if the class declared singleton
     *
     * @param string $class
     * @return bool
     */
    private function isSingleton($class)
    {
        return $this->getInjectableAnnotation($class)->isSingleton();
    }

    /**
     * Checks if the class declared lazy
     *
     * @param string $class
     * @return bool
     */
    private function isLazy($class)
    {
        return $this->getInjectableAnnotation($class)->lazy;
    }

}
