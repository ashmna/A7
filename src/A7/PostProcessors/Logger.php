<?php


namespace A7\PostProcessors;


use A7\AnnotationManagerInterface;
use A7\PostProcessInterface;
use A7\Proxy;

class Logger implements PostProcessInterface {

    private $a7;
    private $parameters;


    function postProcessBeforeInitialization($instance, $className) {
        return $instance;
    }

    function postProcessAfterInitialization($instance, $className) {
        if(!($instance instanceof Proxy)) {
            $instance = new Proxy($this->a7, $className, $instance);
        }

        $instance->a7AddBeforeCall([$this, 'beforeCall']);
        $instance->a7AddAfterCall( [$this, 'afterCall'] );

        return $instance;
    }


    function beforeCall($className, $methodName, $arguments, &$params) {
        $argumentsString = var_export($arguments, true);
        $date = date('Y-m-d H:i:s');
        file_put_contents($this->parameters['path'], "$date: $className->$methodName  arguments: \n$argumentsString\n", FILE_APPEND);
        $params['startTime'] = microtime(true);
    }

    function afterCall($className, $methodName, $result, &$params) {
        $endTime   = microtime(true);
        $startTime = $params['startTime'];
        $executeTime = $endTime - $startTime;
        $date = date('Y-m-d H:i:s');
        $resultString = var_export($result, true);
        file_put_contents($this->parameters['path'], "$date: $className->$methodName  executeTime: $executeTime, result:\n$resultString\n", FILE_APPEND);
    }


}