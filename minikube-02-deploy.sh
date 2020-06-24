#!/bin/bash
. $(multiwerf use 1.1 stable --as-file)

werf deploy --stages-storage :local --images-repo :minikube --tag-custom voip-gui --env dev

sudo sed -i -e '/^.*voip-gui\.local$/d' /etc/hosts
echo `minikube ip`" voip-gui.local" | sudo tee -a /etc/hosts
