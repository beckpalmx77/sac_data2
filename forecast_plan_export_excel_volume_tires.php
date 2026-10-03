<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include('config/connect_db.php');

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

if (empty($month) || empty($year)) {
    echo "กรุณาระบุเดือนและปีที่ต้องการ Export";
    exit;
}

$account_type = $_SESSION['account_type'] ?? '';
$first_name = $_SESSION['first_name'] ?? '';

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

$sql = "SELECT p.*, 
               IF(p.amphur = '' OR p.amphur IS NULL, v.TRD_AMPHUR, p.amphur) as amphur,
               IF(p.province = '' OR p.province IS NULL, v.TRD_PROVINCE, p.province) as province,
               IF(p.zone = '' OR p.zone IS NULL, v.ZONE, p.zone) as zone
        FROM ims_tires_plan_forecast_volume_tires p
        LEFT JOIN v_customer_data_sale_all_zone v ON p.ar_code = v.AR_CODE
        $where
        ORDER BY p.ar_name ASC, p.sku_name ASC";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $filename = "Volume_Tires_Forecast_Plan_" . $month . "_" . $year . "_" . date('Ymd_His') . ".csv";

    header('Content-Type: text/csv; charset=UTF-8');
    header("Content-Disposition: attachment; filename=" . $filename);

    echo "\xEF\xBB\xBF";

    // CSV Header row
    $csv_header = "เซลล์,เทค,รายชื่อลูกค้า,รหัสลูกค้า,จังหวัด,ประเภท,รหัสสินค้า,รายละเอียด,ยี่ห้อ,เฉลี่ย 12 เดือน ปีก่อน,ยอดขายสูงสุดต่อเดือน,ยอดขายต่ำสุดต่อเดือน,3 เดือนย้อนหลัง,2 เดือนย้อนหลัง,1 เดือนย้อนหลัง,ค่าเฉลี่ย 3 เดือน,แพลนยางเดือนที่ต้องการ,เทียบ แพลนยาง ลบ ค่าเฉลี่ย,เทียบ แพลนยาง ลบ ค่าเฉลี่ย %,ผู้รับผิดชอบ,หมายเหตุ,วันที่นำเข้า\n";
    echo $csv_header;

    foreach ($rows as $row) {
        $forecast = (float)($row['forecast_qty_month_1'] ?? 0);

        $fields = [
            '"' . str_replace('"', '""', (string)($row['sale_name'] ?? '')) . '"',
            '"' . str_replace('"', '""', (string)($row['take_name'] ?? '')) . '"',
            '"' . str_replace('"', '""', (string)($row['ar_name'] ?? '')) . '"',
            '"' . str_replace('"', '""', (string)($row['ar_code'] ?? '')) . '"',
            '"' . str_replace('"', '""', (string)($row['province'] ?? '')) . '"',
            '"' . str_replace('"', '""', (string)($row['product_type'] ?? '')) . '"',
            '"' . str_replace('"', '""', (string)($row['sku_code'] ?? '')) . '"',
            '"' . str_replace('"', '""', (string)($row['sku_name'] ?? '')) . '"',
            '"' . str_replace('"', '""', (string)($row['brand_code'] ?? '')) . '"',
            number_format((float)($row['avg_per_month_year'] ?? 0), 0, '.', ''),
            number_format((float)($row['max_qty_year'] ?? 0), 0, '.', ''),
            number_format((float)($row['min_qty_year'] ?? 0), 0, '.', ''),
            number_format((float)($row['sales_month_1'] ?? 0), 0, '.', ''),
            number_format((float)($row['sales_month_2'] ?? 0), 0, '.', ''),
            number_format((float)($row['sales_month_3'] ?? 0), 0, '.', ''),
            number_format((float)($row['avg_3_month'] ?? 0), 2, '.', ''),
            number_format($forecast, 0, '.', ''),
            number_format((float)($row['compare_forecast_month_1'] ?? 0), 2, '.', ''),
            number_format((float)($row['compare_sales_percent'] ?? 0), 1, '.', '') . '%',
            '"' . str_replace('"', '""', (string)($row['responsible_person'] ?? '')) . '"',
            '"' . str_replace('"', '""', (string)($row['remark'] ?? '')) . '"',
            '"' . str_replace('"', '""', (string)($row['import_date'] ?? '')) . '"'
        ];

        echo implode(',', $fields) . "\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
