<?php
session_start();
include('config/connect_db.php');

// Set resource limits for large datasets
ini_set('memory_limit', '512M');
set_time_limit(300);

if (!isset($_SESSION['alogin']) || $_SESSION['alogin'] == '') {
    exit;
}

$month = $_POST['month'] ?? '';
$year = $_POST['year'] ?? '';
$sale_name = $_POST['sale_name'] ?? '';
$take_name = $_POST['take_name'] ?? '';
$ar_name = $_POST['ar_name'] ?? '';
$brand_code = $_POST['brand_code'] ?? '';
$qty_condition = $_POST['qty_condition'] ?? 'all';
$province = $_POST['province'] ?? '';
$amphur = $_POST['amphur'] ?? '';
$zone = $_POST['zone'] ?? '';
$search = $_POST['search']['value'] ?? '';
$start = intval($_POST['start'] ?? 0);
$length = intval($_POST['length'] ?? 10);
$order_col_idx = intval($_POST['order'][0]['column'] ?? 28);
$order_dir = $_POST['order'][0]['dir'] ?? 'desc';

$account_type = $_SESSION['account_type'] ?? '';
$first_name = $_SESSION['first_name'] ?? '';

$columns = [
    0 => 'p.ar_code', 
    1 => 'p.ar_name', 
    2 => 'p.brand_code', 
    3 => 'p.sku_code', 
    4 => 'p.avg_per_month_year', 
    5 => 'p.max_qty_year', 
    6 => 'p.min_qty_year', 
    7 => 'p.sales_month_3', 
    8 => 'p.sales_month_2', 
    9 => 'p.sales_month_1', 
    10 => 'p.avg_3_month', 
    11 => 'p.target_qty_month', 
    12 => 'p.forecast_qty_month_1', 
    13 => 'p.compare_forecast_month_1', 
    14 => 'p.compare_sales_percent', 
    15 => 'sales_actual_month',
    16 => 'sales_actual_month', 
    17 => 'p.remark', 
    18 => 'p.sale_name', 
    19 => 'p.take_name', 
    20 => 'amphur', 
    21 => 'province', 
    22 => 'p.import_date',
    23 => 'p.id'
];

$order_by = $columns[$order_col_idx] ?? 'p.import_date';

// If month and year are empty, return empty result
if (empty($month) || empty($year)) {
    header('Content-Type: application/json');
    echo json_encode([
        "draw" => intval($_POST['draw'] ?? 0),
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "data" => []
    ]);
    exit;
}

// 1. First Load Check: If load_time = 0, trigger initial sync
$stmt_check_sync = $conn->prepare("SELECT COUNT(*) FROM ims_tires_plan_forecast_realtime WHERE forcast_month = ? AND forcast_year = ? AND load_time > 0");
$stmt_check_sync->execute([$month, $year]);
$has_synced = $stmt_check_sync->fetchColumn();

if (!$has_synced) {
    $m1 = $month - 1; $y1 = $year; if ($m1 <= 0) { $m1 += 12; $y1--; }
    $m2 = $month - 2; $y2 = $year; if ($m2 <= 0) { $m2 += 12; $y2--; }
    $m3 = $month - 3; $y3 = $year; if ($m3 <= 0) { $m3 += 12; $y3--; }
    $ly = $year - 1;

    $sql_sync = "UPDATE ims_tires_plan_forecast_realtime p
    LEFT JOIN (
        SELECT AR_CODE, SKU_CODE, 
            SUM(CASE WHEN DI_MONTH = ? AND DI_YEAR = ? THEN CAST(TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as s1,
            SUM(CASE WHEN DI_MONTH = ? AND DI_YEAR = ? THEN CAST(TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as s2,
            SUM(CASE WHEN DI_MONTH = ? AND DI_YEAR = ? THEN CAST(TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as s3,
            SUM(CASE WHEN DI_YEAR = ? THEN CAST(TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) / 12 as avg_ly
        FROM ims_data_sale_sac_all
        WHERE (DI_YEAR = ? AND DI_MONTH = ?) OR (DI_YEAR = ? AND DI_MONTH = ?) OR (DI_YEAR = ? AND DI_MONTH = ?) OR (DI_YEAR = ?)
        GROUP BY AR_CODE, SKU_CODE
    ) s ON p.ar_code = s.AR_CODE AND p.sku_code = s.SKU_CODE
    SET 
        p.sales_month_1 = IFNULL(s.s1, 0),
        p.sales_month_2 = IFNULL(s.s2, 0),
        p.sales_month_3 = IFNULL(s.s3, 0),
        p.avg_per_month_year = IFNULL(s.avg_ly, 0),
        p.avg_3_month = (IFNULL(s.s1, 0) + IFNULL(s.s2, 0) + IFNULL(s.s3, 0)) / 3,
        p.compare_forecast_month_1 = p.forecast_qty_month_1 - ((IFNULL(s.s1, 0) + IFNULL(s.s2, 0) + IFNULL(s.s3, 0)) / 3),
        p.compare_sales_percent = CASE 
            WHEN (IFNULL(s.s1, 0) + IFNULL(s.s2, 0) + IFNULL(s.s3, 0)) / 3 = 0 THEN 0 
            ELSE ((p.forecast_qty_month_1 - ((IFNULL(s.s1, 0) + IFNULL(s.s2, 0) + IFNULL(s.s3, 0)) / 3)) / ((IFNULL(s.s1, 0) + IFNULL(s.s2, 0) + IFNULL(s.s3, 0)) / 3)) * 100
        END,
        p.load_time = p.load_time + 1
    WHERE p.forcast_month = ? AND p.forcast_year = ?";
    
    try {
        $stmt_sync = $conn->prepare($sql_sync);
        $stmt_sync->execute([
            $m1, $y1, $m2, $y2, $m3, $y3, $ly, // Subquery columns
            $y1, $m1, $y2, $m2, $y3, $m3, $ly, // Subquery WHERE
            $month, $year                      // Main WHERE
        ]);
    } catch (PDOException $e) {
        // error_log($e->getMessage());
    }
}

$month_str = (string)$month;
$month_padded = str_pad($month_str, 2, '0', STR_PAD_LEFT);
$month_int = strval(intval($month_str));

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
}

// Save basic filters for recordsTotal calculation
$base_where = $where;
$base_params = $params;

if (!empty($sale_name)) {
    $where .= " AND p.sale_name = :sale_name";
    $params[':sale_name'] = $sale_name;
    $base_where .= " AND p.sale_name = :sale_name";
    $base_params[':sale_name'] = $sale_name;
}
if (!empty($take_name)) {
    $where .= " AND p.take_name = :take_name";
    $params[':take_name'] = $take_name;
    $base_where .= " AND p.take_name = :take_name";
    $base_params[':take_name'] = $take_name;
}
if (!empty($ar_name)) {
    $where .= " AND p.ar_name = :ar_name";
    $params[':ar_name'] = $ar_name;
    $base_where .= " AND p.ar_name = :ar_name";
    $base_params[':ar_name'] = $ar_name;
}
if (!empty($brand_code)) {
    $where .= " AND p.brand_code = :brand_code";
    $params[':brand_code'] = $brand_code;
    $base_where .= " AND p.brand_code = :brand_code";
    $base_params[':brand_code'] = $brand_code;
}
if ($qty_condition === 'gt0_either') {
    $where .= " AND (p.target_qty_month > 0 OR p.forecast_qty_month_1 > 0)";
    $base_where .= " AND (p.target_qty_month > 0 OR p.forecast_qty_month_1 > 0)";
} elseif ($qty_condition === 'gt0_target') {
    $where .= " AND p.target_qty_month > 0";
    $base_where .= " AND p.target_qty_month > 0";
} elseif ($qty_condition === 'gt0_forecast') {
    $where .= " AND p.forecast_qty_month_1 > 0";
    $base_where .= " AND p.forecast_qty_month_1 > 0";
} elseif ($qty_condition === 'gt0_both') {
    $where .= " AND (p.target_qty_month > 0 AND p.forecast_qty_month_1 > 0)";
    $base_where .= " AND (p.target_qty_month > 0 AND p.forecast_qty_month_1 > 0)";
} elseif ($qty_condition === 'eq0_both') {
    $where .= " AND (IFNULL(p.target_qty_month, 0) = 0 AND IFNULL(p.forecast_qty_month_1, 0) = 0)";
    $base_where .= " AND (IFNULL(p.target_qty_month, 0) = 0 AND IFNULL(p.forecast_qty_month_1, 0) = 0)";
}
if (!empty($province)) {
    $where .= " AND p.province = :province";
    $params[':province'] = $province;
    $base_where .= " AND p.province = :province";
    $base_params[':province'] = $province;
}
if (!empty($amphur)) {
    $where .= " AND p.amphur = :amphur";
    $params[':amphur'] = $amphur;
    $base_where .= " AND p.amphur = :amphur";
    $base_params[':amphur'] = $amphur;
}
if (!empty($zone)) {
    $where .= " AND p.zone = :zone";
    $params[':zone'] = $zone;
    $base_where .= " AND p.zone = :zone";
    $base_params[':zone'] = $zone;
}
if (!empty($search)) {
    $where .= " AND (p.ar_code LIKE :search OR p.ar_name LIKE :search OR p.sku_code LIKE :search OR p.sale_name LIKE :search OR p.brand_code LIKE :search)";
    $params[':search'] = "%$search%";
}

try {
    // Count records (Filtered by search)
    $stmt_count = $conn->prepare("SELECT COUNT(*) FROM ims_tires_plan_forecast_realtime p $where");
    $stmt_count->execute($params);
    $recordsFiltered = intval($stmt_count->fetchColumn());

    // Total count (Skip redundant query if search is empty)
    if (empty($search)) {
        $recordsTotal = $recordsFiltered;
    } else {
        $stmt_total = $conn->prepare("SELECT COUNT(*) FROM ims_tires_plan_forecast_realtime p $base_where");
        $stmt_total->execute($base_params);
        $recordsTotal = intval($stmt_total->fetchColumn());
    }

    // Fetch data with fallback for amphur, province, zone (Optimized with Deferred Join)
    $limit_sql = "";
    if ($length != -1) {
        $limit_sql = "LIMIT $start, $length";
    }

    $act_month = strval(intval($month));
    $act_month_padded = str_pad($act_month, 2, '0', STR_PAD_LEFT);

    $sql = "SELECT p.*, 
        IF(p.amphur = '' OR p.amphur IS NULL, v.TRD_AMPHUR, p.amphur) as amphur,
        IF(p.province = '' OR p.province IS NULL, v.TRD_PROVINCE, p.province) as province,
        IF(p.zone = '' OR p.zone IS NULL, v.ZONE, p.zone) as zone,
        IFNULL(act.actual_qty, 0) as sales_actual_month
    FROM (
        SELECT p.id 
        FROM ims_tires_plan_forecast_realtime p 
        $where 
        ORDER BY $order_by $order_dir 
        $limit_sql
    ) page
    JOIN ims_tires_plan_forecast_realtime p ON p.id = page.id
    LEFT JOIN v_customer_data_sale_all_zone v ON p.ar_code = v.AR_CODE
    LEFT JOIN (
        SELECT AR_CODE, SKU_CODE, SUM(CAST(TRD_QTY AS DECIMAL(10,2))) as actual_qty
        FROM ims_product_sale_sac
        WHERE (DI_MONTH = :act_month OR DI_MONTH = :act_month_padded) AND DI_YEAR = :act_year
        GROUP BY AR_CODE, SKU_CODE
    ) act ON p.ar_code = act.AR_CODE AND p.sku_code = act.SKU_CODE
    ORDER BY $order_by $order_dir";

    $params[':act_month'] = $act_month;
    $params[':act_month_padded'] = $act_month_padded;
    $params[':act_year'] = $year;

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $thai_months = [
        1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.',
        4 => 'เม.ย.', 5 => 'พ.ค.', 6 => 'มิ.ย.',
        7 => 'ก.ค.', 8 => 'ส.ค.', 9 => 'ก.ย.',
        10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
    ];

    $result = [];
    foreach ($data as $row) {
        $avg_3m = floatval($row['avg_3_month'] ?? 0);
        $is_locked = intval($row['is_locked'] ?? 0);
        $locked_title = 'ข้อมูลถูกบันทึกและล็อคแล้ว (เฉพาะ Admin หรือหัวหน้างานที่สามารถปลดล็อคได้)';

        if ($is_locked == 1) {
            $target_input = '<input type="text" class="form-control form-control-sm text-end target-qty locked-input" value="'.number_format($row['target_qty_month'], 0).'" data-id="'.$row['id'].'" data-ar-code="'.htmlspecialchars($row['ar_code'] ?? '').'" readonly title="'.$locked_title.'">';
            $forecast_input = '<input type="text" class="form-control form-control-sm text-end forecast-qty locked-input" value="'.number_format($row['forecast_qty_month_1'], 0).'" data-id="'.$row['id'].'" readonly title="'.$locked_title.'">';
            $remark_input = '<input type="text" class="form-control form-control-sm remark locked-input" value="'.htmlspecialchars($row['remark'] ?? '').'" data-id="'.$row['id'].'" readonly title="'.$locked_title.'">';
            $resp_input = '<input type="text" class="form-control form-control-sm responsible-person locked-input" value="'.htmlspecialchars($row['responsible_person'] ?? '').'" data-id="'.$row['id'].'" readonly title="'.$locked_title.'">';
            $actions_html = '<div class="d-flex align-items-center justify-content-center text-muted small fw-bold" title="'.$locked_title.'"><i class="fas fa-lock me-1 text-warning"></i> ล็อค</div>';
            $sku_display = htmlspecialchars($row['sku_code']) . ' <i class="fas fa-lock text-warning ms-1" style="font-size:0.75rem;" title="'.$locked_title.'"></i>';
        } else {
            $target_input = '<input type="text" class="form-control form-control-sm text-end target-qty" value="'.number_format($row['target_qty_month'], 0).'" data-id="'.$row['id'].'" data-ar-code="'.htmlspecialchars($row['ar_code'] ?? '').'">';
            $forecast_input = '<input type="text" class="form-control form-control-sm text-end forecast-qty" value="'.number_format($row['forecast_qty_month_1'], 0).'" data-id="'.$row['id'].'">';
            $remark_input = '<input type="text" class="form-control form-control-sm remark" value="'.htmlspecialchars($row['remark'] ?? '').'" data-id="'.$row['id'].'">';
            $resp_input = '<input type="text" class="form-control form-control-sm responsible-person" value="'.htmlspecialchars($row['responsible_person'] ?? '').'" data-id="'.$row['id'].'">';
            $actions_html = '<div class="d-flex gap-1 justify-content-center">
                                <button type="button" class="btn btn-success btn-sm btn-save-row-inline" 
                                    data-id="'.$row['id'].'" 
                                    title="บันทึกรายการนี้">
                                    <i class="fas fa-save"></i> บันทึก
                                </button>
                                <button type="button" class="btn btn-danger btn-sm btn-delete-row" 
                                    data-id="'.$row['id'].'" 
                                    data-customer="'.htmlspecialchars($row['ar_name'] ?? '').'" 
                                    data-sku="'.$row['sku_code'].'" 
                                    title="ลบรายการนี้">
                                    <i class="fas fa-trash-alt"></i> ลบ
                                </button>
                              </div>';
            $sku_display = htmlspecialchars($row['sku_code']);
        }

        $result[] = [
            "actions" => $actions_html,
            "forcast_month_name" => $thai_months[intval($row['forcast_month'])] ?? $row['forcast_month'],
            "forcast_year" => $row['forcast_year'],
            "ar_code" => $row['ar_code'],
            "ar_name" => $row['ar_name'],
            "sale_name" => $row['sale_name'],
            "take_name" => $row['take_name'],
            "team_name" => $row['team_name'],
            "brand_code" => $row['brand_code'],
            "sku_code" => $sku_display,
            "amphur" => $row['amphur'],
            "province" => $row['province'],
            "zone" => $row['zone'],
            "avg_per_month_year" => number_format($row['avg_per_month_year'], 0),
            "max_qty_year" => number_format($row['max_qty_year'], 0),
            "min_qty_year" => number_format($row['min_qty_year'], 0),
            "target_qty_month" => $target_input,
            "sales_month_1" => number_format($row['sales_month_1'], 0),
            "sales_month_2" => number_format($row['sales_month_2'], 0),
            "sales_month_3" => number_format($row['sales_month_3'], 0),
            "avg_3_month" => '<span class="avg-3m" data-val="'.$avg_3m.'">'.number_format($avg_3m, 0).'</span>',
            "target_qty_month_2" => '<input type="text" class="form-control form-control-sm text-end target-qty-2" value="'.number_format($row['target_qty_month_2'], 0).'" data-id="'.$row['id'].'">',
            "forecast_qty_month_1" => $forecast_input,
            "sales_actual_month" => number_format($row['sales_actual_month'], 0),
            "sales_diff_actual" => '<input type="text" class="form-control form-control-sm text-end diff-actual' . (($row['sales_actual_month'] - $row['forecast_qty_month_1'] < 0) ? ' text-danger fw-bold' : (($row['sales_actual_month'] - $row['forecast_qty_month_1'] > 0) ? ' text-primary fw-bold' : '')) . '" value="'.number_format($row['sales_actual_month'] - $row['forecast_qty_month_1'], 0).'" readonly>',
            "compare_forecast_month_1" => '<input type="text" class="form-control form-control-sm text-end compare-1" value="'.number_format($row['compare_forecast_month_1'], 0).'" readonly>',
            "compare_sales_percent" => '<input type="text" class="form-control form-control-sm text-end compare-pct" value="'.number_format($row['compare_sales_percent'], 0).'%" readonly>',
            "remark" => $remark_input,
            "responsible_person" => $resp_input,
            "forecast_qty_month_2" => '<input type="text" class="form-control form-control-sm text-end forecast-qty-2" value="'.number_format($row['forecast_qty_month_2'], 0).'" data-id="'.$row['id'].'">',
            "compare_forecast_month_2" => '<input type="text" class="form-control form-control-sm text-end compare-2" value="'.number_format($row['compare_forecast_month_2'], 0).'" readonly>',
            "compare_sales_percent_2" => '<input type="text" class="form-control form-control-sm text-end compare-pct-2" value="'.number_format($row['compare_sales_percent_2'], 0).'%" readonly>',
            "import_date" => date('d/m/Y H:i', strtotime($row['import_date']))
        ];
    }

    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        "draw" => intval($_POST['draw'] ?? 0),
        "recordsTotal" => $recordsTotal,
        "recordsFiltered" => $recordsFiltered,
        "data" => $result
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        "draw" => intval($_POST['draw'] ?? 0),
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "data" => [],
        "error" => $e->getMessage()
    ]);
}
