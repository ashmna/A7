<?php


namespace A7\PostProcessors;


use A7\AbstractPostProcess;
use A7\Proxy;

class Cache extends AbstractPostProcess
{
    private $cache = [];
    private $key = "RT"; // Run Time

    public function init()
    {
        if (isset($this->parameters["key"])) {
            $this->key .= "-" . $this->parameters["key"];
        }
    }

    public function postProcessAfterInitialization($instance, $className)
    {
        if (!($instance instanceof Proxy)) {
            $instance = new Proxy($this->a7, $className, $instance);
        }

        $instance->a7AddBeforeCall([$this, "beforeCall"]);
        $instance->a7AddAfterCall([$this, "afterCall"]);

        return $instance;
    }

    public function beforeCall($arguments, $methodName, $className, &$isCallable, &$result, &$params)
    {
        list($isEnabled, $key, $ttl) = $this->checkCache($className, $methodName);

        if (!$isEnabled) {
            return;
        }

        if (!isset($key)) {
            $key = $this->getKey($className, $methodName, $arguments);
        }

        if (isset($this->cache[$key])) {
            $result = $this->cache[$key];
            $isCallable = false;
        } else {
            $params["Cache.addToCache"] = true;
            $params["Cache.key"] = $key;
        }
    }

    public function afterCall(&$result, $params)
    {
        if (!empty($params["Cache.addToCache"])) {
            $this->cache[$params["Cache.key"]] = $result;
        }
    }

    private function getKey($className, $methodName, $arguments)
    {
        $hash = md5(serialize($arguments));
        return $this->key . "-$className-$methodName-$hash";
    }

    private function checkCache($className, $methodName)
    {
        $isEnabled = false;
        $key = null;
        $ttl = null;

        /** @var \A7\Annotations\Cache|NULL $classCache */
        $classCache = $this->annotationManager->getClassAnnotation($className, "Cache");
        if (isset($classCache)) {
            $isEnabled = $classCache->isEnabled();
            $ttl = $classCache->ttl;
        }

        /** @var \A7\Annotations\Cache|NULL $methodCache */
        $methodCache = $this->annotationManager->getMethodAnnotation($className, $methodName, "Cache");
        if (isset($methodCache)) {
            $isEnabled = $methodCache->isEnabled();
            if ($methodCache->ttl) {
                $ttl = $methodCache->ttl;
            };
            $key = $methodCache->key;
        }

        $isEnabled &= isset($this->DBInstance);
        $isEnabled = (bool)$isEnabled;

        return [$isEnabled, $key, $ttl];
    }

}
