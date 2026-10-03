<?php
include('../config/connect_db.php');
$filename = "balance-stock" . "-" . date('m/d/Y H:i:s', time()) . ".csv";
date_default_timezone_set('Asia/Bangkok');
@header('Content-type: text/csv; charset=UTF-8');
@header('Content-Encoding: UTF-8');
@header("Content-Disposition: attachment; filename=" . $filename);

/*
$doc_date_start = $_POST["doc_date_start"] ;
$doc_date_to = $_POST["doc_date_to"];

$start_date_formatted = DateTime::createFromFormat('d-m-Y', $doc_date_start)->format('Y-m-d');
$end_date_formatted = DateTime::createFromFormat('d-m-Y', $doc_date_to)->format('Y-m-d');
*/

// สร้างคำสั่ง SQL
$select_query_wh_balance = "SELECT * FROM v_wh_stock_balance
                            ORDER BY product_id,wh,wh_week_id,location ";
/*
$txt =$select_query_wh_balance;
$my_file = fopen("exp_wh_param.txt", "w") or die("Unable to open file!");
fwrite($my_file, $txt);
fclose($my_file);
*/

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