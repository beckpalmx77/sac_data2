<?php
include "../config/connect_db.php";

$stmt = $conn->prepare("SELECT * FROM v_wh_stock_transaction WHERE doc_id LIKE 'BF%' ORDER BY id DESC");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(["data" => $rows]);


