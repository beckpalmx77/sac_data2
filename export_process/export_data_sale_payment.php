<?php
date_default_timezone_set('Asia/Bangkok');

$filename = "Data_Sale_Return-Daily-" . date('m/d/Y H:i:s', time()) . ".csv";

@header('Content-type: text/csv; charset=UTF-8');
@header('Content-Encoding: UTF-8');
@header("Content-Disposition: attachment; filename=" . $filename);

include('../config/connect_sqlserver.php');
include('../cond_file/doc_sale_payment.php');

$table_filed_where  = "v_sale_payment.BUY_DATE";

$doc_date_start = substr($_POST['doc_date_start'], 6, 4) . "/" . substr($_POST['doc_date_start'], 3, 2) . "/" . substr($_POST['doc_date_start'], 0, 2);
$doc_date_to = substr($_POST['doc_date_to'], 6, 4) . "/" . substr($_POST['doc_date_to'], 3, 2) . "/" . substr($_POST['doc_date_to'], 0, 2);

$String_Sql = $select_query_sale . " WHERE " . $table_filed_where . " BETWEEN '" . $doc_date_start . "' AND '" . $doc_date_to . "' "
    . $select_query_daily_order;

//$my_file = fopen("D-sac_str1.txt", "w") or die("Unable to open file!");
//fwrite($my_file, $String_Sql);
//fclose($my_file);

$data = "BUY_DATE,AR_NAME,BUY_DATE,BUY_REF,BUY_AMOUNT,ARCD_TERM,DUE_DATE_REF\n";

$query = $conn_sqlsvr->prepare($String_Sql);
$query->execute();

while ($row = $query->fetch(PDO::FETCH_ASSOC)) {

    $data .= str_replace(",", "^", $row['DI_DATE']) . ",";
    $data .= str_replace(",", "^", $row['AR_NAME']) . ",";
    $data .= str_replace(",", "^", $row['BUY_DATE_REF']) . ",";
    $data .= str_replace(",", "^", $row['BUY_REF']) . ",";
    $data .= str_replace(",", "^", $row['BUY_AMOUNT']) . ",";
    $data .= str_replace(",", "^", $row['ARCD_TERM']) . ",";
    $data .= str_replace(",", "^", $row['DUE_DATE_REF']) . ",";
    $data .= str_replace(",", "^", $row['DUE_DATE_REF']) . ",";

    $data .= " " . "," . "\n";


}

$data = iconv("utf-8", "tis-620", $data);
echo $data;

exit();