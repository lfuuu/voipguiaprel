#!/bin/bash
set -e

DIRROOT=$(cd $(dirname $0) && pwd)

pushd ${DIRROOT}/..

curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer
composer global require "fxp/composer-asset-plugin"
composer install

 chown -R apache:apache web \
  &&  chmod 777 runtime \
  &&  chmod 777 web/assets \

popd 

exit 0
