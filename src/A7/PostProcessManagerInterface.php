<?php


namespace A7;


interface PostProcessManagerInterface
{
    function __construct(A7Interface $a7, AnnotationManagerInterface $annotationManager);
    /**
     * @param $postProcessName
     * @param array $parameters
     * @return PostProcessInterface
     */
    function getPostProcessInstance($postProcessName, array $parameters = []);
}