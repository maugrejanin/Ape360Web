#!/bin/bash
echo "$(date +'%h %d %H:%M:%S') - ComunicadoImediato - Inicio" >> ./cron_log.txt
curl https://apps.eurekaria.com/hering/www/server/Servico/start/ComunicadoImediato
echo "$(date +'%h %d %H:%M:%S') - ComunicadoImediato - Fim" >> ./cron_log.txt