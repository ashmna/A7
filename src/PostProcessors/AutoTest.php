<?php

namespace A7\PostProcessors;


use A7\A7Interface;
use A7\AbstractPostProcess;
use A7\ReflectionUtils;

class AutoTest extends AbstractPostProcess
{
    /** @var CallRecord[] */
    private $callStack = [];
    /** @var CallRecord[] */
    private $data = [];
    private $namespacePrefix;
    private $path;

    public function init()
    {
        $this->namespacePrefix = "A7\\Tests\\Auto";
        $this->path = dirname(dirname(__DIR__))."/Tests/Auto/";
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
        $record = new CallRecord(
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
        Gen::generateUnitTests($this->data, $this->path, $this->namespacePrefix);
    }

}


class CallRecord
{
    private $key;
    private $className;
    private $methodName;
    private $arguments = [];
    private $result = null;
    private $exception = null;
    /** @var CallRecord[] */
    private $children = [];
    private $injectClassToPropertyName = [];
    private $injectPropertyValues = [];

    /** @var  A7Interface */
    private $useList = [];
    private $argumentsNames = [];
    private $testObjName;

    public function __construct($instance, $className, $methodName, $arguments, $propertiesAnnotations, A7Interface $a7)
    {
        $this->className = $className;
        $this->methodName = $methodName;
        $this->arguments = $arguments;

        foreach ($propertiesAnnotations as $propertyName => $annotations) {
            if (isset($annotations['Inject'])) {
                /** @var \A7\Annotations\Inject $inject */
                $inject = $annotations['Inject'];
                $reflectionProperty = new \ReflectionProperty($instance, $propertyName);
                $reflectionProperty->setAccessible(true);
                if (isset($annotations['var'])) {
                    $inject->setVar($annotations['var']);
                }
                if ($inject->isInjectObject()) {
                    $this->injectClassToPropertyName["\\" . $a7->getRealClassName($inject->getName())] = $propertyName;
                } else {
                    $this->injectPropertyValues[$propertyName] = $reflectionProperty->getValue($instance);
                }
            }
        }

        $this->key = md5($this->methodName . '-' . serialize($this->arguments));
    }

    public function setResult($result)
    {
        $this->result = $result;
    }

    public function setException($exception)
    {
        $this->exception = $exception;
    }

    public function setChild(CallRecord $child)
    {
        $this->children[] = $child;
    }

    public function getClassName()
    {
        return $this->className;
    }

    public function getUseList()
    {
        return $this->useList;
    }

    public function generateUnitTest()
    {
        $testMethodName = "test" . ucfirst($this->methodName) . "Auto_" . $this->key;
        $this->testObjName = lcfirst(basename(str_replace("\\", "/", $this->className)));
        if (strpos($this->testObjName, "Impl") == strlen($this->testObjName) - 4) {
            $this->testObjName = substr($this->testObjName, 0, -4);
        }
        foreach ($this->children as $child) {
            $child->testObjName = basename(str_replace("\\", "/", $child->className));
            if (strpos($child->testObjName, "Impl") == strlen($child->testObjName) - 4) {
                $child->testObjName = substr($child->testObjName, 0, -4);
            }
        }

        $content = "\tpublic function {$testMethodName}()\n\t{\n";
        $content .= "\t\t// Test Data\n";
        $content .= $this->generateTestData();
        if (!empty($this->children)) {
            $content .= "\t\t// Mocks\n";
            $content .= $this->generateMocks();
            $content .= "\t\t// Expectations\n";
            $content .= $this->generateExpectations();
        }
        if(empty($this->exception)) {
            $content .= "\t\t// Run Test\n";
            $content .= $this->generateRunTest();
            $content .= self::generateAssertions($this->result, "result");
        } else {
            $content .= "\t\ttry {\n";
            $content .= "\t\t\t// Run Test\n";
            $content .= $this->generateRunTest("\t\t\t");
            $content .= "\t\t\t\$this->fail();\n";
            $content .= "\t\t} catch(\\Throwable \$ex) {\n";
            $content .= self::generateAssertions($this->exception, "ex", "\t\t\t");
            $content .= "\t\t}\n";
        }
        $content .= "\t}\n\n";
        return $content;
    }

    private function generateTestData()
    {
        $className = $this->className;
        if (strpos($this->className, "\\") !== false) {
            $this->useList[] = $this->className;
            $className = basename(str_replace("\\", "/", $this->className));
        }
        $content = "\t\t\${$this->testObjName} = new {$className}();\n";

        foreach($this->injectPropertyValues as $propertyName => $propertyValue) {
            $content .= "\t\t\$this->setMock(\${$this->testObjName}, \"{$propertyName}\", ".var_export($propertyValue, true).");\n";
        }

        foreach (ReflectionUtils::getInstance()->getParametersReflection($this->className, $this->methodName) as $i => $parameter) {
            $this->argumentsNames[] = "\$" . $parameter->name;
            $content .= "\t\t\${$parameter->name} = ";
            $content .= str_replace("\n", "\n\t\t", var_export($this->arguments[$i], true));
            $content .= ";\n";
        }

        foreach ($this->children as $child) {
            $content .= "\t\t\$mockResult{$child->testObjName} = ";
            $content .= str_replace("\n", "\n\t\t", var_export($child->result, true));
            $content .= ";\n";
        }

        return $content;
    }

    private function generateMocks()
    {
        $content = "";
        $mocks = [];

        foreach ($this->children as $child) {
            if (!isset($mocks[$child->testObjName])) {
                $mocks[$child->testObjName] = array(
                    "class"   => "\\" . $child->className,
                    "methods" => [],
                );
            }
            $mocks[$child->testObjName]['methods'][] = "\"{$child->methodName}\"";
        }

        foreach ($mocks as $name => $mock) {
            $content .= "\t\t\$mock{$name} = \$this->getMockBuilder(" . var_export($mock["class"], true) . ")\n";
            $content .= "\t\t\t->setMethods([" . implode(", ", $mock["methods"]) . "])\n";
            $content .= "\t\t\t->getMock();\n";
        }

        foreach ($mocks as $name => $mock) {
            if (!isset($this->injectClassToPropertyName[$mock["class"]])) {
                continue;
            }
            $property = $this->injectClassToPropertyName[$mock["class"]];
            $content .= "\t\t\$this->setMock(\${$this->testObjName}, \"{$property}\", \$mock{$name});\n";
        }

        return $content;
    }

    private function generateExpectations()
    {
        $content = "";
        foreach ($this->children as $child) {
            $content .= "\t\t\$mock{$child->testObjName}->expects(\$this->once())\n";
            $content .= "\t\t\t->method(\"{$child->methodName}\")\n";
            //$content .= "->with(\"{$child->methodName}\")\n";
            $content .= "\t\t\t->willReturn(\$mockResult{$child->testObjName});\n";
        }

        return $content;
    }

    private function generateRunTest($tap = "\t\t")
    {
        $s = "";
        if(empty($this->exception)) {
            $s = "\$result = ";
        }
        $content = "{$tap}{$s}\${$this->testObjName}->{$this->methodName}(";
        $content .= implode(", ", $this->argumentsNames);
        $content .= ");\n";
        return $content;
    }

    private static function generateAssertions($data, $name, $tap = "\t\t")
    {
        $content = "";
        $doEquals = true;
        if (is_null($data)) {
            $content .= "{$tap}\$this->assertNull(\${$name});\n";
            $doEquals = false;
        } elseif (is_object($data)) {
            $content .= "{$tap}\$this->assertInstanceOf(". var_export("\\".get_class($data), true) . ", \${$name});\n";
            $doEquals = false;
        } elseif (is_array($data)) {
            foreach ($data as $value) {
                $doEquals &= !is_object($value);
                if (!$doEquals) {
                    break;
                }
            }
            if (!$doEquals) {
                foreach ($data as $key => $value) {
                    $content .= self::generateAssertions($value, $name . "[\"{$key}\"]");
                }
            }
        }

        if ($doEquals) {
            $content .= "{$tap}\$this->assertEquals(" . var_export($data, true) . ", \${$name});\n";
        }
        return $content;
    }

}

class Gen
{
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
