<?php

include('../config/connect_db.php');

// ดึงข้อมูลจากตารางสินค้า
$query = $conn->query("SELECT product_id, product_name FROM wh_product_master");

// สร้าง JSON ของข้อมูล
$products = $query->fetchAll(PDO::FETCH_ASSOC);

// ส่งข้อมูล JSON กลับไปที่ AJAX
echo json_encode($products);

