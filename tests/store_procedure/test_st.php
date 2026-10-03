<?php

// ต้องมั่นใจว่าในไฟล์ 'connect_db.php' มีการสร้างการเชื่อมต่อ PDO
// และตัวแปร PDO connection ถูกตั้งชื่อว่า $conn

include('../../config/connect_db.php'); // $conn ควรเป็น PDO Object

// 1. กำหนดค่า ID ที่ต้องการส่งไป
$product_id_to_find = 'LE2054017-LISP';

// 2. เตรียมคำสั่ง CALL พร้อมกับตัวยึดตำแหน่ง (?)
$stmt = $conn->prepare("CALL GetProductById(?)");

// 3. ผูกค่าตัวแปร (Binding) - ถูกต้องแล้วสำหรับ PDO
$stmt->bindParam(1, $product_id_to_find, PDO::PARAM_STR);

// 4. รัน (Execute) Stored Procedure
$stmt->execute(); // ถูกต้องสำหรับ PDO

// 5. ดึงข้อมูล Result Set ทั้งหมดด้วย PDO
$product_data = $stmt->fetchAll(PDO::FETCH_ASSOC); // ใช้ fetchAll() ของ PDO

if (count($product_data) > 0) { // ใช้ count() นับจำนวนแถวใน Array
    echo "<h2>Product Details for ID: " . htmlspecialchars($product_id_to_find) . "</h2>";
    foreach ($product_data as $row) {
        print_r($row);
    }
} else {
    echo "Product ID " . htmlspecialchars($product_id_to_find) . " not found.";
}

// 6. ปิด Statement และการเชื่อมต่อ
$stmt = null; // ปิด PDO Statement
$conn = null; // ปิด PDO Connection (ตั้งเป็น null)