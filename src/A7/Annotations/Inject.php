<?php


namespace A7\Annotations;

/**
 * "Inject" annotation
 *
 * @Annotation
 * @package A7\Annotations
 */
final class Inject
{
    public function __construct(array $array) {

    }

    private $parameters = [];

}