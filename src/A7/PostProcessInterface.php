<?php


namespace A7;


interface PostProcessInterface
{
    //processMode  0 all , 1 only instance , 2 only (a7) proxy

    function postProcessBeforeInitialization($instance, $className);
    function postProcessAfterInitialization($instance, $className);
}