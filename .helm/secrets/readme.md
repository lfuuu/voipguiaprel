Работа с секретами 

Генерация нового ключа шифровании.
```
werf helm secret generate-secret-key
```

Для шифрования и дешифрования данных необходим ключ шифрования. Есть два места откуда werf может прочитать этот ключ:

1. из переменной окружения WERF_SECRET_KEY
1. из специального файла .werf_secret_key, находящегося в корневой папке проекта
1. из файла ~/.werf/global_secret_key (глобальный ключ)


Зашифровать файл:

```
werf helm secret values encrypt test.yaml -o .helm/secret-values.yaml
```


Расшифровать файл:

```
werf helm secret values decrypt .helm/secret-values.yaml
```