<?php
require_once dirname(__DIR__). '/vendor/autoload.php';

if(!class_exists('Memcached')) {
    require_once dirname(__DIR__).'/memcached.php';
}

require_once  'AffiliatesImpl.php';

$a7 = new \A7\A7();
$a7->enablePostProcessor('DependencyInjection');
//$a7->enablePostProcessor('Logger');
$a7->enablePostProcessor('Cache');

echo '<pre>';
/** @var AffiliatesImpl $s */
$s = $a7->get('AffiliatesImpl');


echo $s->kuku('call 1');
echo $s->kuku('call 2');
echo $s->kuku('call 1');
echo $s->kuku('call 4');
echo $s->kuku('call 1');








echo '</pre>';




















