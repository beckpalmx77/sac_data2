<?php

include('../config/connect_db.php');

$field = "SALE_NAME";
$table_name = "ims_data_sale_sac_all";
$where = " WHERE SALE_NAME NOT LIKE '%R%'";


if(isset($_POST['take_name'])) {
    $takeName = $_POST['take_name'];
    $where_take = " AND TAKE_NAME = '" . $takeName . "' ";
}


// ดึงข้อมูลจากตารางสินค้า
$query = $conn->query("SELECT DISTINCT(". $field . ") AS NAME FROM " . $table_name . $where . $where_take);

// สร้าง JSON ของข้อมูล
$sale_take = $query->fetchAll(PDO::FETCH_ASSOC);

// ส่งข้อมูล JSON กลับไปที่ AJAX
echo json_encode($sale_take);

