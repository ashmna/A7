<?php

namespace A7\Tests\Integration;


use A7\A7;
use A7\Tests\Resources\AbstractUnitTestCase;

class DependencyInjectionTest extends AbstractUnitTestCase
{
    public function testWithSingletonAndLazyLoading()
    {
        $a7 = new A7();
        $a7->enablePostProcessor('DependencyInjection', [
            'injectValue' => 42
        ]);

        /** @var \A7\Tests\Resources\Di\A $a */
        $a = $a7->get("\\A7\\Tests\\Resources\\Di\\A");


        $this->assertInstanceOf("\\A7\\Proxy", $a);
        $this->assertNull($this->getMember($a, "a7Instance"));
        $this->assertTrue($a->getDemoValue());

        $aInstance = $this->getMember($a, "a7Instance");
        $b = $this->getMember($aInstance, "b");
        $this->assertInstanceOf("\\A7\\Tests\\Resources\\Di\\A", $aInstance);
        $this->assertInstanceOf("\\A7\\Proxy", $b);
        $this->assertNull($this->getMember($b, "a7Instance"));

        $val = $a->getDemoValueFromB();
        $this->assertEquals(42, $val);

        /** @var \A7\Tests\Resources\Di\B $b */
        $b = $this->getMember($aInstance, "b");
        $bInstance = $this->getMember($b, "a7Instance");
        $this->assertInstanceOf("\\A7\\Tests\\Resources\\Di\\B", $bInstance);

        $val = $b->getAfterInitValue();
        $this->assertEquals(84, $val);
    }
}
