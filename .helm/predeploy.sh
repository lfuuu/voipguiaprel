#!/bin/bash

THIS=`readlink -f "${BASH_SOURCE[0]}"`
DIR=`dirname "${THIS}"`


if [ "$ENVNAME" = "dev" ]; then

cd $DIR
cp ~/.ssh/id_rsa .werf/tmp/id_rsa
cp ~/.pgpass .werf/tmp/.pgpass

fi
