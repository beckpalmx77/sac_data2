<?php
header('Content-Type: application/json');

include("../config/connect_db.php");

$year = date("Y");

$doc_date = str_replace("-", "/", $_POST["doc_date"]);
$branch = $_POST["SLMN_NAME"];
$sql_get = "
 SELECT BRN_CODE,BRN_NAME,sum(CAST(TRD_QTY AS DECIMAL(10,2))) as  TRD_QTY
 ,sum(CAST(TRD_G_KEYIN AS DECIMAL(10,2))) as TRD_G_KEYIN 
 FROM ims_product_sale_sac 
 WHERE PGROUP IN ('P1')
 AND DI_YEAR = '" . $year . "'
 GROUP BY BRN_NAME
 ORDER BY BRN_NAME 
 ";

//$myfile = fopen("sql_get.txt", "w") or die("Unable to open file!");
//fwrite($myfile,  $sql_get);
//fclose($myfile);

$return_arr = array();

$statement = $conn->query($sql_get);
$results = $statement->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as $result) {
    $return_arr[] = array("BRN_CODE" => $result['BRN_CODE'],
        "BRN_NAME" => $result['BRN_NAME'],
        "TRD_QTY" => $result['TRD_QTY'],
        "TRD_G_KEYIN" => $result['TRD_G_KEYIN']);
}

//$myfile = fopen("qry_file_pie.txt", "w") or die("Unable to open file!");
//fwrite($myfile, $sql_get);
//fclose($myfile);

//$myfile = fopen("ret_data.txt", "w") or die("Unable to open file!");
//fwrite($myfile, json_encode($return_arr));
//fclose($myfile);

echo json_encode($return_arr);

