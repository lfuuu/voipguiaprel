# ENVNAME 
#   prod - конфигурация "Продакшин", приложение смотрит на боевой сервер.
#   dev* - конфигурация для разработки, запускаются база и пгадмин 


TAG=1.250
APPNAME=voip-gui


function dev()
{
	ENVNAME=dev
	CI_URL="$APPNAME-$ENVNAME.local"
        PGADMIN_IN_DEV="yes"
}

function stage()
{
	ENVNAME=stage
	CI_URL="$APPNAME-$ENVNAME.mcn.ru"
}

function vagrant()
{
	ENVNAME=vagrant
	CI_URL="$APPNAME-$ENVNAME.mcn.ru"
}

function prod()
{
	ENVNAME=prod
	CI_URL="voipgui.mcn.ru"
}

