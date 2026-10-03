<?php

require_once '../config_pg/connect_pg_db.php';
require_once '../config/connect_db.php';
require_once '../config/config_rabbit.inc';
require_once '../util/send_message.php';
require_once '../vendor/autoload.php';

$stmt = $conn->prepare("SELECT line_api_token FROM aline_api WHERE doc_type = 'SAC_DATA2'");
$stmt->execute();
$line_api_tokens = $stmt->fetchAll(PDO::FETCH_ASSOC);

$stmt = $conn->prepare("SELECT user_id FROM ims_line_sale_app_users WHERE status = 'Y'");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$id = "9331";
$date = "05-04-2025";
$code = "SAC00002111";
$name = "My Name";
$contract_name = "Sale-001";
$contract_phone = "0900106833";
$take_name = "Take-001";

$sMessage = "ทดสอบ เลขที่เอกสาร = " . $id . " " . $date . " " . $code . " " . $name
    . "\n\r" . "ผู้ติดต่อ : " . $contract_name . " โทรฯ : " . $contract_phone
    . "\n\r" . "ผู้รับผิดชอบ : " . $take_name
    . "\n\r" . "https://app.sanguanautocar.co.th/orders/" . $id ;

echo $sMessage . "\n\r";

if (!empty($users) && !empty($line_api_tokens)) {
    foreach ($line_api_tokens as $line_api_token) {
        $channelAccessToken = $line_api_token['line_api_token'];

        foreach ($users as $user) {
            $userId = $user['user_id'];

            echo $channelAccessToken . " | " . $userId . "\n\r";

            sendLineMessage($conn, $channelAccessToken, $userId, $sMessage);
        }
    }
} else {
    //error_log("No users or no line_api_tokens found.");
}
