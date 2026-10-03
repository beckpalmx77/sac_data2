<?php
header('Content-Type: application/json; charset=utf-8');
include('../config/connect_db.php');

$ar_name = trim($_POST['ar_name'] ?? '');
$filter_type = $_POST['filter_type'] ?? 'all';
$name = trim($_POST['name'] ?? '');
$year = trim($_POST['year'] ?? '');
$month = trim($_POST['month'] ?? '');
$sku_cat = trim($_POST['sku_cat'] ?? '');

if (empty($ar_name)) {
    echo json_encode(['status' => 'error', 'message' => 'กรุณาระบุชื่อร้านค้า'], JSON_UNESCAPED_UNICODE);
    exit;
}

$where = " WHERE AR_NAME = :ar_name ";
$params = [':ar_name' => $ar_name];

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

try {
    // 1. Top 20 SKUs for this store
    $sql_top_skus = "SELECT 
                        SKU_CODE,
                        SKU_NAME,
                        BRAND,
                        SKU_CAT,
                        SUM(CAST(TRD_QTY AS DECIMAL(10,2))) AS TOTAL_QTY,
                        SUM(CAST(TRD_Q_FREE AS DECIMAL(10,2))) AS TOTAL_FREE,
                        SUM(CAST(TRD_AMOUNT_PRICE AS DECIMAL(12,2))) AS TOTAL_AMOUNT
                    FROM ims_data_sale_sac_all
                    {$where}
                    GROUP BY SKU_CODE, SKU_NAME, BRAND, SKU_CAT
                    ORDER BY TOTAL_AMOUNT DESC
                    LIMIT 20";

    $stmt_skus = $conn->prepare($sql_top_skus);
    $stmt_skus->execute($params);
    $top_skus_rows = $stmt_skus->fetchAll(PDO::FETCH_ASSOC);

    $store_total_amount = 0;
    foreach ($top_skus_rows as $r) {
        $store_total_amount += (float)$r['TOTAL_AMOUNT'];
    }

    $top_20_skus = [];
    $rank = 1;
    foreach ($top_skus_rows as $row) {
        $amt = (float)$row['TOTAL_AMOUNT'];
        $share = $store_total_amount > 0 ? (($amt / $store_total_amount) * 100) : 0;
        $top_20_skus[] = [
            'rank' => $rank++,
            'SKU_CODE' => $row['SKU_CODE'] ?: '-',
            'SKU_NAME' => $row['SKU_NAME'] ?: '-',
            'BRAND' => $row['BRAND'] ?: '-',
            'SKU_CAT' => $row['SKU_CAT'] ?: '-',
            'TOTAL_QTY' => (float)$row['TOTAL_QTY'],
            'TOTAL_FREE' => (float)$row['TOTAL_FREE'],
            'TOTAL_AMOUNT' => $amt,
            'SHARE_PERCENT' => round($share, 2)
        ];
    }

    // 2. All document transactions
    $sql_docs = "SELECT 
                    DI_DATE,
                    DI_REF,
                    SKU_CODE,
                    SKU_NAME,
                    BRAND,
                    SKU_CAT,
                    SALE_NAME,
                    TAKE_NAME,
                    CAST(TRD_QTY AS DECIMAL(10,2)) AS TRD_QTY,
                    CAST(TRD_Q_FREE AS DECIMAL(10,2)) AS TRD_Q_FREE,
                    CAST(TRD_AMOUNT_PRICE AS DECIMAL(12,2)) AS TRD_AMOUNT_PRICE
                FROM ims_data_sale_sac_all
                {$where}
                ORDER BY DI_DATE DESC, id DESC";

    $stmt_docs = $conn->prepare($sql_docs);
    $stmt_docs->execute($params);
    $doc_rows = $stmt_docs->fetchAll(PDO::FETCH_ASSOC);

    $grand_qty = 0;
    $grand_amount = 0;
    foreach ($doc_rows as &$r) {
        $r['TRD_QTY'] = (float)$r['TRD_QTY'];
        $r['TRD_Q_FREE'] = (float)$r['TRD_Q_FREE'];
        $r['TRD_AMOUNT_PRICE'] = (float)$r['TRD_AMOUNT_PRICE'];
        $grand_qty += $r['TRD_QTY'];
        $grand_amount += $r['TRD_AMOUNT_PRICE'];
    }

    echo json_encode([
        'status' => 'success',
        'ar_name' => $ar_name,
        'summary' => [
            'total_items' => count($doc_rows),
            'total_sku_count' => count($top_20_skus),
            'total_qty' => $grand_qty,
            'total_amount' => $grand_amount
        ],
        'top_20_skus' => $top_20_skus,
        'data' => $doc_rows
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
