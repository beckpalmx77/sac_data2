<?php
header('Content-Type: application/json');
// *** ปรับพาธให้ถูกต้องตามโครงสร้างของคุณ ***
include("../config/connect_db.php");

if (!isset($_POST['id_main']) || empty($_POST['id_main'])) {
    echo json_encode(["success" => false, "error" => "Missing ID for update."]);
    exit();
}

$record_id = trim($_POST['id_main']);
$new_serial_number = trim($_POST['serial_number'] ?? '');
$new_ar_name = trim($_POST['ar_name'] ?? '');

try {
    $sql = "
        UPDATE 
            ims_product_sale_sac 
        SET 
            serial_tires_number = :serial_number
        WHERE 
            id = :record_id   /* ใช้ ID ใน WHERE clause */
    ";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':serial_number', $new_serial_number, PDO::PARAM_STR);
    $stmt->bindParam(':record_id', $record_id, PDO::PARAM_INT);

    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        echo json_encode(["success" => true, "message" => "Update successful."]);
    } else {
        echo json_encode(["success" => false, "error" => "No changes made or record not found."]);
    }

} catch (PDOException $e) {
    echo json_encode(["success" => false, "error" => "Database error: " . $e->getMessage()]);
}

$conn = null;