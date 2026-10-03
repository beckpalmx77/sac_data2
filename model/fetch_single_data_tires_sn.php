<?php
header('Content-Type: application/json');
// *** ปรับพาธให้ถูกต้องตามโครงสร้างของคุณ ***
include("../config/connect_db.php");

if (!isset($_POST['id']) || empty($_POST['id'])) {
    echo json_encode(["error" => "Missing ID parameter."]);
    exit();
}

$record_id = trim($_POST['id']);

try {
    $sql = "
        SELECT 
            id,              /* ดึง ID กลับมาใช้ใน Modal */
            DI_REF, 
            AR_NAME, 
            SKU_CODE,
            SKU_NAME,
            serial_tires_number 
        FROM 
            ims_product_sale_sac 
        WHERE 
            ID = :record_id   /* ใช้ ID ในการค้นหา */
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':record_id', $record_id, PDO::PARAM_INT);
    $stmt->execute();

    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        echo json_encode(["data" => $data]);
    } else {
        echo json_encode(["error" => "Record not found."]);
    }

} catch (PDOException $e) {
    echo json_encode(["error" => "Database error: " . $e->getMessage()]);
}

$conn = null;