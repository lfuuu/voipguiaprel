#!/bin/bash

#
# В миникубе нужно включить дополнения ingress и registry
# > minikube addons enable ingress
# > minikube addons enable registry
#
# https://ru.werf.io/documentation/reference/development_and_debug/setup_minikube.html
# настройка реестра в миникубе

. $(multiwerf use 1.1 stable --as-file)
werf build-and-publish --stages-storage :local --tag-custom voip-gui --images-repo :minikube
