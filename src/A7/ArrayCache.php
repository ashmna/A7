<?php


namespace A7;


class ArrayCache implements CacheInterface
{
    protected $data = [];

    public function inCache($key)
    {
        return in_array($key, $this->data);
    }

    public function setCache($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function getCache($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    public function clear()
    {
        $this->data = [];
    }
}