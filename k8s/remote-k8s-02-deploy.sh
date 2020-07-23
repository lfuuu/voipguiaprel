#!/bin/bash
. $(multiwerf use 1.1 stable --as-file)

source ./k8s-def.sh

werf deploy --kube-config=$KUBE_CONFIG --env=$ENVNAME --dir ../ --stages-storage :local \
--images-repo-implementation='harbor' --insecure-registry=true \
--skip-tls-verify-registry=true -i=$REGISTRY/$PROJECT/$APPNAME \
--tag-custom $TAG

echo "Сервис доступен по адресу http://voip-gui.k8s-test.mcn.loc/login"
