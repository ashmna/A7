<?php

namespace A7\Annotations;

/**
 * "Transactional" annotation
 *
 * @Annotation
 * @package A7\Annotations
 */
final class Transactional
{
    public $enable = true;

    public function isEnabled() {
        return $this->enable;
    }
}