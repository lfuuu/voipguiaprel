#!/bin/bash
. $(multiwerf use 1.1 stable --as-file)

source ./k8s-def.sh

werf deploy --kube-config=$KUBE_CONFIG --env=$ENVNAME --dir ../ --stages-storage :local \
--images-repo-implementation='harbor' --insecure-registry=true \
--skip-tls-verify-registry=true -i=$REGISTRY/$PROJECT/$APPNAME \
--tag-custom $TAG --set ci_url=$CI_URL

echo "Сервис доступен по адресу http://$CI_URL"
