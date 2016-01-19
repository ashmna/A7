<?php

namespace A7\Tests\Unit;

require_once dirname(__DIR__)."/Resources/EmptyClass6.php";


use A7\A7;
use A7\Annotations\Init;
use A7\Annotations\Injectable;
use A7\Tests\Resources\AbstractUnitTestCase;
use A7\Tests\Resources\EmptyClass8;

class A7Test extends AbstractUnitTestCase
{
    // Test class
    /** @var A7 */
    private $a7;
    // Mocks
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $cache;
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $annotationManager;
    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $postProcessManager;

    public function setUp()
    {
        // Init test class
        $this->a7 = new A7();
        // Init mocks
        $this->cache = $this->getMockBuilder('\A7\ArrayCache')->getMock();
        $this->annotationManager = $this->getMockBuilder('\A7\AnnotationManager')->getMock();
        $this->postProcessManager = $this->getMockBuilder('\A7\PostProcessManager')
            ->setConstructorArgs([$this->a7, $this->annotationManager])->getMock();
        // Set mocks
        $this->setMock($this->a7, "cache", $this->cache);
        $this->setMock($this->a7, "annotationManager", $this->annotationManager);
        $this->setMock($this->a7, "postProcessManager", $this->postProcessManager);
    }

    public function testConstructWithArgument()
    {
        // Init test class
        $this->a7 = new A7($this->cache);
        // Set mocks
        $this->setMock($this->a7, "cache", $this->cache);
        $this->setMock($this->a7, "annotationManager", $this->annotationManager);
        $this->setMock($this->a7, "postProcessManager", $this->postProcessManager);
    }

    public function testGetWithInterfaceImplFolderImpl()
    {
        // Test data
        $interfaceName = 'A7\Tests\Resources\EmptyClass1';
        $objectInstance = 'A7\Tests\Resources\Impl\EmptyClass1Impl';
        $injectable = new Injectable();
        $injectable->lazy = false;
        // Expectations
        $this->annotationManager
            ->expects($this->exactly(2))
            ->method("getClassAnnotation")
            ->with($objectInstance, "Injectable")
            ->willReturn($injectable);
        $this->annotationManager
            ->expects($this->once())
            ->method("getMethodsAnnotations")
            ->with($objectInstance)
            ->willReturn([]);
        // Run Test
        $object = $this->a7->get($interfaceName);
        $this->assertInstanceOf($objectInstance, $object);
    }

    public function testGetWithInterfaceImplFolder()
    {
        // Test data
        $interfaceName = 'A7\Tests\Resources\EmptyClass2';
        $objectInstance = 'A7\Tests\Resources\Impl\EmptyClass2';
        $injectable = new Injectable();
        $injectable->lazy = false;
        // Expectations
        $this->annotationManager
            ->expects($this->exactly(2))
            ->method("getClassAnnotation")
            ->with($objectInstance, "Injectable")
            ->willReturn($injectable);
        $this->annotationManager
            ->expects($this->once())
            ->method("getMethodsAnnotations")
            ->with($objectInstance)
            ->willReturn([]);
        // Run Test
        $object = $this->a7->get($interfaceName);
        $this->assertInstanceOf($objectInstance, $object);
    }

    public function testGetWithInterfaceImpl()
    {
        // Test data
        $interfaceName = 'A7\Tests\Resources\EmptyClass3';
        $objectInstance = 'A7\Tests\Resources\EmptyClass3Impl';
        $injectable = new Injectable();
        $injectable->lazy = false;
        // Expectations
        $this->annotationManager
            ->expects($this->exactly(2))
            ->method("getClassAnnotation")
            ->with($objectInstance, "Injectable")
            ->willReturn($injectable);
        $this->annotationManager
            ->expects($this->once())
            ->method("getMethodsAnnotations")
            ->with($objectInstance)
            ->willReturn([]);
        // Run Test
        $object = $this->a7->get($interfaceName);
        $this->assertInstanceOf($objectInstance, $object);
    }

    public function testGetWithClassName()
    {
        // Test data
        $className = 'A7\Tests\Resources\EmptyClass4';
        $injectable = new Injectable();
        $injectable->lazy = false;
        // Expectations
        $this->annotationManager
            ->expects($this->exactly(2))
            ->method("getClassAnnotation")
            ->with($className, "Injectable")
            ->willReturn($injectable);
        $this->annotationManager
            ->expects($this->once())
            ->method("getMethodsAnnotations")
            ->with($className)
            ->willReturn([]);
        // Run Test
        $object = $this->a7->get($className);
        $this->assertInstanceOf($className, $object);
    }

    public function testGetForInitMethod()
    {
        // Test data
        $className = 'A7\Tests\Resources\EmptyClass5';
        $injectable = new Injectable();
        $injectable->lazy = false;
        // Expectations
        $this->annotationManager
            ->expects($this->exactly(2))
            ->method("getClassAnnotation")
            ->with($className, "Injectable")
            ->willReturn($injectable);
        $this->annotationManager
            ->expects($this->once())
            ->method("getMethodsAnnotations")
            ->with($className)
            ->willReturn([
                'emptyMethod1' => [],
                'emptyMethod2' => [],
                'emptyMethod3' => [],
                'emptyMethod4' => ['Init' => new Init()],
                'emptyMethod5' => ['Init' => new Init()],
                'emptyMethod6' => ['Init' => new Init()],
            ]);
        // Run Test
        $object = $this->a7->get($className);
        $this->assertInstanceOf($className, $object);
        $this->assertEquals(3, $object->i);
    }

    public function testGetWithNoneNamespace()
    {
        // Test data
        $className = 'EmptyClass6';
        $injectable = new Injectable();
        $injectable->lazy = false;
        // Expectations
        $this->annotationManager
            ->expects($this->exactly(2))
            ->method("getClassAnnotation")
            ->with($className, "Injectable")
            ->willReturn($injectable);
        $this->annotationManager
            ->expects($this->once())
            ->method("getMethodsAnnotations")
            ->with($className)
            ->willReturn([]);
        // Run Test
        $object = $this->a7->get($className);
        $this->assertInstanceOf($className, $object);
    }

    /**
     * @expectedException \Exception
     */
    public function testGetWithException()
    {
        // Test data
        $className = 'EmptyClass';
        // Run Test
        try {
            $this->a7->get($className);
        } catch (\Exception $e) {
            $this->assertInstanceOf('\Exception', $e);
            $this->assertEquals('EmptyClass class not found', $e->getMessage());
            throw $e;
        }
    }

    public function testGetWithSingletonList()
    {
        // Test data
        $className = 'A7\Tests\Resources\EmptyClass7';
        $injectable = new Injectable();
        $injectable->lazy = false;
        // Expectations
        $this->annotationManager
            ->expects($this->exactly(2))
            ->method("getClassAnnotation")
            ->with($className, "Injectable")
            ->willReturn($injectable);
        $this->annotationManager
            ->expects($this->once())
            ->method("getMethodsAnnotations")
            ->with($className)
            ->willReturn([]);
        // Run Test
        $object1 = $this->a7->get($className);
        $this->assertInstanceOf($className, $object1);
        $object2 = $this->a7->get($className);
        $this->assertInstanceOf($className, $object2);
        $this->assertEquals($object1, $object2);
    }

    public function testCall()
    {
        // Test Data
        $className = 'A7\Tests\Resources\EmptyClass8';
        $injectable = new Injectable();
        $injectable->lazy = false;
        $arguments = [
            'key'  => 'value',
            'key1' => 'value1',
            'key2' => 'value2',
            'key3' => 'value3',
        ];
        // Expectations
        $this->annotationManager
            ->expects($this->exactly(2))
            ->method("getClassAnnotation")
            ->with($className, "Injectable")
            ->willReturn($injectable);
        $this->annotationManager
            ->expects($this->once())
            ->method("getMethodsAnnotations")
            ->with($className)
            ->willReturn([]);
        // Run Test
        $res = $this->a7->call($className, 'methodReturnTrue', []);
        $this->assertTrue($res);
        $res = $this->a7->call($className, 'methodReturnTrue', $arguments);
        $this->assertTrue($res);
        $res = $this->a7->call($className, 'returnArguments', $arguments);
        $this->assertEquals($res, []);
        $res = $this->a7->call($className, 'returnAgr', $arguments);
        $this->assertEquals($res, 'value');
        $res = $this->a7->call($className, 'returnAgrArray', $arguments);
        $this->assertEquals($res, ['value']);
        $res = $this->a7->call($className, 'returnAgrDefaultValue', []);
        $this->assertEquals($res, 10);
        $res = $this->a7->call($className, 'returnAgrDefaultValueArray', []);
        $this->assertEquals($res, []);
        $res = $this->a7->call(new EmptyClass8(), 'methodReturnTrue', []);
        $this->assertTrue($res);
    }

    public function testEnablePostProcessor()
    {
        //$postProcessor, array $parameters = []
    }

    public function testDisablePostProcessor()
    {
        //$postProcessor
    }

    public function testInitClass()
    {
        //$class, $checkLazy = true
    }

    public function testDoPostProcessors()
    {
        //$instance, $class, array $postProcessors, $proxyInstance = null
    }



}
