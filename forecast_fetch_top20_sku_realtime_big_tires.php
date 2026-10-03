<?php
session_start();
include('config/connect_db.php');

header('Content-Type: application/json');

// Set resource limits
ini_set('memory_limit', '512M');
set_time_limit(180);

if (!isset($_SESSION['alogin']) || $_SESSION['alogin'] == '') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$month = $_POST['month'] ?? '';
$year = $_POST['year'] ?? '';
$sale_name = $_POST['sale_name'] ?? '';
$take_name = $_POST['take_name'] ?? '';
$ar_name = $_POST['ar_name'] ?? '';
$ar_code = $_POST['ar_code'] ?? '';
$province = $_POST['province'] ?? '';
$amphur = $_POST['amphur'] ?? '';
$zone = $_POST['zone'] ?? '';

$account_type = $_SESSION['account_type'] ?? '';
$first_name = $_SESSION['first_name'] ?? '';

if (empty($month) || empty($year)) {
    echo json_encode(['success' => false, 'message' => 'กรุณาระบุเดือนและปี']);
    exit;
}

$act_month = strval(intval($month));
$act_month_padded = str_pad($act_month, 2, '0', STR_PAD_LEFT);

$where = "WHERE (p.forcast_month = :month OR p.forcast_month = :month_padded OR p.forcast_month = :month_int) AND p.forcast_year = :year";
$params = [
    ':month' => $month,
    ':month_padded' => $act_month_padded,
    ':month_int' => $act_month,
    ':year' => $year
];

// Security filter based on session role
if ($account_type === 'sale_user') {
    $where .= " AND p.sale_name = :first_name";
    $params[':first_name'] = $first_name;
} elseif ($account_type === 'take') {
    $where .= " AND p.take_name = :first_name";
    $params[':first_name'] = $first_name;
}

// User filter from UI
if (!empty($sale_name)) {
    $where .= " AND p.sale_name = :sale_name";
    $params[':sale_name'] = $sale_name;
}
if (!empty($take_name)) {
    $where .= " AND p.take_name = :take_name";
    $params[':take_name'] = $take_name;
}
if (!empty($ar_code)) {
    $where .= " AND p.ar_code = :ar_code";
    $params[':ar_code'] = $ar_code;
} elseif (!empty($ar_name)) {
    $where .= " AND p.ar_name = :ar_name";
    $params[':ar_name'] = $ar_name;
}
if (!empty($province)) {
    $where .= " AND p.province = :province";
    $params[':province'] = $province;
}
if (!empty($amphur)) {
    $where .= " AND p.amphur = :amphur";
    $params[':amphur'] = $amphur;
}
if (!empty($zone)) {
    $where .= " AND p.zone = :zone";
    $params[':zone'] = $zone;
}

try {
    $sql = "SELECT 
                p.*,
                IFNULL(act.actual_qty, 0) as sales_actual_month
            FROM ims_tires_plan_forecast_realtime_big_tirs p
            LEFT JOIN (
                SELECT AR_CODE, SKU_CODE, SUM(CAST(TRD_QTY AS DECIMAL(10,2))) as actual_qty
                FROM ims_data_sale_sac_all
                WHERE (DI_MONTH = :act_month OR DI_MONTH = :act_month_padded) AND DI_YEAR = :year
                GROUP BY AR_CODE, SKU_CODE
            ) act ON p.ar_code = act.AR_CODE AND p.sku_code = act.SKU_CODE
            $where
            ORDER BY GREATEST(IFNULL(act.actual_qty, 0), p.forecast_qty_month_1) DESC, IFNULL(act.actual_qty, 0) DESC, p.forecast_qty_month_1 DESC
            LIMIT 20";

    $params_query = array_merge($params, [
        ':act_month' => $act_month,
        ':act_month_padded' => $act_month_padded
    ]);

    $stmt = $conn->prepare($sql);
    $stmt->execute($params_query);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $summary = [
        'total_actual' => 0,
        'total_forecast' => 0,
        'total_target' => 0,
        'store_count' => 0,
        'sku_count' => count($rows)
    ];

    $stores_map = [];

    foreach ($rows as $index => &$row) {
        $row['rank'] = $index + 1;
        $row['avg_per_month_year'] = (float)($row['avg_per_month_year'] ?? 0);
        $row['max_qty_year'] = (float)($row['max_qty_year'] ?? 0);
        $row['min_qty_year'] = (float)($row['min_qty_year'] ?? 0);
        $row['sales_month_3'] = (float)($row['sales_month_3'] ?? 0);
        $row['sales_month_2'] = (float)($row['sales_month_2'] ?? 0);
        $row['sales_month_1'] = (float)($row['sales_month_1'] ?? 0);
        $row['avg_3_month'] = (float)($row['avg_3_month'] ?? 0);
        $row['target_qty_month'] = (float)($row['target_qty_month'] ?? 0);
        $row['forecast_qty_month_1'] = (float)($row['forecast_qty_month_1'] ?? 0);
        $row['compare_forecast_month_1'] = (float)($row['compare_forecast_month_1'] ?? 0);
        $row['compare_sales_percent'] = (float)($row['compare_sales_percent'] ?? 0);
        $row['sales_actual_month'] = (float)($row['sales_actual_month'] ?? 0);

        $diff_actual = $row['sales_actual_month'] - $row['forecast_qty_month_1'];
        $row['diff_actual'] = $diff_actual;

        $summary['total_actual'] += $row['sales_actual_month'];
        $summary['total_forecast'] += $row['forecast_qty_month_1'];
        $summary['total_target'] += $row['target_qty_month'];
        $stores_map[$row['ar_code']] = true;
    }
    unset($row);

    $summary['store_count'] = count($stores_map);

    // Fetch Top 20 Stores aggregated by total actual sales / forecast
    $sql_stores = "SELECT 
                p.ar_code,
                p.ar_name,
                p.sale_name,
                p.take_name,
                COUNT(DISTINCT p.sku_code) as sku_count,
                SUM(p.target_qty_month) as total_target,
                SUM(p.forecast_qty_month_1) as total_forecast,
                SUM(p.avg_3_month) as total_avg_3m,
                SUM(p.avg_per_month_year) as total_avg_12m,
                IFNULL(act_store.actual_qty, 0) as total_actual
            FROM ims_tires_plan_forecast_realtime_big_tirs p
            LEFT JOIN (
                SELECT AR_CODE, SUM(CAST(TRD_QTY AS DECIMAL(10,2))) as actual_qty
                FROM ims_data_sale_sac_all
                WHERE (DI_MONTH = :act_month OR DI_MONTH = :act_month_padded) AND DI_YEAR = :year
                GROUP BY AR_CODE
            ) act_store ON p.ar_code = act_store.AR_CODE
            $where
            GROUP BY p.ar_code, p.ar_name, p.sale_name, p.take_name
            ORDER BY GREATEST(IFNULL(act_store.actual_qty, 0), SUM(p.forecast_qty_month_1)) DESC, IFNULL(act_store.actual_qty, 0) DESC, SUM(p.forecast_qty_month_1) DESC
            LIMIT 20";

    $stmt_stores = $conn->prepare($sql_stores);
    $stmt_stores->execute($params_query);
    $top_stores = $stmt_stores->fetchAll(PDO::FETCH_ASSOC);

    foreach ($top_stores as $s_index => &$s_row) {
        $s_row['rank'] = $s_index + 1;
        $s_row['total_target'] = (float)($s_row['total_target'] ?? 0);
        $s_row['total_forecast'] = (float)($s_row['total_forecast'] ?? 0);
        $s_row['total_actual'] = (float)($s_row['total_actual'] ?? 0);
        $s_row['total_avg_3m'] = (float)($s_row['total_avg_3m'] ?? 0);
        $s_row['total_avg_12m'] = (float)($s_row['total_avg_12m'] ?? 0);
        $s_row['sku_count'] = (int)($s_row['sku_count'] ?? 0);
        $s_row['diff_actual'] = $s_row['total_actual'] - $s_row['total_forecast'];
    }
    unset($s_row);

    echo json_encode([
        'success' => true,
        'data' => $rows,
        'top_stores' => $top_stores,
        'summary' => $summary
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
