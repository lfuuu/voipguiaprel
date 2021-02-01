#!/bin/bash

THIS=`readlink -f "${BASH_SOURCE[0]}"`
DIR=`dirname "${THIS}"`
cd $DIR

rm -rf .werf/tmp/id_rsa
rm -rf .werf/tmp/.pgpass

source ./def.sh

env=$1
$env

if [ "$ENVNAME" = "dev" ]; then
    echo "-- Подготовка workspace"
    PODNAME="$APPNAME-backend-dev-0"
    NAMESPACE="$APPNAME-$ENVNAME"
    kubectl -n $NAMESPACE wait --for=condition=ready --timeout=120s pods $PODNAME
    kubectl -n $NAMESPACE exec -it $PODNAME -c php-fpm -- /bin/bash /root/prepare-scripts/init-dev-env.sh
fi
