<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('config/connect_db.php');

if (!isset($_SESSION['alogin']) || $_SESSION['alogin'] == '') {
    echo json_encode(['success' => false, 'message' => 'Session expired']);
    exit;
}

$month = $_POST['month'] ?? '';
$year = $_POST['year'] ?? '';
$sale_name = $_POST['sale_name'] ?? '';
$take_name = $_POST['take_name'] ?? '';
$ar_name = $_POST['ar_name'] ?? '';
$product_type = $_POST['product_type'] ?? '';
$requested_brands = $_POST['brands'] ?? [];

$account_type = $_SESSION['account_type'] ?? '';
$first_name = $_SESSION['first_name'] ?? '';

if (empty($month) || empty($year)) {
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

$month_padded = str_pad($month, 2, '0', STR_PAD_LEFT);
$month_int = strval(intval($month));

$where = "WHERE (p.forcast_month = :month OR p.forcast_month = :month_padded OR p.forcast_month = :month_int) AND p.forcast_year = :year";
$params = [
    ':month' => $month,
    ':month_padded' => $month_padded,
    ':month_int' => $month_int,
    ':year' => $year
];

if ($account_type === 'sale_user') {
    $where .= " AND p.sale_name = :first_name";
    $params[':first_name'] = $first_name;
} elseif ($account_type === 'take') {
    $where .= " AND p.take_name = :first_name";
    $params[':first_name'] = $first_name;
} else {
    if (!empty($sale_name)) {
        $where .= " AND p.sale_name = :sale_name";
        $params[':sale_name'] = $sale_name;
    }
}

if (!empty($take_name)) {
    $where .= " AND p.take_name = :take_name";
    $params[':take_name'] = $take_name;
}

if (!empty($ar_name)) {
    $where .= " AND p.ar_name = :ar_name";
    $params[':ar_name'] = $ar_name;
}

if (!empty($product_type)) {
    $where .= " AND p.product_type = :product_type";
    $params[':product_type'] = $product_type;
}

try {
    $sql = "SELECT 
                IFNULL(p.brand_code, 'N/A') as brand,
                COUNT(DISTINCT p.ar_code) as total_customers,
                COUNT(p.id) as total_items,
                SUM(p.forecast_qty_month_1) as total_forecast_qty,
                SUM(p.forecast_qty_month_1 * p.volume) as total_volume,
                SUM(p.avg_3_month) as total_avg3m,
                SUM(p.compare_forecast_month_1) as total_diff
            FROM ims_tires_plan_forecast_volume_tires p
            $where
            GROUP BY p.brand_code
            ORDER BY total_forecast_qty DESC";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'data' => $rows]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
