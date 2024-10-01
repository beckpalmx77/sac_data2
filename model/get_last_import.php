<?php
include('../config/connect_db.php');

$return_arr = array();
$table_name = $_POST["table_name"];
$screen_name = $_POST["screen_name"];
$sql_get = "SELECT * FROM " . $table_name . " WHERE screen_name = '" . $screen_name . "'";
$statement = $conn->query($sql_get);
$results = $statement->fetchAll(PDO::FETCH_ASSOC);
foreach ($results as $result) {
    $data = "UPLOAD ล่าสุด File Update = " . $result['detail2'] . " โดย " . $result['create_by'] . " วันที่ " . $result['create_date'] . " จำนวน " . $result['import_record'] . " รายการ ";
}
echo $data;
