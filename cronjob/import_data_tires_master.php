<?php

ini_set('display_errors', 1);
error_reporting(~0);

include ("../config/connect_sqlserver.php");
include ("../config/connect_db.php");

include ("../cond_file/doc_info_customer_ar.php");

$sql_mysql= " SELECT * FROM ims_tires_master_main order by id ";

//$myfile = fopen("qry_file1.txt", "w") or die("Unable to open file!");
//fwrite($myfile, $sql_sqlsvr);
//fclose($myfile);

$stmt_mysql = $conn->prepare($sql_mysql);
$stmt_mysql->execute();

while ($result_mysql = $stmt_mysql->fetch(PDO::FETCH_ASSOC)) {

    $sql_find = "SELECT * FROM ims_tires_master WHERE brand = '" . $result_mysql["tires_brand"] . "'"
    . " AND class = '" . $result_mysql["tires_class"] . "'"
    . " AND tires_code = '" . $result_mysql["tires_code"] . "'"
    . " AND detail = '" . $result_mysql["tires_detail"] . "'" ;

    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {

        $sql = "UPDATE ims_tires_master SET brand=:brand,class=:class,tires_code=:tires_code,detail=:detail
        WHERE brand=:tires_brand AND class=:tires_class AND tires_code=:tires_code AND detail=:tires_detail ";
        echo "Update Tires : " . $result_mysql["tires_brand"] . " | " . $result_mysql["tires_code"] . "\n\r";
        $query = $conn->prepare($sql);
        $query->bindParam(':brand', $result_mysql["tires_brand"], PDO::PARAM_STR);
        $query->bindParam(':class', $result_mysql["tires_class"], PDO::PARAM_STR);
        $query->bindParam(':tires_code', $result_mysql["tires_code"], PDO::PARAM_STR);
        $query->bindParam(':detail', $result_mysql["tires_detail"], PDO::PARAM_STR);
        $query->bindParam(':tires_brand', $result_mysql["tires_brand"], PDO::PARAM_STR);
        $query->bindParam(':tires_class', $result_mysql["tires_class"], PDO::PARAM_STR);
        $query->bindParam(':tires_code', $result_mysql["tires_code"], PDO::PARAM_STR);
        $query->bindParam(':tires_detail', $result_mysql["tires_detail"], PDO::PARAM_STR);
        $query->execute();

    } else {

        echo "Insert Tires : " . $result_mysql["tires_brand"] . " | " . $result_mysql["tires_code"] . "\n\r";
        $sql = "INSERT INTO ims_tires_master(brand,class,tires_code,detail)
        VALUES (:brand,:class,:tires_code,:detail)";
        $query = $conn->prepare($sql);
        $query->bindParam(':brand', $result_mysql["tires_brand"], PDO::PARAM_STR);
        $query->bindParam(':class', $result_mysql["tires_class"], PDO::PARAM_STR);
        $query->bindParam(':tires_code', $result_mysql["tires_code"], PDO::PARAM_STR);
        $query->bindParam(':detail', $result_mysql["tires_detail"], PDO::PARAM_STR);
        $query->execute();
        $lastInsertId = $conn->lastInsertId();

        if ($lastInsertId) {
            echo "Save OK ";
        } else {
            echo "Error";
        }


    }
}

$conn_sqlsvr=null;

