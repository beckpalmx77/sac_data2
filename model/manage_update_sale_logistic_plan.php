<?php
// update_target.php

include('../config/connect_db.php'); // Your database connection file

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if the required data is present
    if (isset($_POST['id']) && isset($_POST['new_target'])) {
        $id = intval($_POST['id']);
        $new_target = floatval($_POST['new_target']);

        try {
            // Prepare the SQL statement to update the record using the ID
            $sql = "UPDATE ims_sale_logistic_plan_detail SET TARGET = :new_target WHERE id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':new_target', $new_target);
            $stmt->bindParam(':id', $id);

            // Execute the update
            $stmt->execute();

            // Return a success response in JSON format
            echo json_encode(['success' => true, 'message' => 'อัปเดตเป้าการขายเรียบร้อยแล้ว']);
        } catch (PDOException $e) {
            // Return an error response if the update fails
            echo json_encode(['success' => false, 'message' => 'เกิดข้อผิดพลาดในการอัปเดตข้อมูล: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'ข้อมูลไม่ครบถ้วน']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'ไม่รองรับวิธีการร้องขอนี้']);
}