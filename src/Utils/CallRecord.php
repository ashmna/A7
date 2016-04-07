<?php

namespace A7\Utils;


use A7\A7Interface;
use A7\ReflectionUtils;

class CallRecord
{
    /** @var string */
    private $key;
    /** @var string */
    private $className;
    /** @var string */
    private $methodName;
    /** @var mixed[] */
    private $arguments = [];
    /** @var mixed */
    private $result = null;
    /** @var \Throwable|null */
    private $exception = null;
    /** @var CallRecord[] */
    private $children = [];
    /** @var array */
    private $injectClassToPropertyName = [];
    /** @var array */
    private $injectPropertyValues = [];

    /** @var array */
    private $useList = [];
    /** @var string[] */
    private $argumentsNames = [];
    /** @var string */
    private $testObjName;
    /** @var string */
    private $testClassName;

    const T = "    "; // tab = 4 space

    /**
     * CallRecord constructor.
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Init
     *
     * @param object $instance
     * @param string $className
     * @param string $methodName
     * @param mixed[] $arguments
     * @param array $propertiesAnnotations
     * @param A7Interface $a7
     */
    public function init($instance, $className, $methodName, $arguments, $propertiesAnnotations, A7Interface $a7)
    {
        $this->className  = $className;
        $this->methodName = $methodName;
        $this->arguments  = $arguments;

        foreach ($propertiesAnnotations as $propertyName => $annotations) {
            if (!isset($annotations['Inject'])) {
                continue;
            }

            $reflectionProperty = new \ReflectionProperty($instance, $propertyName);
            $inject = $this->getInjectAnnotations($reflectionProperty, $annotations);

            if ($inject->isInjectObject()) {
                $this->injectClassToPropertyName["\\" . $a7->getRealClassName($inject->getName())] = $propertyName;
            } else {
                $this->injectPropertyValues[$propertyName] = $reflectionProperty->getValue($instance);
            }
        }

        $this->key = md5($this->methodName . '-' . serialize($this->arguments));
    }

    /*~ Setters ~*/

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

    /*~ Getters ~*/

    public function getKey()
    {
        return $this->key;
    }

    public function getClassName()
    {
        return $this->className;
    }

    public function getUseList()
    {
        return $this->useList;
    }

    public function getRecordAsUnitTestFunction()
    {
        return $this->toTestFunction();
    }

    public function getRecordAsIntegrationTestFunction()
    {
        $children = $this->children;
        $this->children = [];
        $res = $this->toTestFunction();
        $this->children = $children;
        return $res;
    }

    /*~ Private Methods ~*/

    /**
     * Get inject annotations
     *
     * @param \ReflectionProperty $reflectionProperty
     * @param $annotations
     * @return \A7\Annotations\Inject
     */
    private function getInjectAnnotations(\ReflectionProperty $reflectionProperty, $annotations)
    {
        /** @var \A7\Annotations\Inject $inject */
        $inject = $annotations['Inject'];
        $reflectionProperty->setAccessible(true);
        if (isset($annotations['var'])) {
            $inject->setVar($annotations['var']);
        }
        return $inject;
    }

    private function toTestFunction()
    {
        $this->genTestObjNames();

        $c = $this->generateTest(self::T);
        $c[] = "";

        return implode("\n", $c);
    }

    private function genTestObjNames()
    {
        $this->useList = [];

        $this->testObjName = lcfirst(self::shortClassName($this->className));
        foreach($this->children as $child) {
            $child->testObjName = self::shortClassName($child->className);
        }

        $this->testClassName = $this->className;

        if (strpos($this->className, "\\") !== false) {
            $this->useList[] = $this->className;
            $this->testClassName = basename(str_replace("\\", "/", $this->testClassName));
        }
    }

    private function generateTest($t)
    {
        $methodName = "test" . ucfirst($this->methodName) . "_" . $this->key;
        $c = [];
        $c[] = "public function $methodName()";
        $c[] = "{";
        $c = array_merge($c, $this->generateTestData($t));
        $c = array_merge($c, $this->generateMocks($t));
        $c = array_merge($c, $this->generateExpectations($t));
        $c = array_merge($c, $this->generateRun($t));
        $c[] = "}";

        return self::addTabs($c, $t);
    }

    private function generateTestData($t)
    {
        $c = [];

        $c[] = "// Test Data";
        $c[] = "\${$this->testObjName} = new {$this->testClassName}();";

        foreach($this->injectPropertyValues as $propertyName => $propertyValue) {
            $c[] = "\$this->setMock(\${$this->testObjName}, \"{$propertyName}\", {$this->s($propertyValue, $t)});";
        }

        foreach (ReflectionUtils::getInstance()->getParametersReflection($this->className, $this->methodName) as $i => $parameter) {
            $this->argumentsNames[] = "\${$parameter->name}";
            $c[] = "\${$parameter->name} = {$this->s($this->arguments[$i], $t)};";
        }

        foreach ($this->children as $child) {
            $c[] = "\$mockResult{$child->testObjName} = {$this->s($child->result, $t)};";
        }

        return self::addTabs($c, $t);
    }

    private function generateMocks($t)
    {
        $c = [];

        if (empty($this->children)) {
            return $c;
        }

        $c[] = "// Mocks";
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
            $mockMethods = implode(", ", $mock["methods"]);

            $c[] = "\$mock{$name} = \$this->getMockBuilder({$this->s($mock["class"], $t)})";
            $c[] = "{$t}->setMethods([{$mockMethods}])";
            $c[] = "{$t}->getMock();";
        }

        foreach ($mocks as $name => $mock) {
            if (!isset($this->injectClassToPropertyName[$mock["class"]])) {
                continue;
            }

            $property = $this->injectClassToPropertyName[$mock["class"]];

            $c[] = "\$this->setMock(\${$this->testObjName}, \"{$property}\", \$mock{$name});";
        }

        return self::addTabs($c, $t);
    }

    private function generateExpectations($t)
    {
        $c = [];

        if (empty($this->children)) {
            return $c;
        }

        $c[] = "// Expectations";

        foreach ($this->children as $child) {
            $c[] = "\$mock{$child->testObjName}->expects(\$this->once())";
            $c[] = "{$t}->method(\"{$child->methodName}\")";
            // $c[] = "{$t}->with(\"{$child->methodName}\")";
            $c[] = "{$t}->willReturn(\$mockResult{$child->testObjName});";
        }

        return self::addTabs($c, $t);
    }

    private function generateRun($t)
    {
        $c = [];
        $ars = implode($this->argumentsNames);

        if(empty($this->exception)) {
            $c[] = "// Run Test";
            $c[] = "\$result = \${$this->testObjName}->{$this->methodName}({$ars});";
            $c = array_merge($c, $this->generateAssertions($this->result, "result", ""));
        } else {
            $c[] = "try {";
            $c[] = "{$t}// Run Test";
            $c[] = "{$t}\${$this->testObjName}->{$this->methodName}({$ars});";
            $c[] = "{$t}\$this->fail();";
            $c[] = "} catch(\\Throwable \$exception) {";
            $c = array_merge($c, $this->generateAssertions($this->exception, "exception", $t));
            $c = array_merge($c, $this->generateAssertions($this->exception->getCode(), "exception->getCode()", $t));
            $c = array_merge($c, $this->generateAssertions($this->exception->getMessage(), "exception->getMessage()", $t));
            $c[] = "}";
        }

        return self::addTabs($c, $t);
    }

    private function generateAssertions($data, $name, $t)
    {
        $c = [];

        if(is_array($data)) {
            foreach ($data as $key => $value) {
                $c = array_merge($c, $this->generateAssertions($value, $name . "[\"{$key}\"]", $t));
            }
        }

        $c[] = $this->generateAssertionByType($data, $name, $t);

        return self::addTabs($c, $t);
    }

    private function generateAssertionByType($data, $name, $t)
    {
        switch($data) {
            case is_null($data):
                return "\$this->assertNull(\${$name});";
            case is_object($data):
                return "\$this->assertInstanceOf({$this->s(get_class($data))}, \${$name});";
            case $data === true:
                return "\$this->assertTrue(\${$name});";
            case $data === false:
                return "\$this->assertFalse(\${$name});";
            default:
                return "\$this->assertEquals({$this->s($data, $t)}, \${$name});";
        }
    }

    private static function addTabs($arr, $t)
    {
        foreach($arr as &$row) {
            $row = $t.$row;
        }
        return $arr;
    }

    private function s($data, $tabs = "")
    {
        return str_replace("\n", "{$tabs}\n", var_export($data, true));
    }

    private static function shortClassName($className)
    {
        $className = basename(str_replace("\\", "/", $className));
        if (strpos($className, "Impl") == strlen($className) - 4) {
            $className = substr($className, 0, -4);
        }
        return $className;
    }

}
