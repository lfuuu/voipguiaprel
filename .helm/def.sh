# ENVNAME 
#   prod - конфигурация "Продакшин", приложение смотрит на боевой сервер.
#   dev* - конфигурация для разработки, запускаются база и пгадмин 


TAG=1.365
APPNAME=voip-gui


function dev()
{
	export ENVNAME=dev
	CI_URL="$APPNAME-$ENVNAME.local"
        PGADMIN_IN_DEV="yes"
}

function prodenvci()
{
    MINIKUBE_IP=`minikube ip`
    export ENVNAME="prod-env-ci"
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
	CI_URL="voipgui.mcntele.com"
}
