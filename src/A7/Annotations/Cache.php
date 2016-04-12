<?php


namespace A7\Annotations;

/**
 * "Cache" annotation
 *
 * @Annotation
 * @package A7\Annotations
 */
final class Cache
{
    public $enable = true;
    public $key;
    public $ttl;

    public function isEnabled()
    {
        return $this->enable;
    }

}
