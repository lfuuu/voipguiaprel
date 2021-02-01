#!/bin/bash

THIS=`readlink -f "${BASH_SOURCE[0]}"`
DIR=`dirname "${THIS}"`

pushd $DIR

### Эта команда прописывает пользоватлей в базе данных. но это делается автоматически при инициализации контейнера
###./nispd_util.sh initDB

./nispd_util.sh makeSQLDumpFromProd
./nispd_util.sh createCentralNISPD

popd