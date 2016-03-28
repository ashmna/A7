<?php

namespace A7\Tests\Resources\Di\Impl;

use A7\Tests\Resources\Di\B;

/**
 * Class BImpl
 * @package A7\Tests\Resources\Di\Impl
 *
 * @Injectable(lazy=true, scope="singleton")
 */
class BImpl implements B
{
    /**
     * @Inject("injectValue")
     * @var int
     */
    private $val;
    /**
     * @var int
     */
    private $dupleVal;

    /**
     * This method called after injection
     *
     * @Init
     */
    private function init()
    {
        $this->dupleVal = $this->val * 2;
    }

    public function getDemoValue()
    {
        return $this->val;
    }

    public function getAfterInitValue()
    {
        return $this->dupleVal;
    }

}
