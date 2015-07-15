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
        if (! isset($values['value'])) {
            return;
        }
        $values = $values['value'];
        // @Inject("foo")
        if (is_string($values)) {
            $this->name = $values;
        }
        // @Inject({...}) on a method
        if (is_array($values)) {
            foreach ($values as $key => $value) {
                if (! is_string($value)) {
                    throw new \RuntimeException(sprintf(
                        '@Inject({"param" = "value"}) expects "value" to be a string, %s given.',
                        json_encode($value)
                    ));
                }
                $this->parameters[$key] = $value;
            }
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

    public function getParameters()
    {
        return $this->parameters;
    }

    public function setVar($var)
    {
        if(empty($this->name)) {
            $this->name = $var;
        }
    }

}