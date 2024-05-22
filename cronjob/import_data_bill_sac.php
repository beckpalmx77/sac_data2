<?php

ini_set('display_errors', 1);
error_reporting(~0);

include("../config/connect_sqlserver.php");
include("../config/connect_db.php");
include('../util/month_util.php');

$sql_query_data = " SELECT DOCTYPE.DT_DOCCODE,DOCTYPE.DT_THAIDESC,DOCINFO.DI_REF,DOCINFO.DI_DATE,DOCINFO.DI_AMOUNT,ARFILE.AR_CODE,ARFILE.AR_NAME
,ARDETAIL.ARD_BIL_DA,ARDETAIL.ARD_DUE_DA,ARDETAIL.ARD_CHQ_DA,ARFILE.AR_SLMNCODE,SALESMAN.SLMN_NAME,ARFILE.AR_REMARK ,DOCINFO.DI_REMARK,DOCINFO.DI_ACTIVE 
FROM DOCINFO 
LEFT JOIN ARDETAIL ON DOCINFO.DI_KEY = ARDETAIL.ARD_DI
LEFT JOIN ARFILE ON ARDETAIL.ARD_AR = ARFILE.AR_KEY 
LEFT JOIN SALESMAN ON SALESMAN.SLMN_CODE = ARFILE.AR_SLMNCODE
LEFT JOIN DOCTYPE ON DOCTYPE.DT_KEY = DOCINFO.DI_DT ";


$str_doc1 = array("DS","DS1","IV01","DS02","2","30","DS4","DDS5","IV3","/SAC","S.1","S.2","S.3","S.4","S.5","S.6");

echo "Today is " . date("Y/m/d");
echo "\n\r" . date("Y/m/d", strtotime("yesterday"));

$query_daily_cond_doc_type = " WHERE (DOCTYPE.DT_DOCCODE in ('DS','DS1','IV01','DS02','30','DS4','DDS5','IV3','/SAC','S.1','S.2','S.3','S.4','S.5','S.6')) ";

//$select_query_daily_cond = " AND DOCINFO.DI_DATE BETWEEN '2023/01/01' AND '" . date("Y/m/d") . "'";

$select_query_daily_cond = " AND DOCINFO.DI_DATE BETWEEN '" . date("Y/m/d", strtotime("yesterday")) . "' AND '" . date("Y/m/d") . "'";

$sql_sqlsvr = $sql_query_data . $query_daily_cond_doc_type . $select_query_daily_cond . " ORDER BY DOCINFO.DI_DATE , DOCINFO.DI_REF ";

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

    $sql_find = "SELECT * FROM ims_document_bill "
        . " WHERE DI_REF = '" . $result_sqlsvr["DI_REF"]  . "'";

    //echo $sql_find . "\n\r";

    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {

        $sql_update = " UPDATE ims_document_bill  SET DI_AMOUNT=:DI_AMOUNT,DI_ACTIVE=:DI_ACTIVE,AR_SLMNCODE=:AR_SLMNCODE,SLMN_NAME=:SLMN_NAME                 
        WHERE DI_REF  = :DI_REF ";

        $query = $conn->prepare($sql_update);
        $query->bindParam(':DI_AMOUNT', $result_sqlsvr["DI_AMOUNT"], PDO::PARAM_STR);
        $query->bindParam(':DI_ACTIVE', $result_sqlsvr["DI_ACTIVE"], PDO::PARAM_STR);
        $query->bindParam(':AR_SLMNCODE', $result_sqlsvr["AR_SLMNCODE"], PDO::PARAM_STR);
        $query->bindParam(':SLMN_NAME', $result_sqlsvr["SLMN_NAME"], PDO::PARAM_STR);
        $query->bindParam(':DI_REF', $result_sqlsvr["DI_REF"], PDO::PARAM_STR);
        $query->execute();

        $update_data = $result_sqlsvr["DI_DATE"] . " : " . $result_sqlsvr["DI_REF"] . " | " . $result_sqlsvr["DI_AMOUNT"] . " | " . $result_sqlsvr["SLMN_NAME"] . " | " . $result_sqlsvr["DI_ACTIVE"] . "\n\r";

        echo "UPDATE DATA " . $update_data;

        //$myfile = fopen("update_chk.txt", "w") or die("Unable to open file!");
        //fwrite($myfile, $update_data);
        //fclose($myfile);

    } else {

        $sql = " INSERT INTO ims_document_bill (DT_DOCCODE,DT_THAIDESC,DI_REF,DI_DATE,DI_AMOUNT,AR_CODE,AR_NAME
                 ,ARD_BIL_DA,ARD_DUE_DA,ARD_CHQ_DA,AR_SLMNCODE,SLMN_NAME,AR_REMARK ,DI_REMARK,DI_ACTIVE)
                 VALUES (:DT_DOCCODE,:DT_THAIDESC,:DI_REF,:DI_DATE,:DI_AMOUNT,:AR_CODE,:AR_NAME
                 ,:ARD_BIL_DA,:ARD_DUE_DA,:ARD_CHQ_DA,:AR_SLMNCODE,:SLMN_NAME,:AR_REMARK ,:DI_REMARK,:DI_ACTIVE) ";
        $query = $conn->prepare($sql);
        $query->bindParam(':DT_DOCCODE', $result_sqlsvr["DT_DOCCODE"], PDO::PARAM_STR);
        $query->bindParam(':DT_THAIDESC', $result_sqlsvr["DT_THAIDESC"], PDO::PARAM_STR);
        $query->bindParam(':DI_REF', $result_sqlsvr["DI_REF"], PDO::PARAM_STR);
        $query->bindParam(':DI_DATE', $result_sqlsvr["DI_DATE"], PDO::PARAM_STR);
        $query->bindParam(':DI_AMOUNT', $result_sqlsvr["DI_AMOUNT"], PDO::PARAM_STR);
        $query->bindParam(':AR_CODE', $result_sqlsvr["AR_CODE"], PDO::PARAM_STR);
        $query->bindParam(':AR_NAME', $result_sqlsvr["AR_NAME"], PDO::PARAM_STR);
        $query->bindParam(':ARD_BIL_DA', $result_sqlsvr["ARD_BIL_DA"], PDO::PARAM_STR);
        $query->bindParam(':ARD_DUE_DA', $result_sqlsvr["ARD_DUE_DA"], PDO::PARAM_STR);
        $query->bindParam(':ARD_CHQ_DA', $result_sqlsvr["ARD_CHQ_DA"], PDO::PARAM_STR);
        $query->bindParam(':AR_SLMNCODE', $result_sqlsvr["AR_SLMNCODE"], PDO::PARAM_STR);
        $query->bindParam(':SLMN_NAME', $result_sqlsvr["SLMN_NAME"], PDO::PARAM_STR);
        $query->bindParam(':AR_REMARK', $result_sqlsvr["AR_REMARK"], PDO::PARAM_STR);
        $query->bindParam(':DI_REMARK', $result_sqlsvr["DI_REMARK"], PDO::PARAM_STR);
        $query->bindParam(':DI_ACTIVE', $result_sqlsvr["DI_ACTIVE"], PDO::PARAM_STR);
        $query->execute();
        $lastInsertId = $conn->lastInsertId();
        if ($lastInsertId) {
            $insert_data = $result_sqlsvr["DI_DATE"] . " : " . $result_sqlsvr["DI_REF"] . " | " . $result_sqlsvr["DI_AMOUNT"] . " | " . $result_sqlsvr["SLMN_NAME"]  . " | " . $result_sqlsvr["DI_ACTIVE"] . "\n\r";
            echo "INSERT DATA " . $insert_data;
        } else {
            echo " Error ";
        }


    }

}

$conn_sqlsvr = null;

