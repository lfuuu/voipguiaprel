
. $(multiwerf use 1.1 stable --as-file)

werf helm secret values encrypt values.yaml -o ../secret-values.yaml --dir ../../
