#!/bin/sh

RETVAL=0

test_config_exist() {

    if [ ! -f "./conf/db-local.php" ];     then   echo "Ошибка: Настройте ваш conf/db-local.php" ;      exit 1; fi

    if [ ! -f "./conf/log-local.php" ];    then   echo "Ошибка: Настройте ваш conf/log-local.php";      exit 1; fi

    if [ ! -f "./conf/params-local.php" ]; then   echo "Ошибка: Настройте ваш conf/params-local.php";   exit 1; fi

}

usage ()
{
	echo $"Usage: $0 {git|volume}" 1>&2
	RETVAL=2
}

build_git ()
{
   cp ~/.ssh/id_rsa tmp/id_rsa
   docker build --rm -t voip_gui_git -f Dockerfile-git .
   rm tmp/id_rsa
}


build_volume()
{
   docker build --rm -t voip_gui_volume  -f Dockerfile-volume .
}


case "$1" in
    git)      build_git ;;
    volume)   build_volume ;;
    *)   usage ;;
esac

