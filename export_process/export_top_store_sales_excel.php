<?php
session_start();
include('../config/connect_db.php');

$filter_type = $_GET['filter_type'] ?? $_POST['filter_type'] ?? 'all';
$name = trim($_GET['name'] ?? $_POST['name'] ?? '');
$year = trim($_GET['year'] ?? $_POST['year'] ?? '');
$month = trim($_GET['month'] ?? $_POST['month'] ?? '');
$sku_cat = trim($_GET['sku_cat'] ?? $_POST['sku_cat'] ?? '');
$top_limit = isset($_GET['top_limit']) && $_GET['top_limit'] !== '' && $_GET['top_limit'] !== '-' ? (int)$_GET['top_limit'] : 0;
$search_text = trim($_GET['search_text'] ?? $_POST['search_text'] ?? '');

$where = " WHERE 1=1 AND AR_NAME IS NOT NULL AND AR_NAME != '' AND AR_NAME != '-' ";
$params = [];

if ($year !== '' && $year !== '-') {
    $where .= " AND DI_YEAR = :year ";
    $params[':year'] = $year;
}

if ($month !== '' && $month !== '-') {
    $month_int = (int)$month;
    $month_str = sprintf("%02d", $month_int);
    $where .= " AND (DI_MONTH = :month_int OR DI_MONTH = :month_str OR DI_MONTH LIKE :month_like) ";
    $params[':month_int'] = (string)$month_int;
    $params[':month_str'] = $month_str;
    $params[':month_like'] = "%" . $month_str . "%";
}

if (!empty($name) && $name !== '-') {
    if ($filter_type === 'sale') {
        $where .= " AND SALE_NAME = :name ";
        $params[':name'] = $name;
    } elseif ($filter_type === 'take') {
        $where .= " AND TAKE_NAME = :name ";
        $params[':name'] = $name;
    } else {
        $where .= " AND (SALE_NAME = :name OR TAKE_NAME = :name) ";
        $params[':name'] = $name;
    }
}

if (!empty($sku_cat) && $sku_cat !== '-') {
    $where .= " AND SKU_CAT = :sku_cat ";
    $params[':sku_cat'] = $sku_cat;
}

if (!empty($search_text)) {
    $where .= " AND (AR_NAME LIKE :search OR AR_CODE LIKE :search) ";
    $params[':search'] = '%' . $search_text . '%';
}

try {
    $sql = "SELECT 
                AR_NAME,
                MAX(AR_CODE) AS AR_CODE,
                GROUP_CONCAT(DISTINCT SALE_NAME ORDER BY SALE_NAME SEPARATOR ', ') AS SALE_NAMES,
                GROUP_CONCAT(DISTINCT TAKE_NAME ORDER BY TAKE_NAME SEPARATOR ', ') AS TAKE_NAMES,
                COUNT(DISTINCT DI_REF) AS TOTAL_DOCS,
                SUM(CAST(TRD_QTY AS DECIMAL(12,2))) AS TOTAL_QTY,
                SUM(CAST(TRD_AMOUNT_PRICE AS DECIMAL(12,2))) AS TOTAL_AMOUNT,
                SUM(CASE WHEN SKU_CAT = 'LTB' THEN CAST(TRD_AMOUNT_PRICE AS DECIMAL(12,2)) ELSE 0 END) AS LTB_AMOUNT,
                SUM(CASE WHEN SKU_CAT = 'LTR' THEN CAST(TRD_AMOUNT_PRICE AS DECIMAL(12,2)) ELSE 0 END) AS LTR_AMOUNT,
                SUM(CASE WHEN SKU_CAT = 'TBB' THEN CAST(TRD_AMOUNT_PRICE AS DECIMAL(12,2)) ELSE 0 END) AS TBB_AMOUNT,
                SUM(CASE WHEN SKU_CAT = 'TBR' THEN CAST(TRD_AMOUNT_PRICE AS DECIMAL(12,2)) ELSE 0 END) AS TBR_AMOUNT,
                SUM(CASE WHEN SKU_CAT = 'ยางเล็ก' THEN CAST(TRD_AMOUNT_PRICE AS DECIMAL(12,2)) ELSE 0 END) AS SMT_AMOUNT,
                SUM(CASE WHEN SKU_CAT NOT IN ('LTB','LTR','TBB','TBR','ยางเล็ก') OR SKU_CAT IS NULL THEN CAST(TRD_AMOUNT_PRICE AS DECIMAL(12,2)) ELSE 0 END) AS OTHER_AMOUNT
            FROM ims_data_sale_sac_all
            {$where}
            GROUP BY AR_NAME
            HAVING TOTAL_AMOUNT > 0
            ORDER BY TOTAL_AMOUNT DESC";

    if ($top_limit > 0) {
        $sql .= " LIMIT " . $top_limit;
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $grand_total = 0;
    foreach ($data as $r) {
        $grand_total += (float)$r['TOTAL_AMOUNT'];
    }

    $filename = "top_store_sales_" . date("Ymd_His") . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    
    // Output UTF-8 BOM so Excel opens Thai text properly
    echo "\xEF\xBB\xBF";
    $output = fopen('php://output', 'w');

    // Header row
    fputcsv($output, [
        'อันดับ',
        'รหัสร้านค้า',
        'ชื่อร้านค้า',
        'Sale ที่ดูแล',
        'Take ที่ดูแล',
        'จำนวนเอกสาร',
        'จำนวนรวม (ชิ้น)',
        'ยอดขาย LTB (บาท)',
        'ยอดขาย LTR (บาท)',
        'ยอดขาย TBB (บาท)',
        'ยอดขาย TBR (บาท)',
        'ยอดขาย ยางเล็ก (บาท)',
        'ยอดขาย อื่นๆ (บาท)',
        'ยอดขายรวมทั้งหมด (บาท)',
        'สัดส่วนยอดขาย (%)'
    ]);

    $rank = 1;
    foreach ($data as $row) {
        $amt = (float)$row['TOTAL_AMOUNT'];
        $share = $grand_total > 0 ? (($amt / $grand_total) * 100) : 0;

        fputcsv($output, [
            $rank++,
            $row['AR_CODE'] ?: '-',
            $row['AR_NAME'],
            $row['SALE_NAMES'] ?: '-',
            $row['TAKE_NAMES'] ?: '-',
            $row['TOTAL_DOCS'],
            number_format((float)$row['TOTAL_QTY'], 2, '.', ''),
            number_format((float)$row['LTB_AMOUNT'], 2, '.', ''),
            number_format((float)$row['LTR_AMOUNT'], 2, '.', ''),
            number_format((float)$row['TBB_AMOUNT'], 2, '.', ''),
            number_format((float)$row['TBR_AMOUNT'], 2, '.', ''),
            number_format((float)$row['SMT_AMOUNT'], 2, '.', ''),
            number_format((float)$row['OTHER_AMOUNT'], 2, '.', ''),
            number_format($amt, 2, '.', ''),
            number_format($share, 2, '.', '') . '%'
        ]);
    }

    fclose($output);
    exit;

} catch (Exception $e) {
    echo "Error exporting data: " . $e->getMessage();
}
