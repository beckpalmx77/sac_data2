<?php
session_start();
include('config/connect_db.php');

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['alogin']) || $_SESSION['alogin'] == '') {
    echo json_encode(['success' => false, 'message' => 'Session expired']);
    exit;
}

$month = $_POST['month'] ?? '';
$year = $_POST['year'] ?? '';
$sale_name = $_POST['sale_name'] ?? '';
$brand_code = $_POST['brand_code'] ?? '';

if (empty($month) || empty($year)) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
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
    $sql = "SELECT ar_code, ar_name, brand_code, sku_code, forecast_qty_month_1, sale_name 
            FROM ims_tires_plan_forecast_realtime 
            $where 
            ORDER BY ar_code ASC, sku_code ASC";
    
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
