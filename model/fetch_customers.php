<?php
require '../config/connect_db.php'; // เรียกใช้การเชื่อมต่อฐานข้อมูล

try {
    $sql = "SELECT customer_id, f_name AS customer_name FROM ims_customer_ar ";
    $stmt = $conn->query($sql);

    $customers = [];
    while ($row = $stmt->fetch()) {
        $customers[] = $row;
    }

    echo json_encode($customers);

} catch (PDOException $e) {
    echo "เกิดข้อผิดพลาดในการดึงข้อมูลลูกค้า: " . $e->getMessage();
}
?>
