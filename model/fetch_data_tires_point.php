<?php
include "../config/connect_db.php";

$sql_get = "SELECT * 
FROM ims_sac_tires_point 
WHERE 1
GROUP BY SKU_CODE,SKU_NAME,BRAND,SKU_CAT,TIRES_EDGE 
ORDER BY SKU_CODE,SKU_NAME,BRAND,SKU_CAT,TIRES_EDGE " ;
$stmt = $conn->prepare($sql_get);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(["data" => $rows]);

