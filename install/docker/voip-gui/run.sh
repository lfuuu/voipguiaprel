#!/bin/sh

#docker volume create --name=voip-gui-src

#docker run --dns 8.8.8.8 -p 127.0.0.1:8000:80 \
#       -d -i -t  voip_gui

#docker run -it --dns 8.8.8.8 -p 127.0.0.1:8000:80 voip_gui

docker run --dns 8.8.8.8 -p 127.0.0.1:8000:80 \
       -d -i -t  voip_gui
