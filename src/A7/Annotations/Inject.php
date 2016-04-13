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

    private $name;
    private $parameters = [];

    /**
     * @param array $values
     * @throws \RuntimeException
     */
    public function __construct(array $values)
    {
        // @Inject(name="foo")
        if (isset($values['name']) && is_string($values['name'])) {
            $this->name = $values['name'];
            return;
        }
        // @Inject
        if (!isset($values['value'])) {
            return;
        }
        $values = $values['value'];
        // @Inject("foo")
        if (is_string($values)) {
            $this->name = $values;
        }
    }

    public function isInjectObject()
    {
        return isset($this->name) && (interface_exists($this->name) || class_exists($this->name));
    }

    public function getName()
    {
        return $this->name;
    }

    public function setVar($var)
    {
        if (empty($this->name)) {
            $this->name = $var;
        }
    }

}
