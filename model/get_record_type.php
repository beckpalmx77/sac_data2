<?php

include('../config/connect_db.php');

// ดึงข้อมูลจากตารางสินค้า
$query = $conn->query("SELECT * FROM wh_record_type");

// สร้าง JSON ของข้อมูล
$record_type = $query->fetchAll(PDO::FETCH_ASSOC);

// ส่งข้อมูล JSON กลับไปที่ AJAX
echo json_encode($record_type);

