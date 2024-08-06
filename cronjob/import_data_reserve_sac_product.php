<?php

ini_set('display_errors', 1);
error_reporting(~0);

include ("../config/connect_sqlserver.php");
include ("../config/connect_db.php");
include('../cond_file/query_reserve_sac.php');

$query_year = " AND DI_DATE BETWEEN '" . date("Y/m/d", strtotime("yesterday")) . "' AND '" . date("Y/m/d") . "'";
$sql_sqlsvr = $sql_reserve . $query_year ;
$stmt_sqlsvr = $conn_sqlsvr->prepare($sql_sqlsvr);
$stmt_sqlsvr->execute();

while ($result_sqlsvr = $stmt_sqlsvr->fetch(PDO::FETCH_ASSOC)) {

    $sql_find = "SELECT * FROM ims_reserve_product_sac WHERE DI_KEY = '" . $result_sqlsvr["DI_KEY"] . "'"
    . " AND TRD_U_PRC = '" . $result_sqlsvr["TRD_U_PRC"] . "'"
    . " AND SKU_CODE = '" . $result_sqlsvr["SKU_CODE"] . "'"
    . " AND TRD_SEQ = '" . $result_sqlsvr["TRD_SEQ"] . "'"
    . " AND AR_NAME = '" . $result_sqlsvr["AR_NAME"] . "'"
    . " AND WL_CODE = '" . $result_sqlsvr["WL_CODE"] . "'" ;
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        echo "Dup" . "\n\r";
    } else {

        $DI_DATE = substr($result_sqlsvr["DI_DATE"],0,10);
        $DI_DATE = str_replace('/','-',$DI_DATE);

        $sql = "INSERT INTO ims_reserve_product_sac(DI_KEY,TRD_SEQ,DI_DATE,SKU_CODE,SKU_NAME,TRD_QTY,TRD_U_PRC,WL_CODE,DI_REF,SLMN_NAME,AR_NAME) 
                VALUES (:DI_KEY,:TRD_SEQ,:DI_DATE,:SKU_CODE,:SKU_NAME,:TRD_QTY,:TRD_U_PRC,:WL_CODE,:DI_REF,:SLMN_NAME,:AR_NAME)";
        $query = $conn->prepare($sql);
        $query->bindParam(':DI_KEY', $result_sqlsvr["DI_KEY"], PDO::PARAM_STR);
        $query->bindParam(':TRD_SEQ', $result_sqlsvr["TRD_SEQ"], PDO::PARAM_STR);
        $query->bindParam(':DI_DATE', $DI_DATE, PDO::PARAM_STR);
        $query->bindParam(':SKU_CODE', $result_sqlsvr["SKU_CODE"], PDO::PARAM_STR);
        $query->bindParam(':SKU_NAME', $result_sqlsvr["SKU_NAME"], PDO::PARAM_STR);
        $query->bindParam(':TRD_QTY', $result_sqlsvr["TRD_QTY"], PDO::PARAM_STR);
        $query->bindParam(':TRD_U_PRC', $result_sqlsvr["TRD_U_PRC"], PDO::PARAM_STR);
        $query->bindParam(':WL_CODE', $result_sqlsvr["WL_CODE"], PDO::PARAM_STR);
        $query->bindParam(':DI_REF', $result_sqlsvr["DI_REF"], PDO::PARAM_STR);
        $query->bindParam(':SLMN_NAME', $result_sqlsvr["SLMN_NAME"], PDO::PARAM_STR);
        $query->bindParam(':AR_NAME', $result_sqlsvr["AR_NAME"], PDO::PARAM_STR);
        $query->execute();

        $lastInsertId = $conn->lastInsertId();

        if ($lastInsertId) {
            echo "Save OK" . "\n\r";;
        } else {
            echo "Error" . "\n\r";;
        }

    }
}

$conn_sqlsvr=null;

