#!/bin/bash
. $(multiwerf use 1.1 stable --as-file)

source ./minikube-def.sh

werf deploy --stages-storage :local --images-repo :minikube --tag-custom $APPNAME --env $ENVNAME

# TODO: Сделать, если строчка есть, что бы ничего не менялось и пароль не спрашивало лишний раз

sudo sed -i -e '/^.*'$APPNAME'-'$ENVNAME'\.local$/d' /etc/hosts
echo `minikube ip`" $APPNAME-$ENVNAME.local" | sudo tee -a /etc/hosts

echo "Сервис доступен по адресу http://$APPNAME-$ENVNAME.local"
