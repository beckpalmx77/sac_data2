<?php

include('../config/connect_db.php');

$field = "TRD_PROVINCE";
$table_name = "ims_data_sale_sac_all";

// ดึงข้อมูลจากตารางสินค้า
$query = $conn->query("SELECT DISTINCT(". $field . ") AS TRD_PROVINCE FROM " . $table_name);

// สร้าง JSON ของข้อมูล
$province_name = $query->fetchAll(PDO::FETCH_ASSOC);

// ส่งข้อมูล JSON กลับไปที่ AJAX
echo json_encode($province_name);

