<?php

// รับชื่อไฟล์จาก AJAX
if (isset($_POST['fileNames'])) {
    $fileNames = $_POST['fileNames'];
    $fileNamesString = implode(",", $fileNames); // รวมชื่อไฟล์เป็นสตริงที่คั่นด้วยจุลภาค

    // บันทึกชื่อไฟล์ลงฐานข้อมูล
    $stmt = $conn->prepare("INSERT INTO uploaded_files (file_names) VALUES (?)");
    $stmt->bind_param("s", $fileNamesString);

    if ($stmt->execute()) {
        echo "บันทึกชื่อไฟล์สำเร็จ!";
    } else {
        echo "เกิดข้อผิดพลาดในการบันทึกชื่อไฟล์: " . $conn->error;
    }
    $stmt->close();
}
