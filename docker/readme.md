# Lanzar

Para poder subir las credenciales al servicio es necesario enlazar los directorios
> sudo docker run -d -p 8020:8020 -v {local}:/gmail/crenciales -v {local}:/gmail/crenciales --name gmail gmail:1.0