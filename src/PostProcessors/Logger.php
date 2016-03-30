<?php


namespace A7\PostProcessors;


use A7\AbstractPostProcess;
use A7\Proxy;

/**
 * Class Logger
 * 
 * parameters = [
 *      "namespace" => ""  // if empty logging all
 *      "file"      => "log-%s.html"
 *
 *      "configure" => [
 *          "default" => [
 *              "class" => "LoggerAppenderDailyFile"
 *              "layout" => [
 *                  "class" => "LoggerLayoutHtml"
 *              ]
 *              "params" => [
 *                  "datePattern" => "Y-m-d"
 *                  "file"        => "log-%s.html"
 *              ]
 *          ]
 *          "rootLogger" => [
 *              "appenders" => ["default"],
 *          ]
 *      ]
 * ]
 *
 * @package A7\PostProcessors
 */
class Logger extends AbstractPostProcess
{
    /** @var \LoggerRoot */
    private $log;
    /** @var  string */
    private $namespace = "";

    public function init()
    {
        if (isset($this->parameters["namespace"])) {
            $this->namespace = $this->parameters["namespace"];
        }

        $configure = [
            "appenders"  => [
                "default" => [
                    "class"  => "LoggerAppenderDailyFile",
                    "layout" => [
                        "class" => "LoggerLayoutHtml",
                    ],
                    "params" => [
                        "datePattern" => "Y-m-d",
                        "file"        => "log-%s.html",
                    ],
                ],
            ],
            "rootLogger" => [
                "appenders" => ["default"],
            ],
        ];

        if (isset($this->parameters["file"])) {
            $configure["appenders"]["default"]["params"]["file"] = $this->parameters["file"];
        }

        if (isset($this->parameters["configure"])) {
            $configure = $this->parameters["configure"];
        }

        \Logger::configure($configure);

        $this->log = \Logger::getRootLogger();
    }

    public function postProcessAfterInitialization($instance, $className)
    {
        if (!($instance instanceof Proxy)) {
            $instance = new Proxy($this->a7, $className, $instance);
        }

        if (strpos($className, $this->namespace) === 0) {
            $instance->a7AddBeforeCall([$this, "beforeCall"]);
            $instance->a7AddAfterCall([$this, "afterCall"]);
        }

        $instance->a7AddExceptionHandling([$this, "exceptionHandling"]);

        return $instance;
    }

    public function beforeCall($className, $methodName)
    {
        $this->log->info("Start $className->$methodName");
    }

    public function afterCall($className, $methodName)
    {
        $this->log->info("End   $className->$methodName");
    }

    public function exceptionHandling($className, $methodName, $exception)
    {
        $this->log->error("$className->$methodName", $exception);
    }

}
