<?php

namespace A7\Tests\Resources;


class SomeClass
{

    public $a;
    private $b;
    protected $c;

    public function someMethod($a, $b, $c)
    {
        $this->a = $a;
        $this->b = $b;
        $this->c = $c;
    }

    public function someMethod2($d)
    {
        return $d;
    }
}