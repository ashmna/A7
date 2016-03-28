<?php


namespace A7;


class ReflectionUtils implements ReflectionUtilsInterface
{
    /** @var CacheInterface */
    private $cache;
    /** @var ReflectionUtils */
    private static $instance;

    /**
     * Get a singleton Instance
     *
     * @return ReflectionUtilsInterface
     */
    public static function getInstance() {
        if (null === static::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * ReflectionUtils constructor
     */
    private function __construct()
    {
        $this->cache = new ArrayCache();
    }

    /**
     * @inheritdoc
     */
    public function getClassReflection($className)
    {
        $key = "A7-CR-".$className;
        if(!$this->inCache($key)) {
            $this->setCache($key, new \ReflectionClass($className));
        }
        return $this->getCache($key);
    }

    /**
     * @inheritdoc
     */
    public function getPropertiesReflection($className) {
        $key = "A7-PR-".$className;
        if(!$this->inCache($key)) {
            $this->setCache($key, $this->getClassReflection($className)->getProperties());
        }
        return $this->getCache($key);
    }

    /**
     * @inheritdoc
     */
    public function getPropertyReflection($className, $propertyName)
    {
        $key = "A7-PR-".$className."-".$propertyName;
        if(!$this->inCache($key)) {
            $this->setCache($key, $this->getClassReflection($className)->getProperty($propertyName));
        }
        return $this->getCache($key);
    }


    /**
     * @inheritdoc
     */
    public function getMethodsReflection($className)
    {
        $key = "A7-MR-".$className;
        if(!$this->inCache($key)) {
            $this->setCache($key, $this->getClassReflection($className)->getMethods());
        }
        return $this->getCache($key);
    }

    /**
     * @inheritdoc
     */
    public function getMethodReflection($className, $methodName)
    {
        $key = "A7-MR-".$className."-".$methodName;
        if(!$this->inCache($key)) {
            $this->setCache($key, new \ReflectionMethod($className, $methodName));
        }
        return $this->getCache($key);
    }

    /**
     * @inheritdoc
     */
    public function getParametersReflection($className, $methodName)
    {
        $key = "A7-PR-".$className."-".$methodName;
        if(!$this->inCache($key)) {
            $this->setCache($key, $this->getMethodReflection($className, $methodName)->getParameters());
        }
        return $this->getCache($key);
    }

    /**
     * Checks if the exists in cache
     *
     * @param string $key
     * @return bool
     */
    private function inCache($key)
    {
        return $this->cache->inCache($key);
    }

    /**
     * Set in cache
     *
     * @param string $key
     * @param mixed $value
     */
    private function setCache($key, $value)
    {
        $this->cache->setCache($key, $value);
    }

    /**
     * Get from cache
     *
     * @param string $key
     * @return mixed
     */
    private function getCache($key)
    {
        return $this->cache->getCache($key);
    }

}
