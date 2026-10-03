<?php
header('Content-Type: application/json');
// ตรวจสอบเส้นทางของไฟล์ connect_db.php (สมมติว่าอยู่เหนือ model/)
include '../config/connect_db.php';

$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
$data = [];

/*
$myfile = fopen("a-param.txt", "w") or die("Unable to open file!");
fwrite($myfile, $searchTerm );
fclose($myfile);
*/

// เงื่อนไข: ถ้าไม่มีคำค้นหา หรือคำค้นหาสั้นเกินไป ให้ไม่ต้องค้นหา
if (strlen($searchTerm) <= 1) {
    echo json_encode([]);
    exit;
}

try {
    // เตรียม SQL Query เพื่อค้นหาชื่อลูกค้าที่ไม่ซ้ำกัน
    $sql = "SELECT DISTINCT customer_name FROM ims_tires_number 
            WHERE customer_name IS NOT NULL AND customer_name != '' 
            AND customer_name LIKE ? 
            ORDER BY customer_name ASC LIMIT 50";

    $stmt = $conn->prepare($sql);

    // ผูกค่าคำค้นหา
    $searchParam = '%' . $searchTerm . '%';
    $stmt->execute([$searchParam]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // จัดรูปแบบข้อมูลให้เข้ากับ Select2 (ใช้ key 'name' ตามที่ JS กำหนด)
    $customerNames = [];
    foreach ($results as $row) {
        $customerNames[] = [
            'name' => $row['customer_name']
        ];
    }

    echo json_encode($customerNames);

} catch (PDOException $e) {
    error_log("Database Error in fetch_customer_names: " . $e->getMessage());
    echo json_encode([]);
}

$conn = null;
exit();
?>