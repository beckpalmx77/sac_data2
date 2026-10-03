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
$search = $_REQUEST['search'] ?? '';

if (empty($month) || empty($year)) {
    echo "กรุณาระบุเดือนและปีที่ต้องการ Export Template";
    exit;
}

$account_type = $_SESSION['account_type'] ?? '';
$first_name = $_SESSION['first_name'] ?? '';

// Build WHERE clause with flexible month matching (supports '7', '07', 7)
$month_str = (string)$month;
$month_padded = str_pad($month_str, 2, '0', STR_PAD_LEFT);
$month_int = strval(intval($month_str));

$where = "WHERE (p.forcast_month = :month OR p.forcast_month = :month_padded OR p.forcast_month = :month_int) AND p.forcast_year = :year AND (p.brand_code IS NULL OR p.brand_code NOT LIKE '%ของแถม%')";
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
if (!empty($search)) {
    $where .= " AND (p.ar_code LIKE :search OR p.ar_name LIKE :search OR p.sku_code LIKE :search OR p.sale_name LIKE :search OR p.brand_code LIKE :search)";
    $params[':search'] = "%$search%";
}

// SQL query
$sql = "SELECT p.ar_code, p.ar_name, p.sale_name, p.take_name, p.team_name, p.brand_code, p.sku_code,
               s.SKU_NAME as sku_name,
               IF(p.amphur = '' OR p.amphur IS NULL, v.TRD_AMPHUR, p.amphur) as amphur,
               IF(p.province = '' OR p.province IS NULL, v.TRD_PROVINCE, p.province) as province,
               IF(p.zone = '' OR p.zone IS NULL, v.ZONE, p.zone) as zone,
               p.avg_per_month_year, p.max_qty_year, p.min_qty_year,
               p.sales_month_3, p.sales_month_2, p.sales_month_1, p.avg_3_month,
               p.target_qty_month, p.forecast_qty_month_1, IFNULL(act.actual_qty, 0) as sales_actual_month,
               p.compare_forecast_month_1, p.compare_sales_percent,
               p.remark, p.forecast_qty_month_2, p.compare_forecast_month_2, p.compare_sales_percent_2,
               p.import_date
        FROM ims_tires_plan_forecast_realtime p
        LEFT JOIN v_customer_data_sale_all_zone v ON p.ar_code = v.AR_CODE
        LEFT JOIN (
            SELECT AR_CODE, SKU_CODE, MAX(SKU_NAME) as SKU_NAME
            FROM ims_data_sale_sac_all
            GROUP BY AR_CODE, SKU_CODE
        ) s ON p.ar_code = s.AR_CODE AND p.sku_code = s.SKU_CODE
        LEFT JOIN (
            SELECT AR_CODE, SKU_CODE, SUM(CAST(TRD_QTY AS DECIMAL(10,2))) as actual_qty
            FROM ims_product_sale_sac
            WHERE DI_MONTH = :act_month AND DI_YEAR = :act_year
              AND CAST(TRD_QTY AS DECIMAL(10,2)) > 0
              AND (TRD_Q_FREE IS NULL OR CAST(TRD_Q_FREE AS DECIMAL(10,2)) = 0)
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

    // Calculate dates for relative historical months
    $int_month = intval($month);
    $int_year = intval($year);
    $prev_year = $int_year - 1;

    $m1 = $int_month - 1; $y1 = $int_year; if ($m1 <= 0) { $m1 += 12; $y1--; }
    $m2 = $int_month - 2; $y2 = $int_year; if ($m2 <= 0) { $m2 += 12; $y2--; }
    $m3 = $int_month - 3; $y3 = $int_year; if ($m3 <= 0) { $m3 += 12; $y3--; }

    // Fallback: If no records found in forecast table, pull customer + SKU list directly from ims_data_sale_sac_all with stats
    if (empty($rows)) {
        $where_fb = "WHERE s.SALE_NAME IS NOT NULL AND TRIM(s.SALE_NAME) != '' AND TRIM(s.SALE_NAME) != '-' AND s.SALE_NAME NOT LIKE '%BTC%' AND s.SALE_NAME NOT LIKE '%RQ%' AND s.SALE_NAME NOT LIKE '%ส่วนกลาง%' AND s.SALE_NAME NOT LIKE '%READYQUICK%' AND s.SALE_NAME NOT LIKE '%READY QUICK%' AND CAST(s.TRD_QTY AS DECIMAL(10,2)) > 0 AND (s.TRD_Q_FREE IS NULL OR CAST(s.TRD_Q_FREE AS DECIMAL(10,2)) = 0) AND (s.SKU_CAT IS NULL OR s.SKU_CAT NOT LIKE '%ของแถม%')";
        $params_fb = [
            ':act_month' => strval(intval($month)),
            ':act_year' => $year,
            ':m1' => $m1, ':y1' => $y1,
            ':m2' => $m2, ':y2' => $y2,
            ':m3' => $m3, ':y3' => $y3,
            ':prev_year' => $prev_year
        ];

        if (!empty($sale_name)) {
            $clean_name = trim(preg_replace('/\s*\([^)]*\)/', '', $sale_name));
            $first_word = explode(' ', $clean_name)[0];

            $where_fb .= " AND (s.SALE_NAME = :sale_name OR s.SALE_NAME LIKE :sale_like_full OR s.SALE_NAME LIKE :sale_like_clean)";
            $params_fb[':sale_name'] = $sale_name;
            $params_fb[':sale_like_full'] = "%" . $sale_name . "%";
            $params_fb[':sale_like_clean'] = "%" . $first_word . "%";
        }

        if ($account_type === 'sale_user') {
            $where_fb .= " AND s.SALE_NAME = :first_name";
            $params_fb[':first_name'] = $first_name;
        } elseif ($account_type === 'take') {
            $where_fb .= " AND (s.SALE_NAME = :first_name OR s.TAKE_NAME = :first_name)";
            $params_fb[':first_name'] = $first_name;
        }

        $sql_fb = "SELECT s.AR_CODE as ar_code, s.AR_NAME as ar_name, s.SALE_NAME as sale_name, 
                          s.TAKE_NAME as take_name, '' as team_name, s.SKU_CAT as brand_code, s.SKU_CODE as sku_code,
                          MAX(s.SKU_NAME) as sku_name,
                          v.TRD_AMPHUR as amphur, v.TRD_PROVINCE as province, v.ZONE as zone,
                          
                          -- Historical sales M-3, M-2, M-1
                          SUM(CASE WHEN CAST(s.DI_MONTH AS UNSIGNED) = :m3 AND CAST(s.DI_YEAR AS UNSIGNED) = :y3 THEN CAST(s.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as sales_month_3,
                          SUM(CASE WHEN CAST(s.DI_MONTH AS UNSIGNED) = :m2 AND CAST(s.DI_YEAR AS UNSIGNED) = :y2 THEN CAST(s.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as sales_month_2,
                          SUM(CASE WHEN CAST(s.DI_MONTH AS UNSIGNED) = :m1 AND CAST(s.DI_YEAR AS UNSIGNED) = :y1 THEN CAST(s.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as sales_month_1,

                          -- Previous year statistics
                          ROUND(SUM(CASE WHEN CAST(s.DI_YEAR AS UNSIGNED) = :prev_year THEN CAST(s.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) / 12, 2) as avg_per_month_year,
                          
                          GREATEST(
                              SUM(CASE WHEN CAST(s.DI_MONTH AS UNSIGNED) = 1 AND CAST(s.DI_YEAR AS UNSIGNED) = :prev_year THEN CAST(s.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END),
                              SUM(CASE WHEN CAST(s.DI_MONTH AS UNSIGNED) = 2 AND CAST(s.DI_YEAR AS UNSIGNED) = :prev_year THEN CAST(s.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END),
                              SUM(CASE WHEN CAST(s.DI_MONTH AS UNSIGNED) = 3 AND CAST(s.DI_YEAR AS UNSIGNED) = :prev_year THEN CAST(s.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END),
                              SUM(CASE WHEN CAST(s.DI_MONTH AS UNSIGNED) = 4 AND CAST(s.DI_YEAR AS UNSIGNED) = :prev_year THEN CAST(s.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END),
                              SUM(CASE WHEN CAST(s.DI_MONTH AS UNSIGNED) = 5 AND CAST(s.DI_YEAR AS UNSIGNED) = :prev_year THEN CAST(s.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END),
                              SUM(CASE WHEN CAST(s.DI_MONTH AS UNSIGNED) = 6 AND CAST(s.DI_YEAR AS UNSIGNED) = :prev_year THEN CAST(s.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END),
                              SUM(CASE WHEN CAST(s.DI_MONTH AS UNSIGNED) = 7 AND CAST(s.DI_YEAR AS UNSIGNED) = :prev_year THEN CAST(s.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END),
                              SUM(CASE WHEN CAST(s.DI_MONTH AS UNSIGNED) = 8 AND CAST(s.DI_YEAR AS UNSIGNED) = :prev_year THEN CAST(s.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END),
                              SUM(CASE WHEN CAST(s.DI_MONTH AS UNSIGNED) = 9 AND CAST(s.DI_YEAR AS UNSIGNED) = :prev_year THEN CAST(s.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END),
                              SUM(CASE WHEN CAST(s.DI_MONTH AS UNSIGNED) = 10 AND CAST(s.DI_YEAR AS UNSIGNED) = :prev_year THEN CAST(s.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END),
                              SUM(CASE WHEN CAST(s.DI_MONTH AS UNSIGNED) = 11 AND CAST(s.DI_YEAR AS UNSIGNED) = :prev_year THEN CAST(s.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END),
                              SUM(CASE WHEN CAST(s.DI_MONTH AS UNSIGNED) = 12 AND CAST(s.DI_YEAR AS UNSIGNED) = :prev_year THEN CAST(s.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END)
                          ) as max_qty_year,

                          LEAST(
                              SUM(CASE WHEN CAST(s.DI_MONTH AS UNSIGNED) = 1 AND CAST(s.DI_YEAR AS UNSIGNED) = :prev_year THEN CAST(s.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END),
                              SUM(CASE WHEN CAST(s.DI_MONTH AS UNSIGNED) = 2 AND CAST(s.DI_YEAR AS UNSIGNED) = :prev_year THEN CAST(s.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END),
                              SUM(CASE WHEN CAST(s.DI_MONTH AS UNSIGNED) = 3 AND CAST(s.DI_YEAR AS UNSIGNED) = :prev_year THEN CAST(s.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END),
                              SUM(CASE WHEN CAST(s.DI_MONTH AS UNSIGNED) = 4 AND CAST(s.DI_YEAR AS UNSIGNED) = :prev_year THEN CAST(s.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END),
                              SUM(CASE WHEN CAST(s.DI_MONTH AS UNSIGNED) = 5 AND CAST(s.DI_YEAR AS UNSIGNED) = :prev_year THEN CAST(s.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END),
                              SUM(CASE WHEN CAST(s.DI_MONTH AS UNSIGNED) = 6 AND CAST(s.DI_YEAR AS UNSIGNED) = :prev_year THEN CAST(s.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END),
                              SUM(CASE WHEN CAST(s.DI_MONTH AS UNSIGNED) = 7 AND CAST(s.DI_YEAR AS UNSIGNED) = :prev_year THEN CAST(s.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END),
                              SUM(CASE WHEN CAST(s.DI_MONTH AS UNSIGNED) = 8 AND CAST(s.DI_YEAR AS UNSIGNED) = :prev_year THEN CAST(s.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END),
                              SUM(CASE WHEN CAST(s.DI_MONTH AS UNSIGNED) = 9 AND CAST(s.DI_YEAR AS UNSIGNED) = :prev_year THEN CAST(s.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END),
                              SUM(CASE WHEN CAST(s.DI_MONTH AS UNSIGNED) = 10 AND CAST(s.DI_YEAR AS UNSIGNED) = :prev_year THEN CAST(s.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END),
                              SUM(CASE WHEN CAST(s.DI_MONTH AS UNSIGNED) = 11 AND CAST(s.DI_YEAR AS UNSIGNED) = :prev_year THEN CAST(s.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END),
                              SUM(CASE WHEN CAST(s.DI_MONTH AS UNSIGNED) = 12 AND CAST(s.DI_YEAR AS UNSIGNED) = :prev_year THEN CAST(s.TRD_QTY AS DECIMAL(10,2)) ELSE 0 END)
                          ) as min_qty_year,

                          0 as target_qty_month, 0 as forecast_qty_month_1, IFNULL(act.actual_qty, 0) as sales_actual_month,
                          0 as compare_forecast_month_1, 0 as compare_sales_percent,
                          '' as remark, 0 as forecast_qty_month_2, 0 as compare_forecast_month_2, 0 as compare_sales_percent_2,
                          '' as import_date
                   FROM ims_data_sale_sac_all s
                   LEFT JOIN v_customer_data_sale_all_zone v ON s.AR_CODE = v.AR_CODE
                   LEFT JOIN (
                       SELECT AR_CODE, SKU_CODE, SUM(CAST(TRD_QTY AS DECIMAL(10,2))) as actual_qty
                       FROM ims_product_sale_sac
                       WHERE DI_MONTH = :act_month AND DI_YEAR = :act_year
                       GROUP BY AR_CODE, SKU_CODE
                   ) act ON s.AR_CODE = act.AR_CODE AND s.SKU_CODE = act.SKU_CODE
                   $where_fb
                   GROUP BY s.AR_CODE, s.AR_NAME, s.SALE_NAME, s.SKU_CAT, s.SKU_CODE
                   ORDER BY s.AR_CODE ASC, s.SKU_CODE ASC";

        $stmt_fb = $conn->prepare($sql_fb);
        $stmt_fb->execute($params_fb);
        $rows = $stmt_fb->fetchAll(PDO::FETCH_ASSOC);
    }

    $filename = "Forecast_Template_Realtime_" . $month . "_" . $year . "_" . date('Ymd_His') . ".csv";

    header('Content-Type: text/csv; charset=UTF-8');
    header("Content-Disposition: attachment; filename=" . $filename);

    // Send UTF-8 BOM so Excel opens Thai language properly
    echo "\xEF\xBB\xBF";

    // Build dynamic header names based on selected month and year
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

    $int_month = intval($month);
    $int_year = intval($year);
    $prev_year = $int_year - 1;
    $prev_year_short = substr((string)$prev_year, -2);
    $year_short = substr((string)$int_year, -2);

    // Calculate M-1, M-2, M-3 months and years
    $m1 = $int_month - 1; $y1 = $int_year; if ($m1 <= 0) { $m1 += 12; $y1--; }
    $m2 = $int_month - 2; $y2 = $int_year; if ($m2 <= 0) { $m2 += 12; $y2--; }
    $m3 = $int_month - 3; $y3 = $int_year; if ($m3 <= 0) { $m3 += 12; $y3--; }

    $m3_header = $thai_months_full[$m3] . "'" . substr((string)$y3, -2);
    $m2_header = $thai_months_full[$m2] . "'" . substr((string)$y2, -2);
    $m1_header = $thai_months_full[$m1] . "'" . substr((string)$y1, -2);
    $forecast_header = "แพลนยางเดือนที่ต้องการ";

    // Header 20 columns matching doc/plan_tires_r1.xlsx template format exactly
    $csv_headers = [
        "รหัสลูกค้า",                           // Col A (0)
        "รายชื่อลูกค้า",                        // Col B (1)
        "เซลล์",                              // Col C (2)
        "เทค",                               // Col D (3)
        "อำเภอ",                              // Col E (4)
        "จังหวัด",                            // Col F (5)
        "ยี่ห้อ",                              // Col G (6)
        "รหัสสินค้า 1",                        // Col H (7)
        "รายละเอียด",                          // Col I (8)
        "เฉลี่ย 12 เดือน ปีก่อน",                  // Col J (9)
        "ยอดขายสูงสุดต่อเดือน(ปีก่อน)",             // Col K (10)
        "ยอดขายต่ำสุดต่อเดือน(ปีก่อน)",             // Col L (11)
        $m3_header,                           // Col M (12)
        $m2_header,                           // Col N (13)
        $m1_header,                           // Col O (14)
        "ค่าเฉลี่ย 3 เดือน",                     // Col P (15)
        "เป้ารายเดือน",                        // Col Q (16)
        $forecast_header,                     // Col R (17)
        "เทียบ แพลนยาง ลบ ค่าเฉลี่ย",            // Col S (18)
        "เทียบ แพลนยาง ลบ ค่าเฉลี่ย %",          // Col T (19)
        "ผู้รับผิดชอบ"                           // Col U (20)
    ];

    echo implode(',', array_map(function($h) {
        return '"' . str_replace('"', '""', $h) . '"';
    }, $csv_headers)) . "\n";

    foreach ($rows as $row) {
        $avg_ly = (float)($row['avg_per_month_year'] ?? 0);
        $m3 = (float)($row['sales_month_3'] ?? 0);
        $m2 = (float)($row['sales_month_2'] ?? 0);
        $m1 = (float)($row['sales_month_1'] ?? 0);
        $avg3 = (isset($row['avg_3_month']) && (float)$row['avg_3_month'] > 0)
            ? (float)$row['avg_3_month'] 
            : round(($m3 + $m2 + $m1) / 3, 2);

        $line = [
            '"' . str_replace('"', '""', (string)($row['ar_code'] ?? '')) . '"',    // Col A (0)
            '"' . str_replace('"', '""', (string)($row['ar_name'] ?? '')) . '"',    // Col B (1)
            '"' . str_replace('"', '""', (string)($row['sale_name'] ?? '')) . '"',  // Col C (2)
            '"' . str_replace('"', '""', (string)($row['take_name'] ?? '')) . '"',  // Col D (3)
            '"' . str_replace('"', '""', (string)($row['amphur'] ?? '')) . '"',     // Col E (4)
            '"' . str_replace('"', '""', (string)($row['province'] ?? '')) . '"',   // Col F (5)
            '"' . str_replace('"', '""', (string)($row['brand_code'] ?? '')) . '"', // Col G (6)
            '"' . str_replace('"', '""', (string)($row['sku_code'] ?? '')) . '"',   // Col H (7)
            '"' . str_replace('"', '""', (string)($row['sku_name'] ?? '')) . '"',   // Col I (8: รายละเอียด)
            $avg_ly,                                                                // Col J (9: เฉลี่ย 12 เดือน ปีก่อน)
            (float)($row['max_qty_year'] ?? 0),                                     // Col K (10: ยอดขายสูงสุด...)
            (float)($row['min_qty_year'] ?? 0),                                     // Col L (11: ยอดขายต่ำสุด...)
            $m3,                                                                    // Col M (12: 3 เดือน ย้อนหลัง)
            $m2,                                                                    // Col N (13: 2 เดือนย้อนหลัง)
            $m1,                                                                    // Col O (14: 1 เดือนย้อนหลัง)
            $avg3,                                                                  // Col P (15: ค่าเฉลี่ย 3 เดือน)
            (float)($row['target_qty_month'] ?? 0),                                 // Col Q (16: เป้ารายเดือน)
            (float)($row['forecast_qty_month_1'] ?? 0),                             // Col R (17: แพลนยางเดือนที่ต้องการ)
            (float)($row['compare_forecast_month_1'] ?? 0),                         // Col S (18: เทียบ แพลนยาง ลบ ค่าเฉลี่ย)
            (float)($row['compare_sales_percent'] ?? 0),                            // Col T (19: เทียบ แพลนยาง ลบ ค่าเฉลี่ย %)
            '"' . str_replace('"', '""', (string)($row['responsible_person'] ?? '')) . '"' // Col U (20: ผู้รับผิดชอบ)
        ];

        echo implode(',', $line) . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
?>
