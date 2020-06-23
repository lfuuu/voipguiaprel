#!/bin/bash
. $(multiwerf use 1.1 stable --as-file)

werf run --stages-storage :local --docker-options="-d -e POSTGRES_HOST=eridanus.mcn.ru -e POSTGRES_USER=pgsqltest -e POSTGRES_PASSWORD=xxxxxxxxxxxxxxxx"
