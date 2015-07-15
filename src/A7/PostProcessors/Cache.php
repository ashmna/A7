<?php


namespace A7\PostProcessors;


use A7\AnnotationManagerInterface;
use A7\PostProcessInterface;
use A7\Proxy;

class Cache implements PostProcessInterface
{
    /** @var  AnnotationManagerInterface */
    protected $annotationManager;
    protected $a7;


    protected $cache = [];
    function postProcessBeforeInitialization($instance, $className)
    {
        return $instance;
    }

    function postProcessAfterInitialization($instance, $className)
    {
        /** @var \A7\Annotations\Cache $cache */
        $cache = $this->annotationManager->getClassAnnotation($className, 'Cache');

        if(isset($cache) && $cache->enable) {
            if(!($instance instanceof Proxy)) {
                $instance = new Proxy($this->a7, $className, $instance);
            }

            $instance->a7AddBeforeCall([$this, 'beforeCall']);
            $instance->a7AddAfterCall([$this, 'afterCall']);

        }
        return $instance;
    }

    function beforeCall($arguments, $methodName, $className, &$isCallable, &$result, &$params)
    {
        $hash = md5(serialize($arguments));
        $key = "$className-$methodName-$hash";
        if(isset($this->cache[$key])) {
            $result = $this->cache[$key];
            $isCallable = false;
        } else {
            $params['add_to_cache'] = true;
        }
    }

    function afterCall($arguments, $methodName, $className, &$result, $params)
    {
        $hash = md5(serialize($arguments));
        $key = "$className-$methodName-$hash";
        if(!empty($params['add_to_cache'])) {
            $this->cache[$key] = $result;
        }
    }

}