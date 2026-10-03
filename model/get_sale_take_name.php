<?php

include('../config/connect_db.php');

$table_name = "ims_data_sale_sac_all";

$sql = "
    SELECT DISTINCT TRIM(NAME) AS NAME FROM (
        SELECT SALE_NAME AS NAME FROM " . $table_name . " WHERE SALE_NAME IS NOT NULL AND SALE_NAME != '' AND SALE_NAME != '-'
        UNION
        SELECT TAKE_NAME AS NAME FROM " . $table_name . " WHERE TAKE_NAME IS NOT NULL AND TAKE_NAME != '' AND TAKE_NAME != '-'
    ) AS temp_names 
    WHERE NAME IS NOT NULL AND NAME != '' AND NAME != '-' 
    ORDER BY NAME ASC
";

$query = $conn->query($sql);
$sale_take = $query->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($sale_take);


