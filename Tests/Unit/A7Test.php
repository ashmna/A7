<?php

namespace A7\Tests\Unit;


use A7\A7;
use A7\Tests\Resources\AbstractUnitTestCase;

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
        $this->postProcessManager = $this->getMockBuilder('\A7\PostProcessManager')->getMock();
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

    public function testGet($class)
    {
        // Test data
        // Expectations
        // Run Test
        //EmptyClass
        //$this->a7->get("EmptyClass");
    }

    public function testCall($class, $method, array $arguments)
    {

    }

    public function testEnablePostProcessor($postProcessor, array $parameters = [])
    {

    }

    public function testDisablePostProcessor($postProcessor)
    {

    }

    public function testInitClass($class, $checkLazy = true)
    {

    }

    public function testDoPostProcessors($instance, $class, array $postProcessors, $proxyInstance = null)
    {

    }



}
