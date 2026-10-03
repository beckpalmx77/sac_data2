<?php
// กำหนด Content Type เป็น JSON ทันที
header('Content-Type: application/json');

// 💡 1. ป้องกัน PHP Errors/Warnings ปนกับ JSON: ล้าง Output Buffer ทันที
if (ob_get_level()) {
    ob_clean();
}

// ⚠️ ตรวจสอบเส้นทางของไฟล์เชื่อมต่อฐานข้อมูลของคุณ
include('../config/connect_db.php');
// สมมติว่าไฟล์ connect_db.php สร้างตัวแปร $conn สำหรับการเชื่อมต่อแบบ PDO

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

// 1. รับค่าที่ส่งมาจาก AJAX
$id = $_POST['id'] ?? null;
$brand = $_POST['brand'] ?? null;
$size = $_POST['size'] ?? null;
$model = $_POST['model'] ?? null;
$tires_number = $_POST['tires_number'] ?? null;
$production_year = $_POST['production_year'] ?? null;

// 💡 (โค้ด Debug การเขียนไฟล์ถูกนำออกเพื่อความเป็นระเบียบ)

if (!$id) {
    http_response_code(400); // Bad Request
    echo json_encode(['status' => 'error', 'message' => 'ไม่พบ ID ที่ต้องการแก้ไข.']);
    exit;
}

// 2. ตรวจสอบการเชื่อมต่อ PDO
// PDO จะโยน Exception เมื่อเชื่อมต่อไม่ได้ ดังนั้นตรวจสอบแค่ว่าตัวแปร $conn ถูกสร้างขึ้นหรือไม่
if (!isset($conn) || !($conn instanceof PDO)) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'การเชื่อมต่อฐานข้อมูลล้มเหลว หรือตัวแปร $conn ไม่ใช่ PDO Object.']);
    exit;
}

try {
    // 3. เตรียมคำสั่ง SQL สำหรับ UPDATE (ใช้ Named Placeholders)
    $sql = "UPDATE ims_tires_number SET 
                tires_brand = :brand, 
                tires_size = :size, 
                tires_model = :model, 
                tires_number = :tires_number, 
                tires_year = :production_year 
            WHERE id = :id";

    // 4. เตรียม Prepared Statement (PDO)
    $stmt = $conn->prepare($sql);

    // 5. Execute และ Bind parameters โดยใช้ Array (ง่ายกว่า)
    $result = $stmt->execute([
        ':brand' => $brand,
        ':size' => $size,
        ':model' => $model,
        ':tires_number' => $tires_number,
        ':production_year' => $production_year, // PDO จะจัดการประเภทข้อมูลได้ยืดหยุ่นกว่า
        ':id' => $id
    ]);

    // 6. ตรวจสอบผลลัพธ์
    if ($result) {
        // rowCount() จะคืนค่าจำนวนแถวที่ได้รับผลกระทบ
        if ($stmt->rowCount() >= 0) {
            echo json_encode(['status' => 'success', 'message' => 'บันทึกการแก้ไขข้อมูลเรียบร้อยแล้ว.']);
        }
    } else {
        // หาก execute ล้มเหลว (เช่น SQL syntax error)
        $errorInfo = $stmt->errorInfo();
        throw new Exception("Execute ล้มเหลว: " . $errorInfo[2]);
    }

} catch (PDOException $e) {
    // ดักจับข้อผิดพลาดของ PDO โดยเฉพาะ
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'ข้อผิดพลาดฐานข้อมูล: ' . $e->getMessage()]);
} catch (Exception $e) {
    // ดักจับข้อผิดพลาดอื่นๆ
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'ข้อผิดพลาด Server: ' . $e->getMessage()]);
}

exit;
?>