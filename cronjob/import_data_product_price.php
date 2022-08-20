<?php

ini_set('display_errors', 1);
error_reporting(~0);

include("../config/connect_sqlserver.php");
include("../config/connect_db.php");
include("../cond_file/query-product-price-main.php");

$sql_sqlsvr = $select_query . $sql_cond . $sql_order;

//$myfile = fopen("sqlqry_file1.txt", "w") or die("Unable to open file!");
//fwrite($myfile, $sql_sqlsvr);
//fclose($myfile);

$stmt_sqlsvr = $conn_sqlsvr->prepare($sql_sqlsvr);
$stmt_sqlsvr->execute();

while ($result_sqlsvr = $stmt_sqlsvr->fetch(PDO::FETCH_ASSOC)) {

    $sql_find = "SELECT * FROM ims_product WHERE product_id = '" . $result_sqlsvr["SKU_CODE"] ."'"
        . " AND product_key = '" . $result_sqlsvr["SKU_KEY"] . "'"
        . " AND price_code = '" . $result_sqlsvr["ARPRB_CODE"] . "'";

    //$myfile = fopen("myqeury_file_find.txt", "w") or die("Unable to open file!");
    //fwrite($myfile, $sql_find);
    //fclose($myfile);

    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        $sql = "UPDATE ims_product SET name_t=:name_t , brand_id=:brand_id , pgroup_id=:pgroup_id , price=:price "
            . " WHERE product_id = '" . $result_sqlsvr["SKU_CODE"] . "'"
            . " AND product_key = '" . $result_sqlsvr["SKU_KEY"] . "'"
            . " AND price_code = '" . $result_sqlsvr["ARPRB_CODE"] . "'";
        //$myfile = fopen("myqeury_file1.txt", "w") or die("Unable to open file!");
        //fwrite($myfile, $sql);
        //fclose($myfile);
        $query = $conn->prepare($sql);
        $query->bindParam(':name_t', $result_sqlsvr["SKU_NAME"], PDO::PARAM_STR);
        $query->bindParam(':brand_id', $result_sqlsvr["BRN_CODE"], PDO::PARAM_STR);
        $query->bindParam(':pgroup_id', $result_sqlsvr["ICCAT_CODE"], PDO::PARAM_STR);
        $query->bindParam(':price', $result_sqlsvr["ARPLU_U_PRC"], PDO::PARAM_STR);
        $query->execute();
        echo " Update OK ";

    } else {

        $sql = "INSERT INTO ims_product(product_key,product_id,pgroup_id,name_t,brand_id,price_code,price) 
                VALUES (:product_key,:product_id,:pgroup_id,:name_t,:brand_id,:price_code,:price)";
        //$myfile = fopen("myqeury_file2.txt", "w") or die("Unable to open file!");
        //fwrite($myfile, $sql);
        //fclose($myfile);
        $query = $conn->prepare($sql);
        $query->bindParam(':product_key', $result_sqlsvr["SKU_KEY"], PDO::PARAM_STR);
        $query->bindParam(':product_id', $result_sqlsvr["SKU_CODE"], PDO::PARAM_STR);
        $query->bindParam(':pgroup_id', $result_sqlsvr["ICCAT_CODE"], PDO::PARAM_STR);
        $query->bindParam(':name_t', $result_sqlsvr["SKU_NAME"], PDO::PARAM_STR);
        $query->bindParam(':brand_id', $result_sqlsvr["BRN_CODE"], PDO::PARAM_STR);
        $query->bindParam(':price_code', $result_sqlsvr["ARPRB_CODE"], PDO::PARAM_STR);
        $query->bindParam(':price', $result_sqlsvr["ARPLU_U_PRC"], PDO::PARAM_STR);
        $query->execute();

        $lastInsertId = $conn->lastInsertId();

        if ($lastInsertId) {
            echo " Save OK ";
        } else {
            echo " Error ";
        }

    }
}

$conn_sqlsvr = null;

