<?php

namespace A7\Tests\Resources\Transaction;


interface A
{
    function someTransactionalMethod();
    function someNotTransactionalMethod();
    function someFailMethod();
}
