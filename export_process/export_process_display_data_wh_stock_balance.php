<?php
include('../config/connect_db.php');
$filename = "balance-display-stock" . "-" . date('m/d/Y H:i:s', time()) . ".csv";
date_default_timezone_set('Asia/Bangkok');
@header('Content-type: text/csv; charset=UTF-8');
@header('Content-Encoding: UTF-8');
@header("Content-Disposition: attachment; filename=" . $filename);


$doc_date_start = $_POST["doc_date_start"] ;
$doc_date_to = $_POST["doc_date_to"];

$start_date_formatted = DateTime::createFromFormat('d-m-Y', $doc_date_start)->format('Y-m-d');
$end_date_formatted = DateTime::createFromFormat('d-m-Y', $doc_date_to)->format('Y-m-d');


// สร้างคำสั่ง SQL
$select_query_wh_balance = "SELECT     p.product_id,p.product_name,t.wh,t.wh_week_id,t.location,
                SUM(
                    CASE 
                        WHEN t.record_type = '+' THEN t.qty
                        WHEN t.record_type = '-' THEN -t.qty
                        ELSE 0
                    END) AS total_qty
                FROM wh_stock_transaction t
                JOIN wh_product_master p ON t.product_id = p.product_id
                WHERE (t.doc_date BETWEEN '". $doc_date_start . "' AND '". $doc_date_to ."') " .
                " GROUP BY p.product_id,p.product_name,t.wh,t.wh_week_id,t.location";



$line_no = 0;

$String_Sql = $select_query_wh_balance;

$data = "ลำดับ,รหัสสินค้า,รายละเอียด,คลังปี,สัปดาห์,ตำแหน่ง,จำนวน\n";

$query = $conn->prepare($String_Sql);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

if ($query->rowCount() >= 1) {
    foreach ($results as $result) {
        $line_no++;
        $data .= $line_no . ",";
        $data .= $result->product_id . ",";
        $data .= $result->product_name . ",";
        $data .= $result->wh . ",";
        $data .= $result->wh_week_id . ",";
        $data .= $result->location . ",";
        $data .= $result->total_qty . "\n";
    }
}

$data = iconv("utf-8", "tis-620", $data);
echo $data;

exit();