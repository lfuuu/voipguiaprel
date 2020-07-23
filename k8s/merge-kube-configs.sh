cp $HOME/.kube/config $HOME/.kube/conf_save
configs=$HOME/.kube/config
for file in ~/.kube/*config*
do
	[ ! "$file" = "$HOME/.kube/config" ] && configs="$configs:$file"
done
KUBECONFIG=$configs kubectl config view --merge --flatten > ~/.kube/config
