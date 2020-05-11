#!/bin/bash
docker build --tag gmail:1.0
docker run -d -p 8020:8020 -v {local}:/gmail/credenciales -v {local}:/gmail/tokens --name gmail gmail:1.0