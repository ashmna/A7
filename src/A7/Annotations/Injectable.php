<?php


namespace A7\Annotations;

/**
 * "Injectable" annotation
 *
 * @Annotation
 * @package A7\Annotations
 */
final class Injectable
{

    const SINGLETON = 'singleton';
    const PROTOTYPE = 'prototype';

    public $scope = self::SINGLETON;
    public $lazy  = true;

    public function isSingleton() {
        return $this->scope == self::SINGLETON;
    }

}
