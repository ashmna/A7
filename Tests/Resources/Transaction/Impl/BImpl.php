<?php

namespace A7\Tests\Resources\Transaction\Impl;

use A7\Tests\Resources\Transaction\B;

/**
 * Class BImpl
 * @package A7\Tests\Resources\Transaction\Impl
 *
 * @Injectable(lazy=false, scope="prototype")
 */
class BImpl implements B
{
    /**
     * @Transactional
     */
    public function someTransactionalMethod()
    {
    }

    public function someNotTransactionalMethod()
    {
    }

}
