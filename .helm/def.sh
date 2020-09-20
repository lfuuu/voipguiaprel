# ENVNAME 
#   prod - конфигурация "Продакшин", приложение смотрит на боевой сервер.
#   dev* - конфигурация для разработки, запускаются база и пгадмин 

function dev()
{
	APPNAME=voip-gui
	ENVNAME=dev
	CI_URL="$APPNAME-$ENVNAME.local"
	TAG=1.137
        PGADMIN_IN_DEV="yes"
}

function stage()
{
	APPNAME=voip-gui
	ENVNAME=stage
	CI_URL="$APPNAME-$ENVNAME.mcn.ru"
	TAG=1.137
}

function vagrant()
{
	APPNAME=voip-gui
	ENVNAME=vagrant
	CI_URL="$APPNAME-$ENVNAME.mcn.ru"
	TAG=1.137
}

function prod()
{
	APPNAME=voip-gui
	ENVNAME=prod
	CI_URL="voipgui2.mcn.ru"
	TAG=1.137
}

