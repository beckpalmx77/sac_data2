<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include(__DIR__ . '/../config/connect_db.php');

header('Content-Type: application/json; charset=UTF-8');
date_default_timezone_set('Asia/Bangkok');

if (!isset($_SESSION['alogin']) || $_SESSION['alogin'] == '') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

$account_type = $_SESSION['account_type'] ?? '';
$first_name = $_SESSION['first_name'] ?? '';

$doc_date_start = trim($_POST['doc_date_start'] ?? '');
$doc_date_to = trim($_POST['doc_date_to'] ?? '');
$target_month = isset($_POST['target_month']) && $_POST['target_month'] !== '' ? intval($_POST['target_month']) : intval(date('n'));
$target_year = isset($_POST['target_year']) && $_POST['target_year'] !== '' ? intval($_POST['target_year']) : intval(date('Y'));

$filter_sale = $_POST['SALE_NAME'] ?? '';
$filter_ar = trim($_POST['AR_NAME'] ?? '');
$filter_province = trim($_POST['TRD_PROVINCE'] ?? '');
$filter_sku_cat = $_POST['SKU_CAT'] ?? [];
$filter_brand = $_POST['BRAND'] ?? [];

// Fallback dates
if (empty($doc_date_to)) {
    $doc_date_to = date('d-m-Y');
}
if (empty($doc_date_start)) {
    $doc_date_start = date('d-m-Y', strtotime('-6 months'));
}

try {
    $d_start = DateTime::createFromFormat('d-m-Y', $doc_date_start)->format('Y-m-d');
    $d_end = DateTime::createFromFormat('d-m-Y', $doc_date_to)->format('Y-m-d');
} catch (Exception $e) {
    $d_start = date('Y-m-d', strtotime('-6 months'));
    $d_end = date('Y-m-d');
}

$start_dt = new DateTime($d_start);
$end_dt = new DateTime($d_end);

// Calculate all distinct months in the user-specified date range
$period = new DatePeriod(
    new DateTime($start_dt->format('Y-m-01')),
    new DateInterval('P1M'),
    (new DateTime($end_dt->format('Y-m-01')))->modify('+1 month')
);

$months_in_range = [];
$years_arr = [$target_year];
foreach ($period as $dt) {
    $m = intval($dt->format('n'));
    $y = intval($dt->format('Y'));
    $months_in_range[] = ['m' => $m, 'y' => $y, 'label' => $dt->format('Y-m')];
    $years_arr[] = $y;
}
$total_months = max(1, count($months_in_range));
$years_in = implode(',', array_unique($years_arr));

// Calculate M-1, M-2, M-3 relative to target month/year
$m1 = $target_month - 1; $y1 = $target_year; while ($m1 <= 0) { $m1 += 12; $y1--; }
$m2 = $target_month - 2; $y2 = $target_year; while ($m2 <= 0) { $m2 += 12; $y2--; }
$m3 = $target_month - 3; $y3 = $target_year; while ($m3 <= 0) { $m3 += 12; $y3--; }

$thai_months_full = [
    1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม',
    4 => 'เมษายน', 5 => 'พฤษภาคม', 6 => 'มิถุนายน',
    7 => 'กรกฎาคม', 8 => 'สิงหาคม', 9 => 'กันยายน',
    10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
];

$month_cases = [];
foreach ($months_in_range as $idx => $mInfo) {
    $month_cases[] = "SUM(CASE WHEN CAST(s.DI_MONTH AS UNSIGNED) = {$mInfo['m']} AND CAST(s.DI_YEAR AS UNSIGNED) = {$mInfo['y']} THEN CAST(s.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as qty_range_{$idx}";
}
$month_cases[] = "SUM(CASE WHEN CAST(s.DI_MONTH AS UNSIGNED) = {$m1} AND CAST(s.DI_YEAR AS UNSIGNED) = {$y1} THEN CAST(s.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as qty_m1";
$month_cases[] = "SUM(CASE WHEN CAST(s.DI_MONTH AS UNSIGNED) = {$m2} AND CAST(s.DI_YEAR AS UNSIGNED) = {$y2} THEN CAST(s.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as qty_m2";
$month_cases[] = "SUM(CASE WHEN CAST(s.DI_MONTH AS UNSIGNED) = {$m3} AND CAST(s.DI_YEAR AS UNSIGNED) = {$y3} THEN CAST(s.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as qty_m3";
$month_cases_sql = implode(",\n    ", $month_cases);

$where_clauses = [
    "s.DI_YEAR IN ({$years_in})",
    "STR_TO_DATE(s.DI_DATE, '%d-%m-%Y') BETWEEN :date_start AND :date_end",
    "CAST(s.TRD_QTY AS DECIMAL(10,2)) > 0",
    "(s.TRD_Q_FREE IS NULL OR CAST(s.TRD_Q_FREE AS DECIMAL(10,2)) = 0)",
    "(s.SKU_CAT IS NULL OR s.SKU_CAT NOT LIKE '%ของแถม%')",
    "(s.BRAND IS NULL OR s.BRAND NOT LIKE '%ของแถม%')",
    "s.SALE_NAME IS NOT NULL AND TRIM(s.SALE_NAME) != '' AND TRIM(s.SALE_NAME) != '-'",
    "s.SALE_NAME NOT LIKE '%BTC%' AND s.SALE_NAME NOT LIKE '%RQ%' AND s.SALE_NAME NOT LIKE '%ส่วนกลาง%' AND s.SALE_NAME NOT LIKE '%READY%QUICK%'",
    "s.DI_REF NOT LIKE 'DS%'"
];

$params = [
    ':date_start' => $d_start,
    ':date_end' => $d_end
];

if ($account_type === 'sale_user') {
    $where_clauses[] = "(REPLACE(s.SALE_NAME, ' ', '') = :acc_name)";
    $params[':acc_name'] = preg_replace('/\s+/u', '', $first_name);
} elseif ($account_type === 'take') {
    $where_clauses[] = "(REPLACE(s.TAKE_NAME, ' ', '') = :acc_name OR REPLACE(s.SALE_NAME, ' ', '') = :acc_name)";
    $params[':acc_name'] = preg_replace('/\s+/u', '', $first_name);
} else {
    if (!empty($filter_sale) && $filter_sale !== '-') {
        if (is_array($filter_sale)) {
            $clean_sales = array_filter(array_map(function($s) {
                return preg_replace('/\s+/u', '', trim($s ?? ''));
            }, $filter_sale));
            if (!empty($clean_sales)) {
                $quoted = array_map(function($s) use ($conn) { return $conn->quote($s); }, $clean_sales);
                $in_sql = implode(',', $quoted);
                $where_clauses[] = "(REPLACE(s.SALE_NAME, ' ', '') IN ({$in_sql}))";
            }
        } else {
            $cleaned = preg_replace('/\s+/u', '', trim($filter_sale));
            if ($cleaned !== '') {
                $where_clauses[] = "(REPLACE(s.SALE_NAME, ' ', '') = :filter_sale)";
                $params[':filter_sale'] = $cleaned;
            }
        }
    }
}

if (!empty($filter_ar) && $filter_ar !== '-') {
    $where_clauses[] = "s.AR_NAME = :filter_ar";
    $params[':filter_ar'] = $filter_ar;
}

if (!empty($filter_province) && $filter_province !== '-') {
    $where_clauses[] = "s.TRD_PROVINCE = :filter_province";
    $params[':filter_province'] = $filter_province;
}

// Category filter: SKU_CAT IN ('TBR/LTR (ยางใหญ่)', 'LSR', 'TBB/LTB (ยางผ้าใบ)', 'TBR')
if (!empty($filter_sku_cat)) {
    if (is_array($filter_sku_cat)) {
        $clean_cats = array_filter(array_map('trim', $filter_sku_cat), function($v) { return $v !== '' && $v !== '-'; });
        if (!empty($clean_cats)) {
            $in_cats = implode(',', array_map(function($c) use ($conn) { return $conn->quote($c); }, $clean_cats));
            $where_clauses[] = "s.SKU_CAT IN ({$in_cats})";
        }
    } elseif (trim($filter_sku_cat) !== '-') {
        $where_clauses[] = "s.SKU_CAT = :filter_sku_cat";
        $params[':filter_sku_cat'] = trim($filter_sku_cat);
    }
} else {
    $where_clauses[] = "(s.SKU_CAT IN ('TBR/LTR (ยางใหญ่)', 'LSR', 'TBB/LTB (ยางผ้าใบ)', 'TBR') OR TRIM(s.SKU_CAT) IN ('TBR/LTR (ยางใหญ่)', 'LSR', 'TBB/LTB (ยางผ้าใบ)', 'TBR'))";
}

// Brand filter: BRAND IN ('VB', 'ATLAS', 'AT', 'BS/FS/DT', 'BS/FS', 'BS', '')
if (!empty($filter_brand)) {
    if (is_array($filter_brand)) {
        $clean_brands = array_filter(array_map('trim', $filter_brand), function($v) { return $v !== '' && $v !== '-'; });
        if (!empty($clean_brands)) {
            $in_brands = implode(',', array_map(function($b) use ($conn) { return $conn->quote($b); }, $clean_brands));
            $where_clauses[] = "s.BRAND IN ({$in_brands})";
        }
    } elseif (trim($filter_brand) !== '-') {
        $where_clauses[] = "s.BRAND = :filter_brand";
        $params[':filter_brand'] = trim($filter_brand);
    }
} else {
    $where_clauses[] = "s.BRAND IN ('VB', 'ATLAS', 'AT', 'BS/FS/DT', 'BS/FS', 'BS', '')";
}

$where_sql = "WHERE " . implode(" AND ", $where_clauses);

$sql = "
SELECT 
    s.SALE_NAME,
    s.TAKE_NAME,
    s.AR_NAME,
    s.TRD_PROVINCE,
    s.SKU_CAT,
    s.SKU_CODE,
    s.BRAND,
    s.SKU_NAME,
    COUNT(*) as tx_count,
    SUM(CAST(s.TRD_QTY AS DECIMAL(10,2))) as total_qty,
    {$month_cases_sql}
FROM ims_data_sale_sac_all s
{$where_sql}
GROUP BY s.SALE_NAME, s.TAKE_NAME, s.AR_NAME, s.TRD_PROVINCE, s.SKU_CAT, s.SKU_CODE, s.BRAND, s.SKU_NAME
HAVING total_qty > 0
ORDER BY s.SALE_NAME ASC, s.AR_NAME ASC, s.SKU_CAT ASC, s.BRAND ASC, s.SKU_CODE ASC
";

try {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $all_rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total_records = count($all_rows);
    $sum_total_qty = 0;
    $customers_set = [];
    $skus_set = [];

    $preview_rows = [];
    $count = 0;

    foreach ($all_rows as $r) {
        $q_tot = floatval($r['total_qty']);
        $sum_total_qty += $q_tot;
        $customers_set[$r['AR_NAME']] = true;
        $skus_set[$r['SKU_CODE']] = true;

        if ($count < 50) {
            $range_q = [];
            foreach ($months_in_range as $idx => $mInfo) {
                $range_q[] = floatval($r["qty_range_{$idx}"]);
            }
            $max_q = !empty($range_q) ? max($range_q) : 0;
            $min_q = !empty($range_q) ? min($range_q) : 0;
            $avg_period = round($q_tot / $total_months, 2);

            $q1 = floatval($r['qty_m1']);
            $q2 = floatval($r['qty_m2']);
            $q3 = floatval($r['qty_m3']);
            $avg_3m = round(($q1 + $q2 + $q3) / 3.0, 2);

            $preview_rows[] = [
                'sale_name' => $r['SALE_NAME'],
                'take_name' => $r['TAKE_NAME'],
                'ar_name' => $r['AR_NAME'],
                'province' => $r['TRD_PROVINCE'],
                'sku_cat' => $r['SKU_CAT'],
                'sku_code' => $r['SKU_CODE'],
                'sku_name' => $r['SKU_NAME'],
                'brand' => $r['BRAND'],
                'max_qty' => $max_q,
                'min_qty' => $min_q,
                'qty_m3' => $q3,
                'qty_m2' => $q2,
                'qty_m1' => $q1,
                'avg_3m' => $avg_3m,
                'responsible_person' => $r['SALE_NAME']
            ];
            $count++;
        }
    }

    $m1_label = ($thai_months_full[$m1] ?? '') . "'" . substr((string)$y1, -2);
    $m2_label = ($thai_months_full[$m2] ?? '') . "'" . substr((string)$y2, -2);
    $m3_label = ($thai_months_full[$m3] ?? '') . "'" . substr((string)$y3, -2);

    echo json_encode([
        'status' => 'success',
        'total_records' => $total_records,
        'total_qty' => number_format($sum_total_qty, 2),
        'unique_customers' => count($customers_set),
        'unique_skus' => count($skus_set),
        'total_months' => $total_months,
        'm3_label' => $m3_label,
        'm2_label' => $m2_label,
        'm1_label' => $m1_label,
        'preview_rows' => $preview_rows
    ]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
