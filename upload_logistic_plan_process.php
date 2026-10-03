<?php
require_once __DIR__ . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

header('Content-Type: text/html; charset=utf-8');

// RabbitMQ connection details
$host = '192.168.88.241';
$port = 5672;
$user = 'admin';
$password = 'admin';

// Create a connection to RabbitMQ
try {
    $connection = new AMQPStreamConnection($host, $port, $user, $password);
    $channel = $connection->channel();
} catch (\Exception $e) {
    die("ไม่สามารถเชื่อมต่อกับ RabbitMQ ได้: " . $e->getMessage());
}

$queue_name = 'excel_import_queue';
$channel->queue_declare($queue_name, false, true, false, false);

if (!empty($_FILES['excelFile']['name'])) {
    $inputFileName = $_FILES['excelFile']['tmp_name'];

    try {
        // Load the Excel file and read all data
        $spreadsheet = IOFactory::load($inputFileName);
        $sheetData = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

        // Serialize the data for publishing as a JSON string
        $data_to_publish = json_encode($sheetData);

        // Create a RabbitMQ message with persistent delivery mode
        $msg = new AMQPMessage($data_to_publish, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);

        // Publish the message to the queue
        $channel->basic_publish($msg, '', $queue_name);

        echo "<h2>สถานะการอัปโหลด</h2>";
        echo "<p>ไฟล์ Excel ได้ถูกส่งไปยัง RabbitMQ เพื่อประมวลผลแล้ว. การบันทึกข้อมูลจะดำเนินการในเบื้องหลัง.</p>";

    } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
        echo "<h2>เกิดข้อผิดพลาด</h2>";
        echo "<p>เกิดข้อผิดพลาดในการอ่านไฟล์ Excel: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<h2>เกิดข้อผิดพลาด</h2>";
    echo "<p>ไม่พบไฟล์ที่อัปโหลด กรุณาตรวจสอบและลองใหม่อีกครั้ง</p>";
}

$channel->close();
$connection->close();
