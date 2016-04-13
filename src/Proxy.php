<?php


namespace A7;

/**
 * Class Proxy
 *
 * @package A7
 */
class Proxy
{
    /** @var A7Interface */
    private $a7;
    /** @var PostProcessInterface[] */
    private $a7PostProcessors = [];
    /** @var object|null */
    private $a7Instance;
    /** @var string */
    private $a7ClassName;
    /** @var array */
    private $a7BeforeCall = [];
    /** @var array */
    private $a7AfterCall  = [];
    /** @var array */
    private $a7ExceptionHandling = [];
    /** @var bool */
    private $a7IsDoPostProcessors = true;

    public function __construct(A7Interface $a7, $className, $instance = null)
    {
        $this->a7 = $a7;
        $this->a7ClassName = $className;
        $this->a7Instance  = $instance;
    }

    public function __call($methodName, array $arguments = [])
    {
        $this->a7Init();

        if(!method_exists($this->a7Instance, $methodName)) {
            throw new \RuntimeException($this->a7ClassName."::".$methodName."() method not exists");
        }

        return $this->a7CallMethod($methodName, $arguments);
    }

    public function __get($name)
    {
        $this->a7Init();
        if(property_exists($this->a7Instance, $name)
            && ReflectionUtils::getInstance()->getPropertyReflection($this->a7ClassName, $name)->isPublic()) {
            return $this->a7Instance->{$name};
        } else {
            throw new \RuntimeException($this->a7ClassName."::\${$name} [get] property not exists");
        }
    }

    public function __set($name, $value)
    {
        $this->a7Init();
        if(property_exists($this->a7Instance, $name)
            && ReflectionUtils::getInstance()->getPropertyReflection($this->a7ClassName, $name)->isPublic()) {
            $this->a7Instance->{$name} = $value;
        } else {
            throw new \RuntimeException($this->a7ClassName."::\${$name} [set] property not exists");
        }
    }

    public function a7AddBeforeCall($beforeCallFunction)
    {
        if(is_callable($beforeCallFunction)) {
            $this->a7BeforeCall[] = $beforeCallFunction;
        }
    }

    public function a7AddAfterCall($afterCallFunction)
    {
        if(is_callable($afterCallFunction)) {
            $this->a7AfterCall[] = $afterCallFunction;
        }
    }

    public function a7AddExceptionHandling($exceptionHandling)
    {
        if(is_callable($exceptionHandling)) {
            $this->a7ExceptionHandling[] = $exceptionHandling;
        }
    }

    public function a7AddPostProcessor(PostProcessInterface $postProcessor)
    {
        $this->a7PostProcessors[] = $postProcessor;
        $this->a7IsDoPostProcessors = true;
    }

    public function a7DoPostProcessors()
    {
        if(isset($this->a7Instance) && $this->a7IsDoPostProcessors) {
            $this->a7Instance = $this->a7->doPostProcessors($this->a7Instance, $this->a7ClassName, $this->a7PostProcessors, $this);
            $this->a7PostProcessors = [];
            $this->a7IsDoPostProcessors = false;
        }
    }

    public function a7methodExists($methodName)
    {
        $this->a7Init();
        return method_exists($this->a7Instance, $methodName);
    }

    public function a7getClass()
    {
        return $this->a7ClassName;
    }

    private function a7CallMethod($methodName, array $arguments)
    {
        $isCallable = true;
        $isThrowable = true;
        $result = null;
        $params = $this->a7InitParams($isCallable, $isThrowable, $methodName, $arguments, $result);

        $this->a7CallHandles("a7BeforeCall", $params);

        try {
            if($isCallable) {
                $result = call_user_func_array([$this->a7Instance, $methodName], $arguments);
            }
        } catch(\Exception $exception) {
            $params["exception"] = &$exception;

            $this->a7CallHandles("a7ExceptionHandling", $params);

            if($isThrowable) {
                throw $exception;
            }
        }

        $this->a7CallHandles("a7AfterCall", $params);

        return $result;
    }

    private function a7InitParams(&$isCallable, &$isThrowable, &$methodName, array &$arguments, &$result)
    {
        $transferParams = [];
        return [
            "object"      => &$this->a7Instance,
            "className"   => &$this->a7ClassName,
            "methodName"  => &$methodName,
            "arguments"   => &$arguments,
            "result"      => &$result,
            "params"      => &$transferParams,
            "isCallable"  => &$isCallable,
            "isThrowable" => &$isThrowable
        ];
    }

    private function a7CallHandles($name, &$params)
    {
        foreach($this->$name as $item) {
            $this->a7Call($item, $params);
        }
    }

    private function a7Init()
    {
        if(!isset($this->a7Instance)) {
            $this->a7Instance = $this->a7->initClass($this->a7ClassName, true);
        }
        $this->a7DoPostProcessors();
    }

    private function a7Call(array $callArr, array $params=[])
    {
        list($callClass, $callMethodName) = $callArr;
        $this->a7->call($callClass, $callMethodName, $params);
    }

}
