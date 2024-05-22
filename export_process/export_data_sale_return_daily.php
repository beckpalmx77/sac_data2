<?php
date_default_timezone_set('Asia/Bangkok');

$filename = "Data_Sale_Return-Daily-" . date('m/d/Y H:i:s', time()) . ".csv";

@header('Content-type: text/csv; charset=UTF-8');
@header('Content-Encoding: UTF-8');
@header("Content-Disposition: attachment; filename=" . $filename);

include('../config/connect_sqlserver.php');
include('../cond_file/doc_info_sale_return_daily.php');

$DT_DOCCODE_MINUS = "IS";

$doc_date_start = substr($_POST['doc_date_start'], 6, 4) . "/" . substr($_POST['doc_date_start'], 3, 2) . "/" . substr($_POST['doc_date_start'], 0, 2);
$doc_date_to = substr($_POST['doc_date_to'], 6, 4) . "/" . substr($_POST['doc_date_to'], 3, 2) . "/" . substr($_POST['doc_date_to'], 0, 2);


$String_Sql = $select_query_daily . $select_query_daily_cond . " AND DI_DATE BETWEEN '" . $doc_date_start . "' AND '" . $doc_date_to . "' "
    . $select_query_daily_order;

//$my_file = fopen("D-sac_str1.txt", "w") or die("Unable to open file!");
//fwrite($my_file, $String_Sql);
//fclose($my_file);

$data = "DI_DATE,,,AR_CODE,SKU_CODE,SKU_NAME,BRN_NAME,BRN_CODE,DI_REF,AR_NAME,SLMN_NAME,,TRD_QTY,TRD_U_PRC,TRD_DSC_KEYINV,TRD_B_SELL,TRD_B_VAT,TRD_G_KEYIN,,,WL_CODE\n";

$query = $conn_sqlsvr->prepare($String_Sql);
$query->execute();

while ($row = $query->fetch(PDO::FETCH_ASSOC)) {

    $data .= " " . $row['DI_DATE'] . ",";
    $data .= " " . ",";
    $data .= " " . ",";

    //$DI_DATE = str_replace("\\r\ ", "", $row['DI_DATE']);
    //$data .= $DI_DATE . ",";

    $data .= str_replace(",", "^", $row['AR_CODE']) . ",";
    $data .= str_replace(",", "^", $row['SKU_CODE']) . ",";
    $data .= str_replace(",", "^", $row['SKU_NAME']) . ",";
    $data .= str_replace(",", "^", $row['BRN_NAME']) . ",";
    $data .= str_replace(",", "^", $row['BRN_CODE']) . ",";
    $data .= str_replace(",", "^", $row['DI_REF']) . ",";
    $data .= str_replace(",", "^", $row['AR_NAME']) . ",";
    $data .= str_replace(",", "^", $row['SLMN_CODE']) . ",";
    $data .= str_replace(",", "^", $row['SLMN_NAME']) . ",";


    $TRD_QTY = $row['TRD_Q_FREE'] > 0 ? $row['TRD_QTY'] = $row['TRD_QTY'] + $row['TRD_Q_FREE'] : $row['TRD_QTY'];


    $TRD_U_PRC = $row['TRD_U_PRC'];
    $TRD_DSC_KEYINV = $row['TRD_DSC_KEYINV'];
    $TRD_B_SELL = $row['TRD_G_SELL'];
    $TRD_B_VAT = $row['TRD_G_VAT'];
    $TRD_G_KEYIN = $row['TRD_G_KEYIN'];

    //$my_file = fopen("D-sac_str_return.txt", "w") or die("Unable to open file!");
    //fwrite($my_file, "Data " . " = " . $TRD_QTY . " | " . $TRD_U_PRC . " | "
        //. $TRD_DSC_KEYINV . " | " . $TRD_B_SELL . " | " . $TRD_B_VAT . " | " . $TRD_G_KEYIN);
    //fclose($my_file);



if(strpos($row['DT_DOCCODE'], $DT_DOCCODE_MINUS) !== false){
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


    $data .= " " . ",";
    $data .= " " . ",";

    $data .= str_replace(",", "^", $row['WL_CODE']) . "\n";


}

$data = iconv("utf-8", "tis-620", $data);
echo $data;

exit();