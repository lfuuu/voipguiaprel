#!/bin/bash

rm ../.werf/tmp/id_rsa
rm ../.werf/tmp/.pgpass

if [ $ENVNAME = "dev" ]; then
    echo "Копируем ключ ssh"
    PODNAME="$APPNAME-backend-dev-0"
    NAMESPACE="$APPNAME-$ENVNAME"
    kubectl -n $NAMESPACE wait --for=condition=ready --timeout=120s pods $PODNAME
    kubectl -n $NAMESPACE exec -it $PODNAME -- /bin/bash /root/prepare-scripts/copy_id_rsa.sh `base64 -w 0 ~/.ssh/id_rsa`
    kubectl -n $NAMESPACE exec -it $PODNAME -- /bin/bash /root/prepare-scripts/init-dev-env.sh
fi
