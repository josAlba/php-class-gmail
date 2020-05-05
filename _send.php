<?php

require __DIR__ . '/vendor/autoload.php';

include(__DIR__.'/migmail.php');

$asunto         ='';
$cuerpo         ='';
$email          ='';
$destinatario   =[];
$copia          =[];
$nombre         ='';

if(
    !isset($_POST['a']) ||
    !isset($_POST['b']) ||
    !isset($_POST['e']) ||
    !isset($_POST['d']) ||
    !isset($_POST['n']) ||
    !isset($_POST['c'])
){
    echo json_encode('K.O');
}

$asunto         =$_POST['a'];
$cuerpo         =$_POST['b'];
$email          =$_POST['e'];
$destinatario   =[];
$copia          =[];
$nombre         =$_POST['n'];

$d = explode(',',$_POST['d']);
for($i=0;$i<count($d);$i++){
    $destinatario[]= $d[$i];
}

$c = explode(',',$_POST['c']);
for($i=0;$i<count($d);$i++){
    $copia[]= $c[$i];
}

$g = new MiGmail();
$g->sendMail($asunto,$cuerpo,$email,$destinatario,$copia,$nombre);

echo json_encode('OK');