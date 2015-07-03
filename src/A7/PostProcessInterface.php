<?php


namespace A7;


interface PostProcessInterface
{
    function postProcessBeforeInitialization($instance, $className);
    function postProcessAfterInitialization($instance, $className);
}