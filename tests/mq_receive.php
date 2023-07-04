<?php
include '../config_pg/connect_pg_db.php';
include '../config/config_rabbit.inc';
require_once '../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection($rabbitmqHost, $rabbitmqPort, $rabbitmqUser, $rabbitmqPass);
$channel = $connection->channel();

$channel->queue_declare($rabbitmqQueue, false, false, false, false);

echo "Start = " . $rabbitmqQueue . "\n\r";

echo " [*] Waiting for messages. To exit press CTRL+C\n\r";

$callback = function ($msg) {
    global $conn;

    $data = $msg->body ;
    $String_Sql = " SELECT code_id FROM ims_sac_orders WHERE msg_status = 'N' AND code_id = " . $data  . " | " ;

    echo "Data = " . $String_Sql;

    echo ' [x] Received ', $msg->body, "\n";
};

$channel->basic_consume($rabbitmqQueue, '', false, true, false, false, $callback);

while ($channel->is_open()) {
    $channel->wait();
}

$channel->close();
$connection->close();