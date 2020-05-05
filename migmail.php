<?php

if (!class_exists('PHPMailer')) {
    include(__DIR__.'/phpmail.php');
}

/**
 * Clase principal para trabajar con gmail.
 */
class MiGmail{

    public $credencial;
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

    public function sendMail($asunto="",$cuerpo="",$email="",$destinatario=[],$copia=[],$nombre=""){

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
                    $mail->AddReplyTo($copia[$i]);
                }
            }
            
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

    public function validateANDsend($post){

        $asunto         ='';
        $cuerpo         ='';
        $email          ='';
        $destinatario   =[];
        $copia          =[];
        $nombre         ='';

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

        echo "\n Enviando a ".$email;

        return $this->sendMail($asunto,$cuerpo,$email,$destinatario,$copia,$nombre);

    }

}