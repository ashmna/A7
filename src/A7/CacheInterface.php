<?php


namespace A7;


interface CacheInterface
{
    function inCache($key);
    function setCache($key, $value);
    function getCache($key);
    function clear();
}