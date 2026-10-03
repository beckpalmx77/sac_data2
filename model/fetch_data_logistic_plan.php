<?php
include "../config/connect_db.php";

$sql_get = "SELECT *
FROM ims_sale_logistic_plan_detail 
WHERE 1 " ;
$stmt = $conn->prepare($sql_get);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(["data" => $rows]);

