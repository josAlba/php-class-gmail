<?php
/**
 * Clase principal para gestionar el mail
 * 
 * @author JosAlba
 */

//Comprobamos si existe la clase "PHPMailer", sino la incluimos
if (!class_exists('PHPMailer')) {
    include(__DIR__.'/phpmail.php');
}

/**
 * Clase principal para trabajar con gmail.
 */
class MiGmail{

    /**
     * Almacena la ruta de la credencial
     * @access public
     * @var string
     */
    public $credencial;
    /**
     * Almacena la ruta del token
     * @access public
     * @var string
     */
    public $token;

    public function __construct($credencial='credentials.json',$token='token.json'){
        $this->credencial   =$credencial;
        $this->token        =$token;
    }

    /**
     * Genera un nuevo token a partir de las credenciales.
     */
    public function getClient(){

        $credencial =$this->credencial;
        $token      =$this->token;

        $client = new Google_Client();
        $client->setApplicationName('MiGmail');
        //$client->setScopes(Google_Service_Gmail::GMAIL_READONLY);
        $client->setScopes(Google_Service_Gmail::GMAIL_SEND);
        $client->setAuthConfig(__DIR__.'/credenciales/'.$credencial);
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        // Cargue el token previamente autorizado de un archivo, si existe.
        // El archivo token.json almacena los tokens de acceso y actualización del usuario, y es
        // creado automáticamente cuando se completa el flujo de autorización para el primer
        // tiempo.
        $tokenPath = __DIR__.'/tokens/'.$token;
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

        // Si no hay token anterior o está caducado.
        if ($client->isAccessTokenExpired()) {
            // Actualiza el token si es posible, de lo contrario, busca uno nuevo.
            if ($client->getRefreshToken()) {
                $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
            } else {
                // Solicitar autorización de la usuaria.
                $authUrl = $client->createAuthUrl();
                printf("Open the following link in your browser:\n%s\n", $authUrl);
                print 'Enter verification code: ';
                $authCode = trim(fgets(STDIN));

                // Código de autorización de intercambio para un token de acceso.
                $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
                $client->setAccessToken($accessToken);

                // Verifique si hubo un error.
                if (array_key_exists('error', $accessToken)) {
                    throw new Exception(join(', ', $accessToken));
                }
            }
            // Guarde el token en un archivo.
            if (!file_exists(dirname($tokenPath))) {
                mkdir(dirname($tokenPath), 0700, true);
            }
            file_put_contents($tokenPath, json_encode($client->getAccessToken()));
        }
        return $client;
    }
    /**
     * Envia el mail
     * @access public
     * @param string $asunto Asunto del mail
     * @param string $cuerpo Cuerpo del mensaje ( html )
     * @param string $email Email del remitente ( from )
     * @param array $destinatario Destinatarios del mail ( to )
     * @param array $copia Personas en copia en el mail
     * @param string $nombre Nombre del remitente
     * @return int Indica si habido algun error.
     */
    public function sendMail($asunto="",$cuerpo="",$email="",$destinatario=[],$copia=[],$nombre="",$responder="",$file=[]){

        $client     = $this->getClient();
        $service    = new Google_Service_Gmail($client);

        try {

            $mail = new PHPMailer();
            $mail->CharSet  = "UTF-8";
            $mail->From     = $email;
            $mail->FromName = $nombre;

            for($i=0;$i<count($destinatario);$i++){
                $mail->AddAddress($destinatario[$i]);
            }
            if(count($copia)>0){

                for($i=0;$i<count($copia);$i++){
                    $mail->addCC($copia[$i]);
                }
               
            }

            if($responder!=''){
                $mail->AddReplyTo($responder);
            }

            if(count($file)!=0){

                $adjunto=true;
                for($i=0;$i<count($file);$i++){

                    if(!isset($file[$i]['file'])){
                        $adjunto=false;
                    }
                    if(!isset($file[$i]['name'])){
                        $adjunto=false;
                    }

                    if($adjunto==true){

                        $mail->addAttachment($file[$i]['file'],$file[$i]['name']);
                    }
                }

            }
            
            $mail->isHTML(true);
            $mail->Subject  = $asunto;
            $mail->Body     = $cuerpo;

            $mail->preSend();
            $mime = $mail->getSentMIMEMessage();
            $mime = rtrim(strtr(base64_encode($mime), '+/', '-_'), '=');
            $mensaje = new Google_Service_Gmail_Message();
            $mensaje->setRaw($mime);
            $service->users_messages->send('me', $mensaje);
            $r = 1;

        } catch (Exception $e) {
            print $e->getMessage();
            $r = 0;
        }

        return $r;

    }
    /**
     * Valida los datos del post
     * @access public
     * @param array $post Datos del formulario
     * @return int Valor arrastrado del envio del mail.
     */
    public function validateANDsend($post){

        $asunto         ='';
        $cuerpo         ='';
        $email          ='';
        $destinatario   =[];
        $copia          =[];
        $nombre         ='';
        $responder      ='';

        if(
            !isset($post['a']) ||
            !isset($post['b']) ||
            !isset($post['e']) ||
            !isset($post['d']) ||
            !isset($post['n'])
        ){
            return 0;
        }

        $asunto         =$post['a'];
        $cuerpo         =$post['b'];
        $email          =$post['e'];
        $destinatario   =[];
        $copia          =[];
        $nombre         =$post['n'];

        $d = explode(',',$post['d']);
        for($i=0;$i<count($d);$i++){
            $destinatario[]= $d[$i];
        }

        if(isset($post['c'])){

            $c = explode(',',$post['c']);
            for($i=0;$i<count($d);$i++){
                $copia[]= $c[$i];
            }

        }else{
            $c=[];
        }

        if(isset($post['r'])){
            $responder=$post['r'];
        }

        $adjuntos=[];
        if(isset($post['f'])){
            $archivosADJ=$post['f'];

            $exADJ = explode(',',$archivosADJ);
            for($i=0;$i<count($exADJ);$i++){

                $tmpADJ = explode(';',$exADJ[$i]);

                $hoy = date("Y-m-d H:i:s");
                $archivoTemporal=__DIR__.'/tmp/'.md5($hoy).'-'.$tmpADJ[1];

                $pos = strpos($tmpADJ[0], 'http');
                if ($pos === false) {
                    copy($tmpADJ[0],$archivoTemporal);
                }else{
                    shell_exec(' wget -O '.$archivoTemporal.' '.$tmpADJ[0]);
                }
                
                if(file_exists($archivoTemporal)){

                    $adjunto[]=array(
                        'file'=>$archivoTemporal,
                        'name'=>$tmpADJ[1]
                    );
                }

            }
        }

        echo "\n Enviando desde ".$email." a ".$post['d'];
        $this->sendlog($post['d'],$email);

        return $this->sendMail($asunto,$cuerpo,$email,$destinatario,$copia,$nombre,$responder,$adjunto);

    }
    /**
     * Almacena un log de correos eviados
     * @access private
     * @param string $mail Direccion del envio ( to )
     * @param string $desde Direccion remitente ( from )
     */
    private function sendlog($mail,$desde){
        $ddf = fopen('send.log','a');
        fwrite($ddf,"[".date("r")."] Enviado mail de $desde a $mail\r\n");
        fclose($ddf);
    } 

}