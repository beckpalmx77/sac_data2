<?php
date_default_timezone_set('Asia/Bangkok');

$branch = $_POST["branch"];

$filename = $branch . "-" . "Data_Sale_Daily-" . date('m/d/Y H:i:s', time()) . ".csv";

@header('Content-type: text/csv; charset=UTF-8');
@header('Content-Encoding: UTF-8');
@header("Content-Disposition: attachment; filename=" . $filename);

include('../config/connect_sqlserver.php');
include('../cond_file/doc_info_sale_daily_cp.php');

switch ($branch) {
    case "CP-340":
        $query_daily_cond_ext = " AND (DOCTYPE.DT_DOCCODE in ('30','CS4','CS5','DS4','IS3','IS4','ISC3','ISC4')) ";
        break;
    case "CP-BY":
        $query_daily_cond_ext = " AND (DOCTYPE.DT_DOCCODE in ('CS.8','CS.9','IC.3','IC.4','IS.3','IS.4','S.5','S.6')) ";
        break;
    case "CP-RP":
        $query_daily_cond_ext = " AND (DOCTYPE.DT_DOCCODE in ('CS.6','CS.7','IC.1','IC.2','IS.1','IS.2','S.1','S.2')) ";
        break;
    case "CP-BB":
        $query_daily_cond_ext = " AND (DOCTYPE.DT_DOCCODE in ('CS.2','CS.3','IC.5','IC.6','IS.5','IS.6','S.3','S.4')) ";
        break;
    case "ALL":
        $query_daily_cond_ext = " AND (DOCTYPE.DT_DOCCODE in ('30','CS4','CS5','DS4','IS3','IS4','ISC3','ISC4',
        'CS.8','CS.9','IC.3','IC.4','IS.3','IS.4','S.5','S.6',
        'CS.6','CS.7','IC.1','IC.2','IS.1','IS.2','S.1','S.2',
        'CS.2','CS.3','IC.5','IC.6','IS.5','IS.6','S.3','S.4')) ";
        break;
}

$doc_date_start = substr($_POST['doc_date_start'], 6, 4) . "/" . substr($_POST['doc_date_start'], 3, 2) . "/" . substr($_POST['doc_date_start'], 0, 2);
$doc_date_to = substr($_POST['doc_date_to'], 6, 4) . "/" . substr($_POST['doc_date_to'], 3, 2) . "/" . substr($_POST['doc_date_to'], 0, 2);

$month_arr=array(
    "01"=>"มกราคม",
    "02"=>"กุมภาพันธ์",
    "03"=>"มีนาคม",
    "04"=>"เมษายน",
    "05"=>"พฤษภาคม",
    "06"=>"มิถุนายน",
    "07"=>"กรกฎาคม",
    "08"=>"สิงหาคม",
    "09"=>"กันยายน",
    "10"=>"ตุลาคม",
    "11"=>"พฤศจิกายน",
    "12"=>"ธันวาคม"
);

$month = substr($_POST['doc_date_start'], 3, 2);
$month_name = $month_arr[$month];

$year = substr($_POST['doc_date_to'], 6, 4);

$String_Sql = $select_query_daily . $select_query_daily_cond . " AND DI_DATE BETWEEN '" . $doc_date_start . "' AND '" . $doc_date_to . "' "
    . $query_daily_cond_ext
    . $select_query_daily_order;

//$my_file = fopen("D-CP.txt", "w") or die("Unable to open file!");
//fwrite($my_file, $String_Sql);
//fclose($my_file);

$data = "วันที่,เดือน,ปี,รหัสลูกค้า,รหัสสินค้า,รายละเอียดสินค้า,รายละเอียด,ยี่ห้อ,INV ลูกค้า,ชื่อลูกค้า,ผู้แทนขาย,จำนวน,ราคาขาย,ส่วนลดรวม,ส่วนลดต่อเส้น,มูลค่ารวม,ภาษี 7%,มูลค่ารวมภาษี,คลัง\n";

$query = $conn_sqlsvr->prepare($String_Sql);
$query->execute();

while ($row = $query->fetch(PDO::FETCH_ASSOC)) {

    $data .= " " . $row['DI_DATE'] . ",";
    $data .= " " . $month_name. ",";
    $data .= " " . $year . ",";

    $data .= str_replace(",", "^", $row['AR_CODE']) . ",";
    $data .= str_replace(",", "^", $row['SKU_CODE']) . ",";
    $data .= str_replace(",", "^", $row['SKU_NAME']) . ",";

    $data .= str_replace(",", "^", $row['ICCAT_NAME']) . ",";
    //$data .= " " . ",";
    $data .= str_replace(",", "^", $row['BRN_NAME']) . ",";
    $data .= str_replace(",", "^", $row['DI_REF']) . ",";
    $data .= str_replace(",", "^", $row['AR_NAME']) . ",";
    $data .= str_replace(",", "^", $row['SLMN_CODE']) . ",";


    //$TRD_QTY = $row['TRD_Q_FREE'] > 0 ? $row['TRD_QTY'] = $row['TRD_QTY'] + $row['TRD_Q_FREE'] : $row['TRD_QTY'];

    $TRD_QTY = $row['TRD_QTY'];
    $TRD_U_PRC = $row['TRD_U_PRC'];
    $TRD_DSC_KEYINV = $row['TRD_DSC_KEYINV'];
    $TRD_B_SELL = $row['TRD_G_SELL'];
    $TRD_B_VAT = $row['TRD_G_VAT'];
    $TRD_G_KEYIN = $row['TRD_G_KEYIN'];


    //$my_file = fopen("D-sac_str_return.txt", "w") or die("Unable to open file!");
    //fwrite($my_file, "Data " . " = " . $TRD_QTY . " | " . $TRD_U_PRC . " | "
    //. $TRD_DSC_KEYINV . " | " . $TRD_B_SELL . " | " . $TRD_B_VAT . " | " . $TRD_G_KEYIN);
    //fclose($my_file);

    $data .= $TRD_QTY . ",";
    $data .= $TRD_U_PRC . ",";
    $data .= $TRD_DSC_KEYINV . ",";
    $data .= " " . ",";
    $data .= $TRD_B_SELL . ",";
    $data .= $TRD_B_VAT . ",";
    $data .= $TRD_G_KEYIN . ",";
    $data .= str_replace(",", "^", $row['WL_CODE']) . "\n";

}

$data = iconv("utf-8", "tis-620", $data);
echo $data;

exit();