#!/bin/bash
set -e

cd /vagrant/install

add-apt-repository -y ppa:ondrej/php5
apt-get update
apt-get install -y mc git
export DEBIAN_FRONTEND=noninteractive
apt-get install -y apache2 
apt-get install -y php5 php5-cli php5-curl php5-gd php5-json php5-mcrypt php5-mysqlnd php5-pgsql php5-readline php5-xdebug php5-xmlrpc
pwd
cp ./apache.default.conf /etc/apache2/sites-available/000-default.conf
cp ./php.ini /etc/php5/apache2/php.ini
cp ./php.ini /etc/php5/cli/php.ini
sed 's/www-data/vagrant/g' /etc/apache2/envvars > /etc/apache2/envvars2
mv /etc/apache2/envvars2 /etc/apache2/envvars
a2enmod rewrite

service apache2 restart

curl -sS https://getcomposer.org/installer | php
mv composer.phar /usr/local/bin/composer

cd -

exit 0
