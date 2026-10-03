<?php
include("config/connect_db.php");

$response = ['status' => 'error', 'message' => 'Failed to save customer data.'];

try {
    $conn->beginTransaction();

    // บันทึกข้อมูลลูกค้า
    $stmt = $conn->prepare("INSERT INTO customers (customer_name, contact_date, contact_method, store_name, province, purpose, details) VALUES (:customerName, :contactDate, :contactMethod, :storeName, :province, :purpose, :details)");
    $stmt->execute([
        ':customerName' => $_POST['customerName'],
        ':contactDate' => $_POST['contactDate'],
        ':contactMethod' => $_POST['contactMethod'],
        ':storeName' => $_POST['storeName'],
        ':province' => $_POST['province'],
        ':purpose' => $_POST['purpose'],
        ':details' => $_POST['details']
    ]);
    $customerId = $conn->lastInsertId();

    // อัปโหลดรูปภาพ
    $uploadDir = 'uploads/';
    foreach ($_FILES['files']['name'] as $key => $name) {
        $tmpName = $_FILES['files']['tmp_name'][$key];
        $newName = uniqid() . '-' . basename($name);

        if (move_uploaded_file($tmpName, $uploadDir . $newName)) {
            $stmt = $conn->prepare("INSERT INTO customer_images (customer_id, filename) VALUES (:customer_id, :filename)");
            $stmt->execute([
                ':customer_id' => $customerId,
                ':filename' => $newName
            ]);
        }
    }

    $conn->commit();
    $response = ['status' => 'success', 'message' => 'Customer data and images saved successfully.'];
} catch (Exception $e) {
    $conn->rollBack();
    $response['message'] = $e->getMessage();
}

echo json_encode($response);
