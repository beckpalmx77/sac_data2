<?php
session_start();
include('config/connect_db.php');

if (!isset($_SESSION['alogin']) || $_SESSION['alogin'] == '') {
    exit;
}

$month = $_POST['month'] ?? '';
$year = $_POST['year'] ?? '';
$sale_name = $_POST['sale_name'] ?? '';
$ar_name = $_POST['ar_name'] ?? '';
$search = $_POST['search']['value'] ?? '';
$start = $_POST['start'] ?? 0;
$length = $_POST['length'] ?? 10;
$order_col_idx = $_POST['order'][0]['column'] ?? 28; // Default to import_date
$order_dir = $_POST['order'][0]['dir'] ?? 'desc';

$account_type = $_SESSION['account_type'] ?? '';
$first_name = $_SESSION['first_name'] ?? '';

$columns = [
    0 => 'forcast_month', 1 => 'forcast_year', 2 => 'ar_code', 3 => 'ar_name', 
    4 => 'sale_name', 5 => 'take_name', 6 => 'team_name', 7 => 'brand_code', 
    8 => 'sku_code', 9 => 'amphur', 10 => 'province', 11 => 'zone', 
    12 => 'avg_per_month_year', 13 => 'max_qty_year', 14 => 'min_qty_year', 
    15 => 'target_qty_month', 16 => 'sales_month_1', 17 => 'sales_month_2', 
    18 => 'sales_month_3', 19 => 'avg_3_month', 20 => 'target_qty_month_2', 
    21 => 'forecast_qty_month_1', 22 => 'compare_forecast_month_1', 
    23 => 'compare_sales_percent', 24 => 'remark', 25 => 'forecast_qty_month_2', 
    26 => 'compare_forecast_month_2', 27 => 'compare_sales_percent_2', 
    28 => 'import_date'
];

$order_by = $columns[$order_col_idx] ?? 'import_date';

$where = "WHERE 1=1";
$params = [];

if ($account_type === 'sale_user') {
    $where .= " AND sale_name = :first_name";
    $params[':first_name'] = $first_name;
} elseif ($account_type === 'take') {
    $where .= " AND take_name = :first_name";
    $params[':first_name'] = $first_name;
}

if (!empty($month)) {
    $where .= " AND forcast_month = :month";
    $params[':month'] = $month;
}
if (!empty($year)) {
    $where .= " AND forcast_year = :year";
    $params[':year'] = $year;
}
if (!empty($sale_name)) {
    $where .= " AND sale_name = :sale_name";
    $params[':sale_name'] = $sale_name;
}
if (!empty($ar_name)) {
    $where .= " AND ar_name = :ar_name";
    $params[':ar_name'] = $ar_name;
}
if (!empty($search)) {
    $where .= " AND (ar_code LIKE :search OR ar_name LIKE :search OR sku_code LIKE :search OR sale_name LIKE :search OR brand_code LIKE :search)";
    $params[':search'] = "%$search%";
}

// Count total records
$sql_count = "SELECT COUNT(*) FROM ims_tires_plan_forecast $where";
$stmt_count = $conn->prepare($sql_count);
$stmt_count->execute($params);
$recordsTotal = $stmt_count->fetchColumn();

// Fetch data
$sql = "SELECT * FROM ims_tires_plan_forecast $where ORDER BY $order_by $order_dir LIMIT $start, $length";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$thai_months = [
    1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม',
    4 => 'เมษายน', 5 => 'พฤษภาคม', 6 => 'มิถุนายน',
    7 => 'กรกฎาคม', 8 => 'สิงหาคม', 9 => 'กันยายน',
    10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
];

$result = [];
foreach ($data as $row) {
    $result[] = [
        "forcast_month_name" => $thai_months[intval($row['forcast_month'])] ?? $row['forcast_month'],
        "forcast_year" => $row['forcast_year'],
        "ar_code" => $row['ar_code'],
        "ar_name" => $row['ar_name'],
        "sale_name" => $row['sale_name'],
        "take_name" => $row['take_name'],
        "team_name" => $row['team_name'],
        "brand_code" => $row['brand_code'],
        "sku_code" => $row['sku_code'],
        "amphur" => $row['amphur'],
        "province" => $row['province'],
        "zone" => $row['zone'],
        "avg_per_month_year" => number_format($row['avg_per_month_year'], 0),
        "max_qty_year" => number_format($row['max_qty_year'], 0),
        "min_qty_year" => number_format($row['min_qty_year'], 0),
        "target_qty_month" => number_format($row['target_qty_month'], 0),
        "sales_month_1" => number_format($row['sales_month_1'], 0),
        "sales_month_2" => number_format($row['sales_month_2'], 0),
        "sales_month_3" => number_format($row['sales_month_3'], 0),
        "avg_3_month" => number_format($row['avg_3_month'], 0),
        "target_qty_month_2" => number_format($row['target_qty_month_2'], 0),
        "forecast_qty_month_1" => number_format($row['forecast_qty_month_1'], 0),
        "compare_forecast_month_1" => number_format($row['compare_forecast_month_1'], 0),
        "compare_sales_percent" => number_format($row['compare_sales_percent'], 0) . '%',
        "remark" => $row['remark'],
        "forecast_qty_month_2" => number_format($row['forecast_qty_month_2'], 0),
        "compare_forecast_month_2" => number_format($row['compare_forecast_month_2'], 0),
        "compare_sales_percent_2" => number_format($row['compare_sales_percent_2'], 0) . '%',
        "import_date" => date('d/m/Y H:i', strtotime($row['import_date']))
    ];
}

echo json_encode([
    "draw" => intval($_POST['draw']),
    "recordsTotal" => $recordsTotal,
    "recordsFiltered" => $recordsTotal,
    "data" => $result
]);
