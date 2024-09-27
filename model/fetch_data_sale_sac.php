<?php
include "../config/connect_db.php";

$screen_name = "ims_data_sale_sac_all";
$sql_get = "SELECT seq_record FROM log_import_data WHERE screen_name = '" . $screen_name . "' ORDER BY id DESC limit 1";
$statement = $conn->query($sql_get);
$results = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($results as $result) {
    $latestImp = ($result['seq_record']);
}

$stmt = $conn->prepare("SELECT * FROM ims_data_sale_sac_all WHERE 1 AND seq_record = '" . $latestImp . "' ORDER BY id DESC");
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(["data" => $rows]);