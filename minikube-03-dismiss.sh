#!/bin/bash
. $(multiwerf use 1.1 stable --as-file)
werf dismiss --env dev --with-namespace
