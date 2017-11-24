#!/bin/sh


echo "Собираем образ voip-gui"

[ ! -f "conf/db-local.php" ] &&  echo "Ошибка: Настройте Ваш conf/db-local.php" ; exit 1
[ ! -f "conf/log-local.php" ] &&  echo "Ошибка: Настройте Ваш conf/log-local.php" ; exit 1
[ ! -f "conf/params-local.php" ] &&  echo "Ошибка: Настройте Ваш conf/params-local.php" ; exit 1

cp ~/.ssh/id_rsa tmp/id_rsa
docker build --rm -t voip_gui .
rm tmp/id_rsa
