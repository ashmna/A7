<?php


namespace A7;


class PostProcessManager implements PostProcessManagerInterface
{

    /**
     * @inheritdoc
     */
    public function getPostProcessInstance($postProcessName)
    {
        $c = 'A7\PostProcessors\\'.$postProcessName;
        return new $c();
    }
}