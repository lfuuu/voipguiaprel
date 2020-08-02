# ENVNAME 
#   prod - конфигурация "Продакшин", приложение смотрит на боевой сервер.
#   dev* - конфигурация для разработки, запускаются база и пгадмин 

APPNAME=voip-gui
ENVNAME=dev
CI_URL="$APPNAME-$ENVNAME.local"
TAG=1.129

