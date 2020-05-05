# PHP Gmail
Servicio http para enviar mails a traves de gmail.

## Directorios

Crear los siguientes directorios en la raiz del proyecto.

```
mkdir credenciales
mkdir tokens
```

## Crear credenciales

> https://developers.google.com/gmail/api/quickstart/php

## Generar un token

Guardamos la credencial como {nombre}.credentials.json en la carpeta "credenciales". Y ejecutamos el siguiente comando.

```

php _newtoken.php {nombre}

```

Esto generara un {nombre}.token.json en el directorio "tokens"

## Iniciar el servicio

Para iniciar el servicio modo test.

```

php _server.php start

```

Para iniciar el servicio modo normal.

```

php _server.php start -d

```

Para detener el servicio.

```

php _server.php stop

```

Para reiniciar el servicio.

```

php _server.php restart

```