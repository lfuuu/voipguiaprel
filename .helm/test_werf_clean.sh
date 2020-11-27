#!/bin/bash

. $(multiwerf use 1.1 stable --as-file)

werf cleanup --dir ../ --stages-storage :local --images-repo werf-registry.kube-system.svc.cluster.local --log-debug=true --log-verbose=true
werf stages purge --dir ../ --force --stages-storage=:local
