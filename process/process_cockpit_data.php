<?php


//header('Content-Type: application/json');

include("../config/connect_db.php");

//$month = $_POST["month"];
//$year = $_POST["year"];

$month = '4';
$year = '2022';

$sql_get = " SELECT BRANCH,DI_MONTH,DI_MONTH_NAME,DI_YEAR
 ,sum(CAST(TRD_QTY AS DECIMAL(10,2))) as  TRD_QTY
 ,sum(CAST(TRD_G_KEYIN AS DECIMAL(10,2))) as  TRD_G_KEYIN
 FROM ims_product_sale_cockpit 
 WHERE DI_MONTH = '" . $month . "'
 AND DI_YEAR = '" . $year . "'
 AND (PGROUP = 'P1' OR PGROUP = 'P2' OR PGROUP = 'P3')    
 GROUP BY  BRANCH,DI_MONTH,DI_MONTH_NAME,DI_YEAR 
 ORDER BY DI_MONTH , TRD_G_KEYIN DESC 
";
$statement = $conn->query($sql_get);
$results = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($results as $result) {
    $sql_find = "SELECT * FROM ims_report_product_sale_summary WHERE BRANCH = '" . $result["BRANCH"] ."'"
        . " AND DI_MONTH = '" . $result["DI_MONTH"] . "'"
        . " AND DI_YEAR = '" . $result["DI_YEAR"] . "'";
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        $sql = " UPDATE ims_report_product_sale_summary SET total_amt=:total_amt 
                WHERE BRANCH = :BRANCH AND DI_MONTH=:DI_MONTH AND DI_YEAR=:DI_YEAR ";
        $query = $conn->prepare($sql);
        $query->bindParam(':total_amt', $result["TRD_G_KEYIN"], PDO::PARAM_STR);
        $query->bindParam(':BRANCH', $result["BRANCH"], PDO::PARAM_STR);
        $query->bindParam(':DI_MONTH', $result["DI_MONTH"], PDO::PARAM_STR);
        $query->bindParam(':DI_YEAR', $result["DI_YEAR"], PDO::PARAM_STR);
        $query->execute();
    } else {
        $sql = "INSERT INTO ims_report_product_sale_summary(BRANCH,DI_MONTH,DI_MONTH_NAME,DI_YEAR,total_amt) 
                VALUES (:BRANCH,:DI_MONTH,:DI_MONTH_NAME,:DI_YEAR,:total_amt)";
        $query = $conn->prepare($sql);
        $query->bindParam(':total_amt', $result["TRD_G_KEYIN"], PDO::PARAM_STR);
        $query->bindParam(':BRANCH', $result["BRANCH"], PDO::PARAM_STR);
        $query->bindParam(':DI_MONTH', $result["DI_MONTH"], PDO::PARAM_STR);
        $query->bindParam(':DI_MONTH_NAME', $result["DI_MONTH_NAME"], PDO::PARAM_STR);
        $query->bindParam(':DI_YEAR', $result["DI_YEAR"], PDO::PARAM_STR);
        $query->execute();
    }
}

$sql_get = " SELECT BRANCH,DI_MONTH,DI_MONTH_NAME,DI_YEAR
 ,sum(CAST(TRD_QTY AS DECIMAL(10,2))) as  TRD_QTY
 ,sum(CAST(TRD_G_KEYIN AS DECIMAL(10,2))) as  TRD_G_KEYIN
 FROM ims_product_sale_cockpit 
 WHERE DI_MONTH = '" . $month . "'
 AND DI_YEAR = '" . $year . "'
 AND PGROUP = 'P1'   
 GROUP BY  BRANCH,DI_MONTH,DI_MONTH_NAME,DI_YEAR 
 ORDER BY DI_MONTH , TRD_G_KEYIN DESC 
";
$statement = $conn->query($sql_get);
$results = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($results as $result) {
    $sql_find = "SELECT * FROM ims_report_product_sale_summary WHERE BRANCH = '" . $result["BRANCH"] ."'"
        . " AND DI_MONTH = '" . $result["DI_MONTH"] . "'"
        . " AND DI_YEAR = '" . $result["DI_YEAR"] . "'";
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows <= 0) {
        $sql = "INSERT INTO ims_report_product_sale_summary(BRANCH,DI_MONTH,DI_YEAR,tires_total_qty,tires_total_amt) 
                VALUES (:BRANCH,:DI_MONTH,:DI_YEAR,:tires_total_qty,:tires_total_amt)";
        $query = $conn->prepare($sql);
        $query->bindParam(':BRANCH', $result["BRANCH"], PDO::PARAM_STR);
        $query->bindParam(':DI_MONTH', $result["DI_MONTH"], PDO::PARAM_STR);
        $query->bindParam(':DI_MONTH_NAME', $result["DI_MONTH_NAME"], PDO::PARAM_STR);
        $query->bindParam(':DI_YEAR', $result["DI_YEAR"], PDO::PARAM_STR);
        $query->bindParam(':tires_total_qty', $result["TRD_QTY"], PDO::PARAM_STR);
        $query->bindParam(':tires_total_amt', $result["TRD_G_KEYIN"], PDO::PARAM_STR);
        $query->execute();
        $lastInsertId = $conn->lastInsertId();
    } else {
        $sql = " UPDATE ims_report_product_sale_summary SET DI_MONTH_NAME=:DI_MONTH_NAME,tires_total_qty=:tires_total_qty,tires_total_amt=:tires_total_amt 
                WHERE BRANCH = :BRANCH AND DI_MONTH=:DI_MONTH AND DI_YEAR=:DI_YEAR ";
        $query = $conn->prepare($sql);
        $query->bindParam(':DI_MONTH_NAME', $result["DI_MONTH_NAME"], PDO::PARAM_STR);
        $query->bindParam(':tires_total_qty', $result["TRD_QTY"], PDO::PARAM_STR);
        $query->bindParam(':tires_total_amt', $result["TRD_G_KEYIN"], PDO::PARAM_STR);
        $query->bindParam(':BRANCH', $result["BRANCH"], PDO::PARAM_STR);
        $query->bindParam(':DI_MONTH', $result["DI_MONTH"], PDO::PARAM_STR);
        $query->bindParam(':DI_YEAR', $result["DI_YEAR"], PDO::PARAM_STR);
        $query->execute();
    }
}

$sql_get = " SELECT BRANCH,DI_MONTH,DI_MONTH_NAME,DI_YEAR
 ,sum(CAST(TRD_QTY AS DECIMAL(10,2))) as  TRD_QTY
 ,sum(CAST(TRD_G_KEYIN AS DECIMAL(10,2))) as  TRD_G_KEYIN
 FROM ims_product_sale_cockpit 
 WHERE DI_MONTH = '" . $month . "'
 AND DI_YEAR = '" . $year . "'
 AND PGROUP = 'P2'
 GROUP BY  BRANCH,DI_MONTH,DI_MONTH_NAME,DI_YEAR 
 ORDER BY DI_MONTH , TRD_G_KEYIN DESC 
";
$statement = $conn->query($sql_get);
$results = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($results as $result) {
    $sql_find = "SELECT * FROM ims_report_product_sale_summary WHERE BRANCH = '" . $result["BRANCH"] ."'"
        . " AND DI_MONTH = '" . $result["DI_MONTH"] . "'"
        . " AND DI_YEAR = '" . $result["DI_YEAR"] . "'";
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        $sql = " UPDATE ims_report_product_sale_summary SET part_total_amt=:part_total_amt 
                WHERE BRANCH = :BRANCH AND DI_MONTH=:DI_MONTH AND DI_YEAR=:DI_YEAR ";
        $query = $conn->prepare($sql);
        $query->bindParam(':part_total_amt', $result["TRD_G_KEYIN"], PDO::PARAM_STR);
        $query->bindParam(':BRANCH', $result["BRANCH"], PDO::PARAM_STR);
        $query->bindParam(':DI_MONTH', $result["DI_MONTH"], PDO::PARAM_STR);
        $query->bindParam(':DI_YEAR', $result["DI_YEAR"], PDO::PARAM_STR);
        $query->execute();
    }

}

$sql_get = " SELECT BRANCH,DI_MONTH,DI_MONTH_NAME,DI_YEAR
 ,sum(CAST(TRD_QTY AS DECIMAL(10,2))) as  TRD_QTY
 ,sum(CAST(TRD_G_KEYIN AS DECIMAL(10,2))) as  TRD_G_KEYIN
 FROM ims_product_sale_cockpit 
 WHERE DI_MONTH = '" . $month . "'
 AND DI_YEAR = '" . $year . "'
 AND PGROUP = 'P3'   
 GROUP BY  BRANCH,DI_MONTH,DI_MONTH_NAME,DI_YEAR 
 ORDER BY DI_MONTH , TRD_G_KEYIN DESC 
";
$statement = $conn->query($sql_get);
$results = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($results as $result) {
    $sql_find = "SELECT * FROM ims_report_product_sale_summary WHERE BRANCH = '" . $result["BRANCH"] ."'"
        . " AND DI_MONTH = '" . $result["DI_MONTH"] . "'"
        . " AND DI_YEAR = '" . $result["DI_YEAR"] . "'";
    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {
        $sql = " UPDATE ims_report_product_sale_summary SET svr_total_amt=:svr_total_amt 
                WHERE BRANCH = :BRANCH AND DI_MONTH=:DI_MONTH AND DI_YEAR=:DI_YEAR ";
        $query = $conn->prepare($sql);
        $query->bindParam(':svr_total_amt', $result["TRD_G_KEYIN"], PDO::PARAM_STR);
        $query->bindParam(':BRANCH', $result["BRANCH"], PDO::PARAM_STR);
        $query->bindParam(':DI_MONTH', $result["DI_MONTH"], PDO::PARAM_STR);
        $query->bindParam(':DI_YEAR', $result["DI_YEAR"], PDO::PARAM_STR);
        $query->execute();
    }
}




