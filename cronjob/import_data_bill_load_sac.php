<?php

ini_set('display_errors', 1);
error_reporting(~0);

include("../config/connect_sqlserver.php");
include("../config/connect_db.php");
include('../util/month_util.php');

$sql_query_data = " SELECT 
 DOCINFO.DI_REF,
 DOCINFO.DI_DATE,
 DOCINFO.DI_ACTIVE,
 ARFILE.AR_CODE,
 ARFILE.AR_NAME,
 TRANPAYH.TPH_LSTATUS,
 TRANPAYA.TPA_REFER_REF,
 TRANPAYA.TPA_REFER_DATE,
 TRANPAYA.TPA_REFER_DI,
 ARDETAIL.ARD_LASTPM_DI,
 TRANPAYA.TPA_B4_A_SNV,
 TRANPAYA.TPA_B4_A_SV,
 TRANPAYA.TPA_B4_A_VAT,
 TRANPAYA.TPA_B4_A_AMT,
 TRANPAYA.TPA_B4_P_AMT,
 TRANPAYA.TPA_B4_Q_AMT,
 TRANPAYA.TPA_WHTX_R,
 TRANPAYA.TPA_DISC_A,
 TRANPAYA.TPA_NOTE_A,
 TRANPAYA.TPA_CASH_A,
 TRANPAYA.TPA_CHEQ_A,
 TRANPAYA.TPA_WHTX_A,
 TRANPAYA.TPA_TRFR_A,
 TRANPAYA.TPA_OTHR_A,
 TRANPAYA.TPA_SHRT_A,
 TRANPAYA.TPA_GROUP,
 TRANPAYA.TPA_VAT_DATE,
 TRANPAYA.TPA_VAT_REF,
 ARDETAIL.ARD_DUE_DA,
 ARDETAIL.ARD_BIL_DA,
 ARDETAIL.ARD_G_SNV,
 ARDETAIL.ARD_G_SV,
 ARDETAIL.ARD_G_VAT,
 ARDETAIL.ARD_G_KEYIN,
 ARDETAIL.ARD_TDSC_KEYIN,
 ARDETAIL.ARD_TDSC_KEYINV,
 ARDETAIL.ARD_N_SNV,
 ARDETAIL.ARD_N_SV,
 ARDETAIL.ARD_N_VAT,
 ARDETAIL.ARD_N_AMT,
 ARDETAIL.ARD_XCHG,
 ARDETAIL.ARD_A_SNV,
 ARDETAIL.ARD_A_SV,
 ARDETAIL.ARD_A_VAT,
 ARDETAIL.ARD_A_AMT,
 ARDETAIL.ARD_P_AMT,
 ARDETAIL.ARD_Q_AMT,
 ARDETAIL.ARD_BILL_DI
 
FROM
 DOCINFO 
 JOIN DOCTYPE ON DI_DT = DT_KEY
 JOIN TRANPAYH ON DI_KEY = TPH_DI
 JOIN TRANPAYA ON TPH_KEY = TPA_TPH
 JOIN ARDETAIL ON TPA_REFER_ARPD = ARD_KEY
 JOIN ARFILE ON TPH_AR = AR_KEY
 JOIN ARCAT ON AR_ARCAT = ARCAT_KEY

WHERE
 (DOCTYPE.DT_PROPERTIES = '406') AND
 (DOCINFO.DI_ACTIVE = 0) ";

$order_by = "
ORDER BY DOCINFO.DI_DATE ASC, DOCINFO.DI_REF ASC ";


echo "Today is " . date("Y/m/d");
echo "\n\r" . date("Y/m/d", strtotime("yesterday"));

//$select_query_daily_cond = " AND DOCINFO.DI_DATE BETWEEN '2023/01/01' AND '" . date("Y/m/d") . "'";

$select_query_daily_cond = " AND DOCINFO.DI_DATE BETWEEN '" . date("Y/m/d", strtotime("yesterday")) . "' AND '" . date("Y/m/d") . "'";

$sql_sqlsvr = $sql_query_data . $select_query_daily_cond . $order_by ;

/*
$myfile = fopen("qry_file_mssql_server.txt", "w") or die("Unable to open file!");
fwrite($myfile, $sql_sqlsvr);
fclose($myfile);
*/

/*
 select * from ims_product_sale_sac
    order by
        STR_TO_DATE(DI_DATE, '%m/%d/%Y') desc
 */

$insert_data = "";
$update_data = "";

$res = "";

$stmt_sqlsvr = $conn_sqlsvr->prepare($sql_sqlsvr);
$stmt_sqlsvr->execute();

$return_arr = array();

while ($result_sqlsvr = $stmt_sqlsvr->fetch(PDO::FETCH_ASSOC)) {

    $sql_find = "SELECT * FROM ims_document_bill_load "
        . " WHERE DI_REF = '" . $result_sqlsvr["DI_REF"]  . "'";

    //echo $sql_find . "\n\r";

    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {

        $sql_update = " UPDATE ims_document_bill_load  SET TPA_REFER_REF=:TPA_REFER_REF,TPA_REFER_DATE=:TPA_REFER_DATE,DI_ACTIVE=:DI_ACTIVE,
        ARD_A_SV=:ARD_A_SV,ARD_A_VAT=:ARD_A_VAT,ARD_A_AMT=:ARD_A_AMT   
        WHERE DI_REF  = :DI_REF ";

        $query = $conn->prepare($sql_update);
        $query->bindParam(':TPA_REFER_REF', $result_sqlsvr["TPA_REFER_REF"], PDO::PARAM_STR);
        $query->bindParam(':TPA_REFER_DATE', $result_sqlsvr["TPA_REFER_DATE"], PDO::PARAM_STR);
        $query->bindParam(':DI_ACTIVE', $result_sqlsvr["DI_ACTIVE"], PDO::PARAM_STR);
        $query->bindParam(':ARD_A_SV', $result_sqlsvr["ARD_A_SV"], PDO::PARAM_STR);
        $query->bindParam(':ARD_A_VAT', $result_sqlsvr["ARD_A_VAT"], PDO::PARAM_STR);
        $query->bindParam(':ARD_A_AMT', $result_sqlsvr["ARD_A_AMT"], PDO::PARAM_STR);
        $query->bindParam(':DI_REF', $result_sqlsvr["DI_REF"], PDO::PARAM_STR);
        $query->execute();

        $update_data = $result_sqlsvr["DI_DATE"] . " : " . $result_sqlsvr["DI_REF"] . " | " . $result_sqlsvr["AR_NAME"] . " | " .  $result_sqlsvr["DI_ACTIVE"] . "\n\r";

        echo "UPDATE DATA " . $update_data;

        //$myfile = fopen("update_chk.txt", "w") or die("Unable to open file!");
        //fwrite($myfile, $update_data);
        //fclose($myfile);

    } else {

        $sql = " INSERT INTO ims_document_bill_load (DI_REF,DI_DATE,TPA_REFER_REF,TPA_REFER_DATE,AR_CODE,AR_NAME
                 ,ARD_BIL_DA,ARD_DUE_DA,ARD_A_SV,ARD_A_VAT,ARD_A_AMT,DI_ACTIVE)
                 VALUES (:DI_REF,:DI_DATE,:TPA_REFER_REF,:TPA_REFER_DATE,:AR_CODE,:AR_NAME
                 ,:ARD_BIL_DA,:ARD_DUE_DA,:ARD_A_SV,:ARD_A_VAT,:ARD_A_AMT,:DI_ACTIVE) ";
        $query = $conn->prepare($sql);
        $query->bindParam(':DI_REF', $result_sqlsvr["DI_REF"], PDO::PARAM_STR);
        $query->bindParam(':DI_DATE', $result_sqlsvr["DI_DATE"], PDO::PARAM_STR);
        $query->bindParam(':TPA_REFER_REF', $result_sqlsvr["TPA_REFER_REF"], PDO::PARAM_STR);
        $query->bindParam(':TPA_REFER_DATE', $result_sqlsvr["TPA_REFER_DATE"], PDO::PARAM_STR);
        $query->bindParam(':AR_CODE', $result_sqlsvr["AR_CODE"], PDO::PARAM_STR);
        $query->bindParam(':AR_NAME', $result_sqlsvr["AR_NAME"], PDO::PARAM_STR);
        $query->bindParam(':ARD_BIL_DA', $result_sqlsvr["ARD_BIL_DA"], PDO::PARAM_STR);
        $query->bindParam(':ARD_DUE_DA', $result_sqlsvr["ARD_DUE_DA"], PDO::PARAM_STR);
        $query->bindParam(':ARD_A_SV', $result_sqlsvr["ARD_A_SV"], PDO::PARAM_STR);
        $query->bindParam(':ARD_A_VAT', $result_sqlsvr["ARD_A_VAT"], PDO::PARAM_STR);
        $query->bindParam(':ARD_A_AMT', $result_sqlsvr["ARD_A_AMT"], PDO::PARAM_STR);
        $query->bindParam(':DI_ACTIVE', $result_sqlsvr["DI_ACTIVE"], PDO::PARAM_STR);
        $query->execute();
        $lastInsertId = $conn->lastInsertId();
        if ($lastInsertId) {
            $insert_data = $result_sqlsvr["DI_DATE"] . " : " . $result_sqlsvr["DI_REF"] . " | " . $result_sqlsvr["AR_NAME"] . " | " .  $result_sqlsvr["DI_ACTIVE"] . "\n\r";
            echo "INSERT DATA " . $insert_data;
        } else {
            echo " Error ";
        }


    }

}

$conn_sqlsvr = null;


