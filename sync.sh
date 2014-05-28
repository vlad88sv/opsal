#!/bin/bash
#. sync.txt.sh
#. beta.sync.sh
rsync  --compress-level=9 -v --exclude '*.psd' --exclude '*.xcf' --exclude '*.git' --rsh='ssh -p22' -a /home/vladimir/Documentos/Codigo/opsal.com/ root@72.14.176.185:/var/www/sistemaopsal.tk
