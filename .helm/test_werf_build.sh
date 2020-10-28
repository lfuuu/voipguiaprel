#!/bin/bash

. $(multiwerf use 1.1 stable --as-file)

werf build --dir ../ --stages-storage :local --log-debug=true --log-verbose=true

