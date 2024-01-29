# ENVNAME 
#   prod - конфигурация "Продакшин", приложение смотрит на боевой сервер.
#   dev* - конфигурация для разработки, запускаются база и пгадмин 


TAG=1.365
APPNAME=voip-gui





function dev_kvm_envci()
{
    export ENVNAME="dev"
    CI_URL="$APPNAME.192.168.122.69.nip.io"
    PGADMIN_IN_DEV="yes"
}

function dev_minikube_envci()
{
    MINIKUBE_IP=`minikube ip`
    export ENVNAME="dev"
    CI_URL="$APPNAME-$ENVNAME.$MINIKUBE_IP.nip.io"
    PGADMIN_IN_DEV="yes"
}





function stage()
{
	export ENVNAME=stage
	CI_URL="$APPNAME-$ENVNAME.mcn.ru"
}

function vagrant()
{
	export ENVNAME=vagrant
	CI_URL="$APPNAME-$ENVNAME.mcn.ru"
}

function prod()
{
	export ENVNAME=prod
	CI_URL="voipgui.mcn.ru"
}

function euprod()
{
	export ENVNAME=euprod
	CI_URL="voipgui.kompaas.tech"
}
