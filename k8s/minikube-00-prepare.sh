# Этот скрипт нужно запустить в отдельном терминале.
# Он подготавливает minikube. Запускает в нем службу реестра и ингресса. а так же выставляет службу реестра наружу.

minikube stop
minikube start --addon registry --addon ingress
kubectl -n kube-system expose rc/registry --type=ClusterIP --port=5000 --target-port=5000 --name=werf-registry

export REGISTRY_IP=$(kubectl -n kube-system get svc/werf-registry -o=template={{.spec.clusterIP}})
minikube ssh "cat /etc/hosts |  grep -v werf-registry | sudo tee /etc/hosts"
minikube ssh "echo '$REGISTRY_IP werf-registry.kube-system.svc.cluster.local' | sudo tee -a /etc/hosts"

sudo sed -i -e '/^.*werf-registry.*l$/d' /etc/hosts
echo "127.0.0.1 werf-registry.kube-system.svc.cluster.local" | sudo tee -a /etc/hosts


kubectl wait --for=condition=available --timeout=60s --all deployments -A
kubectl -n kube-system wait --for=condition=ready pods -l actual-registry=true
kubectl port-forward --namespace kube-system service/werf-registry 5000
