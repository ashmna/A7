<?php

namespace A7\Tests\Resources;

/**
 * Class SomeClass
 * @package A7\Tests\Resources
 *
 * @property int $a public
 * @property int $b private
 * @property int $c protected
 * @method void unknownMethod not exists method
 */
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

        return $a*$b*$c;
    }

    public function someMethod2($d)
    {
        return $d;
    }

    public function throwMethod()
    {
        throw new \Exception("Test Exception");
    }

}
