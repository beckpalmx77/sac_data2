#!/bin/env bash
cd /var/www/html/sac_data2/cronjob
while [ true ]; do
 sleep 5
 # do what you need to here
 php  /var/www/html/sac_data2/cronjob/mq_send.php
done
