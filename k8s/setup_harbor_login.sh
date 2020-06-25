mkdir -p /etc/docker
if [ ! -f /etc/docker/daemon.json ]; then
touch /etc/docker/daemon.json
cat > /etc/docker/daemon.json <<-EOF
{
  "insecure-registries": ["harbor.mcn.loc"]
}
EOF
systemctl restart docker
elif [ ! -s /etc/docker/daemon.json ]; then
cat > /etc/docker/daemon.json <<-EOF
{
  "insecure-registries": ["harbor.mcn.loc"]
}
EOF
systemctl restart docker
elif [ "$(jq . /etc/docker/daemon.json)" = "{}" ]; then
cat > /etc/docker/daemon.json <<-EOF
{
	  "insecure-registries": ["harbor.mcn.loc"]
}
EOF
systemctl restart docker
elif [ $(cat /etc/docker/daemon.json | grep insecure-registries | wc -l) -lt 1 ]; then
	sed -i "s/{/{\n  \"insecure-registries\": [\"harbor.mcn.loc\"],/" /etc/docker/daemon.json
	systemctl restart docker
elif [ $(cat /etc/docker/daemon.json | grep harbor | wc -l) -lt 1 ]; then
        sed -i "s/\"insecure-registries\": \[/\"insecure-registries\": [\"harbor.mcn.loc\", /" /etc/docker/daemon.json
        systemctl restart docker
fi
