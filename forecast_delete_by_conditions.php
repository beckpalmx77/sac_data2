<?php
session_start();
include('config/connect_db.php');

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['alogin']) || $_SESSION['alogin'] == '') {
    echo json_encode(['success' => false, 'message' => 'Session expired']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$month = $_POST['month'] ?? '';
$year = $_POST['year'] ?? '';
$sale_name = $_POST['sale_name'] ?? '';
$brand_code = $_POST['brand_code'] ?? '';

if (empty($month) || empty($year)) {
    echo json_encode(['success' => false, 'message' => 'กรุณาระบุเดือนและปี']);
    exit;
}

$account_type = $_SESSION['account_type'] ?? '';
$first_name = $_SESSION['first_name'] ?? '';

// Build WHERE clause
$where = "WHERE forcast_month = :month AND forcast_year = :year";
$params = [
    ':month' => $month,
    ':year' => $year
];

// Security constraints
if ($account_type === 'sale_user') {
    $where .= " AND sale_name = :first_name";
    $params[':first_name'] = $first_name;
} elseif ($account_type === 'take') {
    $where .= " AND take_name = :first_name";
    $params[':first_name'] = $first_name;
} else {
    // Admin or other roles can delete by sale_name filter
    if (!empty($sale_name)) {
        $where .= " AND sale_name = :sale_name";
        $params[':sale_name'] = $sale_name;
    }
}

// Brand Filter
if (!empty($brand_code) && $brand_code !== 'all') {
    $where .= " AND brand_code = :brand_code";
    $params[':brand_code'] = $brand_code;
}

try {
    // First check if any records match the conditions to avoid deleting 0 rows without knowing
    $sql_check = "SELECT COUNT(*) as cnt FROM ims_tires_plan_forecast $where";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->execute($params);
    $count = $stmt_check->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;

    if ($count == 0) {
        echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลตามเงื่อนไขที่ระบุ']);
        exit;
    }

    // Perform deletion
    $sql_del = "DELETE FROM ims_tires_plan_forecast $where";
    $stmt_del = $conn->prepare($sql_del);
    $stmt_del->execute($params);

    echo json_encode([
        'success' => true, 
        'message' => 'ลบข้อมูลสำเร็จ ทั้งหมด ' . $count . ' รายการ'
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
