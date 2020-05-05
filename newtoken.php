<?php
/**
 * 
 */

require __DIR__ . '/vendor/autoload.php';

if (php_sapi_name() != 'cli') {
    throw new Exception('This application must be run on the command line.');
}

/**
 * Devuelve un cliente API autorizado.
 * @return Google_Client the authorized client object
 */
function getClient($credencial='credentials.json')
{
    $client = new Google_Client();
    $client->setApplicationName('Gmail API PHP Quickstart');
    $client->setScopes(Google_Service_Gmail::GMAIL_READONLY);
    $client->setAuthConfig(__DIR__.'/credenciales/'.$credencial);
    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');

    // Cargue el token previamente autorizado de un archivo, si existe.
    // El archivo token.json almacena los tokens de acceso y actualización del usuario, y es
    // creado automáticamente cuando se completa el flujo de autorización para el primer
    // tiempo.
    $tokenPath = 'token.json';
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


// Obtenga el cliente API y construya el objeto de servicio.
$client = getClient();
$service = new Google_Service_Gmail($client);

// Imprima las etiquetas en la cuenta del usuario.
$user = 'me';
$results = $service->users_labels->listUsersLabels($user);

if (count($results->getLabels()) == 0) {
  print "No labels found.\n";
} else {
  print "Labels:\n";
  foreach ($results->getLabels() as $label) {
    printf("- %s\n", $label->getName());
  }
}