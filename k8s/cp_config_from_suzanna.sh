echo "enter pass: admink8s"
scp k8s@suzanna.mcn.loc:~/.kube/config ~/.kube/config_suzanna
sed -i 's/kubernetesadmin@kubernetes/suzannaadmin@k8ssuzanna/g' ~/.kube/config_suzanna
sed -i 's/cluster: kubernetes/cluster: k8ssuzanna/g' ~/.kube/config_suzanna
sed -i 's/kubernetes-admin/suzannaadmin/g' ~/.kube/config_suzanna
sed -i 's/@kubernetes/@k8ssuzanna/g' ~/.kube/config_suzanna
sed -i 's/name: kubernetes/name: k8ssuzanna/g' ~/.kube/config_suzanna
