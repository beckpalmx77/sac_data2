<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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
$product_type = $_POST['product_type'] ?? '';

if (empty($month) || empty($year)) {
    echo json_encode(['success' => false, 'message' => 'กรุณาระบุเดือนและปี']);
    exit;
}

$account_type = $_SESSION['account_type'] ?? '';
$first_name = $_SESSION['first_name'] ?? '';

$where = "WHERE forcast_month = :month AND forcast_year = :year";
$params = [
    ':month' => $month,
    ':year' => $year
];

if ($account_type === 'sale_user') {
    $where .= " AND sale_name = :first_name";
    $params[':first_name'] = $first_name;
} elseif ($account_type === 'take') {
    $where .= " AND take_name = :first_name";
    $params[':first_name'] = $first_name;
} else {
    if (!empty($sale_name)) {
        $where .= " AND sale_name = :sale_name";
        $params[':sale_name'] = $sale_name;
    }
}

if (!empty($brand_code) && $brand_code !== 'all') {
    $where .= " AND brand_code = :brand_code";
    $params[':brand_code'] = $brand_code;
}

if (!empty($product_type) && $product_type !== 'all') {
    $where .= " AND product_type = :product_type";
    $params[':product_type'] = $product_type;
}

// Lock check: if not admin/sale_sup, cannot delete if locked
if (!in_array($account_type, ['admin', 'sale_sup'])) {
    $stmt_check_lock = $conn->prepare("SELECT COUNT(*) FROM ims_tires_plan_forecast_volume_tires $where AND is_locked = 1");
    $stmt_check_lock->execute($params);
    if ($stmt_check_lock->fetchColumn() > 0) {
        echo json_encode(['success' => false, 'message' => 'ไม่สามารถลบข้อมูลได้ เนื่องจากมีข้อมูลที่ถูกล็อคแล้ว (กรุณาติดต่อ Admin เพื่อปลดล็อคก่อน)']);
        exit;
    }
}

try {
    $sql_check = "SELECT COUNT(*) as cnt FROM ims_tires_plan_forecast_volume_tires $where";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->execute($params);
    $count = $stmt_check->fetch(PDO::FETCH_ASSOC)['cnt'] ?? 0;

    if ($count == 0) {
        echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลตามเงื่อนไขที่ระบุ']);
        exit;
    }

    $sql_del = "DELETE FROM ims_tires_plan_forecast_volume_tires $where";
    $stmt_del = $conn->prepare($sql_del);
    $stmt_del->execute($params);

    echo json_encode([
        'success' => true, 
        'message' => 'ลบข้อมูลสำเร็จ ทั้งหมด ' . $count . ' รายการ',
        'count' => $count
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
