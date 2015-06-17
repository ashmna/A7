<?php

namespace A7;


interface A7Interface
{
    public function get($class);
    public function call($class, $method, array $arguments);
    public function enablePostProcessor($postProcessor);
    public function disablePostProcessor($postProcessor);
}