<?php


namespace A7;


interface PostProcessManagerInterface
{
    /**
     * @param $postProcessName
     * @return PostProcessInterface
     */
    function getPostProcessInstance($postProcessName);
}