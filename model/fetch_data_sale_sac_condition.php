<?php
include "../config/connect_db.php";

$cond = "";
$params = [];

// กรณีมีการส่งค่า search_date มา (จากการกดค้นหา หรือ โหลดหน้าเว็บครั้งแรก)
if (isset($_GET['search_date']) && !empty($_GET['search_date'])) {
    $search_date = $_GET['search_date'];
    // เพิ่มเงื่อนไขค้นหาตาม DI_DATE
    $cond = " AND DI_DATE = :search_date ";
    $params[':search_date'] = $search_date;

} else {
    // กรณีไม่มีการส่งค่ามา (Fallback Logic เดิม: ดึง import ล่าสุด)
    $screen_name = "ims_data_sale_sac_all";
    $latestImp = "";

    $sql_get_seq = "SELECT seq_record FROM log_import_data WHERE DI_DATE = '" . $screen_name . "' ORDER BY id DESC limit 1";
    $statement = $conn->query($sql_get_seq);
    $result = $statement->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $latestImp = $result['seq_record'];
    }

    if ($latestImp !== '') {
        $cond = " AND seq_record = :latestImp ";
        $params[':latestImp'] = $latestImp;
    }
}

// สร้าง SQL Query
$sql_get = "SELECT * FROM ims_data_sale_sac_all WHERE 1 " . $cond . " ORDER BY id DESC";

$stmt = $conn->prepare($sql_get);
$stmt->execute($params);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(["data" => $rows]);