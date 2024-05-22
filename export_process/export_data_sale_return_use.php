<?php
date_default_timezone_set('Asia/Bangkok');

$filename = "Data_Sale_Return-" . date('m/d/Y H:i:s', time()) . ".csv";

@header('Content-type: text/csv; charset=UTF-8');
@header('Content-Encoding: UTF-8');
@header("Content-Disposition: attachment; filename=" . $filename);

include('../config/connect_sqlserver.php');
include('../cond_file/doc_info_credit_sale.php');
include('../cond_file/doc_info_return_product.php');

$doc_date_start = substr($_POST['doc_date_start'], 6, 4) . "/" . substr($_POST['doc_date_start'], 3, 2) . "/" . substr($_POST['doc_date_start'], 0, 2);
$doc_date_to = substr($_POST['doc_date_to'], 6, 4) . "/" . substr($_POST['doc_date_to'], 3, 2) . "/" . substr($_POST['doc_date_to'], 0, 2);

for ($loop = 1; $loop <= 2; $loop++) {

    if ($loop === 1) {

        $String_Sql = $select_query_sale . $sql_cond_sale . " AND DI_DATE BETWEEN '" . $doc_date_start . "' AND '" . $doc_date_to . "' "
            . $sql_order_sale;

        //$my_file = fopen("sac_str1.txt", "w") or die("Unable to open file!");
        //fwrite($my_file, $String_Sql);
        //fclose($my_file);

        $data = "DI_REF,DI_DATE,AR_CODE,AR_NAME,SLMN_CODE,SLMN_NAME,SKU_CODE,SKU_NAME,BRN_NAME,TRD_QTY,TRD_Q_FREE,TRD_U_PRC,TRD_G_KEYIN,TRD_G_SELL,TRD_G_VAT,TRD_B_AMT,WL_CODE,ARCD_NAME\n";

    } else {

        $String_Sql = $select_query_return . $sql_cond_return . " AND DI_DATE BETWEEN '" . $doc_date_start . "' AND '" . $doc_date_to . "' "
            . $sql_order_return;

        //$my_file = fopen("sac_str2.txt", "w") or die("Unable to open file!");
        //fwrite($my_file, $String_Sql);
        //fclose($my_file);

    }

    $query = $conn_sqlsvr->prepare($String_Sql);
    $query->execute();

    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {

        $data .= $row['DI_REF'] . ",";

        $data .= " " . $row['DI_DATE'] . ",";

        //$DI_DATE = str_replace("\\r\ ", "", $row['DI_DATE']);
        //$data .= $DI_DATE . ",";

        $data .= str_replace(",", "^", $row['AR_CODE']) . ",";
        $data .= str_replace(",", "^", $row['AR_NAME']) . ",";
        $data .= str_replace(",", "^", $row['SLMN_CODE']) . ",";
        $data .= str_replace(",", "^", $row['SLMN_NAME']) . ",";
        $data .= str_replace(",", "^", $row['SKU_CODE']) . ",";
        $data .= str_replace(",", "^", $row['SKU_NAME']) . ",";
        $data .= str_replace(",", "^", $row['BRN_NAME']) . ",";
        $data .= str_replace(",", "^", $row['BRN_CODE']) . ",";

        if ($loop === 2) {
            $TRD_QTY = "-" . $row['TRD_QTY'];
            $TRD_Q_FREE = "-" . $row['TRD_Q_FREE'];
            $TRD_U_PRC = "-" . $row['TRD_U_PRC'];
            $TRD_G_KEYIN = "-" . $row['TRD_G_KEYIN'];
            $TRD_G_SELL = "-" . $row['TRD_G_SELL'];
            $TRD_G_VAT = "-" . $row['TRD_G_VAT'];
            //$my_file = fopen("sac_str_sale.txt", "w") or die("Unable to open file!");
            //fwrite($my_file, "Loop " . $loop . " = " . $TRD_QTY . " | " . $TRD_Q_FREE . " | " . $TRD_U_PRC . " | "
                //. $TRD_G_KEYIN . " | " . $TRD_G_SELL . " | " . $TRD_G_VAT);
            //fclose($my_file);
        } else {
            $TRD_QTY = $row['TRD_QTY'];
            $TRD_Q_FREE = $row['TRD_Q_FREE'];
            $TRD_U_PRC = $row['TRD_U_PRC'];
            $TRD_G_KEYIN = $row['TRD_G_KEYIN'];
            $TRD_G_SELL = $row['TRD_G_SELL'];
            $TRD_G_VAT = $row['TRD_G_VAT'];
            $my_file = fopen("sac_str_return.txt", "w") or die("Unable to open file!");
            //fwrite($my_file, "Loop " . $loop . " = " . $TRD_QTY . " | " . $TRD_Q_FREE . " | " . $TRD_U_PRC . " | "
                //. $TRD_G_KEYIN . " | " . $TRD_G_SELL . " | " . $TRD_G_VAT);
            //fclose($my_file);
        }

        $data .= $TRD_QTY . ",";
        $data .= $TRD_Q_FREE . ",";
        $data .= $TRD_U_PRC . ",";
        $data .= $TRD_G_KEYIN . ",";
        $data .= $TRD_G_SELL . ",";
        $data .= $TRD_G_VAT . ",";

        //$data .= $row['TRD_QTY'] . ",";
        //$data .= $row['TRD_Q_FREE'] . ",";
        //$data .= $row['TRD_U_PRC'] . ",";
        //$data .= $row['TRD_G_KEYIN'] . ",";
        //$data .= $row['TRD_G_SELL'] . ",";
        //$data .= $row['TRD_G_VAT'] . ",";
        $data .= str_replace(",", "^", $row['WL_CODE']) . ",";
        $data .= str_replace(",", "^", $row['ARCD_NAME']) . "\n";

    }
}

$data = iconv("utf-8", "tis-620", $data);
echo $data;

exit();