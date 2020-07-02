#!/bin/bash
. $(multiwerf use 1.1 stable --as-file)

source ./k8s-def.sh

werf deploy --kube-config=$KUBE_CONFIG --env=dev --dir ../ --stages-storage :local \
--images-repo-implementation='harbor' --insecure-registry=true \
--skip-tls-verify-registry=true -i=$REGISTRY/$PROJECT/$APPNAME \
--tag-custom $TAG

sudo sed -i -e '/^.*'$APPNAME'-'$ENVNAME'\.remote$/d' /etc/hosts
echo "$HOST_IP $APPNAME-$ENVNAME.remote" | sudo tee -a /etc/hosts

echo "Сервис доступен по адресу http://$APPNAME-$ENVNAME.remote"