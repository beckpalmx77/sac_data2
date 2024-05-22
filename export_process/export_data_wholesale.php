<?php
date_default_timezone_set('Asia/Bangkok');

$filename = "Data_Sale_Return-Daily-" . date('m/d/Y H:i:s', time()) . ".csv";

@header('Content-type: text/csv; charset=UTF-8');
@header('Content-Encoding: UTF-8');
@header("Content-Disposition: attachment; filename=" . $filename);

include('../config/connect_sqlserver.php');
include('../cond_file/doc_info_wholesale.php');

$table_filed_where = "DOCINFO.DI_DATE";

$doc_date_start = substr($_POST['doc_date_start'], 6, 4) . "/" . substr($_POST['doc_date_start'], 3, 2) . "/" . substr($_POST['doc_date_start'], 0, 2);
$doc_date_to = substr($_POST['doc_date_to'], 6, 4) . "/" . substr($_POST['doc_date_to'], 3, 2) . "/" . substr($_POST['doc_date_to'], 0, 2);

$String_Sql = $select_query_sale
    . $sql_cond_sale
    . " AND " . $table_filed_where . " BETWEEN '" . $doc_date_start . "' AND '" . $doc_date_to . "' "
    . $sql_order_sale;

//$my_file = fopen("wholesale_sql.txt", "w") or die("Unable to open file!");
//fwrite($my_file, $String_Sql);
//fclose($my_file);

$data = "DI_DATE,AR_NAME,ICCAT_CODE,ICCAT_NAME,SKU_NAME,SKU_E_NAME,BRN_NAME,TRD_B_AMT,SLMN_CODE,SLMN_NAME,\n";

$query = $conn_sqlsvr->prepare($String_Sql);
$query->execute();

while ($row = $query->fetch(PDO::FETCH_ASSOC)) {

    $data .= str_replace(",", "^", $row['DI_DATE']) . ",";
    $data .= str_replace(",", "^", $row['AR_NAME']) . ",";
    $data .= str_replace(",", "^", $row['ICCAT_CODE']) . ",";
    $data .= str_replace(",", "^", $row['ICCAT_NAME']) . ",";
    $data .= str_replace(",", "^", $row['SKU_NAME']) . ",";
    $data .= str_replace(",", "^", $row['SKU_E_NAME']) . ",";
    $data .= str_replace(",", "^", $row['BRN_NAME']) . ",";
    $data .= str_replace(",", "^", $row['TRD_B_AMT']) . ",";
    $data .= str_replace(",", "^", $row['SLMN_CODE']) . ",";
    $data .= str_replace(",", "^", $row['SLMN_NAME']) . ",";

    $data .= " " . "," . "\n";

}

$data = iconv("utf-8", "tis-620", $data);
echo $data;

exit();