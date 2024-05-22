<?php

ini_set('display_errors', 1);
error_reporting(~0);

include ("../config/connect_sqlserver.php");
include ("../config/connect_db.php");

include ("../cond_file/doc_info_credit_sale.php");

$doc_id_prefix = 'BKSV%';
$year = date("Y");
$month = date("m");

echo "Year = " . $year ; echo "\n\r"; echo "Month = " . $month ; echo "\n\r";

$sql_sqlsvr = $select_query . $sql_cond . " AND DI_REF like '" . $doc_id_prefix . "'"
            . " AND YEAR(DI_DATE) = " . $year
            . " AND MONTH(DI_DATE) = " . $month
            . $sql_order ;

//$myfile = fopen("qry_file1.txt", "w") or die("Unable to open file!");
//fwrite($myfile, $sql_sqlsvr);
//fclose($myfile);

$stmt_sqlsvr = $conn_sqlsvr->prepare($sql_sqlsvr);
$stmt_sqlsvr->execute();

while ($result_sqlsvr = $stmt_sqlsvr->fetch(PDO::FETCH_ASSOC)) {

    $sql_find = "SELECT * FROM ims_price_approve_header WHERE DI_KEY = '" . $result_sqlsvr["DI_KEY"] . "'";
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        echo "Dup";
    } else {

        $doc_date = substr($result_sqlsvr["DI_DATE"],8,2) . "/" . substr($result_sqlsvr["DI_DATE"],5,2) . "/" . strval(intval(substr($result_sqlsvr["DI_DATE"],0,4))+543);
        //echo $doc_date . " | " ;

        $sql = "INSERT INTO ims_price_approve_header(DI_KEY,doc_no,doc_date,customer_id,customer_name) VALUES (:DI_KEY,:doc_no,:doc_date,:customer_id,:customer_name)";
        $query = $conn->prepare($sql);
        $query->bindParam(':DI_KEY', $result_sqlsvr["DI_KEY"], PDO::PARAM_STR);
        $query->bindParam(':doc_no', $result_sqlsvr["DI_REF"], PDO::PARAM_STR);
        $query->bindParam(':doc_date', $doc_date, PDO::PARAM_STR);
        $query->bindParam(':customer_id', $result_sqlsvr["AR_CODE"], PDO::PARAM_STR);
        $query->bindParam(':customer_name', $result_sqlsvr["AR_NAME"], PDO::PARAM_STR);
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

