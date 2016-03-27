<?php

namespace A7\Tests\Unit;

require_once dirname(__DIR__)."/Resources/SomePostProcess.php";


use A7\PostProcessors\SomePostProcess;
use A7\Proxy;
use A7\Tests\Resources\AbstractUnitTestCase;
use A7\Tests\Resources\SomeClass;

class ProxyTest extends AbstractUnitTestCase
{

    // Test class
    /** @var Proxy|SomeClass */
    private $proxy;
    // Mocks
    /** @var \PHPUnit_Framework_MockObject_MockObject|\A7\A7Interface */
    private $a7;
    private $someClassName = "A7\\Tests\\Resources\\SomeClass";

    public function setUp()
    {
        $this->a7 = $this->getMockBuilder("\\A7\\A7")->getMock();
        /** @noinspection PhpParamsInspection */
        $this->proxy = new Proxy($this->a7, $this->someClassName);
    }

    public function testA7InitWithoutInstance()
    {
        // Test Data
        $instance = new SomeClass();
        // Expectations
        $this->a7
            ->expects($this->once())
            ->method("initClass")
            ->with($this->someClassName, true)
            ->willReturn($instance);
        // Run Test
        $this->invokeMethod($this->proxy, "a7Init", []);
    }

    public function testA7methodExists()
    {
        // Test Data
        /** @noinspection PhpParamsInspection */
        $this->proxy = new Proxy($this->a7, $this->someClassName, new SomeClass());
        // Run Test
        $res = $this->proxy->a7methodExists("someMethod");
        $this->assertTrue($res);
        $res = $this->proxy->a7methodExists("unknownMethod");
        $this->assertFalse($res);
    }

    public function testA7getClass()
    {
        // Run Test
        $className = $this->proxy->a7getClass();
        $this->assertEquals($this->someClassName, $className);
    }

    public function testA7DoPostProcessors()
    {
        // Test Data
        $instance = new SomeClass();
        $this->proxy = new Proxy($this->a7, $this->someClassName, $instance);
        $postProcessor = new SomePostProcess();
        $this->proxy->a7AddPostProcessor($postProcessor);
        // Expectations
        $this->a7
            ->expects($this->once())
            ->method("doPostProcessors")
            ->with($instance, $this->someClassName, [$postProcessor], $this->proxy)
            ->willReturn($instance);
        // Run Test
        $this->proxy->a7DoPostProcessors();
    }

    public function testA7Call()
    {
        // Expectations
        $this->a7
            ->expects($this->once())
            ->method("call")
            ->with("class", "method", ["arg1", 2, []]);
        // Run Test
        $this->invokeMethod($this->proxy, "a7Call", [["class", "method"], ["arg1", 2, []]]);
    }

    public function testGetMethod()
    {
        // Test Data
        $instance = new SomeClass();
        $instance->someMethod(10, 20, 30);
        $this->proxy = new Proxy($this->a7, $this->someClassName, $instance);
        // Run Test
        $this->assertEquals(10, $this->proxy->a);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetMethodWithException()
    {
        // Test Data
        $this->proxy = new Proxy($this->a7, $this->someClassName, new SomeClass());
        // Run Test
        try {
            $this->proxy->d;
            $this->fail("Private property is visible.");
        } catch (\RuntimeException $e) {
            $this->assertInstanceOf("\\RuntimeException", $e);
            $this->assertEquals("A7\\Tests\\Resources\\SomeClass::\$d [get] property not exists", $e->getMessage());
            throw $e;
        }
    }

    public function testSetMethod()
    {
        // Test Data
        $this->proxy = new Proxy($this->a7, $this->someClassName, new SomeClass());
        // Run Test
        $this->proxy->a = 2345;
        $this->assertEquals(2345, $this->proxy->a);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testSetMethodWithException()
    {
        // Test Data
        $this->proxy = new Proxy($this->a7, $this->someClassName, new SomeClass());
        // Run Test
        try {
            $this->proxy->d = 6789;
            $this->fail("Private property is visible.");
        } catch (\RuntimeException $e) {
            $this->assertInstanceOf("\\RuntimeException", $e);
            $this->assertEquals("A7\\Tests\\Resources\\SomeClass::\$d [set] property not exists", $e->getMessage());
            throw $e;
        }
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testGetMethodWithExceptionWithProtectedProperty()
    {
        // Test Data
        $this->proxy = new Proxy($this->a7, $this->someClassName, new SomeClass());
        // Run Test
        try {
            $this->proxy->c;
            $this->fail("Protected property is visible.");
        } catch (\RuntimeException $e) {
            $this->assertInstanceOf("\\RuntimeException", $e);
            $this->assertEquals("A7\\Tests\\Resources\\SomeClass::\$c [get] property not exists", $e->getMessage());
            throw $e;
        }
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testSetMethodWithExceptionWithProtectedProperty()
    {
        // Test Data
        $this->proxy = new Proxy($this->a7, $this->someClassName, new SomeClass());
        // Run Test
        try {
            $this->proxy->c = 1;
            $this->fail("Protected property is visible.");
        } catch (\RuntimeException $e) {
            $this->assertInstanceOf("\\RuntimeException", $e);
            $this->assertEquals("A7\\Tests\\Resources\\SomeClass::\$c [set] property not exists", $e->getMessage());
            throw $e;
        }
    }

    public function testA7AddBeforeCall()
    {
        // Test Data
        $handelFunction = [$this, "emptyHandelFunction"];
        // Run Test
        $this->proxy->a7AddBeforeCall([]);
        $this->proxy->a7AddBeforeCall($handelFunction);
        $beforeCallList = $this->getMember($this->proxy, "a7BeforeCall");
        $this->assertEquals([$handelFunction], $beforeCallList);
    }

    public function testA7AddAfterCall()
    {
        // Test Data
        $handelFunction = [$this, "emptyHandelFunction"];
        // Run Test
        $this->proxy->a7AddAfterCall([]);
        $this->proxy->a7AddAfterCall($handelFunction);
        $afterCallList = $this->getMember($this->proxy, "a7AfterCall");
        $this->assertEquals([$handelFunction], $afterCallList);
    }

    public function testA7AddExceptionHandling()
    {
        // Test Data
        $handelFunction = [$this, "emptyHandelFunction"];
        // Run Test
        $this->proxy->a7AddExceptionHandling([]);
        $this->proxy->a7AddExceptionHandling($handelFunction);
        $exceptionHandlingList = $this->getMember($this->proxy, "a7ExceptionHandling");
        $this->assertEquals([$handelFunction], $exceptionHandlingList);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testCallUnknownMethod()
    {
        try {
            /** @noinspection PhpUndefinedMethodInspection */
            $this->proxy->unknownMethod();
            $this->fail("Called not existing method.");
        } catch (\RuntimeException $e) {
            $this->assertInstanceOf("\\RuntimeException", $e);
            $this->assertEquals("A7\\Tests\\Resources\\SomeClass::unknownMethod() method not exists", $e->getMessage());
            throw $e;
        }
    }

    public function testCallMethodWithBeforeHandelFunction()
    {
        // Test Data
        $instance = new SomeClass();
        $this->proxy->a7AddBeforeCall([$this, "emptyHandelFunction"]);
        // Expectations
        $this->a7
            ->expects($this->once())
            ->method("initClass")
            ->with($this->someClassName, true)
            ->willReturn($instance);
        $this->a7
            ->expects($this->once())
            ->method("call");
        // Run Test
        $result = $this->proxy->someMethod(1, 2, 3);
        $this->assertEquals(6, $result);
    }

    public function testCallMethodWithAfterHandelFunction()
    {
        // Test Data
        $instance = new SomeClass();
        $this->proxy->a7AddAfterCall([$this, "emptyHandelFunction"]);
        // Expectations
        $this->a7
            ->expects($this->once())
            ->method("initClass")
            ->with($this->someClassName, true)
            ->willReturn($instance);
        $this->a7
            ->expects($this->once())
            ->method("call");
        // Run Test
        $result = $this->proxy->someMethod(1, 2, 3);
        $this->assertEquals(6, $result);
    }

    /**
     * @expectedException \Exception
     */
    public function testCallMethodWithThrow()
    {
        // Test Data
        $instance = new SomeClass();
        $this->proxy->a7AddExceptionHandling([$this, "emptyHandelFunction"]);
        // Expectations
        $this->a7
            ->expects($this->once())
            ->method("initClass")
            ->with($this->someClassName, true)
            ->willReturn($instance);
        $this->a7
            ->expects($this->once())
            ->method("call");
        // Run Test
        try {
            $this->proxy->throwMethod();
        } catch (\Exception $e) {
            $this->assertInstanceOf("\\Exception", $e);
            $this->assertEquals("Test Exception", $e->getMessage());
            throw $e;
        }
    }

    // Util Methods

    public function emptyHandelFunction()
    {
    }

    public function beforeCallHandelFunction($object, $className, $methodName, $arguments, $result, $params, $isCallable)
    {
        // $this->assertInstanceOf($this->someClassName, $object);
        // $this->assertEquals($this->someClassName, $className);
        // $this->assertEquals("someMethod", $methodName);
        // $this->assertEquals([1, 2, 3], $arguments);
        // $this->assertNull($result);
        // $this->assertEquals([], $params);
        // $this->assertTrue($isCallable);
    }

    public function afterCallHandelFunction($object, $className, $methodName, $arguments, $result, $params, $isCallable)
    {
        // $this->assertInstanceOf($this->someClassName, $object);
        // $this->assertEquals($this->someClassName, $className);
        // $this->assertEquals("someMethod", $methodName);
        // $this->assertEquals([1, 2, 3], $arguments);
        // $this->assertEquals(6, $result);
        // $this->assertEquals([], $params);
        // $this->assertTrue($isCallable);

        // $result = 90;
    }

}
