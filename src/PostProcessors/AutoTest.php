<?php

namespace A7\PostProcessors;


use A7\AbstractPostProcess;
use A7\Utils\AutoTestGen;
use A7\Utils\CallRecord;

class AutoTest extends AbstractPostProcess
{
    /** @var CallRecord[] */
    private $callStack = [];
    /** @var CallRecord[] */
    private $data = [];
    /** @var string */
    private $recordFile;

    public function init()
    {
        $path = dirname(dirname(__DIR__))."/Tests/Auto/a7-test-record";

        if (isset($this->parameters["recordFile"])) {
            $path = $this->parameters["recordFile"];
        }

        $this->recordFile = $path.".php";
    }

    public function postProcessAfterInitialization($instance, $className)
    {
        $instance = $this->getProxy($instance, $className);

        $instance->a7AddBeforeCall([$this, "before"]);
        $instance->a7AddAfterCall([$this, "after"]);
        $instance->a7AddExceptionHandling([$this, "exceptionHandling"]);

        return $instance;
    }

    public function before($object, $className, $methodName, $arguments)
    {
        $record = new CallRecord();
        $record->init(
            $object,
            $className,
            $methodName,
            $arguments,
            $this->annotationManager->getPropertiesAnnotations($className),
            $this->a7
        );
        $length = count($this->callStack);
        if ($length) {
            $this->callStack[$length - 1]->setChild($record);
        } else {
            $this->callStack[] = $record;
        }
    }

    public function after($result)
    {
        $record = array_pop($this->callStack);
        $record->setResult($result);
        $this->data[] = $record;
    }

    public function exceptionHandling($exception)
    {
        $record = array_pop($this->callStack);
        $record->setException($exception);
        $this->data[] = $record;
    }

    public function __destruct()
    {
        AutoTestGen::saveCallRecords($this->data, $this->recordFile);
    }

}
