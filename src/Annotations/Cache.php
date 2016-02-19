<?php


namespace A7\Annotations;

/**
 * "Injectable" Cache
 *
 * @Annotation
 * @package A7\Annotations
 */
final class Cache
{
    public $enable = true;
    public $key;
    public $ttl;
}