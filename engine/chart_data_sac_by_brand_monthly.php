<?php
header('Content-Type: application/json');

include("../config/connect_db.php");

$year = $_POST["year"];
$BRAND = $_POST["BRAND"];
$SALE_NAME = $_POST["SALE_NAME"];
$SKU_CAT = $_POST["SKU_CAT"];
$Cond_Query = "";

if ($SALE_NAME!=='-') {
    $Cond_Query .= " AND SALE_NAME = '" . $SALE_NAME . "' ";
} else {
    $Cond_Query .= " AND 1  ";
}

if ($SKU_CAT!=='-') {
    $Cond_Query .= " AND SKU_CAT = '" . $SKU_CAT . "' ";
}

/*
$myfile = fopen("param-1.txt", "w") or die("Unable to open file!");
fwrite($myfile, "Year = " . $year . " | BRAND " . $BRAND . " | SALE_NAME " . $SALE_NAME);
fclose($myfile);
*/

$sql_get = "
 SELECT BRAND,DI_MONTH,DI_MONTH_NAME,DI_DATE
 ,sum(CAST(TRD_QTY AS DECIMAL(10,2))) as TRD_QTY
 ,sum(CAST(TRD_AMOUNT_PRICE AS DECIMAL(10,2))) as  TRD_AMOUNT_PRICE
 FROM ims_data_sale_sac_all
 WHERE DI_YEAR = '" . $year . "'   
 and BRAND = '" . $BRAND . "'" . $Cond_Query
 . " GROUP BY  BRAND,DI_MONTH,DI_MONTH_NAME 
 ORDER BY BRAND,CAST(DI_MONTH AS UNSIGNED)
 ";

$return_arr = array();

$statement = $conn->query($sql_get);
$results = $statement->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as $result) {
    $return_arr[] = array("DI_MONTH_NAME" => $result['DI_MONTH_NAME'],
        "TRD_QTY" => $result['TRD_QTY'],
        "TRD_AMOUNT_PRICE" => $result['TRD_AMOUNT_PRICE']);
}

//$myfile = fopen("qry_file1.txt", "w") or die("Unable to open file!");
//fwrite($myfile, $sql_get);
//fclose($myfile);

echo json_encode($return_arr);

