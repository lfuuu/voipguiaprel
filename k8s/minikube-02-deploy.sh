#!/bin/bash
. $(multiwerf use 1.1 stable --as-file)

set -e

source ./minikube-def.sh

werf deploy --dir ../ --stages-storage :local --images-repo :minikube --tag-custom $APPNAME --env $ENVNAME --log-debug=true 

# TODO: Сделать, если строчка есть, что бы ничего не менялось и пароль не спрашивало лишний раз

sudo sed -i -e '/^.*'$APPNAME'-'$ENVNAME'\.local$/d' /etc/hosts
echo `minikube ip`" $APPNAME-$ENVNAME.local" | sudo tee -a /etc/hosts
echo "Сервис доступен по адресу http://$APPNAME-$ENVNAME.local"

if [ $ENVNAME = "prod" ]; then
echo "Это прод"
else
  sudo sed -i -e '/^.*pgadmin-'$APPNAME'-'$ENVNAME'\.local$/d' /etc/hosts
  echo `minikube ip`" pgadmin-$APPNAME-$ENVNAME.local" | sudo tee -a /etc/hosts
  echo "pgadmin доступен по адресу http://pgadmin-$APPNAME-$ENVNAME.local"
fi

