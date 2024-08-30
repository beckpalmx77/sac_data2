i=0

while [ $i -lt 5 ]; do # 5 five-second intervals in 1 minute

  php  /var/www/html/sac_data2/cronjob/mq_send_linux.php

  sleep 5
  i=$(( i + 1 ))
done

