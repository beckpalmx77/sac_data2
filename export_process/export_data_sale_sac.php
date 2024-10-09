<?php
include('../config/connect_db.php');
require '../vendor/autoload.php'; // Make sure this path is correct

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

date_default_timezone_set('Asia/Bangkok');
$filename = "sac_sale_" . date('Y-m-d_H-i-s') . ".xlsx";

// Set headers to prompt file download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

$doc_date_start = $_POST["doc_date_start"];
$doc_date_to = $_POST["doc_date_to"];
$ar_name = $_POST['AR_NAME'];
$amphur = $_POST['TRD_AMPHUR'];
$province = $_POST['TRD_PROVINCE'];
$sale_name = $_POST['SALE_NAME'];
$sku_cat = $_POST['SKU_CAT'];

$search_Query = "";

// แปลงจากรูปแบบ DD-MM-YYYY เป็น YYYY-MM-DD
$doc_date_start = DateTime::createFromFormat('d-m-Y', $doc_date_start)->format('Y-m-d');
$doc_date_to = DateTime::createFromFormat('d-m-Y', $doc_date_to)->format('Y-m-d');

if (!empty($doc_date_start) && !empty($doc_date_to)) {
    $search_Query .= " AND STR_TO_DATE(DI_DATE, '%d-%m-%Y') BETWEEN '" . $doc_date_start . "' AND '". $doc_date_to ."' " ;
}

if (!empty($ar_name) && $ar_name!=='-') {
    $search_Query .= " AND sale_sac.AR_NAME = '" . $ar_name . "' ";
}
if (!empty($amphur) && $amphur!=='-') {
    $search_Query .= " AND sale_sac.TRD_AMPHUR = '" . $amphur . "' ";
}
if (!empty($province) && $province!=='-') {
    $search_Query .= " AND sale_sac.TRD_PROVINCE = '" . $province . "' ";
}
if (!empty($sale_name) && $sale_name!=='-') {
    $search_Query .= " AND sale_sac.SALE_NAME = '" . $sale_name . "' ";
}
if (!empty($sku_cat) && $sku_cat!=='-') {
    $search_Query .= " AND sale_sac.SKU_CAT = '" . $sku_cat . "' ";
}

$order_by = "order by STR_TO_DATE(DI_DATE, '%d-%m-%Y') ";

// Create SQL query
$select_query_sale_sac = "SELECT * FROM ims_data_sale_sac_all sale_sac WHERE sale_sac.SALE_NAME NOT LIKE '%R%' " . $search_Query . $order_by;

/*
$txt = "sql = " . $select_query_sale_sac;
$my_file = fopen("exp_sale_param1.txt", "w") or die("Unable to open file!");
fwrite($my_file, $txt);
fclose($my_file);
*/

$query = $conn->prepare($select_query_sale_sac);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

// Create new Spreadsheet object
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Set the header for the Excel file
$headers = [
    'ลำดับ', 'วัน', 'เดือน', 'ปี', 'รหัสลูกค้า', 'รหัสสินค้า', 'รายละเอียดสินค้า', 'รายละเอียด',
    'ยี่ห้อสินค้า', 'INV ลูกค้า', 'ชื่อลูกค้า', 'ผู้แทนขาย', 'Take', 'จำนวน',
    'ราคาขาย', 'ส่วนลด', 'มูลค่ารวม', 'ภาษี 7%', 'มูลค่ารวมภาษี', 'คะแนนต่อเส้น',
    'คะแนนที่ได้', 'ปี-เดือน', 'ของแถม /ยางแถม', 'อำเภอ', 'จังหวัด',
    'Mark', 'คะแนนต่อเส้น1', 'คะแนนที่ได้1', 'คะแนน Shop', 'คะแนนที่ได้ SHOP',
    'เทียบ/เว็ป', 'ร้านที่เป็น SHOP', 'สั่งซื้อจากแอฟมือถือ', 'ยางปีเก่า', 'ประเภทยาง'
];

$sheet->fromArray($headers, NULL, 'A1');

// Fill the spreadsheet with data
$line_no = 0;
foreach ($results as $result) {
    $line_no++;
    // ตรวจสอบว่าช่อง TRD_AMOUNT_PRICE ไม่ใช่ช่องว่างหรือเครื่องหมาย "-"
    if ($result->TRD_AMOUNT_PRICE !== '' && $result->TRD_AMOUNT_PRICE !== '-') {
        // แปลงเป็นตัวเลข ถ้าเป็นไปได้
        $amount_price = is_numeric($result->TRD_AMOUNT_PRICE) ? (float)$result->TRD_AMOUNT_PRICE : 0;
    } else {
        // ถ้าเป็นช่องว่างหรือ "-"
        $amount_price = 0;
    }

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
        $result->TRD_MARK,
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
    $sheet->fromArray($row, NULL, 'A' . ($line_no + 1));
}

// Save Excel file to output
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
