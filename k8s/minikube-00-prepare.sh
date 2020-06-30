# Этот скрипт нужно запустить в отдельном терминале.
# Он подготавливает minikube. Запускает в нем службу реестра и ингресса. а так же выставляет службу реестра наружу.

unset KUBECONFIG
if [ $(minikube status | grep Running | wc -l) -gt 0 ]; then
	minikube addons enable ingress registry
else
	minikube start --addons registry --addons ingress
fi

echo "Ожидание старта деплойментов"
kubectl wait --for=condition=available --timeout=60s --all deployments -A
echo "Ожидание старта реестра и ingress controller"
kubectl -n kube-system wait --for=condition=ready --timeout=120s pods -l actual-registry=true
kubectl -n kube-system wait --for=condition=ready --timeout=120s pods -l registry-proxy=true
kubectl -n kube-system wait --for=condition=ready --timeout=120s pods -l app.kubernetes.io/component=controller

kubectl -n kube-system expose rc/registry --type=ClusterIP --port=5000 --target-port=5000 --name=werf-registry --selector='actual-registry=true'

export REGISTRY_IP=$(kubectl -n kube-system get svc/werf-registry -o=template={{.spec.clusterIP}})
minikube ssh "cat /etc/hosts |  grep -v werf-registry | sudo tee /etc/hosts"
minikube ssh "echo '$REGISTRY_IP werf-registry.kube-system.svc.cluster.local' | sudo tee -a /etc/hosts"

sudo sed -i -e '/^.*werf-registry.*l$/d' /etc/hosts
echo "127.0.0.1 werf-registry.kube-system.svc.cluster.local" | sudo tee -a /etc/hosts

echo "test registry: curl -X GET werf-registry.kube-system.svc.cluster.local:5000/v2/_catalog"
[ $(netstat -lnt | grep 5000 | wc -l) -eq 0 ] && \
    kubectl port-forward --namespace kube-system service/werf-registry 5000 || \
    echo "[WARNING] port 5000 is used, port-forward will not run"
