<?php

ini_set('display_errors', 1);
error_reporting(~0);

include("../config/connect_db.php");

//$cond_date = "'" . date("d/m/Y", strtotime("yesterday")) . "' AND '" . date("d/m/Y") . "'";

$cond_date = " STR_TO_DATE(DI_DATE,'%d/%m/%Y') BETWEEN 
STR_TO_DATE(". "'" . date("d/m/Y", strtotime("yesterday")) . "'" . ",'%d/%m/%Y') AND 
STR_TO_DATE(". "'" . date("d/m/Y") . "'" . ",'%d/%m/%Y') ";

$sql_main = " SELECT DI_DATE,BRANCH FROM ims_product_sale_cockpit WHERE " . $cond_date
            . " GROUP BY DI_DATE,BRANCH "
            . " ORDER BY STR_TO_DATE(DI_DATE,'%d/%m/%Y') ";

//echo "SQL MAIN = " . $sql_main . "<br>";

$stmt_main = $conn->prepare($sql_main);
$stmt_main->execute();

while ($result_main = $stmt_main->fetch(PDO::FETCH_ASSOC)) {
    $sql_find1 = "SELECT * FROM ims_report_product_sale WHERE DI_DATE = '" . $result_main["DI_DATE"] . "'"
    . " AND SALE_CODE = '" . $result_main["BRANCH"] . "'";
    $nRows = $conn->query($sql_find1)->fetchColumn();
    if ($nRows <= 0) {
        $sql_ins = " INSERT INTO ims_report_product_sale (DI_DATE,SALE_CODE) VALUES (:DI_DATE,:SALE_CODE) ";
        echo $result_main["DI_DATE"] . "<br>";
        echo "SQL = " . $sql_ins . "<br>";
        $query_ins = $conn->prepare($sql_ins);
        $query_ins->bindParam(':DI_DATE', $result_main["DI_DATE"], PDO::PARAM_STR);
        $query_ins->bindParam(':SALE_CODE', $result_main["BRANCH"], PDO::PARAM_STR);
        $query_ins->execute();
        $lastInsertId = $conn->lastInsertId();
        if ($lastInsertId) {
            echo "Save OK" . "<br>";
        } else {
            echo "Error" . "<br>";
        }
    }
}





    










