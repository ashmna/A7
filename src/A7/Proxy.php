<?php


namespace A7;


class Proxy {

    /** @var A7Interface */
    protected $a7;
    /** @var PostProcessInterface[] */
    protected $a7PostProcessors = [];
    protected $a7Instance;
    protected $a7ClassName;
    protected $a7BeforeCall = [];
    protected $a7AfterCall  = [];
    protected $a7ExceptionHandling = [];


    public function __construct(A7Interface $a7, $className, $instance = null)
    {
        $this->a7 = $a7;
        $this->a7ClassName = $className;
        $this->a7Instance  = $instance;
    }

    public function __call($methodName, array $arguments = [])
    {
        $this->a7Init();

        if(method_exists($this->a7Instance, $methodName)) {
            $isCallable = true;
            $result = null;
            $transferParams = [];
            $params = [
                'object'     => &$this->a7Instance,
                'className'  => &$this->a7ClassName,
                'methodName' => &$methodName,
                'arguments'  => &$arguments,
                'result'     => &$result,
                'params'     => &$transferParams,
                'isCallable' => &$isCallable,
            ];


            foreach($this->a7BeforeCall as $beforeCall) {
                $this->a7Call($beforeCall, $params);
            }

            try {
                if($isCallable) {
                    $result = call_user_func_array([$this->a7Instance, $methodName], $arguments);
                }
            } catch(\Exception $exception) {
                $params['exception'] = &$exception;
                foreach($this->a7ExceptionHandling as $exceptionHandling) {
                    $this->a7Call($exceptionHandling, $params);
                }
                throw $exception;
            }

            foreach($this->a7AfterCall as $afterCall) {
                $this->a7Call($afterCall, $params);
            }

            return $result;
        } else {
            throw new \RuntimeException($this->a7ClassName.'::'.$methodName.'() method not exists');
        }
    }

    public function __get($name)
    {
        $this->a7Init();
        if(property_exists($this->a7Instance, $name)) {
            return $this->a7Instance->{$name};
        } else {
            throw new \RuntimeException($this->a7ClassName.'::$'.$name.' [get] property not exists');
        }
    }

    public function __set($name, $value)
    {
        $this->a7Init();
        if(property_exists($this->a7Instance, $name)) {
            $this->a7Instance->{$name} = $value;
        } else {
            throw new \RuntimeException($this->a7ClassName.'::$'.$name.' [set] property not exists');
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
    }

    public function a7DoPostProcessors()
    {
        if(isset($this->a7Instance) && !empty($this->a7PostProcessors)) {
            $this->a7Instance = $this->a7->doPostProcessors($this->a7Instance, $this->a7ClassName, $this->a7PostProcessors, $this);
            $this->a7PostProcessors = [];
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

    protected function a7Init()
    {
        if(!isset($this->a7Instance)) $this->a7Instance = $this->a7->initClass($this->a7ClassName, true);
        $this->a7DoPostProcessors();
    }

    protected function a7Call(array $callArr, array $params=[])
    {
        list($afterCallClass, $afterCallMethodName) = $callArr;
        $this->a7->call($afterCallClass, $afterCallMethodName, $params);
    }

}