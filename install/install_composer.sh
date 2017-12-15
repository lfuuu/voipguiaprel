#!/bin/bash
set -e

DIRROOT=$(cd $(dirname $0) && pwd)

pushd ${DIRROOT}/..

curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
composer global require "fxp/composer-asset-plugin"
composer install

popd 

exit 0
