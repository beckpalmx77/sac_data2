<?php
include('../config/connect_db.php');

date_default_timezone_set('Asia/Bangkok');
$filename = "sac_sale_" . date('Y-m-d_H-i-s') . ".csv";

// Set headers to prompt file download as CSV
header('Content-Type: text/csv; charset=UTF-8');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

echo "\xEF\xBB\xBF";

// Get POST data
isset($_POST['doc_date_start']) ? $doc_date_start = $_POST['doc_date_start'] : $doc_date_start = "";
isset($_POST['doc_date_to']) ? $doc_date_to = $_POST['doc_date_to'] : $doc_date_to = "";
isset($_POST['AR_NAME']) ? $ar_name = $_POST['AR_NAME'] : $ar_name = "";
isset($_POST['TRD_AMPHUR']) ? $amphur = $_POST['TRD_AMPHUR'] : $amphur = "";
isset($_POST['TRD_PROVINCE']) ? $province = $_POST['TRD_PROVINCE'] : $province = "";
isset($_POST['BRAND']) ? $brand = $_POST['BRAND'] : $brand = "";
isset($_POST['SKU_CAT']) ? $sku_cat = $_POST['SKU_CAT'] : $sku_cat = "";
isset($_POST['geographies']) ? $geographies = $_POST['geographies'] : $geographies = "";

$sale_names_input = isset($_POST['SALE_NAME']) ? $_POST['SALE_NAME'] : [];
if (!is_array($sale_names_input)) {
    $sale_names_input = [$sale_names_input];
}

$clean_sale_names = [];
foreach ($sale_names_input as $s_name) {
    $s_name = trim($s_name ?? '');
    if ($s_name !== '' && $s_name !== '-') {
        $cleaned = preg_replace('/\s+/u', '', $s_name);
        if ($cleaned !== '') {
            $clean_sale_names[] = $cleaned;
        }
    }
}

// Convert date format
$doc_date_start = DateTime::createFromFormat('d-m-Y', $doc_date_start)->format('Y-m-d');
$doc_date_to = DateTime::createFromFormat('d-m-Y', $doc_date_to)->format('Y-m-d');

// Build query condition
$search_Query = "";
if (!empty($doc_date_start) && !empty($doc_date_to)) {
    $search_Query .= " AND STR_TO_DATE(DI_DATE, '%d-%m-%Y') BETWEEN '" . $doc_date_start . "' AND '". $doc_date_to ."' ";
}
if (!empty($ar_name) && $ar_name !== '-') {
    $search_Query .= " AND sale_sac.AR_NAME = '" . $ar_name . "' ";
}
if (!empty($amphur) && $amphur !== '-') {
    $search_Query .= " AND sale_sac.TRD_AMPHUR = '" . $amphur . "' ";
}
if (!empty($province) && $province !== '-') {
    $search_Query .= " AND sale_sac.TRD_PROVINCE = '" . $province . "' ";
}
if (!empty($clean_sale_names)) {
    $escaped_names = array_map(function($item) use ($conn) {
        return $conn->quote($item);
    }, $clean_sale_names);
    $in_clause = "(" . implode(",", $escaped_names) . ")";
    $search_Query .= " AND (REPLACE(sale_sac.SALE_NAME, ' ', '') IN " . $in_clause . " OR REPLACE(sale_sac.TAKE_NAME, ' ', '') IN " . $in_clause . ") ";
}
if (!empty($brand) && $brand !== '-') {
    $search_Query .= " AND sale_sac.BRAND = '" . $brand . "' ";
}
if (!empty($sku_cat) && $sku_cat !== '-') {
    $search_Query .= " AND sale_sac.SKU_CAT = '" . $sku_cat . "' ";
}
if (!empty($geographies) && $geographies !== '-') {
    $search_Query .= " AND province.geographies = '" . $geographies . "' ";
}

$order_by = " ORDER BY SALE_NAME, STR_TO_DATE(DI_DATE, '%d-%m-%Y') ";

// Query with join
$select_query_sale_sac = "
SELECT 
    sale_sac.*, 
    province.geographies 
FROM 
    ims_data_sale_sac_all sale_sac
LEFT JOIN 
    sac_sale_province province 
    ON sale_sac.TRD_PROVINCE = province.province 
WHERE 
    1 " . $search_Query . $order_by;

// Optional: Debug
/*
$txt = "sql = " . $select_query_sale_sac;
$my_file = fopen("exp_sale_param2.txt", "w") or die("Unable to open file!");
fwrite($my_file, $txt);
fclose($my_file);
*/

// Execute query
$query = $conn->prepare($select_query_sale_sac);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

// CSV output
$output = fopen('php://output', 'w');

$headers = [
    'ลำดับ', 'วัน', 'เดือน', 'ปี', 'รหัสลูกค้า', 'รหัสสินค้า', 'รายละเอียดสินค้า', 'ประเภท',
    'ยี่ห้อสินค้า', 'INV ลูกค้า', 'ชื่อลูกค้า', 'ผู้แทนขาย', 'Take', 'จำนวน',
    'ราคาขาย', 'ส่วนลด', 'มูลค่ารวม', 'ภาษี 7%', 'มูลค่ารวมภาษี', 'คะแนนต่อเส้น',
    'คะแนนที่ได้', 'ปี-เดือน', 'ของแถม /ยางแถม', 'อำเภอ', 'จังหวัด', 'เขตการขาย(ภาค)',
    'Mark', 'คะแนนต่อเส้น1', 'คะแนนที่ได้1', 'คะแนน Shop', 'คะแนนที่ได้ SHOP',
    'เทียบ/เว็ป', 'ร้านที่เป็น SHOP', 'สั่งซื้อจากแอฟมือถือ', 'ยางปีเก่า', 'ประเภทยาง'
];
fputcsv($output, $headers);

$line_no = 0;
foreach ($results as $result) {
    $line_no++;
    $amount_price = ($result->TRD_AMOUNT_PRICE !== '' && $result->TRD_AMOUNT_PRICE !== '-') ?
        (is_numeric($result->TRD_AMOUNT_PRICE) ? (float)$result->TRD_AMOUNT_PRICE : 0) : 0;

    $row = [
        $line_no,
        $result->DI_DAY,
        $result->DI_MONTH_NAME,
        $result->DI_YEAR,
        $result->AR_CODE,
        $result->SKU_CODE,
        $result->SKU_NAME,
        $result->DETAIL,
        $result->BRAND,
        $result->DI_REF,
        $result->AR_NAME,
        $result->SALE_NAME,
        $result->TAKE_NAME,
        $result->TRD_QTY,
        $result->TRD_PRC,
        $result->TRD_DISCOUNT,
        $result->TRD_TOTAL_PRICE,
        $result->TRD_VAT,
        $amount_price,
        $result->TRD_PER_POINT,
        $result->TRD_TOTAL_POINT,
        $result->WL_CODE,
        $result->TRD_Q_FREE,
        $result->TRD_AMPHUR,
        $result->TRD_PROVINCE,
        $result->geographies,
        $result->ZONE,
        $result->TRD_U_POINT,
        $result->TRD_R_POINT,
        $result->TRD_S_POINT,
        $result->TRD_T_POINT,
        $result->TRD_COMPARE,
        $result->TRD_SHOP,
        $result->TRD_BY_MOBAPP,
        $result->TRD_YEAR_OLD,
        $result->SKU_CAT
    ];

    fputcsv($output, $row);
}

fclose($output);
exit();
