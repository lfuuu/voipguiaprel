#!/bin/bash
. $(multiwerf use 1.1 stable --as-file)

set -e

source ./minikube-def.sh

werf helm lint --dir ../ --env $ENVNAME --set ci_url=$CI_URL 
werf helm render --dir ../ --images-repo :minikube --tag-custom $TAG --env $ENVNAME --set ci_url=$CI_URL 
