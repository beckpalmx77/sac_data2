<?php
session_start();
include('config/connect_db.php');

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['alogin']) || $_SESSION['alogin'] == '') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$ar_code = $_POST['ar_code'] ?? '';
$ar_name = $_POST['ar_name'] ?? '';
$amphur = $_POST['amphur'] ?? '';
$province = $_POST['province'] ?? '';
$zone = $_POST['zone'] ?? '';

if (empty($ar_code) || empty($ar_name) || empty($amphur) || empty($province)) {
    echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลสำคัญให้ครบถ้วน (รหัสลูกค้า, ชื่อลูกค้า, อำเภอ, จังหวัด)']);
    exit;
}

try {
    $conn->beginTransaction();

    // Update all records with the matching ar_code in the forecast table
    $sql = "UPDATE ims_tires_plan_forecast 
            SET ar_name = ?, amphur = ?, province = ?, zone = ? 
            WHERE ar_code = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute([$ar_name, $amphur, $province, $zone, $ar_code]);
    $affected = $stmt->rowCount();

    $conn->commit();
    echo json_encode([
        'success' => true, 
        'message' => 'อัปเดตข้อมูลลูกค้าเรียบร้อยแล้ว (มีผลต่อแผนงานทั้งหมดจำนวน ' . $affected . ' รายการ)'
    ]);

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
