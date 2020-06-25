#!/bin/bash
. $(multiwerf use 1.1 stable --as-file)

source ./minikube-def.sh

werf dismiss --dir ../ --env $ENVNAME --with-namespace
