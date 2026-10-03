<?php
session_start();
include('config/connect_db.php');

if (!isset($_SESSION['alogin']) || $_SESSION['alogin'] == '') {
    exit;
}

$year = $_POST['year'] ?? date('Y');
$sale_name_filter = $_POST['sale_name'] ?? '';
$search = $_POST['search']['value'] ?? '';

$account_type = $_SESSION['account_type'] ?? '';
$first_name = $_SESSION['first_name'] ?? '';

$where = "WHERE forcast_year = :year";
$params = [':year' => $year];

if ($account_type === 'sale_user') {
    $where .= " AND sale_name = :first_name";
    $params[':first_name'] = $first_name;
} elseif ($account_type === 'take') {
    $where .= " AND take_name = :first_name";
    $params[':first_name'] = $first_name;
} elseif (!empty($sale_name_filter)) {
    // Admin manual filter
    $where .= " AND sale_name = :sale_name_filter";
    $params[':sale_name_filter'] = $sale_name_filter;
}

if (!empty($search)) {
    $where .= " AND (sale_name LIKE :search)";
    $params[':search'] = "%$search%";
}

// SQL to Pivot data by month
// We will sum target_qty_month and forecast_qty_month_1 for each month
$sql = "SELECT 
    sale_name,
    SUM(CASE WHEN CAST(forcast_month AS UNSIGNED) = 1 THEN target_qty_month ELSE 0 END) as t1,
    SUM(CASE WHEN CAST(forcast_month AS UNSIGNED) = 2 THEN target_qty_month ELSE 0 END) as t2,
    SUM(CASE WHEN CAST(forcast_month AS UNSIGNED) = 3 THEN target_qty_month ELSE 0 END) as t3,
    SUM(CASE WHEN CAST(forcast_month AS UNSIGNED) = 4 THEN target_qty_month ELSE 0 END) as t4,
    SUM(CASE WHEN CAST(forcast_month AS UNSIGNED) = 5 THEN target_qty_month ELSE 0 END) as t5,
    SUM(CASE WHEN CAST(forcast_month AS UNSIGNED) = 6 THEN target_qty_month ELSE 0 END) as t6,
    SUM(CASE WHEN CAST(forcast_month AS UNSIGNED) = 7 THEN target_qty_month ELSE 0 END) as t7,
    SUM(CASE WHEN CAST(forcast_month AS UNSIGNED) = 8 THEN target_qty_month ELSE 0 END) as t8,
    SUM(CASE WHEN CAST(forcast_month AS UNSIGNED) = 9 THEN target_qty_month ELSE 0 END) as t9,
    SUM(CASE WHEN CAST(forcast_month AS UNSIGNED) = 10 THEN target_qty_month ELSE 0 END) as t10,
    SUM(CASE WHEN CAST(forcast_month AS UNSIGNED) = 11 THEN target_qty_month ELSE 0 END) as t11,
    SUM(CASE WHEN CAST(forcast_month AS UNSIGNED) = 12 THEN target_qty_month ELSE 0 END) as t12,
    
    SUM(CASE WHEN CAST(forcast_month AS UNSIGNED) = 1 THEN forecast_qty_month_1 ELSE 0 END) as f1,
    SUM(CASE WHEN CAST(forcast_month AS UNSIGNED) = 2 THEN forecast_qty_month_1 ELSE 0 END) as f2,
    SUM(CASE WHEN CAST(forcast_month AS UNSIGNED) = 3 THEN forecast_qty_month_1 ELSE 0 END) as f3,
    SUM(CASE WHEN CAST(forcast_month AS UNSIGNED) = 4 THEN forecast_qty_month_1 ELSE 0 END) as f4,
    SUM(CASE WHEN CAST(forcast_month AS UNSIGNED) = 5 THEN forecast_qty_month_1 ELSE 0 END) as f5,
    SUM(CASE WHEN CAST(forcast_month AS UNSIGNED) = 6 THEN forecast_qty_month_1 ELSE 0 END) as f6,
    SUM(CASE WHEN CAST(forcast_month AS UNSIGNED) = 7 THEN forecast_qty_month_1 ELSE 0 END) as f7,
    SUM(CASE WHEN CAST(forcast_month AS UNSIGNED) = 8 THEN forecast_qty_month_1 ELSE 0 END) as f8,
    SUM(CASE WHEN CAST(forcast_month AS UNSIGNED) = 9 THEN forecast_qty_month_1 ELSE 0 END) as f9,
    SUM(CASE WHEN CAST(forcast_month AS UNSIGNED) = 10 THEN forecast_qty_month_1 ELSE 0 END) as f10,
    SUM(CASE WHEN CAST(forcast_month AS UNSIGNED) = 11 THEN forecast_qty_month_1 ELSE 0 END) as f11,
    SUM(CASE WHEN CAST(forcast_month AS UNSIGNED) = 12 THEN forecast_qty_month_1 ELSE 0 END) as f12,
    
    SUM(target_qty_month) as total_target,
    SUM(forecast_qty_month_1) as total_forecast
FROM ims_tires_plan_forecast
$where
GROUP BY sale_name
ORDER BY sale_name ASC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$result = [];
foreach ($data as $row) {
    $target_sum = floatval($row['total_target']);
    $forecast_sum = floatval($row['total_forecast']);
    $achievement = ($target_sum > 0) ? ($forecast_sum / $target_sum) * 100 : 0;

    $result[] = [
        "sale_name" => $row['sale_name'],
        "m1" => ["target" => number_format($row['t1'], 0), "forecast" => number_format($row['f1'], 0)],
        "m2" => ["target" => number_format($row['t2'], 0), "forecast" => number_format($row['f2'], 0)],
        "m3" => ["target" => number_format($row['t3'], 0), "forecast" => number_format($row['f3'], 0)],
        "m4" => ["target" => number_format($row['t4'], 0), "forecast" => number_format($row['f4'], 0)],
        "m5" => ["target" => number_format($row['t5'], 0), "forecast" => number_format($row['f5'], 0)],
        "m6" => ["target" => number_format($row['t6'], 0), "forecast" => number_format($row['f6'], 0)],
        "m7" => ["target" => number_format($row['t7'], 0), "forecast" => number_format($row['f7'], 0)],
        "m8" => ["target" => number_format($row['t8'], 0), "forecast" => number_format($row['f8'], 0)],
        "m9" => ["target" => number_format($row['t9'], 0), "forecast" => number_format($row['f9'], 0)],
        "m10" => ["target" => number_format($row['t10'], 0), "forecast" => number_format($row['f10'], 0)],
        "m11" => ["target" => number_format($row['t11'], 0), "forecast" => number_format($row['f11'], 0)],
        "m12" => ["target" => number_format($row['t12'], 0), "forecast" => number_format($row['f12'], 0)],
        "total_target" => number_format($target_sum, 0),
        "total_forecast" => number_format($forecast_sum, 0),
        "achievement" => number_format($achievement, 1) . "%"
    ];
}

echo json_encode([
    "draw" => intval($_POST['draw'] ?? 0),
    "recordsTotal" => count($result),
    "recordsFiltered" => count($result),
    "data" => $result
]);
