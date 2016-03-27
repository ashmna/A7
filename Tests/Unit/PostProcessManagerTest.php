<?php

namespace A7\Tests\Unit;

require_once dirname(__DIR__)."/Resources/SomePostProcess.php";


use A7\PostProcessManager;
use A7\Tests\Resources\AbstractUnitTestCase;

class PostProcessManagerTest extends AbstractUnitTestCase
{
    // Test class
    /** @var PostProcessManager */
    private $postProcessManager;
    // Mocks
    /** @var \PHPUnit_Framework_MockObject_MockObject|\A7\A7Interface */
    private $a7;
    /** @var \PHPUnit_Framework_MockObject_MockObject|\A7\AnnotationManagerInterface */
    private $annotationManager;

    public function setUp()
    {
        // Init mocks
        $this->a7 = $this->getMockBuilder("\\A7\\A7")->getMock();
        $this->annotationManager = $this->getMockBuilder("\\A7\\AnnotationManager")->getMock();
        // Init test class
        $this->postProcessManager = new PostProcessManager($this->a7, $this->annotationManager);
    }

    public function testGetPostProcessInstance()
    {
        // Test Data
        $parameters = [];
        // Run Test
        $postProcess = $this->postProcessManager->getPostProcessInstance("SomePostProcess", $parameters);
        $this->assertInstanceOf("\\A7\\PostProcessors\\SomePostProcess", $postProcess);
        $this->assertInstanceOf("\\A7\\A7", $this->getMember($postProcess, "a7"));
        $this->assertInstanceOf("\\A7\\AnnotationManager", $this->getMember($postProcess, "annotationManager"));
        $this->assertEquals($parameters, $this->getMember($postProcess, "parameters"));
    }

}
