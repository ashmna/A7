<?php

namespace A7\Tests\Unit;


use A7\ArrayCache;

class ArrayCacheTest extends AbstractCacheTest {

    public function setUp() {
        $this->cache = new ArrayCache();
    }

}
