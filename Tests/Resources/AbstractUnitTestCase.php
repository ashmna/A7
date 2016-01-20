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

    protected function invokeMethod($class, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionObject($class);
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);
        return $method->invokeArgs($class, $parameters);
    }

    protected function getMember($class, $memberName)
    {
        $reflection = new \ReflectionObject($class);
        $member = $reflection->getProperty($memberName);
        $member->setAccessible(true);
        return $member->getValue($class);
    }
}
