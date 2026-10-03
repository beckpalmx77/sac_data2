<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include(__DIR__ . '/../config/connect_db.php');

ini_set('memory_limit', '1024M');
set_time_limit(300);
date_default_timezone_set('Asia/Bangkok');

require __DIR__ . '/../vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xls;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

// Check user login
if (!isset($_SESSION['alogin']) || $_SESSION['alogin'] == '') {
    echo "Unauthorized access";
    exit;
}

$account_type = $_SESSION['account_type'] ?? '';
$first_name = $_SESSION['first_name'] ?? '';

// Inputs
$doc_date_start = trim($_POST['doc_date_start'] ?? $_GET['doc_date_start'] ?? $_REQUEST['doc_date_start'] ?? '');
$doc_date_to = trim($_POST['doc_date_to'] ?? $_GET['doc_date_to'] ?? $_REQUEST['doc_date_to'] ?? '');
$target_month = intval($_POST['target_month'] ?? $_GET['target_month'] ?? $_REQUEST['target_month'] ?? date('n'));
if ($target_month <= 0 || $target_month > 12) $target_month = intval(date('n'));
$target_year = intval($_POST['target_year'] ?? $_GET['target_year'] ?? $_REQUEST['target_year'] ?? date('Y'));
if ($target_year <= 0) $target_year = intval(date('Y'));

$filter_sale = $_POST['SALE_NAME'] ?? $_GET['SALE_NAME'] ?? $_REQUEST['SALE_NAME'] ?? '';
$filter_ar = trim($_POST['AR_NAME'] ?? $_GET['AR_NAME'] ?? $_REQUEST['AR_NAME'] ?? '');
$filter_province = trim($_POST['TRD_PROVINCE'] ?? $_GET['TRD_PROVINCE'] ?? $_REQUEST['TRD_PROVINCE'] ?? '');
$filter_sku_cat = $_POST['SKU_CAT'] ?? $_GET['SKU_CAT'] ?? $_REQUEST['SKU_CAT'] ?? [];
$filter_brand = $_POST['BRAND'] ?? $_GET['BRAND'] ?? $_REQUEST['BRAND'] ?? [];

$export_format = $_POST['export_format'] ?? $_GET['export_format'] ?? $_REQUEST['export_format'] ?? 'v_template';
$file_type = strtolower($_POST['file_type'] ?? $_GET['file_type'] ?? $_REQUEST['file_type'] ?? 'xls');

// Fallback dates
if (empty($doc_date_to)) {
    $doc_date_to = date('d-m-Y');
}
if (empty($doc_date_start)) {
    $doc_date_start = date('d-m-Y', strtotime('-6 months'));
}

// Convert dates to Y-m-d
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
$thai_months_short = [
    1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.',
    4 => 'เม.ย.', 5 => 'พ.ค.', 6 => 'มิ.ย.',
    7 => 'ก.ค.', 8 => 'ส.ค.', 9 => 'ก.ย.',
    10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
];

// Build dynamic monthly SQL selectors
$month_cases = [];
foreach ($months_in_range as $idx => $mInfo) {
    $month_cases[] = "SUM(CASE WHEN CAST(s.DI_MONTH AS UNSIGNED) = {$mInfo['m']} AND CAST(s.DI_YEAR AS UNSIGNED) = {$mInfo['y']} THEN CAST(s.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as qty_range_{$idx}";
}
$month_cases[] = "SUM(CASE WHEN CAST(s.DI_MONTH AS UNSIGNED) = {$m1} AND CAST(s.DI_YEAR AS UNSIGNED) = {$y1} THEN CAST(s.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as qty_m1";
$month_cases[] = "SUM(CASE WHEN CAST(s.DI_MONTH AS UNSIGNED) = {$m2} AND CAST(s.DI_YEAR AS UNSIGNED) = {$y2} THEN CAST(s.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as qty_m2";
$month_cases[] = "SUM(CASE WHEN CAST(s.DI_MONTH AS UNSIGNED) = {$m3} AND CAST(s.DI_YEAR AS UNSIGNED) = {$y3} THEN CAST(s.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as qty_m3";
$month_cases_sql = implode(",\n    ", $month_cases);

// Build WHERE SQL
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

// Account type restrictions
if ($account_type === 'sale_user') {
    $where_clauses[] = "(REPLACE(s.SALE_NAME, ' ', '') = :acc_name)";
    $params[':acc_name'] = preg_replace('/\s+/u', '', $first_name);
} elseif ($account_type === 'take') {
    $where_clauses[] = "(REPLACE(s.TAKE_NAME, ' ', '') = :acc_name OR REPLACE(s.SALE_NAME, ' ', '') = :acc_name)";
    $params[':acc_name'] = preg_replace('/\s+/u', '', $first_name);
} else {
    // Normal sale filtering
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

// SQL Query
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
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}

$label_target_short = $thai_months_short[$target_month] ?? "เดือน $target_month";
$label_target_full = $thai_months_full[$target_month] ?? "เดือน $target_month";

$m1_label = ($thai_months_full[$m1] ?? '') . "'" . substr((string)$y1, -2);
$m2_label = ($thai_months_full[$m2] ?? '') . "'" . substr((string)$y2, -2);
$m3_label = ($thai_months_full[$m3] ?? '') . "'" . substr((string)$y3, -2);

$timestamp = date('Ymd_His');
$base_filename = "V_TEMPLATE_Export_" . str_replace('-', '', $doc_date_start) . "_" . str_replace('-', '', $doc_date_to) . "_" . $timestamp;

// ==========================================
// 1. CSV EXPORT
// ==========================================
if ($file_type === 'csv') {
    $filename = $base_filename . ".csv";
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    echo "\xEF\xBB\xBF"; // UTF-8 BOM
    $out = fopen('php://output', 'w');

    if ($export_format === '6_months') {
        // Detailed Months across the selected date range
        $csv_headers = [
            'เซลล์', 'เทค', 'รายชื่อลูกค้า', 'จังหวัด', 'ประเภท', 'รหัสสินค้า', 'ยี่ห้อ', 'รายละเอียด',
            "ค่าเฉลี่ย $total_months เดือน", "ยอดขายสูงสุด ($target_year)", "ยอดขายต่ำสุด ($target_year)"
        ];
        foreach ($months_in_range as $mInfo) {
            $csv_headers[] = ($thai_months_full[$mInfo['m']] ?? '') . "'" . substr((string)$mInfo['y'], -2);
        }
        $csv_headers[] = 'ค่าเฉลี่ย 3 เดือนล่าสุด';
        $csv_headers[] = "แพลนเดือน $label_target_short";
        $csv_headers[] = 'เทียบ แพลนยาง ลบ ค่าเฉลี่ย';
        $csv_headers[] = 'เทียบ แพลนยาง ลบ ค่าเฉลี่ย %';
        $csv_headers[] = 'ผู้รับผิดชอบ';

        fputcsv($out, $csv_headers);

        foreach ($rows as $r) {
            $range_q = [];
            foreach ($months_in_range as $idx => $mInfo) {
                $range_q[] = floatval($r["qty_range_{$idx}"]);
            }
            $max_q = !empty($range_q) ? max($range_q) : 0;
            $min_q = !empty($range_q) ? min($range_q) : 0;
            $total_q = floatval($r['total_qty']);
            $avg_period = round($total_q / $total_months, 2);

            $q1 = floatval($r['qty_m1']);
            $q2 = floatval($r['qty_m2']);
            $q3 = floatval($r['qty_m3']);
            $avg_3m = round(($q1 + $q2 + $q3) / 3.0, 2);

            $row_data = [
                $r['SALE_NAME'],
                $r['TAKE_NAME'],
                $r['AR_NAME'],
                $r['TRD_PROVINCE'],
                $r['SKU_CAT'],
                $r['SKU_CODE'],
                $r['BRAND'],
                $r['SKU_NAME'],
                $avg_period,
                $max_q,
                $min_q
            ];
            foreach ($range_q as $qv) {
                $row_data[] = $qv;
            }
            $row_data[] = $avg_3m;
            $row_data[] = ''; // Plan (blank)
            $row_data[] = ''; // Formula
            $row_data[] = ''; // Percent
            $row_data[] = $r['SALE_NAME'];

            fputcsv($out, $row_data);
        }
    } else {
        // Standard: exactly 18 columns matching template_v_tires.xlsx for direct Import
        $csv_headers = [
            'เซลล์', 'เทค', 'รายชื่อลูกค้า', 'จังหวัด', 'ประเภท',
            'รหัสสินค้า', 'รายละเอียด', 'ยี่ห้อ',
            "ยอดขายสูงสุดต่อเดือน $target_year", "ยอดขายต่ำสุดต่อเดือน $target_year",
            $m3_label, $m2_label, $m1_label, 'ค่าเฉลี่ย 3 เดือน',
            'แพลนยางเดือนที่ต้องการ', 'เทียบ แพลนยาง ลบ ค่าเฉลี่ย', 'เทียบ แพลนยาง ลบ ค่าเฉลี่ย %', 'ผู้รับผิดชอบ'
        ];
        fputcsv($out, $csv_headers);

        $rowIdx = 2;
        foreach ($rows as $r) {
            $range_q = [];
            foreach ($months_in_range as $idx => $mInfo) {
                $range_q[] = floatval($r["qty_range_{$idx}"]);
            }
            $max_q = !empty($range_q) ? max($range_q) : 0;
            $min_q = !empty($range_q) ? min($range_q) : 0;

            $q1 = floatval($r['qty_m1']);
            $q2 = floatval($r['qty_m2']);
            $q3 = floatval($r['qty_m3']);
            $avg_3m = round(($q1 + $q2 + $q3) / 3.0, 2);

            fputcsv($out, [
                $r['SALE_NAME'],
                $r['TAKE_NAME'],
                $r['AR_NAME'],
                $r['TRD_PROVINCE'],
                $r['SKU_CAT'],
                $r['SKU_CODE'],
                $r['SKU_NAME'],
                $r['BRAND'],
                $max_q,
                $min_q,
                $q3,
                $q2,
                $q1,
                $avg_3m,
                '', // แพลนยางเดือนที่ต้องการ (blank for input)
                "=O{$rowIdx}-N{$rowIdx}",
                "=IF(N{$rowIdx}>0, (O{$rowIdx}-N{$rowIdx})/N{$rowIdx}, 0)",
                $r['SALE_NAME']
            ]);
            $rowIdx++;
        }
    }
    fclose($out);
    exit();
}

// ==========================================
// 2. EXCEL (.XLS / .XLSX) EXPORT
// ==========================================
if ($export_format === '6_months') {
    // Detailed Months across the selected date range
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setTitle("Forecast_Detailed_Months");

    // Headers
    $headers_row = [
        'เซลล์', 'เทค', 'รายชื่อลูกค้า', 'จังหวัด', 'ประเภท', 'รหัสสินค้า', 'รายละเอียด', 'ยี่ห้อ',
        "ค่าเฉลี่ย $total_months เดือน", "ยอดขายสูงสุด ($target_year)", "ยอดขายต่ำสุด ($target_year)"
    ];
    foreach ($months_in_range as $mInfo) {
        $headers_row[] = ($thai_months_full[$mInfo['m']] ?? '') . "'" . substr((string)$mInfo['y'], -2);
    }
    $headers_row[] = 'ค่าเฉลี่ย 3 เดือนล่าสุด';
    $headers_row[] = "แพลนเดือน $label_target_short";
    $headers_row[] = 'เทียบ แพลนยาง ลบ ค่าเฉลี่ย';
    $headers_row[] = 'เทียบ แพลนยาง ลบ ค่าเฉลี่ย %';
    $headers_row[] = 'ผู้รับผิดชอบ';

    $sheet->fromArray([$headers_row], null, 'A1');
    $sheet->getStyle("A1:" . Coordinate::stringFromColumnIndex(count($headers_row)) . "1")->getFont()->setBold(true);

    $dataRows = [];
    $rowIdx = 2;
    $planColLetter = Coordinate::stringFromColumnIndex(count($headers_row) - 3);
    $avg3ColLetter = Coordinate::stringFromColumnIndex(count($headers_row) - 4);

    foreach ($rows as $r) {
        $range_q = [];
        foreach ($months_in_range as $idx => $mInfo) {
            $range_q[] = floatval($r["qty_range_{$idx}"]);
        }
        $max_q = !empty($range_q) ? max($range_q) : 0;
        $min_q = !empty($range_q) ? min($range_q) : 0;
        $total_q = floatval($r['total_qty']);
        $avg_period = round($total_q / $total_months, 2);

        $q1 = floatval($r['qty_m1']);
        $q2 = floatval($r['qty_m2']);
        $q3 = floatval($r['qty_m3']);
        $avg_3m = round(($q1 + $q2 + $q3) / 3.0, 2);

        $row_item = [
            $r['SALE_NAME'],
            $r['TAKE_NAME'],
            $r['AR_NAME'],
            $r['TRD_PROVINCE'],
            $r['SKU_CAT'],
            $r['SKU_CODE'],
            $r['SKU_NAME'],
            $r['BRAND'],
            $avg_period,
            $max_q,
            $min_q
        ];
        foreach ($range_q as $qv) {
            $row_item[] = $qv;
        }
        $row_item[] = $avg_3m;
        $row_item[] = null; // Plan
        $row_item[] = "={$planColLetter}{$rowIdx}-{$avg3ColLetter}{$rowIdx}";
        $row_item[] = "=IF({$avg3ColLetter}{$rowIdx}>0, ({$planColLetter}{$rowIdx}-{$avg3ColLetter}{$rowIdx})/{$avg3ColLetter}{$rowIdx}, 0)";
        $row_item[] = $r['SALE_NAME'];

        $dataRows[] = $row_item;
        $rowIdx++;
    }

    if (!empty($dataRows)) {
        $sheet->fromArray($dataRows, null, 'A2', true);
    }

    // Auto width
    $colCount = count($headers_row);
    for ($c = 1; $c <= $colCount; $c++) {
        $sheet->getColumnDimension(Coordinate::stringFromColumnIndex($c))->setAutoSize(true);
    }

} else {
    // EXACT 18 columns matching template_v_tires.xlsx for direct Import
    $templateVTires = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'import_process' . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . 'template_v_tires.xlsx';
    if (file_exists($templateVTires)) {
        $reader = IOFactory::createReaderForFile($templateVTires);
        $spreadsheet = $reader->load($templateVTires);
        $sheet = $spreadsheet->getActiveSheet();
    } else {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
    }
    $sheet->setTitle("Sheet1");

    // Dynamic Headers on Row 1 (exact 18 columns of template_v_tires.xlsx)
    $headers_row = [
        'เซลล์', 'เทค', 'รายชื่อลูกค้า', 'จังหวัด', 'ประเภท',
        'รหัสสินค้า', 'รายละเอียด', 'ยี่ห้อ',
        "ยอดขายสูงสุดต่อเดือน $target_year", "ยอดขายต่ำสุดต่อเดือน $target_year",
        $m3_label, $m2_label, $m1_label, 'ค่าเฉลี่ย 3 เดือน',
        'แพลนยางเดือนที่ต้องการ', 'เทียบ แพลนยาง ลบ ค่าเฉลี่ย', 'เทียบ แพลนยาง ลบ ค่าเฉลี่ย %', 'ผู้รับผิดชอบ'
    ];
    $sheet->fromArray([$headers_row], null, 'A1');
    $sheet->getStyle('A1:R1')->getFont()->setBold(true);

    // Clear old sample rows from row 2 upwards
    $oldHighestRow = $sheet->getHighestRow();
    if ($oldHighestRow >= 2) {
        $sheet->removeRow(2, $oldHighestRow - 1);
    }

    // Build data array for bulk insertion starting at row 2
    $dataRows = [];
    $rowIdx = 2;
    foreach ($rows as $r) {
        $range_q = [];
        foreach ($months_in_range as $idx => $mInfo) {
            $range_q[] = floatval($r["qty_range_{$idx}"]);
        }
        $max_q = !empty($range_q) ? max($range_q) : 0;
        $min_q = !empty($range_q) ? min($range_q) : 0;

        $q1 = floatval($r['qty_m1']);
        $q2 = floatval($r['qty_m2']);
        $q3 = floatval($r['qty_m3']);
        $avg_3m = round(($q1 + $q2 + $q3) / 3.0, 2);

        $dataRows[] = [
            $r['SALE_NAME'],
            $r['TAKE_NAME'],
            $r['AR_NAME'],
            $r['TRD_PROVINCE'],
            $r['SKU_CAT'],
            $r['SKU_CODE'],
            $r['SKU_NAME'],
            $r['BRAND'],
            $max_q,
            $min_q,
            $q3,
            $q2,
            $q1,
            $avg_3m,
            null, // Col O: แพลนยางเดือนที่ต้องการ (blank for input)
            "=O{$rowIdx}-N{$rowIdx}", // Col P: เทียบ แพลนยาง ลบ ค่าเฉลี่ย
            "=IF(N{$rowIdx}>0, (O{$rowIdx}-N{$rowIdx})/N{$rowIdx}, 0)", // Col Q: เทียบ แพลนยาง ลบ ค่าเฉลี่ย %
            $r['SALE_NAME'] // Col R: ผู้รับผิดชอบ
        ];
        $rowIdx++;
    }

    if (!empty($dataRows)) {
        $sheet->fromArray($dataRows, null, 'A2', true);
    }

    // Ensure widths are set
    for ($c = 1; $c <= 18; $c++) {
        $colLetter = Coordinate::stringFromColumnIndex($c);
        $w = $sheet->getColumnDimension($colLetter)->getWidth();
        if ($w <= 0 || $w == -1) {
            $sheet->getColumnDimension($colLetter)->setAutoSize(true);
        }
    }
}

// Output headers & file
if ($file_type === 'xlsx') {
    $filename = $base_filename . ".xlsx";
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
} else {
    // Default XLS
    $filename = $base_filename . ".xls";
    header('Content-Type: application/vnd.ms-excel');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    $writer = new Xls($spreadsheet);
    $writer->save('php://output');
}

exit();
