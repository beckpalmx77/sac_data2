<?php
header('Content-Type: application/json');

include("../config/connect_db.php");

$year = $_POST["year"];
$SKU_CAT = $_POST["SKU_CAT"];
$SALE_NAME = $_POST["SALE_NAME"];
$Cond_Query = "";

if ($SALE_NAME!=='-') {
    $Cond_Query .= " AND SALE_NAME = '" . $SALE_NAME . "' ";
}
    $Cond_Query .= " AND DI_REF NOT LIKE 'DS03%' AND DI_REF NOT LIKE 'IS02%' ";
/*
$myfile = fopen("param-brn.txt", "w") or die("Unable to open file!");
fwrite($myfile, "Year = " . $year . " | " . $SKU_CAT . " | " . $SALE_NAME . " | " . $Cond_Query);
fclose($myfile);
*/

$sql_get = "
 SELECT BRAND
 ,sum(CAST(TRD_QTY AS DECIMAL(10,2))) as TRD_QTY
 FROM ims_data_sale_sac_all
 WHERE  SKU_CAT = '" . $SKU_CAT . "'  
 AND DI_YEAR = '" . $year . "'" . $Cond_Query
 . " GROUP BY BRAND ORDER BY BRAND ";

//$myfile = fopen("param-brn2.txt", "w") or die("Unable to open file!");
//fwrite($myfile, "sql_get = " . $sql_get);
//fclose($myfile);

$return_arr = array();

$statement = $conn->query($sql_get);
$results = $statement->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as $result) {
  $return_arr[] = array("BRAND" => $result['BRAND'],
      "TRD_QTY" => $result['TRD_QTY']);
}

//$myfile = fopen("qry_file1.txt", "w") or die("Unable to open file!");
//fwrite($myfile, $sql_get);
//fclose($myfile);

echo json_encode($return_arr);

