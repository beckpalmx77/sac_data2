<?php
include('../config/connect_db.php');
$filename = "movement-stock" . "-" . date('m/d/Y H:i:s', time()) . ".csv";
date_default_timezone_set('Asia/Bangkok');
@header('Content-type: text/csv; charset=UTF-8');
@header('Content-Encoding: UTF-8');
@header("Content-Disposition: attachment; filename=" . $filename);

$doc_date = $_POST["doc_date"];

$select_query_wh_movement = "  SELECT * FROM v_wh_stock_movement WHERE doc_date = '" . $doc_date . "'" . " ORDER BY doc_id,create_by,create_date ";

$String_Sql = $select_query_wh_movement;

$data = "วันที่,รหัสสินค้า,รายละเอียด,จำนวน,คลังปี,สัปดาห์,จากตำแหน่ง,ไปตำแหน่ง\n";

$query = $conn->prepare($String_Sql);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

if ($query->rowCount() >= 1) {
    foreach ($results as $result) {
        $data .= $result->doc_date . ",";
        $data .=  $result->product_id . ",";
        $data .=  $result->product_name . ",";
        $data .=  $result->qty . ",";
        $data .=  $result->wh_org . ",";
        $data .=  $result->wh_week_id . ",";
        $data .=  $result->location_org . ",";
        $data .=  $result->location_to . "\n";
    }
}

$data = iconv("utf-8", "tis-620", $data);
echo $data;

exit();