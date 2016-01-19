<?php

namespace A7\Tests\Resources;


class EmptyClass8 {


    public function methodReturnTrue()
    {
        return true;
    }

    public function returnArguments()
    {
        return func_get_args();
    }

    public function returnAgr($key)
    {
        return $key;
    }

    public function returnAgrArray(array $key)
    {
        return $key;
    }

    public function returnAgrDefaultValue($key = 10)
    {
        return $key;
    }

    public function returnAgrDefaultValueArray(array $key)
    {
        return $key;
    }


}
