<?php
session_start();
include('config/connect_db.php');

if (!isset($_SESSION['alogin']) || $_SESSION['alogin'] == '') {
    exit;
}

$year = $_POST['year'] ?? date('Y');
$sale_name_filter = $_POST['sale_name'] ?? '';
$ar_name_filter = $_POST['ar_name'] ?? '';
$sku_code_filter = $_POST['sku_code'] ?? '';
$search = $_POST['search']['value'] ?? '';

$account_type = $_SESSION['account_type'] ?? '';
$first_name = $_SESSION['first_name'] ?? '';

$where = "WHERE p.forcast_year = :year";
$params = [':year' => $year];

// Apply access control
if ($account_type === 'sale_user') {
    $where .= " AND p.sale_name = :first_name";
    $params[':first_name'] = $first_name;
} elseif ($account_type === 'take') {
    $where .= " AND p.take_name = :first_name";
    $params[':first_name'] = $first_name;
} elseif (!empty($sale_name_filter)) {
    $where .= " AND p.sale_name = :sale_name_filter";
    $params[':sale_name_filter'] = $sale_name_filter;
}

if (!empty($ar_name_filter)) {
    $where .= " AND p.ar_name = :ar_name_filter";
    $params[':ar_name_filter'] = $ar_name_filter;
}

// Multi-select SKU filter
if (!empty($sku_code_filter)) {
    if (is_array($sku_code_filter)) {
        $sku_placeholders = [];
        foreach ($sku_code_filter as $idx => $sku) {
            $ph = ":sku_" . $idx;
            $sku_placeholders[] = $ph;
            $params[$ph] = $sku;
        }
        $where .= " AND p.sku_code IN (" . implode(',', $sku_placeholders) . ")";
    } else {
        $where .= " AND p.sku_code = :sku_code_filter";
        $params[':sku_code_filter'] = $sku_code_filter;
    }
}

if (!empty($search)) {
    $where .= " AND (p.sale_name LIKE :search OR p.ar_name LIKE :search OR p.sku_code LIKE :search)";
    $params[':search'] = "%$search%";
}

// Complex SQL to join Plan data with actual sales from ims_data_sale_sac_all
// We pivot both Plan (Target/Forecast) and Actual Sales
$sql = "SELECT 
    p.sale_name, p.ar_name, p.sku_code, p.brand_code,
    
    -- Actual Sales per month
    SUM(CASE WHEN CAST(p.forcast_month AS UNSIGNED) = 1 THEN p.sales_month_1 ELSE 0 END) as s1,
    SUM(CASE WHEN CAST(p.forcast_month AS UNSIGNED) = 2 THEN p.sales_month_1 ELSE 0 END) as s2,
    SUM(CASE WHEN CAST(p.forcast_month AS UNSIGNED) = 3 THEN p.sales_month_1 ELSE 0 END) as s3,
    SUM(CASE WHEN CAST(p.forcast_month AS UNSIGNED) = 4 THEN p.sales_month_1 ELSE 0 END) as s4,
    SUM(CASE WHEN CAST(p.forcast_month AS UNSIGNED) = 5 THEN p.sales_month_1 ELSE 0 END) as s5,
    SUM(CASE WHEN CAST(p.forcast_month AS UNSIGNED) = 6 THEN p.sales_month_1 ELSE 0 END) as s6,
    SUM(CASE WHEN CAST(p.forcast_month AS UNSIGNED) = 7 THEN p.sales_month_1 ELSE 0 END) as s7,
    SUM(CASE WHEN CAST(p.forcast_month AS UNSIGNED) = 8 THEN p.sales_month_1 ELSE 0 END) as s8,
    SUM(CASE WHEN CAST(p.forcast_month AS UNSIGNED) = 9 THEN p.sales_month_1 ELSE 0 END) as s9,
    SUM(CASE WHEN CAST(p.forcast_month AS UNSIGNED) = 10 THEN p.sales_month_1 ELSE 0 END) as s10,
    SUM(CASE WHEN CAST(p.forcast_month AS UNSIGNED) = 11 THEN p.sales_month_1 ELSE 0 END) as s11,
    SUM(CASE WHEN CAST(p.forcast_month AS UNSIGNED) = 12 THEN p.sales_month_1 ELSE 0 END) as s12,

    -- Target per month
    SUM(CASE WHEN CAST(p.forcast_month AS UNSIGNED) = 1 THEN p.target_qty_month ELSE 0 END) as t1,
    SUM(CASE WHEN CAST(p.forcast_month AS UNSIGNED) = 2 THEN p.target_qty_month ELSE 0 END) as t2,
    SUM(CASE WHEN CAST(p.forcast_month AS UNSIGNED) = 3 THEN p.target_qty_month ELSE 0 END) as t3,
    SUM(CASE WHEN CAST(p.forcast_month AS UNSIGNED) = 4 THEN p.target_qty_month ELSE 0 END) as t4,
    SUM(CASE WHEN CAST(p.forcast_month AS UNSIGNED) = 5 THEN p.target_qty_month ELSE 0 END) as t5,
    SUM(CASE WHEN CAST(p.forcast_month AS UNSIGNED) = 6 THEN p.target_qty_month ELSE 0 END) as t6,
    SUM(CASE WHEN CAST(p.forcast_month AS UNSIGNED) = 7 THEN p.target_qty_month ELSE 0 END) as t7,
    SUM(CASE WHEN CAST(p.forcast_month AS UNSIGNED) = 8 THEN p.target_qty_month ELSE 0 END) as t8,
    SUM(CASE WHEN CAST(p.forcast_month AS UNSIGNED) = 9 THEN p.target_qty_month ELSE 0 END) as t9,
    SUM(CASE WHEN CAST(p.forcast_month AS UNSIGNED) = 10 THEN p.target_qty_month ELSE 0 END) as t10,
    SUM(CASE WHEN CAST(p.forcast_month AS UNSIGNED) = 11 THEN p.target_qty_month ELSE 0 END) as t11,
    SUM(CASE WHEN CAST(p.forcast_month AS UNSIGNED) = 12 THEN p.target_qty_month ELSE 0 END) as t12,
    
    -- Forecast per month
    SUM(CASE WHEN CAST(p.forcast_month AS UNSIGNED) = 1 THEN p.forecast_qty_month_1 ELSE 0 END) as f1,
    SUM(CASE WHEN CAST(p.forcast_month AS UNSIGNED) = 2 THEN p.forecast_qty_month_1 ELSE 0 END) as f2,
    SUM(CASE WHEN CAST(p.forcast_month AS UNSIGNED) = 3 THEN p.forecast_qty_month_1 ELSE 0 END) as f3,
    SUM(CASE WHEN CAST(p.forcast_month AS UNSIGNED) = 4 THEN p.forecast_qty_month_1 ELSE 0 END) as f4,
    SUM(CASE WHEN CAST(p.forcast_month AS UNSIGNED) = 5 THEN p.forecast_qty_month_1 ELSE 0 END) as f5,
    SUM(CASE WHEN CAST(p.forcast_month AS UNSIGNED) = 6 THEN p.forecast_qty_month_1 ELSE 0 END) as f6,
    SUM(CASE WHEN CAST(p.forcast_month AS UNSIGNED) = 7 THEN p.forecast_qty_month_1 ELSE 0 END) as f7,
    SUM(CASE WHEN CAST(p.forcast_month AS UNSIGNED) = 8 THEN p.forecast_qty_month_1 ELSE 0 END) as f8,
    SUM(CASE WHEN CAST(p.forcast_month AS UNSIGNED) = 9 THEN p.forecast_qty_month_1 ELSE 0 END) as f9,
    SUM(CASE WHEN CAST(p.forcast_month AS UNSIGNED) = 10 THEN p.forecast_qty_month_1 ELSE 0 END) as f10,
    SUM(CASE WHEN CAST(p.forcast_month AS UNSIGNED) = 11 THEN p.forecast_qty_month_1 ELSE 0 END) as f11,
    SUM(CASE WHEN CAST(p.forcast_month AS UNSIGNED) = 12 THEN p.forecast_qty_month_1 ELSE 0 END) as f12,

    SUM(p.sales_month_1) as total_sales,
    SUM(p.target_qty_month) as total_target,
    SUM(p.forecast_qty_month_1) as total_forecast
FROM ims_tires_plan_forecast p
$where
GROUP BY p.sale_name, p.ar_name, p.sku_code
ORDER BY p.sale_name ASC, p.ar_name ASC, p.sku_code ASC";

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);

$result = [];
foreach ($data as $row) {
    $result[] = [
        "sale_name" => $row['sale_name'],
        "ar_name" => $row['ar_name'],
        "sku_code" => $row['sku_code'],
        "brand_code" => $row['brand_code'],
        "m1" => ["actual" => number_format($row['s1'], 0), "target" => number_format($row['t1'], 0), "forecast" => number_format($row['f1'], 0)],
        "m2" => ["actual" => number_format($row['s2'], 0), "target" => number_format($row['t2'], 0), "forecast" => number_format($row['f2'], 0)],
        "m3" => ["actual" => number_format($row['s3'], 0), "target" => number_format($row['t3'], 0), "forecast" => number_format($row['f3'], 0)],
        "m4" => ["actual" => number_format($row['s4'], 0), "target" => number_format($row['t4'], 0), "forecast" => number_format($row['f4'], 0)],
        "m5" => ["actual" => number_format($row['s5'], 0), "target" => number_format($row['t5'], 0), "forecast" => number_format($row['f5'], 0)],
        "m6" => ["actual" => number_format($row['s6'], 0), "target" => number_format($row['t6'], 0), "forecast" => number_format($row['f6'], 0)],
        "m7" => ["actual" => number_format($row['s7'], 0), "target" => number_format($row['t7'], 0), "forecast" => number_format($row['f7'], 0)],
        "m8" => ["actual" => number_format($row['s8'], 0), "target" => number_format($row['t8'], 0), "forecast" => number_format($row['f8'], 0)],
        "m9" => ["actual" => number_format($row['s9'], 0), "target" => number_format($row['t9'], 0), "forecast" => number_format($row['f9'], 0)],
        "m10" => ["actual" => number_format($row['s10'], 0), "target" => number_format($row['t10'], 0), "forecast" => number_format($row['f10'], 0)],
        "m11" => ["actual" => number_format($row['s11'], 0), "target" => number_format($row['t11'], 0), "forecast" => number_format($row['f11'], 0)],
        "m12" => ["actual" => number_format($row['s12'], 0), "target" => number_format($row['t12'], 0), "forecast" => number_format($row['f12'], 0)],
        "total_sales" => number_format($row['total_sales'], 0),
        "total_target" => number_format($row['total_target'], 0),
        "total_forecast" => number_format($row['total_forecast'], 0)
    ];
}

echo json_encode([
    "draw" => intval($_POST['draw'] ?? 0),
    "recordsTotal" => count($result),
    "recordsFiltered" => count($result),
    "data" => $result
]);
