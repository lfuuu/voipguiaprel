#!/bin/sh
ssh nedlosster@85.94.32.195 -A -o ServerAliveInterval=60 -4 -p 32223 -A \
    -L 0.0.0.0:5432:eridanus.mcn.ru:5432
