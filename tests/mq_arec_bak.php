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
    $String_Sql = " SELECT code_id FROM ims_sac_orders WHERE msg_status = 'N' AND code_id = " . $data ;

    echo "Data = " . $String_Sql;

    $query = $conn->prepare($String_Sql);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);

    if ($query->rowCount() >= 1) {
        foreach ($results as $result) {
            $sql = "UPDATE ims_sac_orders set msg_status = 'Y' WHERE code_id = " . $data ;
            $query = $conn->prepare($sql);
            $query->execute();


            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);
            error_reporting(E_ALL);
            date_default_timezone_set("Asia/Bangkok");

            $sToken = "fEdAZErH6afcT2QEZBZ8J17bz3QpBrYCZUYyK3v40ob";
            $sMessage = "มีรายการสั่งซื้อเข้า....";


            $chOne = curl_init();
            curl_setopt( $chOne, CURLOPT_URL, "https://notify-api.line.me/api/notify");
            curl_setopt( $chOne, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt( $chOne, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt( $chOne, CURLOPT_POST, 1);
            curl_setopt( $chOne, CURLOPT_POSTFIELDS, "message=".$sMessage);
            $headers = array( 'Content-type: application/x-www-form-urlencoded', 'Authorization: Bearer '.$sToken.'', );
            curl_setopt($chOne, CURLOPT_HTTPHEADER, $headers);
            curl_setopt( $chOne, CURLOPT_RETURNTRANSFER, 1);
            $result = curl_exec( $chOne );

            //Result error
            if(curl_error($chOne))
            {
                echo 'error:' . curl_error($chOne);
            }
            else {
                $result_ = json_decode($result, true);
                echo "status : ".$result_['status']; echo "message : ". $result_['message'];
            }
            curl_close( $chOne );

        }
    }


    echo ' [x] Received ', $msg->body, "\n";
};

$channel->basic_consume($rabbitmqQueue, '', false, true, false, false, $callback);

while ($channel->is_open()) {
    $channel->wait();
}

$channel->close();
$connection->close();