<?php
include "../config/connect_db.php";

$screen_name = "ims_data_sale_sac_all";
$latestImp = "";
$sql_get_seq = "SELECT seq_record FROM log_import_data WHERE screen_name = '" . $screen_name . "' ORDER BY id DESC limit 1";
$statement = $conn->query($sql_get_seq);
$results = $statement->fetchAll(PDO::FETCH_ASSOC);

foreach ($results as $result) {
    $latestImp = ($result['seq_record']);
}

if ($latestImp!=='') {
    $cond = " AND seq_record = '" . $latestImp . "'";
}

$sql_get = "SELECT * FROM ims_data_sale_sac_all WHERE 1 " .$cond . " ORDER BY id DESC" ;
$stmt = $conn->prepare($sql_get);
$stmt->execute();
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode(["data" => $rows]);

