<?php
header('Content-Type: application/json');
// *** ปรับพาธให้ถูกต้องตามโครงสร้างของคุณ ***
include("../config/connect_db.php");

$suggestions = [];
$term = trim($_GET['term'] ?? '');
$field = trim($_GET['field'] ?? '');

if (empty($term) || strlen($term) < 3) {
    echo json_encode($suggestions);
    exit();
}

// กำหนดว่าฟิลด์ที่อนุญาตให้ค้นหามีอะไรบ้าง
$allowed_fields = ['AR_NAME', 'SKU_CODE', 'serial_tires_number'];

if (!in_array($field, $allowed_fields)) {
    echo json_encode($suggestions);
    exit();
}

$table_name = "ims_product_sale_sac";

try {
    // ใช้ DISTINCT เพื่อให้ได้ค่าที่ไม่ซ้ำกัน
    $sql = "
        SELECT 
            DISTINCT {$field} AS label 
        FROM 
            {$table_name} 
        WHERE 
            {$field} LIKE :term 
        LIMIT 10
    ";

    $stmt = $conn->prepare($sql);

    // ผูกค่า LIKE
    $search_term = '%' . $term . '%';
    $stmt->bindParam(':term', $search_term, PDO::PARAM_STR);

    $stmt->execute();

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // แปลงผลลัพธ์ให้อยู่ในรูปแบบ [ "value1", "value2", ... ]
    foreach ($results as $row) {
        $suggestions[] = $row['label'];
    }

} catch (PDOException $e) {
    error_log("Autocomplete Error: " . $e->getMessage());
}

$conn = null;
echo json_encode($suggestions);