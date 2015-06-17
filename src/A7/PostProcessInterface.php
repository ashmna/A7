<?php


namespace A7;


interface PostProcessInterface {
    public function postProcessBeforeInitialization($instance, $className);
    public function postProcessAfterInitialization($instance, $className);
}