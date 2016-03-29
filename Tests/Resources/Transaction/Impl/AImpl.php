<?php

namespace A7\Tests\Resources\Transaction\Impl;

use A7\Tests\Resources\Transaction\A;

/**
 * Class AImpl
 * @package A7\Tests\Resources\Transaction\Impl
 *
 * @Transactional
 */
class AImpl implements A
{

    public function someTransactionalMethod()
    {
    }

    /**
     * @Transactional(enable=false)
     */
    public function someNotTransactionalMethod()
    {
    }

    public function someFailMethod()
    {
        throw new \Exception("Some Exception");
    }

}
