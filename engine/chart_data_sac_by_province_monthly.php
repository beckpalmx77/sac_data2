<?php
header('Content-Type: application/json');

include("../config/connect_db.php");

$month = $_POST["month"];
$year = $_POST["year"];
$SALE_NAME = $_POST["SALE_NAME"];

$Cond_Query = "";

if ($SALE_NAME!=='-') {
    $Cond_Query .= " AND SALE_NAME = '" . $SALE_NAME . "' ";
} else {
    $Cond_Query .= " AND 1  ";
}

$sql_get = "
 SELECT DI_MONTH,DI_MONTH_NAME,DI_DATE,sum(CAST(TRD_AMOUNT_PRICE AS DECIMAL(10,2))) as TRD_AMOUNT_PRICE
 FROM ims_data_sale_sac_all 
 WHERE DI_YEAR = '" . $year . "'" . $Cond_Query
 . " GROUP BY  DI_MONTH,DI_MONTH_NAME ORDER BY CAST(DI_MONTH AS UNSIGNED) ";

$return_arr = array();

$statement = $conn->query($sql_get);
$results = $statement->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as $result) {
  $return_arr[] = array("DI_MONTH_NAME" => $result['DI_MONTH_NAME'],
      "TRD_AMOUNT_PRICE" => $result['TRD_AMOUNT_PRICE']);
}

//$myfile = fopen("qry_file1.txt", "w") or die("Unable to open file!");
//fwrite($myfile, $sql_get);
//fclose($myfile);

echo json_encode($return_arr);

