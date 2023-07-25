<?php
date_default_timezone_set("Asia/Bangkok");
include('../config/db_value.inc');
include '../config/config_rabbit.inc';
require_once '../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection($rabbitmqHost, $rabbitmqPort, $rabbitmqUser, $rabbitmqPass);
$channel = $connection->channel();


echo 'Waiting for messages. To exit, press CTRL+C', "\n";

// Callback function สำหรับรับข้อมูลจาก RabbitMQ
$callback = function ($msg) {

    try {
        $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";port=" . DB_PORT, DB_USER, DB_PASS
            , array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
        exit("Error: " . $e->getMessage());
    }

    $data = $msg->body;

    echo "MSG =  " . $data . "\n\r";

    if (substr($data, 0, 5) !== 'Q_MSG') {

        // ตัวอย่างการแทรกข้อมูลลงในตารางชื่อ users
        $sql = "UPDATE ims_sac_orders set msg_status = 'Y' WHERE code_id = " . $data;
        $stmt = $conn->prepare($sql);

        try {
            $stmt->execute();
            echo 'Update data into MySQL: ' . $data . "\n";


        } catch (PDOException $e) {
            echo 'Error inserting data: ' . $e->getMessage() . "\n";
        }
    }

};

// รับข้อมูลจากคิว RabbitMQ
$channel->basic_consume($rabbitmqQueue, '', false, true, false, false, $callback);

while ($channel->is_consuming()) {
    $channel->wait();
}

// ปิดการเชื่อมต่อ
$channel->close();
$connection->close();



