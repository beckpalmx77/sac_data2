<?php
date_default_timezone_set('Asia/Bangkok');

$filename = "Data_Product_Price-" . date('m/d/Y H:i:s', time()) . ".csv";

@header('Content-type: text/csv; charset=UTF-8');
@header('Content-Encoding: UTF-8');
@header("Content-Disposition: attachment; filename=" . $filename);

include('../config/connect_sqlserver.php');
include('../cond_file/query-product-price-main.php');

$price_code = $_POST['price_code'];
$iccat_codes = $_POST['iccat_code'];

switch ($price_code) {
    case "SAC":
        $sql_cond_ext = " AND (ARPRB_CODE not like 'BTC%' AND ARPRB_CODE not like 'CP%') ";
        break;
    case "BTC":
        $sql_cond_ext = " AND (ARPRB_CODE like 'BTC%') ";
        break;
    case "COCKPIT":
        $sql_cond_ext = " AND (ARPRB_CODE like 'CP%') ";
        break;
    default:
        $sql_cond_ext = "";
        break;
}

$sql_iccat_cond = "";
if (!empty($iccat_codes)) {
    $iccat_in = "'" . implode("','", $iccat_codes) . "'";
    $sql_iccat_cond = " AND ICCAT.ICCAT_CODE IN ($iccat_in) ";
} else {
    $sql_iccat_cond = " AND 1=1 ";
}


$String_Sql = $select_query . $sql_cond . $sql_cond_ext . $sql_iccat_cond . $sql_order;

//$my_file = fopen("D-sac_str1.txt", "w") or die("Unable to open file!");
//fwrite($my_file, $String_Sql);
//fclose($my_file);

$data = "No.,SKU_CODE,SKU_NAME,UTQ_NAME,ARPLU_U_PRC,ARPRB_CODE,ARPRB_NAME\n";

$query = $conn_sqlsvr->prepare($String_Sql);
$query->execute();

$loop = 0;

while ($row = $query->fetch(PDO::FETCH_ASSOC)) {

    $loop++;

    $data .= $loop . ",";

    //$DI_DATE = str_replace("\\r\ ", "", $row['DI_DATE']);
    //$data .= $DI_DATE . ",";

    $data .= str_replace(",", "^", $row['SKU_CODE']) . ",";
    //$data .= str_replace(",", "^", "'". $row['SKU_CODE']) . ",";
    $data .= str_replace(",", "^", $row['SKU_NAME']) . ",";
    $data .= str_replace(",", "^", $row['UTQ_NAME']) . ",";
    $data .= str_replace(",", "^", $row['ARPLU_U_PRC']) . ",";
    $data .= str_replace(",", "^", $row['ARPRB_CODE']) . ",";

    //$my_file = fopen("D-sac_str_return.txt", "w") or die("Unable to open file!");
    //fwrite($my_file, "Data " . " = " . $TRD_QTY . " | " . $TRD_U_PRC . " | "
    //. $TRD_DSC_KEYINV . " | " . $TRD_B_SELL . " | " . $TRD_B_VAT . " | " . $TRD_G_KEYIN);
    //fclose($my_file);

    $data .= str_replace(",", "^", $row['ARPRB_NAME']) . "\n";


}

// $data = iconv("utf-8", "tis-620", $data);
$data = iconv("utf-8", "windows-874//IGNORE", $data);
echo $data;

exit();