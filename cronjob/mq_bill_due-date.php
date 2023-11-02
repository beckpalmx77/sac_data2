<?php
include '../config_pg/connect_pg_db.php';
include '../config/connect_db.php';
include '../config/config_rabbit.inc';
include '../util/send_message.php';
require_once '../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

echo "Time in Bangkok\n";
$date2 = new DateTime();
$date2->setTimezone(new DateTimeZone('Asia/Bangkok'));
echo $date2->format(DateTime::RFC1123) . "\n";

$date_create = $date2->format('Y-m-d-H-i-s');
$data_create = "Q_MSG Create = " . $date_create ;

echo "Before Loop = " . $data_create ;

$connection = new AMQPStreamConnection($rabbitmqHost, $rabbitmqPort, $rabbitmqUser, $rabbitmqPass);
$channel = $connection->channel();

$channel->queue_declare($rabbitmqQueue, false, false, false, false);

$msg = new AMQPMessage($data_create);
$channel->basic_publish($msg, '', $rabbitmqQueue);

echo " [x] Sent 'Send Data'\n\r";

$channel->close();
$connection->close();



$current_date = date("Y-m-d");
//$current_date = "2023-07-04";

echo "Date = " . $current_date . "\n\r";

$sql_pg = "SELECT sac_orders.*,sac_customers.code,sac_customers.name,sac_customers.owner,sac_users.username,sac_users.name  as take_name    
    FROM sac_orders
    LEFT JOIN sac_customers ON sac_customers.id = sac_orders.customer_id  		
    LEFT JOIN sac_users ON sac_users.id = sac_customers.taker_id    
    WHERE date >= '" . $current_date . "'
    ORDER BY id ";

echo $sql_pg . "\n\r";

$stmt = $conn_pg->prepare($sql_pg);
$stmt->execute();
$orders = $stmt->fetchAll();
foreach ($orders as $order) {
    $sql_find = " SELECT code_id FROM ims_sac_orders WHERE code_id = " . $order['id'];
    echo $sql_find . "\n\r";
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        echo "Dup id = " . $order['id'] . "\n\r";
        $data = "";
    } else {
        $data = $order['id'];
        echo "Insert id = " . $data . "\n\r";
        $sql = " INSERT INTO ims_sac_orders (code_id,date,customer_id,customer_code,customer_name,owner,address,contract_name,contract_phone) 
            VALUE (:code_id,:date,:customer_id,:customer_code,:customer_name,:owner,:address,:contract_name,:contract_phone) ";
        $query = $conn->prepare($sql);
        $query->bindParam(':code_id', $order["id"], PDO::PARAM_STR);
        $query->bindParam(':date', $order["date"], PDO::PARAM_STR);
        $query->bindParam(':customer_id', $order["customer_id"], PDO::PARAM_STR);
        $query->bindParam(':customer_code', $order["code"], PDO::PARAM_STR);
        $query->bindParam(':customer_name', $order["name"], PDO::PARAM_STR);
        $query->bindParam(':owner', $order["owner"], PDO::PARAM_STR);
        $query->bindParam(':address', $order["address"], PDO::PARAM_STR);
        $query->bindParam(':contract_name', $order["contract_name"], PDO::PARAM_STR);
        $query->bindParam(':contract_phone', $order["contract_phone"], PDO::PARAM_STR);
        $query->execute();

        $lastInsertId = $conn->lastInsertId();

        if ($lastInsertId) {

            $sToken = "fEdAZErH6afcT2QEZBZ8J17bz3QpBrYCZUYyK3v40ob";
            $sMessage = "มีรายการสั่งซื้อเข้า เลขที่เอกสาร = " . $order["id"] . " " . $order["date"] . " " . $order["code"] . " " . $order["name"]
            . "\n\r" . "ผู้ติดต่อ : " . $order["contract_name"] . " โทรฯ : " .$order["contract_phone"]
            . "\n\r" . "ผู้รับผิดชอบ : " . $order["take_name"]
            . "\n\r" . "https://app.sanguanautocar.co.th/orders/" . $order["id"] ;

            echo $sMessage ;
            sendLineNotify($sMessage,$sToken);

            $connection = new AMQPStreamConnection($rabbitmqHost, $rabbitmqPort, $rabbitmqUser, $rabbitmqPass);
            $channel = $connection->channel();

            $channel->queue_declare($rabbitmqQueue, false, false, false, false);

            $msg = new AMQPMessage($data);
            $channel->basic_publish($msg, '', $rabbitmqQueue);

            echo " [x] Sent 'Send Data'\n\r";

            $channel->close();
            $connection->close();

        }
    }
}


