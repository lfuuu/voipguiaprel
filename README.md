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
composer global require "fxp/composer-asset-plugin"
composer update
```

ОБНОВЛЕНИЕ
----------
```
git pull origin master
composer update
```

ТЕСТИРОВАНИЕ
------------
```
composer global require "codeception/codeception=2.0.*"
composer global require "codeception/specify=*"
composer global require "codeception/verify=*"
```

```
Changed current directory to <directory>
```

Then add `<directory>/vendor/bin` to you `PATH` environment variable. Now we're able to use `codecept` from command
line globally.

```
codecept run
codecept run unit
codecept run functionnal
codecept run acceptance
```
