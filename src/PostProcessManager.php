<?php


namespace A7;

class PostProcessManager implements PostProcessManagerInterface
{
    /** @var A7Interface */
    protected $a7;
    /** @var AnnotationManagerInterface */
    protected $annotationManager;


    /**
     * @inheritdoc
     */
    public function __construct(A7Interface $a7, AnnotationManagerInterface $annotationManager)
    {
        $this->a7 = $a7;
        $this->annotationManager = $annotationManager;
    }

    /**
     * @inheritdoc
     */
    public function getPostProcessInstance($postProcessName, array $parameters = [])
    {
        $postProcessClass = "A7\\PostProcessors\\" . $postProcessName;

        $postProcessObject = new $postProcessClass();
        $postProcessReflectionObject = new \ReflectionObject($postProcessObject);

        foreach (["a7", "annotationManager", "parameters"] as $key) {
            if ($postProcessReflectionObject->hasProperty($key)) {
                $a7Property = $postProcessReflectionObject->getProperty($key);
                $a7Property->setAccessible(true);
                $a7Property->setValue($postProcessObject, $this->$key);
            }
        }

        return $postProcessObject;
    }

}
