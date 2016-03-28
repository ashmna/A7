<?php

namespace A7\Tests\Resources\Di\Impl;

use A7\Tests\Resources\Di\A;

/**
 * Class AImpl
 * @package A7\Tests\Resources\Di\Impl
 *
 * @Injectable(lazy=true, scope="singleton")
 */
class AImpl implements A
{
    /**
     * @Inject
     * @var \A7\Tests\Resources\Di\B
     */
    private $b;

    public function getDemoValue()
    {
        return true;
    }

    public function getDemoValueFromB()
    {
        return $this->b->getDemoValue();
    }

}
