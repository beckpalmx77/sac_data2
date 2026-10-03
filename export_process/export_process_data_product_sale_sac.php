<?php
include('../config/connect_db.php');
date_default_timezone_set('Asia/Bangkok');
$filename = "ims_product_sale_sac-" . date('Y-m-d_H-i-s') . ".csv";

// Helper function to build fast indexed date conditions
function buildExportDateConditions($doc_date_start, $doc_date_to, &$whereClauses, &$searchArray) {
    if (empty($doc_date_start) || empty($doc_date_to)) {
        return;
    }
    $doc_date_start = str_replace('/', '-', trim($doc_date_start));
    $doc_date_to = str_replace('/', '-', trim($doc_date_to));

    $d_start = DateTime::createFromFormat('d-m-Y', $doc_date_start);
    $d_to = DateTime::createFromFormat('d-m-Y', $doc_date_to);

    if (!$d_start || !$d_to) {
        return;
    }

    if ($d_start > $d_to) {
        $tmp = $d_start;
        $d_start = $d_to;
        $d_to = $tmp;
    }

    $diffDays = $d_start->diff($d_to)->days;

    if ($diffDays <= 365) {
        $placeholders = [];
        $i = 0;
        $curr = clone $d_start;
        while ($curr <= $d_to) {
            $p1 = "d_sp" . $i;
            $p2 = "d_no" . $i;
            $placeholders[] = ":$p1";
            $placeholders[] = ":$p2";
            $searchArray[$p1] = $curr->format("d/m/Y ");
            $searchArray[$p2] = $curr->format("d/m/Y");
            $curr->modify("+1 day");
            $i++;
        }
        $whereClauses[] = "DI_DATE IN (" . implode(",", $placeholders) . ")";
    } else {
        $whereClauses[] = "DI_YEAR BETWEEN :y_start AND :y_to";
        $searchArray["y_start"] = $d_start->format("Y");
        $searchArray["y_to"] = $d_to->format("Y");
        $whereClauses[] = "STR_TO_DATE(TRIM(DI_DATE), '%d/%m/%Y') BETWEEN :d_start AND :d_to";
        $searchArray["d_start"] = $d_start->format("Y-m-d");
        $searchArray["d_to"] = $d_to->format("Y-m-d");
    }
}

$doc_date_start = isset($_POST["doc_date_start"]) ? $_POST["doc_date_start"] : '';
$doc_date_to = isset($_POST["doc_date_to"]) ? $_POST["doc_date_to"] : '';

$whereClauses = [];
$searchArray = [];
buildExportDateConditions($doc_date_start, $doc_date_to, $whereClauses, $searchArray);

$whereSQL = !empty($whereClauses) ? " WHERE " . implode(" AND ", $whereClauses) : "";

$select_query = "SELECT DI_DATE, DI_REF, AR_CODE, AR_NAME, SLMN_NAME, SKU_CODE, SKU_NAME, 
                        TRD_QTY, TRD_U_PRC, TRD_B_SELL, BRANCH, PGROUP 
                 FROM ims_product_sale_sac " . $whereSQL . " 
                 ORDER BY id DESC";

header('Content-Type: text/csv; charset=UTF-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');

// Stream UTF-8 BOM so Excel opens Thai characters natively
echo "\xEF\xBB\xBF";

$output = fopen('php://output', 'w');

// Header row
fputcsv($output, [
    'วันที่',
    'เลขที่เอกสาร',
    'รหัสลูกค้า',
    'ชื่อลูกค้า',
    'ผู้แทนขาย',
    'รหัสสินค้า',
    'รายละเอียดสินค้า',
    'จำนวน',
    'ราคาต่อหน่วย',
    'มูลค่าขาย',
    'สาขา',
    'กลุ่มสินค้า'
]);

$query = $conn->prepare($select_query);
$query->execute($searchArray);

while ($result = $query->fetch(PDO::FETCH_ASSOC)) {
    fputcsv($output, [
        $result['DI_DATE'],
        $result['DI_REF'],
        $result['AR_CODE'],
        $result['AR_NAME'],
        $result['SLMN_NAME'],
        $result['SKU_CODE'],
        $result['SKU_NAME'],
        number_format((float)$result['TRD_QTY'], 2),
        number_format((float)$result['TRD_U_PRC'], 2),
        number_format((float)$result['TRD_B_SELL'], 2),
        $result['BRANCH'],
        $result['PGROUP']
    ]);
}

fclose($output);
exit();

