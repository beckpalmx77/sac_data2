<?php
header('Content-Type: application/json; charset=utf-8');
include('../config/connect_db.php');

$filter_type = $_POST['filter_type'] ?? 'all';
$name = trim($_POST['name'] ?? '');
$year = trim($_POST['year'] ?? '');
$month = trim($_POST['month'] ?? '');
$sku_cat = trim($_POST['sku_cat'] ?? '');
$top_limit = isset($_POST['top_limit']) && $_POST['top_limit'] !== '' && $_POST['top_limit'] !== '-' ? (int)$_POST['top_limit'] : 0;
$search_text = trim($_POST['search_text'] ?? '');

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

    // Summary calculation
    $grand_total_amount = 0;
    $grand_total_qty = 0;
    $cat_totals = [
        'LTB' => 0,
        'LTR' => 0,
        'TBB' => 0,
        'TBR' => 0,
        'ยางเล็ก' => 0,
        'อื่นๆ' => 0
    ];

    foreach ($data as $row) {
        $amt = (float)$row['TOTAL_AMOUNT'];
        $qty = (float)$row['TOTAL_QTY'];
        $grand_total_amount += $amt;
        $grand_total_qty += $qty;

        $cat_totals['LTB'] += (float)$row['LTB_AMOUNT'];
        $cat_totals['LTR'] += (float)$row['LTR_AMOUNT'];
        $cat_totals['TBB'] += (float)$row['TBB_AMOUNT'];
        $cat_totals['TBR'] += (float)$row['TBR_AMOUNT'];
        $cat_totals['ยางเล็ก'] += (float)$row['SMT_AMOUNT'];
        $cat_totals['อื่นๆ'] += (float)$row['OTHER_AMOUNT'];
    }

    $total_stores = count($data);
    $avg_amount = $total_stores > 0 ? ($grand_total_amount / $total_stores) : 0;

    // Attach rank and percentage share to each record
    $rank = 1;
    $formatted_data = [];
    foreach ($data as $row) {
        $amt = (float)$row['TOTAL_AMOUNT'];
        $share = $grand_total_amount > 0 ? (($amt / $grand_total_amount) * 100) : 0;

        $formatted_data[] = [
            'rank' => $rank++,
            'AR_NAME' => $row['AR_NAME'],
            'AR_CODE' => $row['AR_CODE'] ?: '-',
            'SALE_NAMES' => $row['SALE_NAMES'] ?: '-',
            'TAKE_NAMES' => $row['TAKE_NAMES'] ?: '-',
            'TOTAL_DOCS' => (int)$row['TOTAL_DOCS'],
            'TOTAL_QTY' => (float)$row['TOTAL_QTY'],
            'TOTAL_AMOUNT' => $amt,
            'LTB_AMOUNT' => (float)$row['LTB_AMOUNT'],
            'LTR_AMOUNT' => (float)$row['LTR_AMOUNT'],
            'TBB_AMOUNT' => (float)$row['TBB_AMOUNT'],
            'TBR_AMOUNT' => (float)$row['TBR_AMOUNT'],
            'SMT_AMOUNT' => (float)$row['SMT_AMOUNT'],
            'OTHER_AMOUNT' => (float)$row['OTHER_AMOUNT'],
            'SHARE_PERCENT' => round($share, 2)
        ];
    }

    $top_1_store = !empty($formatted_data) ? [
        'name' => $formatted_data[0]['AR_NAME'],
        'amount' => $formatted_data[0]['TOTAL_AMOUNT']
    ] : ['name' => '-', 'amount' => 0];

    echo json_encode([
        'status' => 'success',
        'summary' => [
            'grand_total_amount' => $grand_total_amount,
            'grand_total_qty' => $grand_total_qty,
            'total_stores' => $total_stores,
            'avg_amount' => round($avg_amount, 2),
            'top_1_store' => $top_1_store,
            'cat_totals' => $cat_totals
        ],
        'data' => $formatted_data
    ], JSON_UNESCAPED_UNICODE);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
