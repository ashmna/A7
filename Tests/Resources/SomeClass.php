<?php

namespace A7\Tests\Resources;


class SomeClass
{

    private $a;
    private $b;

    public function someMethod($a, $b)
    {
        $this->a = $a;
        $this->b = $b;
    }

    public function someMethode2($a)
    {
        return $a;
    }
}