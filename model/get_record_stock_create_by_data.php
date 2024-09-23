<?php

include('../config/connect_db.php');

// ดึงข้อมูลจากตารางสินค้า
$query = $conn->query("SELECT distinct(create_by) as create_by FROM wh_stock_record ");

// สร้าง JSON ของข้อมูล
$create_user = $query->fetchAll(PDO::FETCH_ASSOC);

// ส่งข้อมูล JSON กลับไปที่ AJAX
echo json_encode($create_user);

