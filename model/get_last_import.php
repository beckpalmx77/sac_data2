<?php
include('../config/connect_db.php');

$return_arr = array();
$data = "";
$table_name = $_POST["table_name"];
$screen_name = $_POST["screen_name"];
$sql_get = "SELECT * FROM " . $table_name . " WHERE screen_name = '" . $screen_name . "' ORDER BY id DESC LIMIT 1 ";

/*
$txt = $sql_get . "\n\r";
$myfile = fopen("a-sale_param.txt", "w") or die("Unable to open file!");
fwrite($myfile, $txt);
fclose($myfile);
*/

$statement = $conn->query($sql_get);
$results = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($results as $result) {
    $data = "UPLOAD ล่าสุด File Update = " . $result['detail2'] . " โดย " . $result['create_by'] . " วันที่ " . $result['create_date'] . " จำนวน " . $result['import_record'] . " รายการ ";
}
echo $data;
