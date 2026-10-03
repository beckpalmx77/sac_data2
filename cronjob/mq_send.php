<?php
include '../config_pg/connect_pg_db.php';
include '../config/connect_db.php';
include '../config/config_rabbit.inc';
include '../util/send_message.php';
require_once '../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// mobile app production token
$sToken = "fEdAZErH6afcT2QEZBZ8J17bz3QpBrYCZUYyK3v40ob";

echo "\n\r" . "Line Token = " . $sToken;
echo "\n\r" . "Time in Bangkok\n\r";

$date2 = new DateTime();
$date2->setTimezone(new DateTimeZone('Asia/Bangkok'));
echo $date2->format(DateTime::RFC1123) . "\n\r";

$date_create = $date2->format('Y-m-d-H-i-s');
$data_create = "Q_MSG Create = " . $date_create;

echo "Before Loop = " . $data_create;

// สร้าง connection ใหม่กับ RabbitMQ
$connection = new AMQPStreamConnection(
    $rabbitmqHost,
    $rabbitmqPort,
    $rabbitmqUser,
    $rabbitmqPass,
    '/' // vhost
);
$channel = $connection->channel();
$channel->queue_declare($rabbitmqQueue, false, true, false, false);

$msg = new AMQPMessage($data_create);
$channel->basic_publish($msg, '', $rabbitmqQueue);

echo " [x] Sent 'Send Data'\n\r";

$channel->close();
$connection->close();

$current_date = date("Y-m-d");

echo "Date = " . $current_date . "\n\r";

$sql_pg = "SELECT sac_orders.*,sac_customers.code,sac_customers.name,sac_customers.owner,
            sac_users.username,sac_users.name as take_name    
    FROM sac_orders
    LEFT JOIN sac_customers ON sac_customers.id = sac_orders.customer_id  		
    LEFT JOIN sac_users ON sac_users.id = sac_customers.taker_id    
    WHERE date >= :current_date
    ORDER BY id";

echo "sql_pg = " . $sql_pg . "\n\r";

$stmt = $conn_pg->prepare($sql_pg);
$stmt->bindParam(':current_date', $current_date, PDO::PARAM_STR);
$stmt->execute();
$orders = $stmt->fetchAll();

foreach ($orders as $order) {
    $sql_find = "SELECT code_id FROM ims_sac_orders WHERE code_id = :order_id";
    $stmt_find = $conn->prepare($sql_find);
    $stmt_find->bindParam(':order_id', $order['id'], PDO::PARAM_STR);
    $stmt_find->execute();
    $nRows = $stmt_find->fetchColumn();

    if ($nRows > 0) {
        echo "Dup id = " . $order['id'] . "\n\r";
        $data = "";
    } else {
        $data = $order['id'];
        echo "Insert id = " . $data . "\n\r";

        $sql = "INSERT INTO ims_sac_orders 
                (code_id, date, customer_id, customer_code, customer_name, owner, address, contract_name, contract_phone) 
                VALUES (:code_id, :date, :customer_id, :customer_code, :customer_name, :owner, :address, :contract_name, :contract_phone)";
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

            $sMessage = "มีรายการสั่งซื้อเข้า เลขที่เอกสาร = " . $order["id"] . " " . $order["date"]
                . "\n\r" . "ลูกค้า : " . $order["code"] . " " . $order["name"]
                . "\n\r" . "ผู้ติดต่อ : " . $order["contract_name"] . " โทรฯ : " . $order["contract_phone"]
                . "\n\r" . "ผู้รับผิดชอบ : " . $order["take_name"]
                . "\n\r" . "https://app.sanguanautocar.co.th/orders/" . $order["id"];

            //echo $sMessage;

            //sendLineNotify($sMessage, $sToken);


            //echo json_encode($sMessage);

            $stmt = $conn->prepare("SELECT line_api_token FROM aline_api WHERE doc_type = 'SAC_DATA2'");
            $stmt->execute();
            $line_api_tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);

            $stmt = $conn->prepare("SELECT user_id FROM ims_line_sale_app_users WHERE status = 'Y'");
            $stmt->execute();
            $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($users) && !empty($line_api_tokens)) {
                foreach ($line_api_tokens as $line_api_token) {
                    $channelAccessToken = $line_api_token['line_api_token'];
                    foreach ($users as $user) {
                        $userId = $user['user_id'];
                        sendLineMessage($conn, $channelAccessToken, $userId, $sMessage);
                    }
                }
            } else {
                //error_log("No users or no line_api_tokens found.");
            }

            // สร้าง connection ใหม่กับ RabbitMQ
            $connection = new AMQPStreamConnection(
                $rabbitmqHost,
                $rabbitmqPort,
                $rabbitmqUser,
                $rabbitmqPass,
                '/' // vhost
            );
            $channel = $connection->channel();
            $channel->queue_declare($rabbitmqQueue, false, true, false, false);

            $msg = new AMQPMessage($data);
            $channel->basic_publish($msg, '', $rabbitmqQueue);

            echo " [x] Sent 'Send Data'\n\r";

            $channel->close();
            $connection->close();
        }
    }
}
