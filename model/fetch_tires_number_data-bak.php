<?php
include "../config/connect_db.php";

$stmt = $conn->prepare("SELECT * FROM ims_tires_number ORDER BY id DESC");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(["data" => $rows]);