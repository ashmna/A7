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
            if(isset($parameters['configure'])) {
                \Logger::configure($parameters['configure']);
            } else {
                \Logger::configure([
                    'appenders'  => [
                        'default' => [
                            'class'  => 'LoggerAppenderDailyFile',
                            'layout' => [
                                'class' => 'LoggerLayoutHtml',
                            ],
                            'params' => [
                                'datePattern' => 'Y-m-d',
                                'file'        => 'file-%s.html',
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

        $instance->a7AddBeforeCall([$this, 'beforeCall']);
        $instance->a7AddAfterCall ([$this, 'afterCall' ]);

        return $instance;
    }


    function beforeCall($className, $methodName, $arguments, &$params) {
        $argumentsString = var_export($arguments, true);
        $this->log->info("$className->$methodName();  arguments: $argumentsString");
        $params['startTime'] = microtime(true);
    }

    function afterCall($className, $methodName) {
        $this->log->info("END $className->$methodName()");
    }


}