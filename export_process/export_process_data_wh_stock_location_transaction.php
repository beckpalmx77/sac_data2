<?php
include('../config/connect_db.php');
$filename = "transaction-stock" . "-" . date('m/d/Y H:i:s', time()) . ".csv";
date_default_timezone_set('Asia/Bangkok');
@header('Content-type: text/csv; charset=UTF-8');
@header('Content-Encoding: UTF-8');
@header("Content-Disposition: attachment; filename=" . $filename);

$doc_date_start = $_POST["doc_date_start"] ;
$doc_date_to = $_POST["doc_date_to"];

// แปลงจากรูปแบบ DD-MM-YYYY เป็น YYYY-MM-DD
$doc_date_start = DateTime::createFromFormat('d-m-Y', $doc_date_start)->format('Y-m-d');
$doc_date_to = DateTime::createFromFormat('d-m-Y', $doc_date_to)->format('Y-m-d');

if (!empty($doc_date_start) && !empty($doc_date_to)) {
    $search_Query .= " AND STR_TO_DATE(doc_date, '%d-%m-%Y') BETWEEN '" . $doc_date_start . "' AND '". $doc_date_to ."' " ;
}

// สร้างคำสั่ง SQL
$select_query_wh_transaction = "SELECT * FROM v_wh_stock_transaction WHERE doc_user_id = 'DM04'" . $search_Query
                                . " ORDER BY create_date DESC ,doc_id,create_by ";
/*
$txt =$select_query_wh_transaction;
$my_file = fopen("exp_wh_param.txt", "w") or die("Unable to open file!");
fwrite($my_file, $txt);
fclose($my_file);
*/

$String_Sql = $select_query_wh_transaction;

$data = "วันที่,รหัสสินค้า,รายละเอียด,ประเภทเอกสาร,จำนวน,คลังปี,สัปดาห์,ตำแหน่ง,create_date,create_by\n";

$query = $conn->prepare($String_Sql);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

if ($query->rowCount() >= 1) {
    foreach ($results as $result) {
        $data .= $result->doc_date . ",";
        $data .= $result->product_id . ",";
        $data .= $result->product_name . ",";
        $data .= $result->record_type . ",";
        $data .= $result->qty . ",";
        $data .= $result->wh . ",";
        $data .= $result->wh_week_id . ",";
        $data .= $result->location . ",";
        $data .= $result->create_date . ",";
        $data .= $result->create_by . "\n";
    }
}

$data = iconv("utf-8", "tis-620", $data);
echo $data;

exit();