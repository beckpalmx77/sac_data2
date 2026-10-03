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

        // 1. สร้างปุ่ม Action: เพิ่มปุ่ม แก้ไข
        $action_buttons = '
            <button type="button" class="btn btn-info btn-sm edit-btn mr-1" data-id="' . $row['id'] . '" title="แก้ไข">
                <i class="fas fa-edit"></i> แก้ไข
            </button>
            <button type="button" class="btn btn-danger btn-sm delete-btn" data-id="' . $row['id'] . '" title="ลบ">
                <i class="fas fa-trash"></i> ลบ
            </button>
        ';

        // 2. จัดเรียงข้อมูลตามลำดับคอลัมน์ในตาราง Datatables (รวม ID ในตำแหน่งที่ 8)
        $data[] = [
            $row['customer_name'],  // 0
            $row['send_date'],      // 1
            $row['tires_brand'],    // 2
            $row['tires_size'],     // 3
            $row['tires_model'],    // 4
            $row['tires_number'],   // 5
            $row['tires_year'],     // 6
            $action_buttons,        // 7: คอลัมน์ Action (จัดการ)
            $row['id']              // 8: คอลัมน์ ID (สำหรับใช้ใน JS/ซ่อนใน Datatables)
        ];
    }

} catch (PDOException $e) {
    // หากเกิดข้อผิดพลาดในการเชื่อมต่อหรือคิวรี่
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
