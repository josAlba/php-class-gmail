<?php



if (php_sapi_name() != 'cli') {
    throw new Exception('This application must be run on the command line.');
}

if(!isset($argv[1])){
    echo "\n Error indique el usuario";
    echo "\n php _newtoken.php {nombre}";
    echo "\n";
    exit();   
}

$us = $argv[1];

$cc1=$us.'.credentials.json';
$cc2=$us.'.token.json';

require __DIR__ . '/vendor/autoload.php';
include(__DIR__.'/migmail.php');

$g = new MiGmail($cc1,$cc2);
$g->getClient();

echo "\n->OK\n";
exit();