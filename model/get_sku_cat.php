<?php

include('../config/connect_db.php');

$field = "SKU_CAT";
$table_name = "ims_data_sale_sac_all";

// ดึงข้อมูลจากตารางสินค้า
$query = $conn->query("SELECT DISTINCT(". $field . ") AS SKU_CAT FROM " . $table_name);

// สร้าง JSON ของข้อมูล
$sku_cat = $query->fetchAll(PDO::FETCH_ASSOC);

// ส่งข้อมูล JSON กลับไปที่ AJAX
echo json_encode($sku_cat);

