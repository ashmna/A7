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
    protected $usedClass;


    protected $cache = [];
    function postProcessBeforeInitialization($instance, $className)
    {
        if(isset($this->usedClass[$className])) return $instance;

        /** @var \A7\Annotations\Cache $cache */
        $cache = $this->annotationManager->getClassAnnotation($className, 'Cache');
        if(isset($cache) && $cache->enable) {
            if(!($instance instanceof Proxy)) {
                $instance = new Proxy($this->a7, $className, $instance);
            }

            $this->usedClass[$className] = true;

            $instance->a7AddBeforeCall(function($arguments, $methodName, $className, &$isCallable, &$result, &$params){
                $hash = md5(json_encode($arguments));
                $key = "$className-$methodName-$hash";
                if(isset($this->cache[$key])) {
                    $result = $this->cache[$key];
                    $isCallable = false;
                } else {
                    $params['add_to_cache'] = true;
                }
            });

            $instance->a7AddAfterCall(function($arguments, $methodName, $className, &$result, $params){
                $hash = md5(json_encode($arguments));
                $key = "$className-$methodName-$hash";
                if(!empty($params['add_to_cache'])) {
                    $this->cache[$key] = $result;
                }
            });

        }
        return $instance;
    }

    function postProcessAfterInitialization($instance, $className)
    {
        return $instance;
    }

}