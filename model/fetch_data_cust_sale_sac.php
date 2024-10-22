<?php
include "../config/connect_db.php";

$sql_get = "SELECT AR_CODE,AR_NAME,SALE_NAME,TAKE_NAME,TRD_AMPHUR,TRD_PROVINCE 
FROM ims_data_sale_sac_all 
WHERE 1
GROUP BY AR_CODE,AR_NAME,SALE_NAME,TAKE_NAME 
ORDER BY AR_CODE,AR_NAME,SALE_NAME,TAKE_NAME " ;
$stmt = $conn->prepare($sql_get);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(["data" => $rows]);

