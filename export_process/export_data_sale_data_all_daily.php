<?php
date_default_timezone_set('Asia/Bangkok');

ini_set('display_errors', 0);
error_reporting(E_ALL & ~E_DEPRECATED);

$filename = "Data_Sale_Free-Daily-" . date('m/d/Y H:i:s', time()) . ".csv";

@header('Content-type: text/csv; charset=UTF-8');
@header('Content-Encoding: UTF-8');
@header("Content-Disposition: attachment; filename=" . $filename);

include('../config/connect_sqlserver.php');
include('../config/connect_db.php');

include('../cond_file/doc_info_sale_return_daily.php');

include('../util/month_util_prefix.php');

$DT_DOCCODE_MINUS = "IS";

$doc_date_start = substr($_POST['doc_date_start'], 6, 4) . "/" . substr($_POST['doc_date_start'], 3, 2) . "/" . substr($_POST['doc_date_start'], 0, 2);
$doc_date_to = substr($_POST['doc_date_to'], 6, 4) . "/" . substr($_POST['doc_date_to'], 3, 2) . "/" . substr($_POST['doc_date_to'], 0, 2);


$String_Sql = $select_query_daily . $select_query_daily_cond . " AND DI_DATE BETWEEN '" . $doc_date_start . "' AND '" . $doc_date_to . "' "
    . $select_query_daily_order;

/*
$my_file = fopen("a_str1.txt", "w") or die("Unable to open file!");
fwrite($my_file, $String_Sql);
fclose($my_file);
*/

$data = "DI_DATE,MONTH,YEAR,AR_CODE,SKU_CODE,SKU_NAME,ICCAT_CODE,ICCAT_NAME,BRN_NAME,BRN_CODE,DI_REF,AR_NAME,SLMN_CODE,SLMN_NAME,TRD_QTY,TRD_U_PRC,TRD_DSC_KEYINV,TRD_B_SELL,TRD_B_VAT,TRD_G_KEYIN,AMPHUR,PROVINCE,ZONE,WL_CODE,TRD_Q_FREE\n";

$query = $conn_sqlsvr->prepare($String_Sql);
$query->execute();

while ($row = $query->fetch(PDO::FETCH_ASSOC)) {


    //$data .= " " . $row['DI_DATE'] . ",";

    $date_parts = explode('/', $row['DI_DATE'] ?? '');
    $DAY = $date_parts[0] ?? '';
    $MONTH = isset($date_parts[1]) && isset($month_arr[$date_parts[1]]) ? $month_arr[$date_parts[1]] : '';
    $YEAR = $date_parts[2] ?? '';

    $data .= " " . $DAY . ",";
    $data .= " " . $MONTH . ",";
    $data .= " " . $YEAR . ",";

    //$data .= " " . ",";
    //$data .= " " . ",";

    //$DI_DATE = str_replace("\\r\ ", "", $row['DI_DATE']);
    //$data .= $DI_DATE . ",";

    $ar_code_clean = $row['AR_CODE'] ?? '';
    $str_sql_addr = "SELECT * FROM ims_customer_ar WHERE customer_id = '" . $ar_code_clean . "'";
    $query_addr = $conn->prepare($str_sql_addr);
    $query_addr->execute();

    $result_addr = $query_addr->fetch(PDO::FETCH_ASSOC);

    if ($result_addr) {
        $amphur = $result_addr['AMPHUR_CUST'] ?? '-';
        $province = $result_addr['PROVINCE_CUST'] ?? '-';
        $zone = $result_addr['ZONE_CUST'] ?? '-';
    } else {
        $amphur = "-";
        $province = "-";
        $zone = "-";
    }


    $data .= str_replace(",", "^", $row['AR_CODE'] ?? '') . ",";
    $data .= str_replace(",", "^", $row['SKU_CODE'] ?? '') . ",";
    $data .= str_replace(",", "^", $row['SKU_NAME'] ?? '') . ",";
    $data .= str_replace(",", "^", $row['ICCAT_CODE'] ?? '') . ",";
    $data .= str_replace(",", "^", $row['ICCAT_NAME'] ?? '') . ",";
    $data .= str_replace(",", "^", $row['BRN_NAME'] ?? '') . ",";
    $data .= str_replace(",", "^", $row['BRN_CODE'] ?? '') . ",";
    $data .= str_replace(",", "^", $row['DI_REF'] ?? '') . ",";
    $data .= str_replace(",", "^", $row['AR_NAME'] ?? '') . ",";
    $data .= str_replace(",", "^", $row['SLMN_CODE'] ?? '') . ",";
    $data .= str_replace(",", "^", $row['SLMN_NAME'] ?? '') . ",";


    $TRD_QTY = $row['TRD_QTY'] ?? 0;
    $TRD_Q_FREE = $row['TRD_Q_FREE'] ?? 0;

    $TRD_U_PRC = $row['TRD_U_PRC'] ?? 0;
    $TRD_DSC_KEYINV = $row['TRD_DSC_KEYINV'] ?? 0;
    $TRD_B_SELL = $row['TRD_G_SELL'] ?? 0;
    $TRD_B_VAT = $row['TRD_G_VAT'] ?? 0;
    $TRD_G_KEYIN = $row['TRD_G_KEYIN'] ?? 0;

    //$my_file = fopen("D-sac_str_return.txt", "w") or die("Unable to open file!");
    //fwrite($my_file, "Data " . " = " . $TRD_QTY . " | " . $TRD_U_PRC . " | "
    //. $TRD_DSC_KEYINV . " | " . $TRD_B_SELL . " | " . $TRD_B_VAT . " | " . $TRD_G_KEYIN);
    //fclose($my_file);


    if (strpos($row['DT_DOCCODE'] ?? '', $DT_DOCCODE_MINUS) !== false) {
        $data .= "-" . $TRD_QTY . ",";
        $data .= "-" . $TRD_U_PRC . ",";
        $data .= "-" . $TRD_DSC_KEYINV . ",";
        $data .= "-" . $TRD_B_SELL . ",";
        $data .= "-" . $TRD_B_VAT . ",";
        $data .= "-" . $TRD_G_KEYIN . ",";
    } else {
        $data .= $TRD_QTY . ",";
        $data .= $TRD_U_PRC . ",";
        $data .= $TRD_DSC_KEYINV . ",";
        $data .= $TRD_B_SELL . ",";
        $data .= $TRD_B_VAT . ",";
        $data .= $TRD_G_KEYIN . ",";
    }
    /*
        $data .= $TRD_QTY . ",";
        $data .= $TRD_U_PRC . ",";
        $data .= $TRD_DSC_KEYINV . ",";
        $data .= $TRD_B_SELL . ",";
        $data .= $TRD_B_VAT . ",";
        $data .= $TRD_G_KEYIN . ",";

    */

    $data .= $amphur . ",";
    $data .= $province . ",";
    $data .= $zone . ",";
    $data .= str_replace(",", "^", $row['WL_CODE'] ?? '') . ",";
    $data .= $TRD_Q_FREE . "\n";


}

// $data = iconv("utf-8", "tis-620", $data);
$data = iconv("utf-8", "windows-874//IGNORE", $data);
echo $data;

exit();