<?php
session_start();
// เปิด Error Reporting ช่วงทดสอบ (ถ้าใช้จริงแล้วค่อยเปลี่ยนเป็น 0)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// --- ส่วนที่ 1: ปลดล็อกข้อจำกัดของ Server ---
set_time_limit(0);              // ปลดล็อกเวลา (รันได้ไม่จำกัดจนกว่าจะเสร็จ)
ini_set('memory_limit', '1024M'); // เพิ่ม RAM ให้ PHP (เผื่อไฟล์ใหญ่มาก)
ini_set('upload_max_filesize', '128M');
ini_set('post_max_size', '128M');

// Include necessary files
include '../config/connect_db.php';
require '../vendor/autoload.php';
include '../util/record_util.php';
include '../util/month_convert_util.php';
include '../util/check_format_number.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_FILES['excelFile']['name']) && $_FILES['excelFile']['error'] == UPLOAD_ERR_OK) {
    $file_Upload = $_FILES['excelFile']['name'];
    $fileTmp = $_FILES['excelFile']['tmp_name'];

    // ตรวจสอบ MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $fileType = finfo_file($finfo, $fileTmp);
    finfo_close($finfo);

    $uploadDir = '../uploads/';
    // สร้าง Folder ถ้ายังไม่มี
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }
    $uploadFile = $uploadDir . basename($file_Upload);

    // อนุญาตไฟล์ Excel ทุกประเภท
    $allowedTypes = [
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-excel',
        'application/octet-stream'
    ];

    if (!in_array($fileType, $allowedTypes)) {
        echo "Error: รูปแบบไฟล์ไม่ถูกต้อง ($fileType)";
        exit;
    }

    if (move_uploaded_file($fileTmp, $uploadFile)) {
        $upload_status = "Upload File สำเร็จ";
    } else {
        echo "Error: Upload File ไม่สำเร็จ";
        exit;
    }

    try {
        // --- ส่วนที่ 2: อ่านไฟล์ Excel แบบประหยัด RAM ---
        $inputFileType = IOFactory::identify($uploadFile);
        $reader = IOFactory::createReader($inputFileType);
        $reader->setReadDataOnly(true); // อ่านเฉพาะข้อมูล ไม่เอา Format (เร็วขึ้น)
        $spreadsheet = $reader->load($uploadFile);

        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        // ตัวแปรสำหรับใช้งาน
        $user_id = $_SESSION['user_id'] ?? 'SYSTEM';
        $table_name = "ims_data_sale_sac_all";
        $updatetedRows = 0;
        $totalRows = 0;
        $screen_name = "update_sale_take_doc_2";

        $str = rand();
        $seq_record = md5($str);

        // ตรวจสอบ Header ก่อนทำรายการ (ป้องกัน User อัปไฟล์ผิด)
        if (isset($rows[0])) {
            $header = $rows[0];
            if (trim($header[0]) != 'รหัสลูกค้า' || trim($header[5]) != 'อำเภอ') {
                throw new Exception("รูปแบบไฟล์ Excel ไม่ถูกต้อง (Header ไม่ตรงกับฟอร์ม)");
            }
        }

        // --- ส่วนที่ 3: เตรียม SQL ครั้งเดียว (Prepared Statement) ---
        // 3.1 SQL เช็คข้อมูล
        $sql_check = "SELECT COUNT(*) FROM $table_name WHERE AR_CODE = :AR_CODE";
        $stmt_check = $conn->prepare($sql_check);

        // 3.2 SQL อัปเดตข้อมูล
        $sql_update = "UPDATE $table_name SET 
                        AR_NAME = :AR_NAME, 
                        SALE_NAME = :SALE_NAME, 
                        TAKE_NAME = :TAKE_NAME, 
                        TRD_AMPHUR = :TRD_AMPHUR, 
                        TRD_PROVINCE = :TRD_PROVINCE, 
                        seq_record = :seq_record 
                        WHERE AR_CODE = :AR_CODE";
        $stmt_update = $conn->prepare($sql_update);

        // --- ส่วนที่ 4: เริ่ม Transaction (เร็วขึ้น 100 เท่า) ---
        $conn->beginTransaction();

        foreach ($rows as $index => $row) {
            if ($index == 0) continue; // ข้าม Header

            // เช็คแถวว่าง
            $isEmptyRow = true;
            foreach ($row as $cell) {
                if (trim($cell) !== '') {
                    $isEmptyRow = false;
                    break;
                }
            }
            if ($isEmptyRow) continue;

            $totalRows++;

            // Map ตัวแปร (แก้ Index ให้ถูกต้องตามไฟล์ Excel)
            $AR_CODE = isset($row[0]) ? trim($row[0]) : "";
            $AR_NAME = isset($row[1]) ? trim($row[1]) : "";
            $SALE_NAME = isset($row[2]) ? trim($row[2]) : "";
            // Index 3 คือ '&' ข้ามไปใช้ Index 4
            $TAKE_NAME = isset($row[4]) ? trim($row[4]) : "";
            $TRD_AMPHUR = isset($row[5]) ? trim($row[5]) : "";
            $TRD_PROVINCE = isset($row[6]) ? trim($row[6]) : "";

            // Execute : เช็คว่ามีข้อมูลไหม
            $stmt_check->execute([':AR_CODE' => $AR_CODE]);
            $exists = $stmt_check->fetchColumn();

            if ($exists) {
                // Execute : อัปเดตข้อมูล
                $stmt_update->execute([
                    ':AR_NAME' => $AR_NAME,
                    ':SALE_NAME' => $SALE_NAME,
                    ':TAKE_NAME' => $TAKE_NAME,
                    ':TRD_AMPHUR' => $TRD_AMPHUR,
                    ':TRD_PROVINCE' => $TRD_PROVINCE,
                    ':seq_record' => $seq_record,
                    ':AR_CODE' => $AR_CODE
                ]);
                $updatetedRows++;
            }
        }

        // --- ส่วนที่ 5: ยืนยันข้อมูลลงฐานข้อมูลทีเดียว ---
        $conn->commit();

        $import_success = "$file_Upload <br> $upload_status <br> พบข้อมูลในไฟล์: $totalRows รายการ <br> อัปเดตสำเร็จ: $updatetedRows รายการ <br>";
        echo $import_success;

        // บันทึก Log
        if ($updatetedRows > 0) {
            $sql_insert_log = "INSERT INTO log_import_data (screen_name, total_record, import_record, detail1, detail2, seq_record, create_by) VALUES (?,?,?,?,?,?,?)";
            $stmt_insert_log = $conn->prepare($sql_insert_log);
            $stmt_insert_log->execute([$screen_name, $totalRows, $updatetedRows, $updatetedRows, $file_Upload, $seq_record, $user_id]);
        }

    } catch (Exception $e) {
        // ถ้า error ให้ยกเลิก SQL ทั้งหมดที่ทำค้างไว้
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        error_log("Error processing file: " . $e->getMessage());
        echo "Error Processing: " . $e->getMessage();
    }
} else {
    $error_code = isset($_FILES['excelFile']['error']) ? $_FILES['excelFile']['error'] : 'Unknown';
    echo "Error uploading file. Code: " . $error_code;
}