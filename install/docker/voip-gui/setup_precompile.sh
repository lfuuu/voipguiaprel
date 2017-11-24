#!/bin/sh

printf "Host github.com\n\tStrictHostKeyChecking no\n\tUserKnownHostsFile=/dev/null\n" >> /etc/ssh/ssh_config
cd /opt
git clone git@github.com:welltime/voip_gui.git



