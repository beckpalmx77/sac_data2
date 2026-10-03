<?php

// ตั้งค่า Timezone
date_default_timezone_set('Asia/Bangkok');

// ข้อมูลการเชื่อมต่อ PostgreSQL
$host = 'sac.cckwqocv7kfy.ap-southeast-1.rds.amazonaws.com';
$port = '5432';
$dbname = 'sac'; // <--- เปลี่ยนเป็นชื่อฐานข้อมูลของคุณ
$user = 'sac';
$password = 'l;ovvF9h8kiN';

$dsn = "pgsql:host=$host;port=$port;dbname=$dbname";

// กำหนดไฟล์สำหรับเก็บ Log (บันทึกไว้ในโฟลเดอร์เดียวกับไฟล์นี้)
$logFile = __DIR__ . '/update_log.txt';

try {
    // เชื่อมต่อฐานข้อมูล
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // คำสั่ง SQL
    $sql = "UPDATE sac_customer_price_group 
            SET price_group_id = 14 , is_default = 't'
            WHERE customer_id = 459 OR customer_id = 984 ";

    // WHERE customer_id = 459 OR customer_id = 984 OR customer_id = 855

    // รันคำสั่ง
    $stmt = $pdo->prepare($sql);
    $stmt->execute();

    // สร้างข้อความแจ้งเตือน
    $rowCount = $stmt->rowCount();
    $logMessage = "[" . date('Y-m-d H:i:s') . "] Success: Updated $rowCount rows.\n";

    // แสดงผลบนหน้าจอ (Echo)
    echo $logMessage;

    // เขียนลงไฟล์ Log
    //file_put_contents($logFile, $logMessage, FILE_APPEND);

} catch (PDOException $e) {
    // สร้างข้อความ Error
    $logMessage = "[" . date('Y-m-d H:i:s') . "] Error: " . $e->getMessage() . "\n";

    // แสดงผลบนหน้าจอ
    echo $logMessage;

    // เขียนลงไฟล์ Log
    //file_put_contents($logFile, $logMessage, FILE_APPEND);
}

