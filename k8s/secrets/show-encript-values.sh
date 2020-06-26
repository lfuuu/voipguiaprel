
. $(multiwerf use 1.1 stable --as-file)

werf helm secret values decrypt ../../.helm/secret-values.yaml --dir ../../ | tee values.yaml
