<?php


namespace A7\PostProcessors;


use A7\PostProcessInterface;
use A7\Proxy;

class Logger implements PostProcessInterface {

    private $a7;
    private $parameters;

    /** @var \LoggerRoot */
    private $log;


    function postProcessBeforeInitialization($instance, $className) {
        if (!isset($this->log)) {
            if(isset($this->parameters['configure'])) {
                \Logger::configure($this->parameters['configure']);
            } else {
                $file = isset($this->parameters['file']) ? $this->parameters['file'] : 'site-%s.html';

                \Logger::configure([
                    'appenders'  => [
                        'default' => [
                            'class'  => 'LoggerAppenderDailyFile',
                            'layout' => [
                                'class' => 'LoggerLayoutHtml',
                            ],
                            'params' => [
                                'datePattern' => 'Y-m-d',
                                'file'        => $file,
                            ],
                        ],
                    ],
                    'rootLogger' => [
                        'appenders' => ['default'],
                    ],
                ]);
            }
            $this->log = \Logger::getRootLogger();
        }
        return $instance;
    }

    function postProcessAfterInitialization($instance, $className) {
        if(!($instance instanceof Proxy)) {
            $instance = new Proxy($this->a7, $className, $instance);
        }
        $ab = true;
        if(isset($this->parameters['classPath'])){
            $ab = strpos($className, $this->parameters['classPath']) === 0;
        }

        if($ab) {
            $instance->a7AddBeforeCall([$this, 'beforeCall']);
            $instance->a7AddAfterCall([$this, 'afterCall']);
        }
        $instance->a7AddExceptionHandling([$this, 'exceptionHandling']);

        return $instance;
    }

    function beforeCall($className, $methodName) {
        $this->log->info("Start $className->$methodName");
    }

    function afterCall($className, $methodName) {
        $this->log->info("End   $className->$methodName");
    }

    function exceptionHandling($className, $methodName, $exception) {
        $this->log->error("$className->$methodName", $exception);
    }


}