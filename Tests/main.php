<?php
require_once dirname(__DIR__). '/vendor/autoload.php';

if(!class_exists('Memcached')) {
    require_once dirname(__DIR__).'/memcached.php';
}

require_once  'AffiliatesImpl.php';

$a7 = new \A7\A7();
$a7->enablePostProcessor('DependencyInjection');


$s = $a7->get('AffiliatesImpl');


var_dump($s);




























