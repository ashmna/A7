<?php


namespace A7;


interface PostProcessManagerInterface {
    /**
     * @param $postProcessName
     * @return PostProcessInterface
     */
    public function getPostProcessInstance($postProcessName);
}