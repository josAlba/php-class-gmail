# Lanzar
Modificar install.sh para poner las rutas de las credenciales.

## Comando para crear la imagen
> docker build --tag gmail:1.0 .
## Comando para lanzar el docker
Para poder subir las credenciales al servicio es necesario enlazar los directorios
> sudo docker run -d -p 8020:8020 -v {local}:/gmail/credenciales -v {local}:/gmail/tokens --name gmail gmail:1.0