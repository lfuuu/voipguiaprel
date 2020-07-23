#!/bin/bash
. $(multiwerf use 1.1 stable --as-file)

source ./k8s-def.sh

werf dismiss --dir ../ --env $ENVNAME --with-namespace

