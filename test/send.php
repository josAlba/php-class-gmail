<?php
/**
 * Enviar mail usando el servicio migmail
 * El html del contenido se tiene que enviar como urlencode para no dar problemas con las etiquetas.
 * 
 * en este fragmento hace la peticion por curl desde php
 */

if(true==true){

	$ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, '10.0.0.105:8020?send=1');
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_POSTFIELDS, 'a={asunto}&b='.urlencode({variable con el html del mensaje}).'&e={mail de quien lo envia}&d={destinatario}&n={nombre}&user={cuenta}'); 
	$head = curl_exec($ch); 
	curl_close($ch); 

	return;
}
