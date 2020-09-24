# ENVNAME 
#   prod - конфигурация "Продакшин", приложение смотрит на боевой сервер.
#   dev* - конфигурация для разработки, запускаются база и пгадмин 


	TAG=1.160

function dev()
{
	APPNAME=voip-gui
	ENVNAME=dev
	CI_URL="$APPNAME-$ENVNAME.local"
        PGADMIN_IN_DEV="yes"
}

function stage()
{
	APPNAME=voip-gui
	ENVNAME=stage
	CI_URL="$APPNAME-$ENVNAME.mcn.ru"
}

function vagrant()
{
	APPNAME=voip-gui
	ENVNAME=vagrant
	CI_URL="$APPNAME-$ENVNAME.mcn.ru"
}

function prod()
{
	APPNAME=voip-gui
	ENVNAME=prod
	CI_URL="voipgui2.mcn.ru"
}

