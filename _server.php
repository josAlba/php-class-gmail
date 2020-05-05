<?php

require __DIR__ . '/vendor/autoload.php';

use Workerman\Worker;
use Workerman\Protocols\Http;

include(__DIR__ .'/include/conf.php');
include(__DIR__.'/migmail.php');

$http_worker = new Worker("http://".$configuracion->ip.":".$configuracion->puerto);
$http_worker->count = $configuracion->hilos;

$http_worker->onMessage = function($connection, $data){

    
    $get = $data->get();

    if(isset($get['send'])){

        $cc1='credentials.json';
        $cc2='token.json';

        $post = $data->post();

        if(isset($post['user'])){

            $cc1=$post['user'].'.credentials.json';
            $cc2=$post['user'].'.token.json';

        }

        $g = new MiGmail($cc1,$cc2);
        $res = $g->validateANDsend($post);

        $connection->send(json_encode(array('Respuesta'=>$res)));

    }else{

        $connection->send(json_encode(array('Respuesta'=>'Servicio OK')));
        
    }

};

// run all workers
Worker::runAll();