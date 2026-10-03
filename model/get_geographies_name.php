<?php

include('../config/connect_db.php');

$field = "geographies";
$table_name = "sac_sale_province";

// ดึงข้อมูลจากตารางสินค้า
$query = $conn->query("SELECT DISTINCT(". $field . ") AS geographies FROM " . $table_name);

// สร้าง JSON ของข้อมูล
$amphur_name = $query->fetchAll(PDO::FETCH_ASSOC);

// ส่งข้อมูล JSON กลับไปที่ AJAX
echo json_encode($amphur_name);

