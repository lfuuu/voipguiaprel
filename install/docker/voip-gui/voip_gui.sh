#!/bin/sh

VOLUME=$(cd $(dirname $0) && cd ../../.. && pwd)

usage ()
{
	echo $"Usage: $0 {run_{git|volume}|install_composer|connect_{git|volume}|commit_{git|volume}}" 1>&2
	RETVAL=2
}

run_git ()
{
  docker run --dns 8.8.8.8 -p 127.0.0.1:8001:80 --name voip_gui_git \
       -d -i -t voip_gui_git
}


run_v()
{
  docker run --dns 8.8.8.8 -p 127.0.0.1:8000:80 --name voip_gui_volume -v $VOLUME:/opt/voip_gui \
       -d -i -t voip_gui_volume
}

install_composer () 
{
  
  docker exec -it voip_gui_volume /opt/voip_gui/install/install_composer.sh
}

connect_git () 
{
  docker exec -it voip_gui_git /bin/bash
}

connect_volume () 
{
  docker exec -it voip_gui_volume /bin/bash
}

commit_volume () 
{
 docker commit voip_gui_volume voip_gui_volume
} 

commit_git () 
{
 docker commit voip_gui_git voip_gui_git
} 

case "$1" in
    run_git)      	run_git ;;
    run_volume)   	run_v ;;
    install_composer)		install_composer ;;
    connect_git)	connect_git ;;
    connect_volume)	connect_volume ;;
    commit_volume)	commit_volume ;;
    commit_git)		commit_git ;;
    *)        usage ;;
esac

