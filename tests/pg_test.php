<?php
include '../config_pg/connect_pg_db.php';
include '../config/connect_db.php';
require_once '../vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

    $current_date = date("Y-m-d");

    echo $current_date . "\n\r" ;

    $sql_pg = "SELECT * FROM sac_orders WHERE date >= '" . $current_date . "'";

    echo $sql_pg . "\n\r" ;

    $stmt = $conn_pg->prepare($sql_pg);
    $stmt->execute();
    $orders = $stmt->fetchAll();
    foreach ($orders as $order) {
        $sql_find = " SELECT code_id FROM ims_sac_orders WHERE code_id = " . $order['id'] ;
        echo $sql_find . "\n\r" ;
        $nRows = $conn->query($sql_find)->fetchColumn();
        if ($nRows > 0) {
            echo "Dup id = " . $order['id'] . "\n\r" ;
            $data = "";
        } else {
            $data = $order['id'];
            echo "Insert id = " . $data . "\n\r" ;
            $sql = " INSERT INTO ims_sac_orders (code_id,date,customer_id,address,contract_name,contract_phone) 
            VALUE (:code_id,:date,:customer_id,:address,:contract_name,:contract_phone) " ;
            $query = $conn->prepare($sql);
            $query->bindParam(':code_id', $order["id"], PDO::PARAM_STR);
            $query->bindParam(':date', $order["date"], PDO::PARAM_STR);
            $query->bindParam(':customer_id', $order["customer_id"], PDO::PARAM_STR);
            $query->bindParam(':address', $order["address"], PDO::PARAM_STR);
            $query->bindParam(':contract_name', $order["contract_name"], PDO::PARAM_STR);
            $query->bindParam(':contract_phone', $order["contract_phone"], PDO::PARAM_STR);
            $query->execute();

            $lastInsertId = $conn->lastInsertId();

            if ($lastInsertId) {
                $connection = new AMQPStreamConnection('192.168.88.8', 5672, 'admin', 'admin');
                $channel = $connection->channel();

                $channel->queue_declare('sac_msg', false, false, false, false);

                $msg = new AMQPMessage($data);
                $channel->basic_publish($msg, '', 'sac_msg');

                echo " [x] Sent 'Send Data'\n\r";

                $channel->close();
                $connection->close();

            }
        }
    }


