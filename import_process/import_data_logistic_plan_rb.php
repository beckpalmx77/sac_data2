<?php
include '../config/connect_db.php';
require '../vendor/autoload.php';
include '../util/record_util.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
use PhpOffice\PhpSpreadsheet\IOFactory;

// กำหนดค่าการเชื่อมต่อ RabbitMQ
$mq_host = '192.168.88.241'; // หรือ IP ของ RabbitMQ server
$mq_port = 5672;
$mq_user = 'admin';
$mq_pass = 'admin';
$queue_name = 'sale_logistic_plan_queue';

// สร้างค่า seq_record และ DOC_ID
$str = rand();
$seq_record = md5($str);
$DOC_DATE = date("d-m-Y");
$cond = " WHERE DOC_DATE = '" . $DOC_DATE . "'";
$run_no = LAST_DOCUMENT_COND($conn, "ims_sale_logistic_plan", $cond);
$DOC_ID = "PL-" . $DOC_DATE . "-" . sprintf('%06s', $run_no);

$line_no = 0;
$status = "N";

// กำหนดค่าตัวแปรที่ขาดหายไป
$table_name = "ims_sale_logistic_plan_detail"; // ตัวอย่างชื่อตาราง
$file_Upload = "filename.xlsx"; // ตัวอย่างชื่อไฟล์ที่อัปโหลด
$user_id = "user123"; // ตัวอย่าง user id

if (isset($_FILES['excelFile']['name']) && $_FILES['excelFile']['error'] == UPLOAD_ERR_OK) {
    $fileTmp = $_FILES['excelFile']['tmp_name'];
    $fileName = $_FILES['excelFile']['name'];

    try {
        $spreadsheet = IOFactory::load($fileTmp);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
    } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
        die('Error loading file: ' . $e->getMessage());
    }

    if (count($rows) < 2) {
        die("ไฟล์ Excel ไม่มีข้อมูลที่ต้องการนำเข้า.");
    }

    // สร้าง array เพื่อเก็บข้อมูลทั้งหมดก่อนส่ง
    $excel_data = [
        'DOC_ID' => $DOC_ID,
        'DOC_DATE' => $DOC_DATE,
        'seq_record' => $seq_record,
        'user_id' => $user_id,
        'file_name' => $fileName,
        'total_rows' => count($rows) - 1,
        'header' => [],
        'details' => []
    ];

    // เก็บข้อมูล Header
    $first_data_row = $rows[1];
    $excel_data['header'] = [
        'MONTH' => ($first_data_row[0] === "" || $first_data_row[0] === null) ? "-" : trim($first_data_row[0]),
        'YEAR' => ($first_data_row[1] === "" || $first_data_row[1] === null) ? "-" : trim($first_data_row[1]),
        'PROVINCE' => ($first_data_row[2] === "" || $first_data_row[2] === null) ? "-" : trim($first_data_row[2]),
        'AMPHUR' => ($first_data_row[3] === "" || $first_data_row[3] === null) ? "-" : trim($first_data_row[3]),
        'CUSTOMER_NAME' => ($first_data_row[4] === "" || $first_data_row[4] === null) ? "0" : $first_data_row[4]
    ];

    // เก็บข้อมูล Detail
    foreach ($rows as $index => $row) {
        if ($index == 0) continue; // ข้ามแถวหัวตาราง
        $excel_data['details'][] = [
            'MONTH' => ($row[0] === "" || $row[0] === null) ? "-" : trim($row[0]),
            'YEAR' => ($row[1] === "" || $row[1] === null) ? "-" : trim($row[1]),
            'PROVINCE' => ($row[2] === "" || $row[2] === null) ? "-" : trim($row[2]),
            'AMPHUR' => ($row[3] === "" || $row[3] === null) ? "-" : trim($row[3]),
            'CUSTOMER_NAME' => ($row[4] === "" || $row[4] === null) ? "0" : $row[4],
            'BRAND' => ($row[5] === "" || $row[5] === null) ? "-" : $row[5],
            'SKU_CODE' => ($row[6] === "" || $row[6] === null) ? "-" : trim($row[6]),
            'target' => ($row[7] === "" || $row[7] === null) ? "-" : trim($row[7])
        ];
    }

    // เชื่อมต่อ RabbitMQ และส่งข้อมูล
    try {
        $connection = new AMQPStreamConnection($mq_host, $mq_port, $mq_user, $mq_pass);
        $channel = $connection->channel();
        $channel->queue_declare($queue_name, false, true, false, false);

        $msg_body = json_encode($excel_data);
        $msg = new AMQPMessage($msg_body, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);

        $channel->basic_publish($msg, '', $queue_name);

        $channel->close();
        $connection->close();

        echo "ส่งข้อมูลไปยัง RabbitMQ เรียบร้อยแล้ว (DOC_ID: $DOC_ID)";

    } catch (Exception $e) {
        die("เกิดข้อผิดพลาดในการเชื่อมต่อหรือส่งข้อมูลไปยัง RabbitMQ: " . $e->getMessage());
    }

} else {
    echo "ไม่พบไฟล์ที่อัปโหลด หรือเกิดข้อผิดพลาดในการอัปโหลดไฟล์.";
}

