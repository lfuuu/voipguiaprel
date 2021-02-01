#!/bin/bash

. $(multiwerf use 1.1 stable --as-file)

source ./def.sh

env=$1
$env
export ENVNAME=${1:-"dev"}

werf build --dir ../ --stages-storage :local

