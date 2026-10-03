<?php
include '/var/www/html/sac_data2/config_pg/connect_pg_db.php';
include '/var/www/html/sac_data2/config/connect_db.php';
include '/var/www/html/sac_data2/config/config_rabbit_linux.inc';
include '/var/www/html/sac_data2/util/send_message.php';
require_once '/var/www/html/sac_data2/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$sToken = "fEdAZErH6afcT2QEZBZ8J17bz3QpBrYCZUYyK3v40ob";

echo "\n\r" . "Line Token = " . $sToken;

echo "\n\r" . "Time in Bangkok\n";
$date2 = new DateTime();
$date2->setTimezone(new DateTimeZone('Asia/Bangkok'));
echo $date2->format(DateTime::RFC1123) . "\n";

while (true) {
    $date_create = $date2->format('Y-m-d-H-i-s');
    $data_create = "Q_MSG Create = " . $date_create;

    echo "Before Loop = " . $data_create;

    $connection = new AMQPStreamConnection($rabbitmqHost, $rabbitmqPort, $rabbitmqUser, $rabbitmqPass);
    $channel = $connection->channel();

    $channel->queue_declare($rabbitmqQueue, false, true, false, false);

    $msg = new AMQPMessage($data_create);
    $channel->basic_publish($msg, '', $rabbitmqQueue);

    echo " [x] Sent 'Send Data'\n\r";

    $channel->close();
    $connection->close();

    $current_date = date("Y-m-d");
    echo "Date = " . $current_date . "\n\r";

    $sql_pg = "SELECT sac_orders.*,sac_customers.code,sac_customers.name,sac_customers.owner,sac_users.username,sac_users.name  as take_name     
        FROM sac_orders
        LEFT JOIN sac_customers ON sac_customers.id = sac_orders.customer_id  
        LEFT JOIN sac_users ON sac_users.id = sac_customers.taker_id    
        WHERE date >= '" . $current_date . "' ORDER BY id ";

    echo $sql_pg . "\n\r";

    $stmt = $conn_pg->prepare($sql_pg);
    $stmt->execute();
    $orders = $stmt->fetchAll();

    foreach ($orders as $order) {
        $sql_find = "SELECT code_id FROM ims_sac_orders WHERE code_id = :code_id";
        $stmt_find = $conn->prepare($sql_find);
        $stmt_find->bindParam(':code_id', $order['id'], PDO::PARAM_INT);
        $stmt_find->execute();
        $nRows = $stmt_find->fetchColumn();

        if ($nRows > 0) {
            echo "Dup id = " . $order['id'] . "\n\r";
        } else {
            echo "Insert id = " . $order['id'] . "\n\r";
            $sql_insert = "INSERT INTO ims_sac_orders (code_id, date, customer_id, customer_code, customer_name, owner, address, contract_name, contract_phone) 
                VALUES (:code_id, :date, :customer_id, :customer_code, :customer_name, :owner, :address, :contract_name, :contract_phone)";
            $stmt_insert = $conn->prepare($sql_insert);

            $stmt_insert->bindParam(':code_id', $order['id'], PDO::PARAM_INT);
            $stmt_insert->bindParam(':date', $order['date'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':customer_id', $order['customer_id'], PDO::PARAM_INT);
            $stmt_insert->bindParam(':customer_code', $order['code'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':customer_name', $order['name'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':owner', $order['owner'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':address', $order['address'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':contract_name', $order['contract_name'], PDO::PARAM_STR);
            $stmt_insert->bindParam(':contract_phone', $order['contract_phone'], PDO::PARAM_STR);
            $stmt_insert->execute();

            $lastInsertId = $conn->lastInsertId();

            if ($lastInsertId) {
                $sMessage = "มีรายการสั่งซื้อเข้า เลขที่เอกสาร = " . $order['id'] . " " . $order['date'] . " " . $order['code'] . " " . $order['name']
                    . "\n\rผู้ติดต่อ : " . $order['contract_name'] . " โทรฯ : " . $order['contract_phone']
                    . "\n\rผู้รับผิดชอบ : " . $order['take_name']
                    . "\n\rhttps://app.sanguanautocar.co.th/orders/" . $order['id'];

                echo $sMessage;
                sendLineNotify($sMessage, $sToken);

                $connection = new AMQPStreamConnection($rabbitmqHost, $rabbitmqPort, $rabbitmqUser, $rabbitmqPass);
                $channel = $connection->channel();

                $channel->queue_declare($rabbitmqQueue, false, true, false, false);

                $msg = new AMQPMessage($order['id']);
                $channel->basic_publish($msg, '', $rabbitmqQueue);

                echo " [x] Sent 'Send Data'\n\r";

                $channel->close();
                $connection->close();
            }
        }
    }

    sleep(10); // Pause for 10 seconds before the next iteration
}
