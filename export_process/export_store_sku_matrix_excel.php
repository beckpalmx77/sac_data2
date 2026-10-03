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
    // Fetch Top Stores
    $sql_stores = "SELECT AR_NAME, MAX(AR_CODE) AS AR_CODE
                   FROM ims_data_sale_sac_all
                   {$where}
                   GROUP BY AR_NAME
                   HAVING SUM(CAST(TRD_AMOUNT_PRICE AS DECIMAL(12,2))) > 0
                   ORDER BY SUM(CAST(TRD_AMOUNT_PRICE AS DECIMAL(12,2))) DESC";

    if ($top_limit > 0) {
        $sql_stores .= " LIMIT " . $top_limit;
    }

    $stmt_s = $conn->prepare($sql_stores);
    $stmt_s->execute($params);
    $stores = $stmt_s->fetchAll(PDO::FETCH_ASSOC);

    $filename = "top_stores_20_skus_" . date("Ymd_His") . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    echo "\xEF\xBB\xBF"; // UTF-8 BOM
    $output = fopen('php://output', 'w');

    // Header row
    fputcsv($output, [
        'ลำดับร้านค้า',
        'รหัสร้านค้า',
        'ชื่อร้านค้า',
        'อันดับ SKU',
        'รหัส SKU',
        'ชื่อยาง / SKU Name',
        'แบรนด์',
        'หมวดหมู่',
        'จำนวนสั่งซื้อ (ชิ้น)',
        'จำนวนแถม (ชิ้น)',
        'ยอดขายรวม (บาท)'
    ]);

    $store_rank = 1;
    foreach ($stores as $st) {
        $ar_name = $st['AR_NAME'];
        $ar_code = $st['AR_CODE'] ?: '-';

        $p_store = $params;
        $p_store[':ar_name'] = $ar_name;

        $sql_sku = "SELECT 
                        SKU_CODE,
                        SKU_NAME,
                        BRAND,
                        SKU_CAT,
                        SUM(CAST(TRD_QTY AS DECIMAL(10,2))) AS TOTAL_QTY,
                        SUM(CAST(TRD_Q_FREE AS DECIMAL(10,2))) AS TOTAL_FREE,
                        SUM(CAST(TRD_AMOUNT_PRICE AS DECIMAL(12,2))) AS TOTAL_AMOUNT
                    FROM ims_data_sale_sac_all
                    {$where} AND AR_NAME = :ar_name
                    GROUP BY SKU_CODE, SKU_NAME, BRAND, SKU_CAT
                    ORDER BY TOTAL_AMOUNT DESC
                    LIMIT 20";

        $stmt_sku = $conn->prepare($sql_sku);
        $stmt_sku->execute($p_store);
        $skus = $stmt_sku->fetchAll(PDO::FETCH_ASSOC);

        $sku_rank = 1;
        foreach ($skus as $sk) {
            fputcsv($output, [
                $store_rank,
                $ar_code,
                $ar_name,
                $sku_rank++,
                $sk['SKU_CODE'] ?: '-',
                $sk['SKU_NAME'] ?: '-',
                $sk['BRAND'] ?: '-',
                $sk['SKU_CAT'] ?: '-',
                number_format((float)$sk['TOTAL_QTY'], 2, '.', ''),
                number_format((float)$sk['TOTAL_FREE'], 2, '.', ''),
                number_format((float)$sk['TOTAL_AMOUNT'], 2, '.', '')
            ]);
        }
        $store_rank++;
    }

    fclose($output);
    exit;

} catch (Exception $e) {
    echo "Error exporting matrix: " . $e->getMessage();
}
