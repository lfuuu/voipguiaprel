#!/bin/bash

. $(multiwerf use 1.1 stable --as-file)

CI_URL=voipgui.mcnhost.ru
USE_NGNIX_VIRTUALSERVER=yes
TAG=1.01

source ./def.sh
env=$1
$env
export ENVNAME=${1:-"prod"}

werf helm lint --dir ../ --env $ENVNAME --set ci_url=$CI_URL --set use_ngnix_virtualserver=$USE_NGNIX_VIRTUALSERVER
werf helm render --dir ../  --tag-custom $TAG --env $ENVNAME --set ci_url=$CI_URL --set use_ngnix_virtualserver=$USE_NGNIX_VIRTUALSERVER

#--images-repo :local
