<?php
include "../config/connect_db.php";

$stmt = $conn->prepare("SELECT cust_id,ar_name,cust_name_1,phone FROM evs_customer ORDER BY cust_id DESC");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(["data" => $rows]);



