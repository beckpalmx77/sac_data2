<?php
header('Content-Type: application/json');

include("../config/connect_db.php");

$year = $_POST["year"];
$brand = $_POST["brand"];

//$myfile = fopen("param-1.txt", "w") or die("Unable to open file!");
//fwrite($myfile, $month  . "| Year = " . $year . "| Branch" . $branch );
//fclose($myfile);

$sql_get = " SELECT BRAND,DI_MONTH,DI_MONTH_NAME,DI_DATE
 ,sum(CAST(TRD_QTY AS DECIMAL(10,2))) as TRD_QTY
 ,sum(CAST(TRD_G_KEYIN AS DECIMAL(10,2))) as  TRD_G_KEYIN
 FROM ims_data_sale_sac_all 
 WHERE DI_YEAR = '" . $year . "'   
 and BRAND = '" . $brand . "' 
 GROUP BY  BRAND,DI_MONTH,DI_MONTH_NAME 
 ORDER BY CAST(DI_MONTH AS UNSIGNED) ASC ";

$return_arr = array();

$statement = $conn->query($sql_get);
$results = $statement->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as $result) {
    $return_arr[] = array("DI_MONTH_NAME" => $result['DI_MONTH_NAME'],
        "TRD_QTY" => $result['TRD_QTY'],
        "TRD_G_KEYIN" => $result['TRD_G_KEYIN']);
}

//$myfile = fopen("qry_file1.txt", "w") or die("Unable to open file!");
//fwrite($myfile, $sql_get);
//fclose($myfile);

echo json_encode($return_arr);

