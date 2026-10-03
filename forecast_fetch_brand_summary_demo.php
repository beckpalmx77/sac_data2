<?php
session_start();
include('config/connect_db.php');

if (!isset($_SESSION['alogin']) || $_SESSION['alogin'] == '') {
    echo json_encode(['success' => false, 'message' => 'Session expired']);
    exit;
}

$month = $_POST['month'] ?? '';
$year = $_POST['year'] ?? '';
$sale_name = $_POST['sale_name'] ?? '';
$ar_name = $_POST['ar_name'] ?? '';

$account_type = $_SESSION['account_type'] ?? '';
$first_name = $_SESSION['first_name'] ?? '';

if (empty($month) || empty($year)) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$where = "WHERE forcast_month = :month AND forcast_year = :year";
$params = [':month' => $month, ':year' => $year];

// Security Filter
if ($account_type === 'sale_user') {
    $where .= " AND sale_name = :first_name";
    $params[':first_name'] = $first_name;
} elseif ($account_type === 'take') {
    $where .= " AND take_name = :first_name";
    $params[':first_name'] = $first_name;
}

// User Filter from UI
if (!empty($sale_name)) {
    $where .= " AND sale_name = :sale_name";
    $params[':sale_name'] = $sale_name;
}
if (!empty($ar_name)) {
    $where .= " AND ar_name = :ar_name";
    $params[':ar_name'] = $ar_name;
}

$sql = "SELECT 
            brand_code,
            SUM(avg_per_month_year) as total_avg_12m,
            SUM(sales_month_1) as total_m1,
            SUM(sales_month_2) as total_m2,
            SUM(sales_month_3) as total_m3,
            SUM(avg_3_month) as total_avg_3m,
            SUM(forecast_qty_month_1) as total_forecast,
            SUM(compare_forecast_month_1) as total_diff
        FROM ims_tires_plan_forecast 
        $where 
        GROUP BY brand_code 
        ORDER BY total_forecast DESC";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode(['success' => true, 'data' => $data]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
