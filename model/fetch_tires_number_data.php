<?php
// กำหนด Content Type เป็น JSON ทันที
header('Content-Type: application/json');

// *** 🔑 แก้ไข: ล้าง Output Buffer และเพิ่ม exit() เพื่อป้องกัน Error/Text ปนใน JSON ***
if (ob_get_level()) {
    ob_clean();
}
// *** ------------------------------------------------------------------------- ***

include '../config/connect_db.php'; // ตรวจสอบเส้นทางของไฟล์ connect_db.php

$data = [];
$error_message = null;

try {
    // ดึงข้อมูลตามลำดับคอลัมน์ในตาราง DataTables
    $stmt = $conn->prepare("SELECT 
        customer_name, 
        send_date, 
        tires_brand, 
        tires_size, 
        tires_model, 
        tires_number, 
        tires_year,
        id  
        FROM ims_tires_number ORDER BY id DESC");

    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // จัดรูปแบบข้อมูลเป็น Array of Arrays สำหรับ DataTables
    foreach ($results as $row) {
        $data[] = [
            $row['customer_name'],
            $row['send_date'],
            $row['tires_brand'],
            $row['tires_size'],
            $row['tires_model'],
            $row['tires_number'],
            $row['tires_year'],
            // คอลัมน์ที่ 8: ปุ่ม Action
            '<button type="button" class="btn btn-danger btn-sm delete-btn" data-id="' . $row['id'] . '"><i class="fas fa-trash"></i> ลบ</button>'
        ];
    }

} catch (PDOException $e) {
    $error_message = "Database Error: " . $e->getMessage();
}

if ($error_message) {
    echo json_encode([
        "error" => $error_message
    ]);
} else {
    echo json_encode([
        "data" => $data
    ]);
}

$conn = null;
exit();