<?php
include('../config/connect_db.php');

$filename = "sac_sale" . "-" . date('m/d/Y H:i:s', time()) . ".csv";

date_default_timezone_set('Asia/Bangkok');
@header('Content-type: text/csv; charset=UTF-8');
@header('Content-Encoding: UTF-8');
@header("Content-Disposition: attachment; filename=" . $filename);

$doc_date_start = $_POST["doc_date_start"];
$doc_date_to = $_POST["doc_date_to"];

$ar_name = $_POST['AR_NAME'];
$province = $_POST['TRD_PROVINCE'];
$name_name = $_POST['SALE_NAME'];
$sku_cat = $_POST['SKU_CAT'];

$search_Query = "";

if (!empty($doc_date_start) && !empty($doc_date_to)) {
    $search_Query .= " AND (sale_sac.DI_DATE BETWEEN '" . $doc_date_start . "' AND '" . $doc_date_to . "') ";
}

if (!empty($ar_name)) {
    $search_Query .= " AND sale_sac.AR_NAME = '" . $ar_name . "' ";
}

if (!empty($province)) {
    $search_Query .= " AND sale_sac.TRD_PROVINCE = '" . $province . "' ";
}

if (!empty($sku_cat)) {
    $search_Query .= " AND sale_sac.SKU_CAT = '" . $sku_cat . "' ";
}


// สร้างคำสั่ง SQL
$select_query_sale_sac = "SELECT * FROM ims_data_sale_sac_all sale_sac WHERE 1 " . $search_Query;

$line_no = 0;

$String_Sql = $select_query_sale_sac;

$data = "ลำดับ,วัน,เดือน,ปี,รหัสลูกค้า,รหัสสินค้า,รายละเอียดสินค้า,รายละเอียด,ยี่ห้อสินค้า,INV ลูกค้า,ชื่อลูกค้า,ผู้แทนขาย,Take,จำนวน,ราคาขาย,ส่วนลด,มูลค่ารวม,ภาษี 7%, มูลค่ารวมภาษี , คะแนนต่อเส้น , คะแนนที่ได้ ,ปี-เดือน,ของแถม /ยางแถม,อำเภอ,จังหวัด,Mark, คะแนนต่อเส้น1 , คะแนนที่ได้1 , คะแนน Shop , คะแนนที่ได้ SHOP ,เทียบ/เว็ป,ร้านที่เป็น SHOP,สั่งซื้อจากแอฟมือถือ,ยางปีเก่า,ประเภทยาง\n";

// Define the header for the CSV file
//$data = "Line No, DI_DAY, DI_MONTH_NAME, DI_YEAR, AR_CODE, SKU_CODE, SKU_NAME, DETAIL, BRAND, DI_REF, AR_NAME, SALE_NAME, TAKE_NAME, TRD_QTY, TRD_PRC, TRD_DISCOUNT, TRD_TOTAL_PRICE, TRD_VAT, TRD_AMOUNT_PRICE, TRD_PER_POINT, TRD_TOTAL_POINT, WL_CODE, TRD_Q_FREE, TRD_AMPHUR, TRD_PROVINCE, TRD_MARK, TRD_U_POINT, TRD_R_POINT, TRD_S_POINT, TRD_T_POINT, TRD_COMPARE, TRD_SHOP, TRD_BY_MOBAPP, TRD_YEAR_OLD, SKU_CAT\n";

$query = $conn->prepare($String_Sql);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);
if ($query->rowCount() >= 1) {
    foreach ($results as $result) {
        $line_no++;
        $data .= $line_no . ","; // Line number
        $data .= $result->DI_DAY . ","; // DI_DAY
        $data .= $result->DI_MONTH_NAME . ","; // DI_MONTH_NAME
        $data .= $result->DI_YEAR . ","; // DI_YEAR
        $data .= $result->AR_CODE . ","; // AR_CODE
        $data .= $result->SKU_CODE . ","; // SKU_CODE
        $data .= $result->SKU_NAME . ","; // SKU_NAME
        $data .= $result->DETAIL . ","; // DETAIL
        $data .= $result->BRAND . ","; // BRAND
        $data .= $result->DI_REF . ","; // DI_REF
        $data .= $result->AR_NAME . ","; // AR_NAME
        $data .= $result->SALE_NAME . ","; // SALE_NAME
        $data .= $result->TAKE_NAME . ","; // TAKE_NAME
        $data .= $result->TRD_QTY . ","; // TRD_QTY
        $data .= $result->TRD_PRC . ","; // TRD_PRC
        $data .= $result->TRD_DISCOUNT . ","; // TRD_DISCOUNT
        $data .= $result->TRD_TOTAL_PRICE . ","; // TRD_TOTAL_PRICE
        $data .= $result->TRD_VAT . ","; // TRD_VAT
        $data .= $result->TRD_AMOUNT_PRICE . ","; // TRD_AMOUNT_PRICE
        $data .= $result->TRD_PER_POINT . ","; // TRD_PER_POINT
        $data .= $result->TRD_TOTAL_POINT . ","; // TRD_TOTAL_POINT
        $data .= $result->WL_CODE . ","; // WL_CODE
        $data .= $result->TRD_Q_FREE . ","; // TRD_Q_FREE
        $data .= $result->TRD_AMPHUR . ","; // TRD_AMPHUR
        $data .= $result->TRD_PROVINCE . ","; // TRD_PROVINCE
        $data .= $result->TRD_MARK . ","; // TRD_MARK
        $data .= $result->TRD_U_POINT . ","; // TRD_U_POINT
        $data .= $result->TRD_R_POINT . ","; // TRD_R_POINT
        $data .= $result->TRD_S_POINT . ","; // TRD_S_POINT
        $data .= $result->TRD_T_POINT . ","; // TRD_T_POINT
        $data .= $result->TRD_COMPARE . ","; // TRD_COMPARE
        $data .= $result->TRD_SHOP . ","; // TRD_SHOP
        $data .= $result->TRD_BY_MOBAPP . ","; // TRD_BY_MOBAPP
        $data .= $result->TRD_YEAR_OLD . ","; // TRD_YEAR_OLD
        $data .= $result->SKU_CAT . "\n"; // SKU_CAT
    }

    $data = iconv("utf-8", "tis-620", $data);
    echo $data;
    exit();
}

