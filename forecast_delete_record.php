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

$id = $_POST['id'] ?? '';
if (empty($id)) {
    echo json_encode(['success' => false, 'message' => 'ไม่ระบุ ID รายการ']);
    exit;
}

$account_type = $_SESSION['account_type'] ?? '';
$first_name = $_SESSION['first_name'] ?? '';

try {
    // Check ownership or admin status
    $sql = "SELECT id, sale_name, take_name FROM ims_tires_plan_forecast WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลรายการ']);
        exit;
    }

    if ($account_type !== 'admin') {
        if ($account_type === 'sale_user' && $row['sale_name'] !== $first_name) {
            echo json_encode(['success' => false, 'message' => 'ไม่มีสิทธิ์ลบรายการของพนักงานท่านอื่น']);
            exit;
        }
        if ($account_type === 'take' && $row['take_name'] !== $first_name) {
            echo json_encode(['success' => false, 'message' => 'ไม่มีสิทธิ์ลบรายการของพนักงานท่านอื่น']);
            exit;
        }
    }

    // Proceed with delete
    $sql_del = "DELETE FROM ims_tires_plan_forecast WHERE id = ?";
    $stmt_del = $conn->prepare($sql_del);
    $stmt_del->execute([$id]);

    echo json_encode(['success' => true, 'message' => 'ลบข้อมูลรายการเรียบร้อยแล้ว']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
