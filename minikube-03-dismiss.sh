#!/bin/bash
. $(multiwerf use 1.1 stable --as-file)

source ./minikube-def.sh

werf dismiss --env $ENVNAME --with-namespace
