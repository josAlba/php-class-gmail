<?php
/**
 * Creamos un nuevo token a traves de la credencial
 */

//Comprobamos que se esta ejecutando desde una consola.
if (php_sapi_name() != 'cli') {
    throw new Exception('Esta aplicación debe ejecutarse en la línea de comando.');
}

//Comprobamos que se le pase un parametro para configurar las peticiones.
if(!isset($argv[1])){
    echo "\n Error indique el usuario";
    echo "\n php _newtoken.php {nombre}";
    echo "\n";
    exit();   
}

//Recuperamos los datos pasados por consola.
$us = $argv[1];

$cc1=$us.'.credentials.json';
$cc2=$us.'.token.json';

require __DIR__ . '/vendor/autoload.php';
include(__DIR__.'/migmail.php');

$g = new MiGmail($cc1,$cc2);
$g->getClient();

echo "\n->OK\n";
exit();