#!/bin/bash

THIS=`readlink -f "${BASH_SOURCE[0]}"`
DIR=`dirname "${THIS}"`
cd $DIR

source ./def.sh

env=$1
$env

if [ "$ENVNAME" = "dev" ]; then

    rm -rf .werf/tmp/id_rsa
    rm -rf .werf/tmp/.pgpass

fi
