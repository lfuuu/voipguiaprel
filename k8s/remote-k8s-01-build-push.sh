#!/bin/bash

. $(multiwerf use 1.1 stable --as-file)

source ./k8s-def.sh

werf build-and-publish --kube-config=$KUBE_CONFIG --dir ../ --stages-storage :local \
--images-repo-implementation='harbor' --insecure-registry=true \
--skip-tls-verify-registry=true -i=$REGISTRY/$PROJECT/$APPNAME \
--tag-custom $TAG
