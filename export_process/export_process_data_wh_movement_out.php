<?php
include('../config/connect_db.php');
$filename = "movement-stock" . "-" . date('m/d/Y H:i:s', time()) . ".csv";
date_default_timezone_set('Asia/Bangkok');
@header('Content-type: text/csv; charset=UTF-8');
@header('Content-Encoding: UTF-8');
@header("Content-Disposition: attachment; filename=" . $filename);

$doc_date_start = $_POST["doc_date_start"] ;
$doc_date_to = $_POST["doc_date_to"];

$start_date_formatted = DateTime::createFromFormat('d-m-Y', $doc_date_start)->format('Y-m-d');
$end_date_formatted = DateTime::createFromFormat('d-m-Y', $doc_date_to)->format('Y-m-d');

$select_query_wh_movement = "SELECT * FROM v_wh_stock_movement_out WHERE doc_date BETWEEN '$doc_date_start' AND '$doc_date_to' 
                            AND wh_week_id <> '' AND wh_week_id <> '' location_org <> '' "
                        . " ORDER BY create_date DESC ,doc_id,create_by ";

$String_Sql = $select_query_wh_movement;

$data = "วันที่,รหัสสินค้า,รายละเอียด,จำนวน,คลังปี,สัปดาห์,จากตำแหน่ง,ไปตำแหน่ง,เวลาทำรายการ\n";

$query = $conn->prepare($String_Sql);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

if ($query->rowCount() >= 1) {
    foreach ($results as $result) {
        $data .= $result->doc_date . ",";
        $data .= $result->product_id . ",";
        $data .= $result->product_name . ",";
        $data .= $result->qty . ",";
        $data .= $result->wh_org . ",";
        $data .= $result->wh_week_id . ",";
        $data .= $result->location_org . ",";
        $data .= $result->location_to . ",";
        $data .= $result->create_date . "\n";
    }
}

$data = iconv("utf-8", "tis-620", $data);
echo $data;

exit();