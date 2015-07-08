<?php


namespace A7;


class ReflectionUtils implements ReflectionUtilsInterface
{
    /** @var CacheInterface */
    private $cache;
    private static $instance;

    /**
     * @return ReflectionUtilsInterface
     */
    public static function getInstance() {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    private function __construct()
    {
        $this->cache = new ArrayCache();
    }

    /** @inheritdoc */
    public function getClassReflection($className)
    {
        $key = 'A7-CR-'.$className;
        if(!$this->inCache($key)) {
            $this->setCache($key, new \ReflectionClass($className));
        }
        return $this->getCache($key);
    }
    /** @inheritdoc */
    public function getPropertiesReflection($className) {
        $key = 'A7-PR-'.$className;
        if(!$this->inCache($key)) {
            $this->setCache($key, $this->getClassReflection($className)->getProperties());
        }
        return $this->getCache($key);
    }
    /** @inheritdoc */
    public function getMethodsReflection($className)
    {
        $key = 'A7-MR-'.$className;
        if(!$this->inCache($key)) {
            $this->setCache($key, $this->getClassReflection($className)->getMethods());
        }
        return $this->getCache($key);
    }
    /** @inheritdoc */
    public function getMethodReflection($className, $methodName)
    {
        $key = 'A7-MR-'.$className.'-'.$methodName;
        if(!$this->inCache($key)) {
            $this->setCache($key, new \ReflectionMethod($className, $methodName));
        }
        return $this->getCache($key);
    }
    /** @inheritdoc */
    public function getParametersReflection($className, $methodName)
    {
        $key = 'A7-PR-'.$className.'-'.$methodName;
        if(!$this->inCache($key)) {
            $this->setCache($key, $this->getMethodReflection($className, $methodName)->getParameters());
        }
        return $this->getCache($key);
    }

    private function inCache($key)
    {
        return $this->cache->inCache($key);
    }

    private function setCache($key, $value)
    {
        $this->cache->setCache($key, $value);
    }

    private function getCache($key)
    {
        return $this->cache->getCache($key);
    }

}