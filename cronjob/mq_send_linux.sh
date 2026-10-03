#!/bin/env bash
while [ true ]; do
 sleep 5
 # do what you need to here
 php  /var/www/html/sac_data2/cronjob/mq_send_lix.php
done
