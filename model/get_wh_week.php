<?php

include('../config/connect_db.php');

// ดึงข้อมูลจากตารางสินค้า
$query = $conn->query("SELECT * FROM wh_week");

// สร้าง JSON ของข้อมูล
$wh_week = $query->fetchAll(PDO::FETCH_ASSOC);

// ส่งข้อมูล JSON กลับไปที่ AJAX
echo json_encode($wh_week);

