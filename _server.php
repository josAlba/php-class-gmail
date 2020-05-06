<?php
/**
 * 
 * Crea un servicio por http, para el envio de correos a traves de gmail
 * 
 * @author JosAlba
 */

require __DIR__ . '/vendor/autoload.php';

use Workerman\Worker;
use Workerman\Protocols\Http;

include(__DIR__ .'/include/conf.php');
include(__DIR__.'/migmail.php');

$http_worker = new Worker("http://".$configuracion->ip.":".$configuracion->puerto);
$http_worker->count = $configuracion->hilos;

//Recive las peticiones.
$http_worker->onMessage = function($connection, $data){

    /**
     * Almacena los parametros GET recividos en la peticion
     * @var array GET
     */
    $get = $data->get();

    //Comprueba que se le a pasado la accion "send"
    if(isset($get['send'])){

        /**
         * Credencial por defecto
         * @var string Ruta credencial
         */
        $cc1='credentials.json';
        /**
         * Token por defecto
         * @var string Ruta token
         */
        $cc2='token.json';

        /**
         * Almacena los parametros POST recividos en la peticion
         * @var array POST
         */
        $post = $data->post();

        //Comprueba que usuario se le a pedido.
        if(isset($post['user'])){

            //Sustituye las credenciales y token por las del usuario.
            $cc1=$post['user'].'.credentials.json';
            $cc2=$post['user'].'.token.json';

        }

        //Creamos la clase
        $g = new MiGmail($cc1,$cc2);
        //Enviamos los parametros del POST para enviar el correo.
        $res = $g->validateANDsend($post);

        //Devolvemos una respuesta para finalizar la peticion.
        $connection->send(json_encode(array('Respuesta'=>$res)));

    }else{

        //En caso de no contener parametros solo informamos que el servicio esta ok.
        $connection->send(json_encode(array('Respuesta'=>'Servicio OK')));
        
    }

};

// run all workers
Worker::runAll();