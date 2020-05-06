# PHP Gmail
Servicio http para enviar mails a traves de gmail.

## Directorios

Crear los siguientes directorios en la raiz del proyecto.

```
mkdir credenciales
mkdir tokens
```

## Configurar el servidor

Modificar conf.json asignando la ip local y el puerto que quereis usar.

```
{
    "ip": "10.0.3.247",
    "puerto": "8020",
    "hilos": 4
}

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

## Lanzar peticion

Peticion para ver el servidor
```
curl 10.0.0.189:8020
```

Peticion para enviar un mail
```
curl -d "a=prueba&b=<h1>hola</h1>&e={tu mail}&d={email destinatario}&n=yo&user={nombre credencial}" 10.0.0.189:8020?send=1
```

Hay que pasar "send" por GET y el contenido del correo por POST

### Parametros

#### POST
- a = Asunto
- b = Cuerpo
- e = Email
- d = Destinatario ( se puede añadir más de uno separandolos por , )
- c = Emails en copia ( se puede añadir más de uno separandolos por , )
- n = Nombre

> Para pasar el cuerpo en html de forma corecta si se hace desde php es recomendado usar la funcion urlencode() , de esta manera el html se enviara sin problemas. ( debe estar codificado para url ).

#### GET
- send = Indica que es un mail