<?php


namespace A7;

class PostProcessManager implements PostProcessManagerInterface
{
    /** @var A7Interface */
    protected $a7;
    /** @var AnnotationManagerInterface */
    protected $annotationManager;

    function __construct(A7Interface $a7, AnnotationManagerInterface $annotationManager) {
        $this->a7 = $a7;
        $this->annotationManager = $annotationManager;
    }

    /**
     * @inheritdoc
     */
    public function getPostProcessInstance($postProcessName, array $parameters = [])
    {
        $postProcessClass = 'A7\PostProcessors\\'.$postProcessName;

        $postProcessObject = new $postProcessClass();
        $postProcessReflectionObject = new \ReflectionObject($postProcessObject);

        if($postProcessReflectionObject->hasProperty('a7')) {
            $a7Property = $postProcessReflectionObject->getProperty('a7');
            $a7Property->setAccessible(true);
            $a7Property->setValue($postProcessObject, $this->a7);
        }
        if($postProcessReflectionObject->hasProperty('annotationManager')) {
            $annotationManagerProperty = $postProcessReflectionObject->getProperty('annotationManager');
            $annotationManagerProperty->setAccessible(true);
            $annotationManagerProperty->setValue($postProcessObject, $this->annotationManager);
        }
        if($postProcessReflectionObject->hasProperty('parameters')) {
            $annotationManagerProperty = $postProcessReflectionObject->getProperty('parameters');
            $annotationManagerProperty->setAccessible(true);
            $annotationManagerProperty->setValue($postProcessObject, $parameters);
        }

        return $postProcessObject;
    }
}