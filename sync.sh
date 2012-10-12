#!/bin/bash
#. sync.txt.sh
#. beta.sync.sh
rsync --compress-level=9 -v --exclude '*.psd' --exclude '*.xcf' --exclude '*.git' -a /home/vladimir/Documentos/Codigo/opsal.com/ root@173.255.192.4:/var/www/sistemaopsal.tk
