<?php
date_default_timezone_set('Asia/Bangkok');

$filename = "Data_Sale_Return-Daily-Out-" . date('m/d/Y H:i:s', time()) . ".csv";

@header('Content-type: text/csv; charset=UTF-8');
@header('Content-Encoding: UTF-8');
@header("Content-Disposition: attachment; filename=" . $filename);

include('../config/connect_sqlserver.php');
include('../cond_file/doc_info_sale_return_daily_out.php');

$DT_DOCCODE_MINUS = "IS";

$doc_date_start = substr($_POST['doc_date_start'], 6, 4) . "/" . substr($_POST['doc_date_start'], 3, 2) . "/" . substr($_POST['doc_date_start'], 0, 2);
$doc_date_to = substr($_POST['doc_date_to'], 6, 4) . "/" . substr($_POST['doc_date_to'], 3, 2) . "/" . substr($_POST['doc_date_to'], 0, 2);


$String_Sql = $select_query_daily . $select_query_daily_cond . " AND DI_DATE BETWEEN '" . $doc_date_start . "' AND '" . $doc_date_to . "' AND TRD_UTQNAME LIKE 'เส้น' "
    . $select_query_daily_order;

/*
$my_file = fopen("D-sac_str1.txt", "w") or die("Unable to open file!");
fwrite($my_file, $String_Sql);
fclose($my_file);
*/

$data = "DI_REF,DI_DATE,AR_NAME,SKU_CODE,SKU_NAME,BRN_NAME,BRN_CODE,TRD_QTY,WL_CODE\n";

$query = $conn_sqlsvr->prepare($String_Sql);
$query->execute();

while ($row = $query->fetch(PDO::FETCH_ASSOC)) {

    $data .= str_replace(",", "^", $row['DI_REF']) . ",";
    $data .= $row['DI_DATE'] . ",";
    $data .= str_replace(",", "^", $row['AR_NAME']) . ",";
    $data .= str_replace(",", "^", $row['SKU_CODE']) . ",";
    $data .= str_replace(",", "^", $row['SKU_NAME']) . ",";
    $data .= str_replace(",", "^", $row['BRN_NAME']) . ",";
    $data .= str_replace(",", "^", $row['BRN_CODE']) . ",";
    $TRD_QTY = $row['TRD_Q_FREE'] > 0 ? $row['TRD_QTY'] = $row['TRD_QTY'] + $row['TRD_Q_FREE'] : $row['TRD_QTY'];
    $data .= $TRD_QTY . ",";
    $data .= str_replace(",", "^", $row['WL_CODE']) . "\n";

}

// $data = iconv("utf-8", "tis-620", $data);
$data = iconv("utf-8", "windows-874//IGNORE", $data);
echo $data;

exit();