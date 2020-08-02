#!/bin/bash
. $(multiwerf use 1.1 stable --as-file)

set -e

source ./minikube-def.sh

cp ~/.ssh/id_rsa ../.helm/.werf/tmp/id_rsa
cp ~/.pgpass ../.helm/.werf/tmp/.pgpass
werf deploy --dir ../ --stages-storage :local --images-repo :minikube --tag-custom $TAG --env $ENVNAME --set ci_url=$CI_URL

rm ../.helm/.werf/tmp/id_rsa
rm ../.helm/.werf/tmp/.pgpass

# TODO: Сделать, если строчка есть, что бы ничего не менялось и пароль не спрашивало лишний раз

if [ $ENVNAME = "dev" ]; then
    echo "Копируем ключ ssh"
    PODNAME="$APPNAME-backend-dev-0"
    NAMESPACE="$APPNAME-$ENVNAME"
    kubectl -n $NAMESPACE wait --for=condition=ready --timeout=120s pods $PODNAME
    kubectl -n $NAMESPACE exec -it $PODNAME -- /bin/bash /root/prepare-scripts/copy_id_rsa.sh `base64 -w 0 ~/.ssh/id_rsa`
    kubectl -n $NAMESPACE exec -it $PODNAME -- /bin/bash /root/prepare-scripts/init-dev-env.sh
fi

sudo sed -i -e '/^.*'$CI_URL'\.local$/d' /etc/hosts
echo `minikube ip`" $CI_URL" | sudo tee -a /etc/hosts
echo "Сервис доступен по адресу http://$CI_URL"

if [ $ENVNAME = "prod" ]; then
echo "Это прод"
else
  sudo sed -i -e '/^.*pgadmin-'$APPNAME'-'$ENVNAME'\.local$/d' /etc/hosts
  echo `minikube ip`" pgadmin-$APPNAME-$ENVNAME.local" | sudo tee -a /etc/hosts
  echo "pgadmin доступен по адресу http://pgadmin-$APPNAME-$ENVNAME.local"
fi

