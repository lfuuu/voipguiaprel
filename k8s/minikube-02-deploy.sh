#!/bin/bash
. $(multiwerf use 1.1 stable --as-file)

set -e

source ./minikube-def.sh

cp ~/.ssh/id_rsa ../.helm/.werf/tmp/id_rsa

werf deploy --dir ../ --stages-storage :local --images-repo :minikube --tag-custom $TAG --env $ENVNAME

rm ../.helm/.werf/tmp/id_rsa

# TODO: Сделать, если строчка есть, что бы ничего не менялось и пароль не спрашивало лишний раз

if [ $ENVNAME = "dev" ]; then
    echo "Копируем ключ ssh"
    PODNAME="$APPNAME-backend-dev-0"
    NAMESPACE="$APPNAME-$ENVNAME"
    kubectl -n $NAMESPACE wait --for=condition=ready --timeout=120s pods $PODNAME
    kubectl -n $NAMESPACE exec -it $PODNAME -- /bin/bash /root/prepare-scripts/copy_id_rca.sh `base64 -w 0 ~/.ssh/id_rsa`
    kubectl -n $NAMESPACE exec -it $PODNAME -- /bin/bash /root/prepare-scripts/init-dev-env.sh
fi

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

