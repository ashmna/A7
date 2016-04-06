<?php

namespace A7\PostProcessors;


use A7\AbstractPostProcess;
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
        }
        $this->callStack[] = $record;
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
        Gen::saveCallRecord($this->data, $this->recordFile);
    }

}

class AutoTestGen
{
    /**
     * @param CallRecord[] $records
     * @param $path
     */
    public static function saveCallRecord($records, $path)
    {
        $data = [];
        if(file_exists($path)) {
            $data = include $path;
        }
        if(!is_array($data)) {
            $data = [];
        }
        foreach($records as $record) {
            $className = $record->getClassName();
            if(!isset($data[$className])) {
                $data[$className] = [
                    'Unit'        => [],
                    'Integration' => [],
                ];
            }

            $data[$className]['Unit'][$record->getKey()] = [$record->getUseList(), $record->getRecordAsUnitTestFunction()];
            $data[$className]['Integration'][$record->getKey()] = [$record->getUseList(), $record->getRecordAsIntegrationTestFunction()];
        }
        $content = "<?php return ".var_export($data, true)."; ";
        file_put_contents($path, $content);
    }

    /**
     * @param CallRecord[] $data
     * @param string $path
     */
    public static function generateUnitTests(array $data, $path, $namespacePrefix)
    {
        $perClass = [];

        foreach ($data as $item) {
            $c = $item->getClassName();
            if(!isset($perClass[$c])) {
                $perClass[$c] = [
                    "content" => "",
                    "useList" => []
                ];
            }
            $perClass[$c]["content"] .= $item->generateUnitTest();
            $perClass[$c]["useList"] = array_merge($perClass[$c]["useList"], $item->getUseList());
        }
        foreach($perClass as $class => $row) {
            $classPath = $path.str_replace("\\", "/", $class);
            $testFileName = basename($classPath). "Test";
            $classPath = dirname($classPath). DIRECTORY_SEPARATOR . $testFileName.".php";
            if(!file_exists(dirname($classPath))) {
                mkdir(dirname($classPath), 0777, true);
            }
            $namespace = str_replace("/", "\\", dirname(str_replace("\\", "/", $class)));

            $testClassContent = "<?php\n";

            $testClassContent .= "namespace {$namespacePrefix}\\{$namespace};\n";

            $testClassContent .= "\n";
            foreach(array_unique($row["useList"]) as $use) {
                $testClassContent .= "use {$use};\n";
            }
            $testClassContent .= "use A7\\Tests\\Resources\\AbstractUnitTestCase;\n";
            $testClassContent .= "\n\n";

            $testClassContent .= "class {$testFileName} extends AbstractUnitTestCase \n{\n\n";
            $testClassContent .= $row["content"];
            $testClassContent .= "\n}\n";

            file_put_contents($classPath, $testClassContent);
        }
    }

}
