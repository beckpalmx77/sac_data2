<?php

include('../config/connect_db.php');

$field = "TRD_AMPHUR";
$table_name = "ims_data_sale_sac_all";

// ดึงข้อมูลจากตารางสินค้า
$query = $conn->query("SELECT DISTINCT(". $field . ") AS TRD_AMPHUR FROM " . $table_name);

// สร้าง JSON ของข้อมูล
$amphur_name = $query->fetchAll(PDO::FETCH_ASSOC);

// ส่งข้อมูล JSON กลับไปที่ AJAX
echo json_encode($amphur_name);
