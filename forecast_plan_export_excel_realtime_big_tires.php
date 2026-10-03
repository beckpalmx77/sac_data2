<?php
session_start();
include('config/connect_db.php');

// Set resource limits for large datasets
ini_set('memory_limit', '512M');
set_time_limit(300);

if (!isset($_SESSION['alogin']) || $_SESSION['alogin'] == '') {
    echo "Unauthorized access";
    exit;
}

$month = $_REQUEST['month'] ?? '';
$year = $_REQUEST['year'] ?? '';
$sale_name = $_REQUEST['sale_name'] ?? '';
$take_name = $_REQUEST['take_name'] ?? '';
$ar_name = $_REQUEST['ar_name'] ?? '';
$brand_code = $_REQUEST['brand_code'] ?? '';
$product_type = $_REQUEST['product_type'] ?? '';
$qty_condition = $_REQUEST['qty_condition'] ?? 'all';
$province = $_REQUEST['province'] ?? '';
$amphur = $_REQUEST['amphur'] ?? '';
$zone = $_REQUEST['zone'] ?? '';
$search = $_REQUEST['search'] ?? '';

if (empty($month) || empty($year)) {
    echo "กรุณาระบุเดือนและปีที่ต้องการ Export";
    exit;
}

$account_type = $_SESSION['account_type'] ?? '';
$first_name = $_SESSION['first_name'] ?? '';

// Build WHERE clause
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

if (!empty($sale_name)) {
    $clean_name = trim(preg_replace('/\s*\([^)]*\)/', '', $sale_name));
    $first_word = explode(' ', $clean_name)[0];

    $where .= " AND (p.sale_name = :sale_name OR p.sale_name LIKE :sale_like_full OR p.sale_name LIKE :sale_like_clean)";
    $params[':sale_name'] = $sale_name;
    $params[':sale_like_full'] = "%" . $sale_name . "%";
    $params[':sale_like_clean'] = "%" . $first_word . "%";
}
if (!empty($take_name)) {
    $clean_take = trim(preg_replace('/\s*\([^)]*\)/', '', $take_name));
    $first_word_take = explode(' ', $clean_take)[0];

    $where .= " AND (p.take_name = :take_name OR p.take_name LIKE :take_like_full OR p.take_name LIKE :take_like_clean)";
    $params[':take_name'] = $take_name;
    $params[':take_like_full'] = "%" . $take_name . "%";
    $params[':take_like_clean'] = "%" . $first_word_take . "%";
}
if (!empty($ar_name)) {
    $where .= " AND p.ar_name = :ar_name";
    $params[':ar_name'] = $ar_name;
}
if (!empty($brand_code)) {
    $where .= " AND p.brand_code = :brand_code";
    $params[':brand_code'] = $brand_code;
}
if (!empty($product_type)) {
    $where .= " AND p.product_type = :product_type";
    $params[':product_type'] = $product_type;
}
if ($qty_condition === 'gt0_either') {
    $where .= " AND (p.target_qty_month > 0 OR p.forecast_qty_month_1 > 0)";
} elseif ($qty_condition === 'gt0_target') {
    $where .= " AND p.target_qty_month > 0";
} elseif ($qty_condition === 'gt0_forecast') {
    $where .= " AND p.forecast_qty_month_1 > 0";
} elseif ($qty_condition === 'gt0_both') {
    $where .= " AND (p.target_qty_month > 0 AND p.forecast_qty_month_1 > 0)";
} elseif ($qty_condition === 'eq0_both') {
    $where .= " AND (IFNULL(p.target_qty_month, 0) = 0 AND IFNULL(p.forecast_qty_month_1, 0) = 0)";
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
if (!empty($search)) {
    $where .= " AND (p.ar_code LIKE :search OR p.ar_name LIKE :search OR p.sku_code LIKE :search OR p.sku_name LIKE :search OR p.product_type LIKE :search OR p.sale_name LIKE :search OR p.brand_code LIKE :search)";
    $params[':search'] = "%$search%";
}

// SQL query
$sql = "SELECT p.ar_code, p.ar_name, p.sale_name, p.take_name, p.team_name, p.brand_code, p.product_type, p.sku_code, p.sku_name,
               IF(p.amphur = '' OR p.amphur IS NULL, v.TRD_AMPHUR, p.amphur) as amphur,
               IF(p.province = '' OR p.province IS NULL, v.TRD_PROVINCE, p.province) as province,
               IF(p.zone = '' OR p.zone IS NULL, v.ZONE, p.zone) as zone,
               p.avg_per_month_year, p.max_qty_year, p.min_qty_year,
               p.sales_month_3, p.sales_month_2, p.sales_month_1, p.avg_3_month,
               p.target_qty_month, p.forecast_qty_month_1, IFNULL(act.actual_qty, 0) as sales_actual_month,
               p.compare_forecast_month_1, p.compare_sales_percent,
               p.remark, p.responsible_person, p.forecast_qty_month_2, p.compare_forecast_month_2, p.compare_sales_percent_2,
               p.import_date
        FROM ims_tires_plan_forecast_realtime_big_tirs p
        LEFT JOIN v_customer_data_sale_all_zone v ON p.ar_code = v.AR_CODE
        LEFT JOIN (
            SELECT AR_CODE, SKU_CODE, SUM(CAST(TRD_QTY AS DECIMAL(10,2))) as actual_qty
            FROM ims_product_sale_sac
            WHERE DI_MONTH = :act_month AND DI_YEAR = :act_year
            GROUP BY AR_CODE, SKU_CODE
        ) act ON p.ar_code = act.AR_CODE AND p.sku_code = act.SKU_CODE
        $where
        ORDER BY p.ar_code ASC, p.sku_code ASC";

try {
    $params[':act_month'] = strval(intval($month));
    $params[':act_year'] = $year;
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $filename = "Big_Tires_Forecast_Plan_Realtime_" . $month . "_" . $year . "_" . date('Ymd_His') . ".csv";

    header('Content-Type: text/csv; charset=UTF-8');
    header("Content-Disposition: attachment; filename=" . $filename);

    // Send UTF-8 BOM
    echo "\xEF\xBB\xBF";

    // CSV Header row
    $csv_header = "รหัสลูกค้า,ชื่อลูกค้า,ยี่ห้อสินค้า,ประเภท,รหัสสินค้า,รายละเอียดสินค้า,เฉลี่ย 12 เดือน ปีก่อน,ยอดขายสูงสุดต่อเดือน(ปีก่อน),ยอดขายต่ำสุดต่อเดือน(ปีก่อน),3 เดือน ย้อนหลัง,2 เดือนย้อนหลัง,1 เดือนย้อนหลัง,เฉลี่ย 3ด.,เป้ารายเดือน,แพลนยางเดือนที่ต้องการ,เทียบ แพลนยาง ลบ ค่าเฉลี่ย,เทียบ แพลนยาง ลบ ค่าเฉลี่ย %,ยอดขายจริง,ขาด/เกิน,หมายเหตุ,พนักงานขาย,คนเทค,ผู้รับผิดชอบ,อำเภอ,จังหวัด,วันที่นำเข้า\n";
    echo $csv_header;

    foreach ($rows as $row) {
        $forecast = (float)($row['forecast_qty_month_1'] ?? 0);
        $sales_actual = (float)($row['sales_actual_month'] ?? 0);
        $diff_actual = $sales_actual - $forecast;

        $fields = [
            '"' . str_replace('"', '""', (string)($row['ar_code'] ?? '')) . '"',
            '"' . str_replace('"', '""', (string)($row['ar_name'] ?? '')) . '"',
            '"' . str_replace('"', '""', (string)($row['brand_code'] ?? '')) . '"',
            '"' . str_replace('"', '""', (string)($row['product_type'] ?? '')) . '"',
            '"' . str_replace('"', '""', (string)($row['sku_code'] ?? '')) . '"',
            '"' . str_replace('"', '""', (string)($row['sku_name'] ?? '')) . '"',
            number_format((float)($row['avg_per_month_year'] ?? 0), 0, '.', ''),
            number_format((float)($row['max_qty_year'] ?? 0), 0, '.', ''),
            number_format((float)($row['min_qty_year'] ?? 0), 0, '.', ''),
            number_format((float)($row['sales_month_3'] ?? 0), 0, '.', ''),
            number_format((float)($row['sales_month_2'] ?? 0), 0, '.', ''),
            number_format((float)($row['sales_month_1'] ?? 0), 0, '.', ''),
            number_format((float)($row['avg_3_month'] ?? 0), 0, '.', ''),
            number_format((float)($row['target_qty_month'] ?? 0), 0, '.', ''),
            number_format($forecast, 0, '.', ''),
            number_format((float)($row['compare_forecast_month_1'] ?? 0), 0, '.', ''),
            number_format((float)($row['compare_sales_percent'] ?? 0), 0, '.', '') . '%',
            number_format($sales_actual, 0, '.', ''),
            number_format($diff_actual, 0, '.', ''),
            '"' . str_replace('"', '""', (string)($row['remark'] ?? '')) . '"',
            '"' . str_replace('"', '""', (string)($row['sale_name'] ?? '')) . '"',
            '"' . str_replace('"', '""', (string)($row['take_name'] ?? '')) . '"',
            '"' . str_replace('"', '""', (string)($row['responsible_person'] ?? '')) . '"',
            '"' . str_replace('"', '""', (string)($row['amphur'] ?? '')) . '"',
            '"' . str_replace('"', '""', (string)($row['province'] ?? '')) . '"',
            '"' . str_replace('"', '""', (string)($row['import_date'] ?? '')) . '"'
        ];

        echo implode(',', $fields) . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
