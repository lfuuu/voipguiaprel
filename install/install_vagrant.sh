#!/bin/bash
set -e

cd /vagrant

composer global require "fxp/composer-asset-plugin:~1.0.0"
composer install

cd -

exit 0
