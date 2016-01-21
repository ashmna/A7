<?php

namespace A7\Tests\Unit;


use A7\Tests\Resources\AbstractUnitTestCase;

abstract class AbstractCacheTest extends AbstractUnitTestCase
{

    /** @var \A7\CacheInterface */
    protected $cache;

    public function testInCache()
    {
        // Test Data
        $key = "key";
        $value = "value";
        // Run test
        $res = $this->cache->inCache($key);
        $this->assertFalse($res);
        $this->cache->setCache($key, $value);
        $res = $this->cache->inCache($key);
        $this->assertTrue($res);
    }

    public function testSetAndGatCache()
    {
        // Test Data
        // number case
        $numberKey = "key-number";
        $numberData = 652312;
        // string case
        $stringKey = "key-string";
        $stringData = "String String Long String";
        // array case
        $arrayKey = "key-array";
        $arrayData = ['key' => 'value', 'int' => 10, 'array' => []];
        // object case
        $objectKey = "key-object";
        $objectData = (object)$arrayData;
        // Run Test
        $this->cache->setCache($numberKey, $numberData);
        $this->cache->setCache($stringKey, $stringData);
        $this->cache->setCache($arrayKey,  $arrayData);
        $this->cache->setCache($objectKey, $objectData);
        $this->assertEquals($numberData, $this->cache->getCache($numberKey));
        $this->assertEquals($stringData, $this->cache->getCache($stringKey));
        $this->assertEquals($arrayData,  $this->cache->getCache($arrayKey));
        $this->assertEquals($objectData, $this->cache->getCache($objectKey));
        $this->assertEquals(null, $this->cache->getCache("not-key"));
    }

    public function testClear()
    {
        // Test Data
        $key1 = "key1";
        $key2 = "key2";
        $value = "value";
        // Run Test
        $this->cache->setCache($key1, $value);
        $this->cache->setCache($key2, $value);
        $this->assertTrue($this->cache->inCache($key1));
        $this->assertTrue($this->cache->inCache($key2));
        $this->cache->clear();
        $this->assertFalse($this->cache->inCache($key1));
        $this->assertFalse($this->cache->inCache($key2));
    }

}
