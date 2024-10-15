<?php

include('../config/connect_db.php');

$sql_get_brand = " select DISTINCT(brand) AS brand from v_wh_stock_movement_out where brand REGEXP '^[A-Z]' ORDER BY brand ";

// ดึงข้อมูลจากตารางสินค้า
$query = $conn->query($sql_get_brand);

// สร้าง JSON ของข้อมูล
$wh_brand = $query->fetchAll(PDO::FETCH_ASSOC);

// ส่งข้อมูล JSON กลับไปที่ AJAX
echo json_encode($wh_brand);

