<?php
// กำหนด Content Type เป็น JSON เพื่อส่งผลลัพธ์กลับไปให้ AJAX
header('Content-Type: application/json');

// *** 🔑 แก้ไข: เพิ่มการล้าง Output Buffer เพื่อป้องกัน PHP Error/Notice ปนมากับ JSON ***
if (ob_get_level()) {
    ob_clean();
}
// *** --------------------------------------------------------------- ***

// ตรวจสอบเส้นทางของไฟล์ connect_db.php (ควรอยู่เหนือโฟลเดอร์ import_process)
include '../config/connect_db.php';

$response = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $record_id = trim($_POST['id']);

    try {
        // ใช้ prepared statement เพื่อป้องกัน SQL Injection
        $stmt = $conn->prepare("DELETE FROM ims_tires_number WHERE id = ?");
        $stmt->execute([$record_id]);

        if ($stmt->rowCount() > 0) {
            $response['status'] = 'success';
            $response['message'] = 'ลบรายการสำเร็จแล้ว';
        } else {
            $response['status'] = 'error';
            $response['message'] = 'ไม่พบรายการที่ต้องการลบ (ID: ' . $record_id . ') หรือเกิดข้อผิดพลาดในการลบ';
        }

    } catch (PDOException $e) {
        $response['status'] = 'error';
        $response['message'] = 'Database Error: ไม่สามารถลบข้อมูลได้ ' . $e->getMessage();
    }
} else {
    $response['status'] = 'error';
    $response['message'] = 'คำขอไม่ถูกต้องหรือไม่มี ID ส่งมา';
}

echo json_encode($response);
$conn = null;
exit(); // *** 🔑 เพิ่ม exit() เพื่อหยุดการทำงานและป้องกัน output อื่น ๆ ตามมา ***
// *** ⚠️ ห้ามมีบรรทัดว่างหรืออักขระใดๆ หลังแท็กปิด PHP ***