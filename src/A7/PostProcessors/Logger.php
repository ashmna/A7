<?php


namespace A7\PostProcessors;


use A7\AnnotationManagerInterface;
use A7\PostProcessInterface;
use A7\Proxy;

class Logger implements PostProcessInterface {

    protected $a7;
    /** @var AnnotationManagerInterface  */
    protected $annotationManager;

    protected $usedClass;

    function postProcessBeforeInitialization($instance, $className) {
        if(isset($this->usedClass[$className])) return $instance;

        if(!($instance instanceof Proxy)) {
            $instance = new Proxy($this->a7, $className, $instance);
        }

        $this->usedClass[$className] = true;

        $instance->a7AddBeforeCall(function($arguments, $className, $methodName, &$result, &$isCallable) {
            $result = 111111111111;
            $isCallable = false;
        });

        $instance->a7AddAfterCall(function($result) {

        });



        return $instance;
    }

    function postProcessAfterInitialization($instance, $className) {
        return $instance;
    }


}