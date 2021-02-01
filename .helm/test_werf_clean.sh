#!/bin/bash

. $(multiwerf use 1.1 stable --as-file)


source ./def.sh

env=$1
$env
export ENVNAME=${1:-"dev"}


werf cleanup --dir ../ --stages-storage :local --images-repo werf-registry.kube-system.svc.cluster.local
werf stages purge --dir ../ --force --stages-storage=:local
