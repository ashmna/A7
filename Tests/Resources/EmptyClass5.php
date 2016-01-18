<?php

namespace A7\Tests\Resources;


class EmptyClass5 {

    public $i = 0;

    private function emptyMethod1()   { $this->i++; }
    protected function emptyMethod2() { $this->i++; }
    public function emptyMethod3()    { $this->i++; }

    /** @Init */
    private function emptyMethod4()   { $this->i++; }
    /** @Init */
    protected function emptyMethod5() { $this->i++; }
    /** @Init */
    public function emptyMethod6()    { $this->i++; }

}
