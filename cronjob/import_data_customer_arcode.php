<?php

ini_set('display_errors', 1);
error_reporting(~0);

include ("../config/connect_sqlserver.php");
include ("../config/connect_db.php");

include ("../cond_file/doc_info_customer_ar.php");

$sql_mysql= " SELECT * FROM v_customer_ar ";

//$myfile = fopen("qry_file1.txt", "w") or die("Unable to open file!");
//fwrite($myfile, $sql_sqlsvr);
//fclose($myfile);

$stmt_mysql = $conn->prepare($sql_mysql);
$stmt_mysql->execute();

while ($result_mysql = $stmt_mysql->fetch(PDO::FETCH_ASSOC)) {

    $sql_find = "SELECT * FROM ims_customer_arcode WHERE AR_CODE = '" . $result_mysql["AR_CODE"] . "'";
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {

        $sql = "UPDATE ims_customer_arcode SET AR_NAME=:AR_NAME
        WHERE AR_CODE = :AR_CODE ";
        echo "Update Customer : " . $result_mysql["AR_CODE"] . " | " . $result_mysql["AR_NAME"] . "\n\r";
        $query = $conn->prepare($sql);
        $query->bindParam(':AR_NAME', $result_mysql["AR_NAME"], PDO::PARAM_STR);
        $query->bindParam(':AR_CODE', $result_mysql["AR_CODE"], PDO::PARAM_STR);
        $query->execute();

    } else {

        echo "Insert Customer : " . $result_mysql["AR_CODE"] . " | " . $result_mysql["AR_NAME"] . "\n\r";
        $sql = "INSERT INTO ims_customer_arcode(AR_CODE,AR_NAME)
        VALUES (:AR_CODE,:AR_NAME)";
        $query = $conn->prepare($sql);
        $query->bindParam(':AR_NAME', $result_mysql["AR_NAME"], PDO::PARAM_STR);
        $query->bindParam(':AR_CODE', $result_mysql["AR_CODE"], PDO::PARAM_STR);
        $query->execute();
        $lastInsertId = $conn->lastInsertId();

        if ($lastInsertId) {
            echo "Save OK";
        } else {
            echo "Error";
        }


    }
}

$conn_sqlsvr=null;

