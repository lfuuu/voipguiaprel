#!/bin/sh

cd /opt/voip_gui/config/

sed -i "s/host=localhost;dbname=nispd/host=$POSTGRES_HOST;dbname=$POSTGRES_DB/" db-local.php
sed -i "s/'username' => ''/'username' => '$POSTGRES_USER'/" db-local.php
sed -i "s/'password' => ''/'password' => '$POSTGRES_PASSWORD'/" db-local.php

/usr/sbin/httpd -DFOREGROUND
