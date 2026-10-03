<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('config/connect_db.php');

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
$product_type = $_POST['product_type'] ?? '';
$qty_condition = $_POST['qty_condition'] ?? 'all';
$province = $_POST['province'] ?? '';
$amphur = $_POST['amphur'] ?? '';
$zone = $_POST['zone'] ?? '';
$search = $_POST['search']['value'] ?? '';
$start = intval($_POST['start'] ?? 0);
$length = intval($_POST['length'] ?? 10);
$order_col_idx = intval($_POST['order'][0]['column'] ?? 0);
$order_dir = $_POST['order'][0]['dir'] ?? 'desc';

$account_type = $_SESSION['account_type'] ?? '';
$first_name = $_SESSION['first_name'] ?? '';

$columns = [
    0 => 'p.ar_code', 
    1 => 'p.ar_name', 
    2 => 'p.sale_name',
    3 => 'p.take_name',
    4 => 'p.province',
    5 => 'p.product_type',
    6 => 'p.sku_code', 
    7 => 'p.sku_name',
    8 => 'p.brand_code', 
    9 => 'p.avg_per_month_year',
    10 => 'p.max_qty_year', 
    11 => 'p.min_qty_year', 
    12 => 'p.sales_month_1', 
    13 => 'p.sales_month_2', 
    14 => 'p.sales_month_3', 
    15 => 'p.avg_3_month', 
    16 => 'p.forecast_qty_month_1', 
    17 => 'p.compare_forecast_month_1', 
    18 => 'p.compare_sales_percent', 
    19 => 'sales_actual_month',
    20 => 'sales_diff_actual',
    21 => 'p.responsible_person',
    22 => 'p.remark',
    23 => 'p.import_date',
    24 => 'p.id'
];

$order_by = $columns[$order_col_idx] ?? 'p.id';

if (empty($month) || empty($year)) {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        "draw" => intval($_POST['draw'] ?? 0),
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "data" => []
    ]);
    exit;
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

$base_where = $where;
$base_params = $params;

if (!empty($sale_name)) {
    $clean_name = trim(preg_replace('/\s*\([^)]*\)/', '', $sale_name));
    $first_word = explode(' ', $clean_name)[0];
    $where .= " AND (p.sale_name = :sale_name OR p.sale_name LIKE :sale_like_full OR p.sale_name LIKE :sale_like_clean)";
    $params[':sale_name'] = $sale_name;
    $params[':sale_like_full'] = "%" . $sale_name . "%";
    $params[':sale_like_clean'] = "%" . $first_word . "%";
    $base_where .= " AND (p.sale_name = :sale_name OR p.sale_name LIKE :sale_like_full OR p.sale_name LIKE :sale_like_clean)";
    $base_params[':sale_name'] = $sale_name;
    $base_params[':sale_like_full'] = "%" . $sale_name . "%";
    $base_params[':sale_like_clean'] = "%" . $first_word . "%";
}
if (!empty($take_name)) {
    $clean_take = trim(preg_replace('/\s*\([^)]*\)/', '', $take_name));
    $first_word_take = explode(' ', $clean_take)[0];
    $where .= " AND (p.take_name = :take_name OR p.take_name LIKE :take_like_full OR p.take_name LIKE :take_like_clean)";
    $params[':take_name'] = $take_name;
    $params[':take_like_full'] = "%" . $take_name . "%";
    $params[':take_like_clean'] = "%" . $first_word_take . "%";
    $base_where .= " AND (p.take_name = :take_name OR p.take_name LIKE :take_like_full OR p.take_name LIKE :take_like_clean)";
    $base_params[':take_name'] = $take_name;
    $base_params[':take_like_full'] = "%" . $take_name . "%";
    $base_params[':take_like_clean'] = "%" . $first_word_take . "%";
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
if (!empty($product_type)) {
    $where .= " AND p.product_type = :product_type";
    $params[':product_type'] = $product_type;
    $base_where .= " AND p.product_type = :product_type";
    $base_params[':product_type'] = $product_type;
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
    $where .= " AND (p.ar_code LIKE :search OR p.ar_name LIKE :search OR p.sku_code LIKE :search OR p.sku_name LIKE :search OR p.product_type LIKE :search OR p.sale_name LIKE :search OR p.brand_code LIKE :search)";
    $params[':search'] = "%$search%";
}

try {
    $stmt_count = $conn->prepare("SELECT COUNT(*) FROM ims_tires_plan_forecast_volume_tires p $where");
    $stmt_count->execute($params);
    $recordsFiltered = intval($stmt_count->fetchColumn());

    if (empty($search)) {
        $recordsTotal = $recordsFiltered;
    } else {
        $stmt_total = $conn->prepare("SELECT COUNT(*) FROM ims_tires_plan_forecast_volume_tires p $base_where");
        $stmt_total->execute($base_params);
        $recordsTotal = intval($stmt_total->fetchColumn());
    }

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
        FROM ims_tires_plan_forecast_volume_tires p 
        $where 
        ORDER BY $order_by $order_dir 
        $limit_sql
    ) page
    JOIN ims_tires_plan_forecast_volume_tires p ON p.id = page.id
    LEFT JOIN v_customer_data_sale_all_zone v ON p.ar_code = v.AR_CODE
    LEFT JOIN (
        SELECT AR_CODE, SKU_CODE, SUM(CAST(TRD_QTY AS DECIMAL(10,2))) as actual_qty
        FROM ims_product_sale_sac
        WHERE (DI_MONTH = :act_month OR DI_MONTH = :act_month_padded) AND DI_YEAR = :act_year
        GROUP BY AR_CODE, SKU_CODE
    ) act ON p.ar_code = act.AR_CODE AND p.sku_code = act.SKU_CODE
    ORDER BY $order_by $order_dir";

    $fetch_params = $params;
    $fetch_params[':act_month'] = $act_month;
    $fetch_params[':act_month_padded'] = $act_month_padded;
    $fetch_params[':act_year'] = $year;

    $stmt_data = $conn->prepare($sql);
    $stmt_data->execute($fetch_params);
    $data_rows = $stmt_data->fetchAll(PDO::FETCH_ASSOC);

    // Check lock status for the month
    $stmt_lock = $conn->prepare("SELECT COUNT(*) as total_rows, SUM(IF(p.is_locked = 1, 1, 0)) as locked_rows, MAX(p.locked_at) as last_locked_at, MAX(p.locked_by) as last_locked_by FROM ims_tires_plan_forecast_volume_tires p $base_where");
    $stmt_lock->execute($base_params);
    $lock_info = $stmt_lock->fetch(PDO::FETCH_ASSOC);
    $is_month_locked = ($lock_info['total_rows'] > 0 && $lock_info['locked_rows'] == $lock_info['total_rows']);

    $thai_months = [
        1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม',
        4 => 'เมษายน', 5 => 'พฤษภาคม', 6 => 'มิถุนายน',
        7 => 'กรกฎาคม', 8 => 'สิงหาคม', 9 => 'กันยายน',
        10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
    ];

    $result = [];
    $is_admin = in_array($account_type, ['admin', 'sale_sup']);

    foreach ($data_rows as $row) {
        $avg_3m = ($row['sales_month_1'] + $row['sales_month_2'] + $row['sales_month_3']) / 3;
        $row_locked = !empty($row['is_locked']);
        $readonly_attr = ($row_locked && !$is_admin) ? 'readonly' : '';
        $disabled_class = ($row_locked && !$is_admin) ? 'locked-input' : '';

        $forecast_input = '<input type="number" step="1" class="form-control form-control-sm text-end forecast-qty-1 '.$disabled_class.'" value="'.number_format($row['forecast_qty_month_1'], 0, '', '').'" data-id="'.$row['id'].'" '.$readonly_attr.'>';
        $remark_input = '<input type="text" class="form-control form-control-sm remark-input '.$disabled_class.'" value="'.htmlspecialchars($row['remark'] ?? '').'" data-id="'.$row['id'].'" '.$readonly_attr.'>';
        $resp_input = '<input type="text" class="form-control form-control-sm resp-input '.$disabled_class.'" value="'.htmlspecialchars($row['responsible_person'] ?? '').'" data-id="'.$row['id'].'" '.$readonly_attr.'>';

        $vol_per_unit = floatval($row['volume'] ?? 0);
        $total_vol = $row['forecast_qty_month_1'] * $vol_per_unit;

        if ($row_locked && !$is_admin) {
            $actions_html = '<div class="btn-group btn-group-sm">
                                <span class="badge bg-secondary p-2" title="รายการนี้ถูกล็อคแล้ว (โดย '.htmlspecialchars($row['locked_by'] ?? 'ระบบ').' เมื่อ '.($row['locked_at'] ? date('d/m/Y H:i', strtotime($row['locked_at'])) : '-').')"><i class="fas fa-lock me-1"></i> ล็อคแล้ว</span>
                             </div>';
            $sku_display = '<span class="text-muted"><i class="fas fa-lock me-1 text-danger"></i>' . htmlspecialchars($row['sku_code']) . '</span>';
        } else {
            $actions_html = '<div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-success btn-sm btn-save-row" 
                                    data-id="'.$row['id'].'" 
                                    title="บันทึกรายการนี้">
                                    <i class="fas fa-save"></i> บันทึก
                                </button>
                                <button type="button" class="btn btn-danger btn-sm btn-delete-row" 
                                    data-id="'.$row['id'].'" 
                                    data-customer="'.htmlspecialchars($row['ar_name'] ?? '').'" 
                                    data-sku="'.htmlspecialchars($row['sku_code'] ?? $row['sku_name']).'" 
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
            "team_name" => $row['team_name'] ?? '',
            "province" => $row['province'],
            "amphur" => $row['amphur'],
            "product_type" => $row['product_type'] ?? '',
            "sku_code" => $sku_display,
            "sku_name" => $row['sku_name'] ?? '',
            "brand_code" => $row['brand_code'],
            "avg_per_month_year" => number_format($row['avg_per_month_year'], 0),
            "volume" => number_format($vol_per_unit, 2),
            "max_qty_year" => number_format($row['max_qty_year'], 0),
            "min_qty_year" => number_format($row['min_qty_year'], 0),
            "sales_month_1" => number_format($row['sales_month_1'], 0),
            "sales_month_2" => number_format($row['sales_month_2'], 0),
            "sales_month_3" => number_format($row['sales_month_3'], 0),
            "avg_3_month" => '<span class="avg-3m" data-val="'.$avg_3m.'">'.number_format($avg_3m, 2).'</span>',
            "forecast_qty_month_1" => $forecast_input,
            "total_volume" => '<span class="total-vol" data-vol="'.$vol_per_unit.'">'.number_format($total_vol, 2).'</span>',
            "compare_forecast_month_1" => '<input type="text" class="form-control form-control-sm text-end compare-1" value="'.number_format($row['compare_forecast_month_1'], 2).'" readonly>',
            "compare_sales_percent" => '<input type="text" class="form-control form-control-sm text-end compare-pct" value="'.number_format($row['compare_sales_percent'], 1).'%" readonly>',
            "sales_actual_month" => number_format($row['sales_actual_month'], 0),
            "sales_diff_actual" => '<input type="text" class="form-control form-control-sm text-end diff-actual' . (($row['sales_actual_month'] - $row['forecast_qty_month_1'] < 0) ? ' text-danger fw-bold' : (($row['sales_actual_month'] - $row['forecast_qty_month_1'] > 0) ? ' text-primary fw-bold' : '')) . '" value="'.number_format($row['sales_actual_month'] - $row['forecast_qty_month_1'], 0).'" readonly>',
            "remark" => $remark_input,
            "responsible_person" => $resp_input,
            "is_locked" => $row_locked ? 1 : 0,
            "import_date" => date('d/m/Y H:i', strtotime($row['import_date']))
        ];
    }

    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        "draw" => intval($_POST['draw'] ?? 0),
        "recordsTotal" => $recordsTotal,
        "recordsFiltered" => $recordsFiltered,
        "is_month_locked" => $is_month_locked ? 1 : 0,
        "lock_info" => $lock_info,
        "data" => $result
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    if (ob_get_length()) ob_clean();
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        "draw" => intval($_POST['draw'] ?? 0),
        "recordsTotal" => 0,
        "recordsFiltered" => 0,
        "is_month_locked" => 0,
        "data" => [],
        "error" => $e->getMessage()
    ]);
}
