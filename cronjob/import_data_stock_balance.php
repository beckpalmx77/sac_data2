<?php

ini_set('display_errors', 1);
error_reporting(~0);

include("../config/connect_sqlserver.php");
include("../config/connect_db.php");

include("../cond_file/query-product-stock-balance.php");

$sql_sqlsvr = $select_query;

//$myfile = fopen("qry_file1.txt", "w") or die("Unable to open file!");
//fwrite($myfile, $sql_sqlsvr);
//fclose($myfile);



$stmt_sqlsvr = $conn_sqlsvr->prepare($sql_sqlsvr);
$stmt_sqlsvr->execute();

echo $sql_sqlsvr ;

$return_arr = array();

while ($result_sqlsvr = $stmt_sqlsvr->fetch(PDO::FETCH_ASSOC)) {

    $sql_find = "SELECT * FROM ims_product_stock_balance WHERE 
    ICCAT_CODE = '" . $result_sqlsvr["ICCAT_CODE"] . "'
    AND SKU_CODE = '" . $result_sqlsvr["SKU_CODE"] . "' 
    AND WH_CODE = '" . $result_sqlsvr["WH_CODE"] . "' 
    AND WL_CODE = '" . $result_sqlsvr["WL_CODE"] . "' 
    AND SKM_LOT_NO = '" . $result_sqlsvr["SKM_LOT_NO"] . "' 
    AND SKM_SERIAL = '" . $result_sqlsvr["SKM_SERIAL"] . "'";

    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {

        $sql_update = " UPDATE ims_product_stock_balance SET ICCAT_NAME=:ICCAT_NAME,DI_DATE=:DI_DATE
,SKU_NAME=:SKU_NAME,UTQ_NAME=:UTQ_NAME,UTQ_QTY=:UTQ_QTY,QTY=:QTY,STOCK_COST=:STOCK_COST,AC_COST=:AC_COST,STD_COST=:STD_COST 
        WHERE 
        ICCAT_CODE = '" . $result_sqlsvr["ICCAT_CODE"] . "'       
        AND SKU_CODE  = '" . $result_sqlsvr["SKU_CODE"] . "'
        AND WH_CODE = '" . $result_sqlsvr["WH_CODE"] . "' 
        AND WL_CODE = '" . $result_sqlsvr["WL_CODE"] . "' 
        AND SKM_LOT_NO = '" . $result_sqlsvr["SKM_LOT_NO"] . "' 
        AND SKM_SERIAL  = '" . $result_sqlsvr["SKM_SERIAL"] . "'";

        echo "Update " . $result_sqlsvr["SKU_CODE"] . " " ;

        $query = $conn->prepare($sql_update);
        $query->bindParam(':ICCAT_NAME', $result_sqlsvr["ICCAT_NAME"], PDO::PARAM_STR);
        $query->bindParam(':DI_DATE', $result_sqlsvr["DI_DATE"], PDO::PARAM_STR);
        $query->bindParam(':SKU_NAME', $result_sqlsvr["SKU_NAME"], PDO::PARAM_STR);
        $query->bindParam(':UTQ_NAME', $result_sqlsvr["UTQ_NAME"], PDO::PARAM_STR);
        $query->bindParam(':UTQ_QTY', $result_sqlsvr["UTQ_QTY"], PDO::PARAM_STR);
        $query->bindParam(':QTY', $result_sqlsvr["QTY"], PDO::PARAM_STR);
        $query->bindParam(':STOCK_COST', $result_sqlsvr["STOCK_COST"], PDO::PARAM_STR);
        $query->bindParam(':AC_COST', $result_sqlsvr["AC_COST"], PDO::PARAM_STR);
        $query->bindParam(':STD_COST', $result_sqlsvr["STD_COST"], PDO::PARAM_STR);
        $query->execute();

    } else {

        echo "Insert SKU_CODE : " . $result_sqlsvr["SKU_CODE"] . " | " . $result_sqlsvr["ICCAT_CODE"] . " | " . $result_sqlsvr["WH_CODE"] . "\n\r";

        $sql = "INSERT INTO ims_product_stock_balance(ICCAT_CODE,ICCAT_NAME,DI_DATE,SKU_CODE,SKU_NAME,WH_CODE,WL_CODE,SKM_LOT_NO,SKM_SERIAL,UTQ_NAME,UTQ_QTY,QTY,STOCK_COST,AC_COST,STD_COST)
        VALUES (:ICCAT_CODE,:ICCAT_NAME,:DI_DATE,:SKU_CODE,:SKU_NAME,:WH_CODE,:WL_CODE,:SKM_LOT_NO,:SKM_SERIAL,:UTQ_NAME,:UTQ_QTY,:QTY,:STOCK_COST,:AC_COST,:STD_COST)";
        $query = $conn->prepare($sql);
        $query->bindParam(':ICCAT_CODE', $result_sqlsvr["ICCAT_CODE"], PDO::PARAM_STR);
        $query->bindParam(':ICCAT_NAME', $result_sqlsvr["ICCAT_NAME"], PDO::PARAM_STR);
        $query->bindParam(':DI_DATE', $result_sqlsvr["DI_DATE"], PDO::PARAM_STR);
        $query->bindParam(':SKU_CODE', $result_sqlsvr["SKU_CODE"], PDO::PARAM_STR);
        $query->bindParam(':SKU_NAME', $result_sqlsvr["SKU_NAME"], PDO::PARAM_STR);
        $query->bindParam(':WH_CODE', $result_sqlsvr["WH_CODE"], PDO::PARAM_STR);
        $query->bindParam(':WL_CODE', $result_sqlsvr["WL_CODE"], PDO::PARAM_STR);
        $query->bindParam(':SKM_LOT_NO', $result_sqlsvr["SKM_LOT_NO"], PDO::PARAM_STR);
        $query->bindParam(':SKM_SERIAL', $result_sqlsvr["SKM_SERIAL"], PDO::PARAM_STR);
        $query->bindParam(':UTQ_NAME', $result_sqlsvr["UTQ_NAME"], PDO::PARAM_STR);
        $query->bindParam(':UTQ_QTY', $result_sqlsvr["UTQ_QTY"], PDO::PARAM_STR);
        $query->bindParam(':QTY', $result_sqlsvr["QTY"], PDO::PARAM_STR);
        $query->bindParam(':STOCK_COST', $result_sqlsvr["STOCK_COST"], PDO::PARAM_STR);
        $query->bindParam(':AC_COST', $result_sqlsvr["AC_COST"], PDO::PARAM_STR);
        $query->bindParam(':STD_COST', $result_sqlsvr["STD_COST"], PDO::PARAM_STR);
        $query->execute();

        $lastInsertId = $conn->lastInsertId();

        if ($lastInsertId) {
            echo "Save OK";
        } else {
            echo "Error";
        }

    }


}

$conn_sqlsvr = null;

