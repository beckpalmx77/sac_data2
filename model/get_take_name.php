<?php

include('../config/connect_db.php');

$field = "TAKE_NAME";
$table_name = "ims_data_sale_sac_all";

// ดึงข้อมูลจากตารางสินค้า
$query = $conn->query("SELECT DISTINCT(". $field . ") AS TAKE_NAME FROM " . $table_name);

// สร้าง JSON ของข้อมูล
$take_name = $query->fetchAll(PDO::FETCH_ASSOC);

// ส่งข้อมูล JSON กลับไปที่ AJAX
echo json_encode($take_name);

