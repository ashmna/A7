<?php
require_once dirname(__DIR__). '/vendor/autoload.php';

if(!class_exists('Memcached')) {
    require_once dirname(__DIR__).'/memcached.php';
}

require_once  'AffiliatesImpl.php';

$m = new Memcached();
$m->addServer('localhost', 11211);

$cache = new \A7\MemcachedCache($m);
//$cache = new \A7\ArrayCache();
//$a7 = new \A7\A7($cache);
//
//$a7->enablePostProcessor('DependencyInjection', [
//    'partner.id' => 5,
//]);
//
//$a7->enablePostProcessor('Cache');
//
///** @var AffiliatesImpl $s */
//$s = $a7->get('AffiliatesImpl');




$start = microtime(true);

for($i=0; $i<1500; ++$i) {

    $cache = new \A7\ArrayCache();

    $a7 = new \A7\A7($cache);

    $a7->enablePostProcessor('DependencyInjection', [
        'partner.id' => 5,
    ]);

    $a7->enablePostProcessor('Cache', [

    ]);

    /** @var AffiliatesImpl $s */
    $s = $a7->get('AffiliatesImpl');
}



$end = microtime(true);
$ArrayCache = $end - $start ;
echo ' ArrayCache  '.$ArrayCache.' ms';

$start = microtime(true);

for($i=0; $i<1500; ++$i) {
    $cache = new \A7\MemcachedCache($m);

    $a7 = new \A7\A7($cache);

    $a7->enablePostProcessor('DependencyInjection', [
        'partner.id' => 5,
    ]);

    $a7->enablePostProcessor('Cache');

    /** @var AffiliatesImpl $s */
    $s = $a7->get('AffiliatesImpl');
}



$end = microtime(true);
$MemcachedCache = $end - $start ;

echo "\n";
echo ' MemcachedCache '.$MemcachedCache .' ms';
echo "\n\n\n\n";
$t = $ArrayCache/$MemcachedCache;
echo ' ArrayCache / MemcachedCache '. $t;










