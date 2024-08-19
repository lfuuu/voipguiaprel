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
composer install
```
```
php yii migrate --migrationPath=@yii/rbac/migrations
php yii rbac/init
```

ОБНОВЛЕНИЕ
----------
```
git pull origin master
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

Если после запуска контейнера не запускается веб-интерфейс, выполнить:
CONT_PATH=$(docker ps | grep voipgui | head -n1 | awk '{print $1;}');docker exec -it $CONT_PATH bash;
/usr/sbin/httpd -DFOREGROUND;

# Развёртывание dev окружения

## Подтягивание окружения
Подтянуть репозиторий с `local_cluster`: https://gitlab.mcnloc.ru/dev-env/local_cluster и установить кластер

## Настройка окружения
1. В `deploy-v1.1` скопировать `productlist_official` в `productlist` оставить только `[voip_gui]=dev_kvm_envci`
2. В `deploy-v1.1` заликновать `voip_gui` в папку `products`

## Шифрование значений
В `voip_gui/.helm/secrets`:
1. `./show-encrypt-values.sh`
2. `./save-encrypt-values.sh`

## SSH
В `~/.ssh` должен находится сгенерированный без пароля файл `id_rsa`, **добавленный в gitlab и github**

## Развёртывание
Запустить `./build-deploy.sh` в `deploy-v1.1`
