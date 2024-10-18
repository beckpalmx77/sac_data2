<?php
header('Content-Type: application/json');

include("../config/connect_db.php");

$year = $_POST["year"];
$SKU_CAT = $_POST["SKU_CAT"];
$TRD_PROVINCE = $_POST["TRD_PROVINCE"];
$Cond_Query = "";

if ($SALE_NAME!=='-') {
    $Cond_Query .= " AND SKU_CAT = '" . $SKU_CAT . "' ";
}
    $Cond_Query .= "  ";
/*
$myfile = fopen("param-brn.txt", "w") or die("Unable to open file!");
fwrite($myfile, "Year = " . $year . " | " . $SKU_CAT . " | " . $SALE_NAME . " | " . $Cond_Query);
fclose($myfile);
*/

$sql_get = "
 SELECT TRD_PROVINCE
 ,sum(CAST(TRD_QTY AS DECIMAL(10,2))) as TRD_QTY
 FROM ims_data_sale_sac_all
 WHERE  SKU_CAT = '" . $SKU_CAT . "'  
 AND DI_YEAR = '" . $year . "'" . $Cond_Query
 . " GROUP BY TRD_PROVINCE ORDER BY TRD_PROVINCE ";

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

