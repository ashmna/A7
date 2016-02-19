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
    /** @var Proxy */
    private $proxy;
    // Mocks
    /** @var \PHPUnit_Framework_MockObject_MockObject */
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
        /** @noinspection PhpParamsInspection */
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
        /** @noinspection PhpParamsInspection */
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
        /** @noinspection PhpParamsInspection */
        $this->proxy = new Proxy($this->a7, $this->someClassName, new SomeClass());
        // Run Test
        try {
            $d = $this->proxy->d;
        } catch (\RuntimeException $e) {
            $this->assertInstanceOf("\\RuntimeException", $e);
            $this->assertEquals("A7\\Tests\\Resources\\SomeClass::\$d [get] property not exists", $e->getMessage());
            throw $e;
        }
    }

    public function testSetMethod()
    {
        // Test Data
        /** @noinspection PhpParamsInspection */
        $this->proxy = new Proxy($this->a7, $this->someClassName, new SomeClass);
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
        /** @noinspection PhpParamsInspection */
        $this->proxy = new Proxy($this->a7, $this->someClassName, new SomeClass());
        // Run Test
        try {
            $this->proxy->d = 6789;
        } catch (\RuntimeException $e) {
            $this->assertInstanceOf("\\RuntimeException", $e);
            $this->assertEquals("A7\\Tests\\Resources\\SomeClass::\$d [set] property not exists", $e->getMessage());
            throw $e;
        }
    }

}
