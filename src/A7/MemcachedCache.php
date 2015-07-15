<?php


namespace A7;


class MemcachedCache implements CacheInterface
{
    /** @var \Memcached */
    protected $memcached;

    public function __construct(\Memcached $m)
    {
        $this->memcached = $m;
    }

    function inCache($key)
    {
        return $this->memcached->get($key)!==false || $this->memcached->getResultCode() != \Memcached::RES_NOTFOUND;
    }

    function setCache($key, $value)
    {
        $this->memcached->set($key, $value);
    }

    function getCache($key)
    {
        $val = $this->memcached->get($key);
        return $val === false ? null : $val;
    }

    function clear()
    {
        $this->memcached->flush();
//        $val = $this->memcached->get($key);
//        if($val !== false) {
//            $this->memcached->delete($key);
//        }
        // TODO: Implement clear() method.
    }


}