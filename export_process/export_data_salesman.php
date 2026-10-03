<?php
date_default_timezone_set('Asia/Bangkok');

$slmn_name = $_POST['slmn_name'];
$filename = "Salesman_Data-" . ($slmn_name == 'ALL' ? 'ALL' : $slmn_name) . "-" . date('m-d-Y_H-i-s', time()) . ".csv";

@header('Content-type: text/csv; charset=UTF-8');
@header('Content-Encoding: UTF-8');
@header("Content-Disposition: attachment; filename=" . $filename);

include('../config/connect_sqlserver.php');

if ($slmn_name == 'ALL') {
    $String_Sql = "SELECT SLMN_CODE, SLMN_NAME FROM SALESMAN ORDER BY SLMN_NAME";
    $query = $conn_sqlsvr->prepare($String_Sql);
} else {
    $String_Sql = "SELECT SLMN_CODE, SLMN_NAME FROM SALESMAN WHERE SLMN_NAME = :slmn_name ORDER BY SLMN_NAME";
    $query = $conn_sqlsvr->prepare($String_Sql);
    $query->bindParam(':slmn_name', $slmn_name);
}

$data = "SLMN_CODE,SLMN_NAME\n";

$query->execute();

while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    $data .= str_replace(",", "^", $row['SLMN_CODE']) . ",";
    $data .= str_replace(",", "^", $row['SLMN_NAME']) . "\n";
}

$data = iconv("utf-8", "windows-874//IGNORE", $data);
echo $data;

exit();