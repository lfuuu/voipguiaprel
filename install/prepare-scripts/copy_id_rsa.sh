mkdir -p /workspace/.ssh
echo $1 | base64 -d > /workspace/.ssh/id_rsa
chmod 600 /workspace/.ssh/id_rsa





