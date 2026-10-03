<?php
$base_dir = __DIR__;
include '../config/connect_db.php';
require '../vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

// กำหนดค่าการเชื่อมต่อ RabbitMQ
$mq_host = '192.168.88.241'; // หรือ IP ของ RabbitMQ server
$mq_port = 5672;
$mq_user = 'admin';
$mq_pass = 'admin';
$queue_name = 'sale_logistic_plan_queue';

// เชื่อมต่อกับ RabbitMQ
$connection = new AMQPStreamConnection($mq_host, $mq_port, $mq_user, $mq_pass);
$channel = $connection->channel();
$channel->queue_declare($queue_name, false, true, false, false);

echo " [*] Waiting for messages. To exit press CTRL+C\n";

$callback = function (AMQPMessage $msg) use ($conn) {
    echo ' [x] Received ', $msg->body, "\n";
    $data = json_decode($msg->body, true);

    if ($data === null) {
        echo " [!] Error decoding JSON. Skipping.\n";
        $msg->ack();
        return;
    }

    $DOC_ID = $data['DOC_ID'];
    $DOC_DATE = $data['DOC_DATE'];
    $seq_record = $data['seq_record'];
    $user_id = $data['user_id'];
    $file_name = $data['file_name'];
    $total_rows = $data['total_rows'];

    $header = $data['header'];
    $details = $data['details'];
    $importedRows = 0;
    $duplicateRows = 0;

    try {
        // เริ่ม Transaction เพื่อให้การ Insert ทั้งหมดสำเร็จพร้อมกัน หรือล้มเหลวพร้อมกัน
        $conn->beginTransaction();

        // Insert Header
        $stmt_header = $conn->prepare("INSERT INTO ims_sale_logistic_plan 
            (DOC_ID, DOC_DATE, MONTH, YEAR, CUSTOMER_NAME, AMPHUR, PROVINCE, seq_record) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt_header->execute([
            $DOC_ID, $DOC_DATE, $header['MONTH'], $header['YEAR'],
            $header['CUSTOMER_NAME'], $header['AMPHUR'], $header['PROVINCE'], $seq_record
        ]);

        // Insert Details
        $line_no = 0;
        foreach ($details as $row) {
            $line_no++;
            $stmt_detail = $conn->prepare("INSERT INTO ims_sale_logistic_plan_detail 
                (MONTH,YEAR,DOC_ID,DOC_DATE,line_no,PROVINCE,AMPHUR,CUSTOMER_NAME,BRAND,SKU_CODE,target,seq_record) 
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt_detail->execute([
                $row['MONTH'], $row['YEAR'], $DOC_ID, $DOC_DATE, $line_no, $row['PROVINCE'], $row['AMPHUR'],
                $row['CUSTOMER_NAME'], $row['BRAND'], $row['SKU_CODE'], $row['target'], $seq_record
            ]);
            $importedRows++;
        }

        $conn->commit();
        echo " [x] Data for DOC_ID: $DOC_ID inserted into MySQL successfully.\n";

        // Insert log
        $import_success = "$file_name \n\r Y \n\r จำนวนที่ Upload จาก Excel : $total_rows รายการ \n\r นำเข้าใหม่สำเร็จ: $importedRows รายการ \n\r มีข้อมูลซ้ำ: $duplicateRows รายการ";
        $sql_insert_log = "INSERT INTO log_import_data (screen_name, total_record, import_record, detail1, detail2, seq_record, create_by) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert_log = $conn->prepare($sql_insert_log);
        $stmt_insert_log->execute(['ims_sale_logistic_plan_detail', $total_rows, $importedRows, $import_success, $file_name, $seq_record, $user_id]);

    } catch (Exception $e) {
        $conn->rollBack();
        echo " [!] Error inserting data for DOC_ID: $DOC_ID. Message: " . $e->getMessage() . "\n";
    }

    $msg->ack(); // ส่งสัญญาณบอก RabbitMQ ว่าประมวลผลเสร็จแล้ว
};

$channel->basic_consume($queue_name, '', false, false, false, false, $callback);

while ($channel->is_consuming()) {
    $channel->wait();
}

$channel->close();
$connection->close();