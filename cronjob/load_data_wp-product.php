<?php

ini_set('display_errors', 1);
error_reporting(~0);

include("../config/connect_sqlserver.php");
include("../config/connect_db_wp.php");
include('../cond_file/query-product-price-main.php');


$IMG_DIR = "http://171.100.56.194:8999/sac_tires/wp-content/uploads/products/";

//$price_code = $_POST['price_code'];
$price_code = "S3";


$sql_where_ext = " AND ICCAT_CODE  in ('1SAC14','4SAC01','3SAC01','1SAC06','1SAC05','1SAC01','1SAC02','1SAC03','1SAC04','1SAC08','1SAC07',
'1SAC09','1SAC10','1SAC11','1SAC12','1SAC13','2SAC09','2SAC04','2SAC13','2SAC14','2SAC02','2SAC03',
'2SAC10','2SAC06','2SAC05','2SAC07','2SAC08','3SAC02','3SAC06','3SAC03','3SAC04','4SAC02','4SAC03',
'4SAC04','4SAC06','3SAC05','4SAC05') AND ARPRB_CODE like '" . $price_code . "'";

$sql_order = " ORDER BY SKU_KEY DESC ";

$sql_sqlsvr = $select_query . $sql_cond . $sql_where_ext . $sql_order;

$stmt_sqlsvr = $conn_sqlsvr->prepare($sql_sqlsvr);
$stmt_sqlsvr->execute();

$return_arr = array();

while ($result_sqlsvr = $stmt_sqlsvr->fetch(PDO::FETCH_ASSOC)) {

    $sql_find = "SELECT * FROM wp_postmeta "
        . " WHERE meta_key = '_sku' AND meta_value = '" . $result_sqlsvr["SKU_CODE"] . "'";

    //echo $sql_find . "\n\r";

    $query = $conn->prepare($sql_find);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);

    if ($query->rowCount() >= 1) {
        foreach ($results as $result) {

            echo $result_sqlsvr["SKU_CODE"] . " - " . $result_sqlsvr["SKU_NAME"] . " - " . $result->post_id . "\n\r";
            $sql_update = "UPDATE wp_postmeta set meta_value = " . $result_sqlsvr["ARPLU_U_PRC"] . " WHERE post_id = " . $result->post_id . " AND meta_key = '_price'" ;
            echo "SQL = " . $sql_update;

            $query = $conn->prepare($sql_update);
            $query->execute();

        }
    }

}

echo "\n\rend ";

$conn_sqlsvr = null;

