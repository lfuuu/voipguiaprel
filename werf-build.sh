#!/bin/bash
. $(multiwerf use 1.1 stable --as-file)
werf build --stages-storage :local #--log-verbose=true --log-debug=true
