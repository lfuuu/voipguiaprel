СОЗДАНИЕ КОНТЕЙНЕРОВ
---------

1. Разворачиваем репозиторий внутри контейнера

install/docker/voip-gui/conf/ - копируем файлы *.tpl.php в *.php , прописываем настройки.


```
./build.sh git
./voip_gui.sh run_git 

При необходимости войти в запущенный контейнер:

./voip_gui.sh connect_git
```


2. Подмонтируем внешнюю папку 

config/ - копируем файлы *.tpl.php в *.php , прописываем настройки.

```
./build.sh volume
./voip_gui.sh run_volume
./voip_gui.sh install_composer

При необходимости войти в запущенный контейнер:

./voip_gui.sh connect_volume

```


