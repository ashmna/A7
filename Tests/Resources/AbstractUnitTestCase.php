<?php

namespace A7\Tests\Resources;

abstract class AbstractUnitTestCase extends \PHPUnit_Framework_TestCase
{
    protected function setMock($class, $memberName, $mock)
    {
        $reflection = new \ReflectionClass(get_class($class));
        $property = $reflection->getProperty($memberName);
        $property->setAccessible(true);
        $property->setValue($class, $mock);
    }
}
