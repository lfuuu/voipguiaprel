СОЗДАНИЕ КОНТЕЙНЕРОВ
---------

1. Разворачиваем репозиторий внутри контейнера

```
./build.sh git
./voip_gui.sh run_git
./voip_gui.sh connect_git
```


2. Подмонтируем внешнюю папку 

```
./build.sh volume
./voip_gui.sh run_volume
./voip_gui.sh connect_volume

```


