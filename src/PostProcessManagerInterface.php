<?php


namespace A7;

interface PostProcessManagerInterface
{


    /**
     * Post process manager constructor
     *
     * @param A7Interface $a7
     * @param AnnotationManagerInterface $annotationManager
     */
    function __construct(A7Interface $a7, AnnotationManagerInterface $annotationManager);

    /**
     * Get post process instance
     *
     * @param $postProcessName
     * @param array $parameters
     * @return PostProcessInterface
     */
    function getPostProcessInstance($postProcessName, array $parameters = []);

}
