УСТАНОВКА
---------
```
git clone git@github.com:welltime/voip_gui.git
```
```
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```
```
composer global require "fxp/composer-asset-plugin:1.0.0-beta1"
composer update
```

ОБНОВЛЕНИЕ
----------
```
git pull origin master
composer update
```


КОНФИГУРАЦИЯ
------------



ИСПОЛЬЗОВАНИЕ
-------------
```
./migration help
./migration voip-nispd-test/create
./migration voip-nispd-test/up
./migration voip-nispd-test/down
./migration voip-nispd-test/recreate-db
```
