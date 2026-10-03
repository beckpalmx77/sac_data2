<?php

include('../config/connect_db.php');

// ดึงข้อมูลจากตารางสินค้า
$query = $conn->query("SELECT * FROM wh_location_out ORDER BY id DESC");

// สร้าง JSON ของข้อมูล
$wh_location = $query->fetchAll(PDO::FETCH_ASSOC);

// ส่งข้อมูล JSON กลับไปที่ AJAX
echo json_encode($wh_location);

