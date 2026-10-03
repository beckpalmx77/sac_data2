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
$ar_name = $_REQUEST['ar_name'] ?? '';
$province = $_REQUEST['province'] ?? '';
$amphur = $_REQUEST['amphur'] ?? '';
$zone = $_REQUEST['zone'] ?? '';

if (empty($month) || empty($year)) {
    echo "กรุณาระบุเดือนและปีที่ต้องการ Export";
    exit;
}

$account_type = $_SESSION['account_type'] ?? '';
$first_name = $_SESSION['first_name'] ?? '';

// Build WHERE clause
$where = "WHERE p.forcast_month = :month AND p.forcast_year = :year";
$params = [':month' => $month, ':year' => $year];

if ($account_type === 'sale_user') {
    $where .= " AND p.sale_name = :first_name";
    $params[':first_name'] = $first_name;
} elseif ($account_type === 'take') {
    $where .= " AND p.take_name = :first_name";
    $params[':first_name'] = $first_name;
}

if (!empty($sale_name)) {
    $where .= " AND p.sale_name = :sale_name";
    $params[':sale_name'] = $sale_name;
}
if (!empty($ar_name)) {
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

// SQL query
$sql = "SELECT p.ar_code, p.ar_name, p.sale_name, p.take_name, p.team_name, p.brand_code, p.sku_code,
               p.amphur, p.province, p.zone, p.avg_per_month_year, p.max_qty_year, p.min_qty_year,
               p.sales_month_3, p.sales_month_2, p.sales_month_1, p.avg_3_month,
               p.forecast_qty_month_1, IFNULL(act.actual_qty, 0) as sales_actual_month,
               p.compare_forecast_month_1, p.compare_sales_percent,
               p.remark, p.forecast_qty_month_2, p.compare_forecast_month_2, p.compare_sales_percent_2,
               p.import_date
        FROM ims_tires_plan_forecast p
        LEFT JOIN (
            SELECT AR_CODE, SKU_CODE, SUM(CAST(TRD_QTY AS DECIMAL(10,2))) as actual_qty
            FROM ims_product_sale_sac
            WHERE DI_MONTH = :act_month AND DI_YEAR = :act_year
            GROUP BY AR_CODE, SKU_CODE
        ) act ON p.ar_code = act.AR_CODE AND p.sku_code = act.SKU_CODE
        $where
        ORDER BY p.ar_code ASC, p.sku_code ASC";

try {
    $params[':act_month'] = $month;
    $params[':act_year'] = $year;
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    $filename = "Forecast_Plan_" . $month . "_" . $year . "_" . date('Ymd_His') . ".csv";

    header('Content-Type: text/csv; charset=UTF-8');
    header("Content-Disposition: attachment; filename=" . $filename);

    // CSV Header row
    $csv_header = "รหัสร้านค้า,ชื่อร้านค้า,พนักงานขาย,คนเทค,ทีม,ยี่ห้อ,รหัสสินค้า (SKU),อำเภอ,จังหวัด,เขต,เฉลี่ย 12ด.,สูงสุดปีที่แล้ว,ต่ำสุดปีที่แล้ว,ยอดขาย M-3,ยอดขาย M-2,ยอดขาย M-1,เฉลี่ย 3ด.,คาดการณ์ 1,ยอดขายจริง 1,เทียบคาดการณ์ 1,% ต่าง 1,หมายเหตุ,คาดการณ์ 2,เทียบคาดการณ์ 2,% ต่าง 2,วันที่นำเข้า\n";

    $csv_data = "";
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $csv_data .= '"' . str_replace('"', '""', (string)$row['ar_code']) . '",';
        $csv_data .= '"' . str_replace('"', '""', (string)$row['ar_name']) . '",';
        $csv_data .= '"' . str_replace('"', '""', (string)$row['sale_name']) . '",';
        $csv_data .= '"' . str_replace('"', '""', (string)$row['take_name']) . '",';
        $csv_data .= '"' . str_replace('"', '""', (string)$row['team_name']) . '",';
        $csv_data .= '"' . str_replace('"', '""', (string)$row['brand_code']) . '",';
        $csv_data .= '"' . str_replace('"', '""', (string)$row['sku_code']) . '",';
        $csv_data .= '"' . str_replace('"', '""', (string)$row['amphur']) . '",';
        $csv_data .= '"' . str_replace('"', '""', (string)$row['province']) . '",';
        $csv_data .= '"' . str_replace('"', '""', (string)$row['zone']) . '",';
        $csv_data .= (float)$row['avg_per_month_year'] . ',';
        $csv_data .= (float)$row['max_qty_year'] . ',';
        $csv_data .= (float)$row['min_qty_year'] . ',';
        $csv_data .= (float)$row['sales_month_3'] . ',';
        $csv_data .= (float)$row['sales_month_2'] . ',';
        $csv_data .= (float)$row['sales_month_1'] . ',';
        $csv_data .= (float)$row['avg_3_month'] . ',';
        $csv_data .= (float)$row['forecast_qty_month_1'] . ',';
        $csv_data .= (float)$row['sales_actual_month'] . ',';
        $csv_data .= (float)$row['compare_forecast_month_1'] . ',';
        $csv_data .= (float)$row['compare_sales_percent'] . ',';
        $csv_data .= '"' . str_replace('"', '""', (string)$row['remark']) . '",';
        $csv_data .= (float)$row['forecast_qty_month_2'] . ',';
        $csv_data .= (float)$row['compare_forecast_month_2'] . ',';
        $csv_data .= (float)$row['compare_sales_percent_2'] . ',';
        $csv_data .= '"' . str_replace('"', '""', (string)$row['import_date']) . '"' . "\n";
    }

    $output = $csv_header . $csv_data;
    $output = iconv("utf-8", "windows-874//IGNORE", $output);
    echo $output;

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
