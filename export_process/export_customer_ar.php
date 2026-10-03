<?php
date_default_timezone_set('Asia/Bangkok');

$filename = "Data_Customer-" . date('m/d/Y H:i:s', time()) . ".csv";

@header('Content-type: text/csv; charset=UTF-8');
@header('Content-Encoding: UTF-8');
@header("Content-Disposition: attachment; filename=" . $filename);

include('../config/connect_sqlserver.php');

$String_Sql = "SELECT AR_CODE,AR_NAME FROM ARFILE ORDER BY AR_KEY ";

$data = "ลำดับที่ , AR_CODE , AR_NAME\n";

$query = $conn_sqlsvr->prepare($String_Sql);
$query->execute();

$loop = 0;

while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    $loop++;
    $data .= $loop . ",";
    $data .= str_replace(",", "^", $row['AR_CODE']) . ",";
    $data .= str_replace(",", "^", $row['AR_NAME']) . "\n";
}

// $data = iconv("utf-8", "tis-620", $data);
$data = iconv("utf-8", "windows-874//IGNORE", $data);
echo $data;

exit();