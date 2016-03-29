<?php


namespace A7;


class AbstractPostProcess implements PostProcessInterface
{
    /** @var  \A7\A7Interface */
    protected $a7;
    /** @var  \A7\AnnotationManagerInterface */
    protected $annotationManager;
    /** @var  array */
    protected $parameters;

    /**
     * @inheritdoc
     */
    public function init()
    {
    }

    /**
     * @inheritdoc
     */
    public function postProcessBeforeInitialization($instance, $className)
    {
        return $instance;
    }

    /**
     * @inheritdoc
     */
    public function postProcessAfterInitialization($instance, $className)
    {
        return $instance;
    }

}
