#!/bin/bash

THIS=`readlink -f "${BASH_SOURCE[0]}"`
DIR=`dirname "${THIS}"`


if [ "$ENVNAME" = "dev" ]; then

cd $DIR
cp ~/.ssh/id_rsa .werf/tmp/id_rsa
cp ~/.pgpass .werf/tmp/.pgpass

echo забираем айпишник базы из хостов и пихаем в мапку 

export PG_IP=$(cat /etc/hosts | grep b_db | awk '{print $1}')
yq eval -i ".postgres_env_ci.host = \"$PG_IP\"" values.yaml

fi
