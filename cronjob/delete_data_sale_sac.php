<?php

ini_set('display_errors', 1);
error_reporting(~0);

include("../config/connect_sqlserver.php");
include("../config/connect_db.php");

$delete_data = "";

$sql_sqlsvr = " SELECT DOCINFO.DI_KEY,DOCINFO.DI_REF,DI_DATE,DOCINFO.DI_ACTIVE FROM DOCINFO WHERE DOCINFO.DI_ACTIVE = 1 ";

$query_year = " AND DI_DATE BETWEEN '" . date("Y/m/d", strtotime("yesterday")) . "' AND '" . date("Y/m/d") . "'";
//$query_year = " AND DI_DATE BETWEEN '2014/01/01' AND '2023/05/31'";

$sql_sqlsvr = $sql_sqlsvr . $query_year ;

echo " MSSQL DATA " . $sql_sqlsvr;

$stmt_sqlsvr = $conn_sqlsvr->prepare($sql_sqlsvr);
$stmt_sqlsvr->execute();

$return_arr = array();

while ($result_sqlsvr = $stmt_sqlsvr->fetch(PDO::FETCH_ASSOC)) {

    $sql_delete = "DELETE FROM ims_product_sale_sac 
                WHERE DI_KEY = '" . $result_sqlsvr["DI_KEY"] . "' AND DI_REF  = '" . $result_sqlsvr["DI_REF"] . "'";

    $query = $conn->prepare($sql_delete);
    //$query->bindParam(':DI_KEY', $result_sqlsvr["DI_KEY"], PDO::PARAM_STR);
    //$query->bindParam(':DI_REF', $result_sqlsvr["DI_REF"], PDO::PARAM_STR);

    $query->execute();

    $delete_data .= $result_sqlsvr["DI_DATE"] . " | " . $result_sqlsvr["DI_KEY"] . ":" . $result_sqlsvr["DI_REF"] . " - " . $sql_delete . "\n\r";

    echo " Delete DATA " . $delete_data;

    //$myfile = fopen("../logs/qry_file_mssql_server_delete.txt", "w") or die("Unable to open file!");
    //fwrite($myfile, $sql_sqlsvr . "\n\r" ." - " . $result_sqlsvr["DI_DATE"] . " | " . $result_sqlsvr["DI_KEY"] . ":" . $result_sqlsvr["DI_REF"] . " - " . $sql_delete);
    //lose($myfile);

}

$conn_sqlsvr = null;