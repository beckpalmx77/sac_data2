<?php

include('../config/connect_db.php');

$field = "AR_NAME";
$table_name = "ims_data_sale_sac_all";

// ดึงข้อมูลจากตารางสินค้า
$query = $conn->query("SELECT DISTINCT(". $field . ") AS AR_NAME FROM " . $table_name);

// สร้าง JSON ของข้อมูล
$ar_name = $query->fetchAll(PDO::FETCH_ASSOC);

// ส่งข้อมูล JSON กลับไปที่ AJAX
echo json_encode($ar_name);

