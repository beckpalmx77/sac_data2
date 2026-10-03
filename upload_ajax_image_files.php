<?php
include 'config/connect_db.php';
header('Content-Type: text/html; charset=utf-8');

$uploadField = $_POST['uploadField']; // ฟิลด์ที่เลือกจาก radio button
$id = $_POST["id"];
$doc_id = $_POST["doc_id"];

/*
$txt = $uploadField . " | ". $doc_id . " | " . $id . "\n\r";
$myfile = fopen("a-sql-param.txt", "w") or die("Unable to open file!");
fwrite($myfile, $txt);
fclose($myfile);
*/

$uploadedFiles = [];
$uploadDir = 'uploads/img/'; // directory to save uploaded files

// ตรวจสอบว่าไดเร็กทอรีสำหรับอัปโหลดมีอยู่หรือไม่
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0777, true);
}

// ประมวลผลไฟล์ที่ถูกอัปโหลด
foreach ($_FILES['files']['tmp_name'] as $key => $tmpName) {
    $originalFileName = basename($_FILES['files']['name'][$key]);
    $fileExtension = pathinfo($originalFileName, PATHINFO_EXTENSION);
    $newFileName = $doc_id . '_' . $id . '_' . uniqid() . '.' . $fileExtension; // สร้างชื่อไฟล์ใหม่ด้วย uniqid()
    $filePath = $uploadDir . $newFileName;

    if (move_uploaded_file($tmpName, $filePath)) {
        $uploadedFiles[] = $uploadDir . $newFileName;
    }
}

if (!empty($uploadedFiles)) {
    $fileNames = implode(',', $uploadedFiles);

    // อัปเดตชื่อไฟล์ในฐานข้อมูล
    $sql = "UPDATE ims_customer_crm_check_header SET $uploadField = :fileNames WHERE doc_id = :doc_id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':fileNames', $fileNames);
    $stmt->bindParam(':doc_id', $doc_id);

    if ($stmt->execute()) {
        echo "อัปโหลดสำเร็จ";
    } else {
        echo "เกิดข้อผิดพลาดในการอัปโหลดไฟล์";
    }
} else {
    echo "กรุณาเลือกไฟล์";
}


