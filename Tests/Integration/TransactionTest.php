<?php

namespace A7\Tests\Integration;


use A7\A7;
use A7\A7Interface;
use A7\Tests\Resources\AbstractUnitTestCase;
use A7\Tests\Resources\Transaction\MockConnector;

class TransactionTest extends AbstractUnitTestCase
{
    /** @var A7Interface */
    private $a7;

    public function setUp()
    {
        $this->a7 = new A7();
        MockConnector::clean();
    }

    /**
     * @expectedException \Exception
     */
    public function testWithInstanceAndTransactionalClass()
    {
        $this->a7->enablePostProcessor('Transaction', [
            "instance"         => new MockConnector(),
            "beginTransaction" => "myBeginTransaction",
            "commit"           => "myCommit",
            "rollback"         => "myRollback",
        ]);

        /** @var \A7\Tests\Resources\Transaction\A $a */
        $a = $this->a7->get("\\A7\\Tests\\Resources\\Transaction\\A");

        MockConnector::clean();
        $a->someTransactionalMethod();
        $this->assertEquals(0, MockConnector::$isCalled["init"]);
        $this->assertEquals(1, MockConnector::$isCalled["begin"]);
        $this->assertEquals(1, MockConnector::$isCalled["commit"]);
        $this->assertEquals(0, MockConnector::$isCalled["rollback"]);

        MockConnector::clean();
        $a->someNotTransactionalMethod();
        $this->assertEquals(0, MockConnector::$isCalled["init"]);
        $this->assertEquals(0, MockConnector::$isCalled["begin"]);
        $this->assertEquals(0, MockConnector::$isCalled["commit"]);
        $this->assertEquals(0, MockConnector::$isCalled["rollback"]);

        try {
            MockConnector::clean();
            $a->someFailMethod();
            $this->fail();
        } catch(\Exception $e) {
            $this->assertEquals(0, MockConnector::$isCalled["init"]);
            $this->assertEquals(1, MockConnector::$isCalled["begin"]);
            $this->assertEquals(0, MockConnector::$isCalled["commit"]);
            $this->assertEquals(1, MockConnector::$isCalled["rollback"]);
            throw $e;
        }
    }

    public function testWithclass()
    {
        $this->a7->enablePostProcessor('Transaction', [
            "class"            => "\\A7\\Tests\\Resources\\Transaction\\MockConnector",
            "beginTransaction" => "myBeginTransaction",
            "commit"           => "myCommit",
            "rollback"         => "myRollback",
        ]);

        /** @var \A7\Tests\Resources\Transaction\B $b */
        $b = $this->a7->get("\\A7\\Tests\\Resources\\Transaction\\B");

        MockConnector::clean();
        $b->someTransactionalMethod();
        $this->assertEquals(1, MockConnector::$isCalled["init"]);
        $this->assertEquals(1, MockConnector::$isCalled["begin"]);
        $this->assertEquals(1, MockConnector::$isCalled["commit"]);
        $this->assertEquals(0, MockConnector::$isCalled["rollback"]);

        MockConnector::clean();
        $b->someNotTransactionalMethod();
        $this->assertEquals(0, MockConnector::$isCalled["init"]);
        $this->assertEquals(0, MockConnector::$isCalled["begin"]);
        $this->assertEquals(0, MockConnector::$isCalled["commit"]);
        $this->assertEquals(0, MockConnector::$isCalled["rollback"]);
    }
}
