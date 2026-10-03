<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('config/connect_db.php');

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['alogin']) || $_SESSION['alogin'] == '') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$month = $_POST['month'] ?? '';
$year = $_POST['year'] ?? '';
$sale_name = $_POST['sale_name'] ?? '';
$brand_code = $_POST['brand_code'] ?? '';
$product_type = $_POST['product_type'] ?? '';

if (empty($month) || empty($year)) {
    echo json_encode(['success' => false, 'message' => 'Invalid parameters']);
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

try {
    $sql = "SELECT ar_code, ar_name, sale_name, brand_code, product_type, sku_code, sku_name, forecast_qty_month_1 as forecast_qty, volume, is_locked 
            FROM ims_tires_plan_forecast_volume_tires 
            $where 
            ORDER BY ar_name ASC, sku_name ASC 
            LIMIT 500";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $rows]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
