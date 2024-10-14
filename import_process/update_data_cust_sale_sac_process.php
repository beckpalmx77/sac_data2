<?php
session_start();
error_reporting(0);

// Include necessary files for database connection and PhpSpreadsheet
include '../config/connect_db.php'; // Include your database connection
require '../vendor/autoload.php'; // Load PhpSpreadsheet library
include '../util/record_util.php'; // Include any utility files
include '../util/month_convert_util.php'; // Include any utility files
include '../util/check_format_number.php'; // Include any utility files

use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_FILES['excelFile']['name']) && $_FILES['excelFile']['error'] == UPLOAD_ERR_OK) {
    $file_Upload = $_FILES['excelFile']['name'];
    $fileTmp = $_FILES['excelFile']['tmp_name'];
    $fileType = mime_content_type($fileTmp); // ตรวจสอบ MIME type

    // กำหนดโฟลเดอร์ปลายทางที่คุณต้องการเก็บไฟล์
    $uploadDir = '../uploads/';
    $uploadFile = $uploadDir . basename($file_Upload);

    // ตรวจสอบ MIME type ของไฟล์ Excel
    if ($fileType !== 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' &&
        $fileType !== 'application/vnd.ms-excel') {
        echo "Invalid file type.";
        exit;
    }

    // ย้ายไฟล์จากตำแหน่งชั่วคราวไปยังโฟลเดอร์ที่กำหนด
    if (move_uploaded_file($fileTmp, $uploadFile)) {
        $upload_status = "Upload File สำเร็จ";
    } else {
        echo "ผิดพลาด Upload File ไม่สำเร็จ";
        exit;
    }

    try {
        // Load the spreadsheet
        $spreadsheet = IOFactory::load($uploadFile);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        $user_id = $_SESSION['user_id'];
        $table_name = "ims_data_sale_sac_all";
        $updatetedRows = 0;
        $duplicateRows = 0;
        $totalRows = 0;
        $screen_name = "update_data_cust_sale";
        $status = "";

        $str = rand();
        $seq_record = md5($str);

        foreach ($rows as $index => $row) {
            if ($index == 0) continue; // Skip header row

            // ตรวจสอบแถวว่าง ถ้าแถวทั้งหมดเป็นค่าว่าง จะข้ามไป
            $isEmptyRow = true;
            foreach ($row as $cell) {
                if (trim($cell) !== '') {
                    $isEmptyRow = false;
                    break;
                }
            }

            if ($isEmptyRow) continue; // ข้ามแถวว่าง

            // นับจำนวนแถวทั้งหมด (ไม่รวม Header)
            $totalRows++;

            // Map data from Excel row to your table structure
            $AR_CODE = isset($row[0]) ? trim($row[0]) : "0";
            $AR_NAME = isset($row[1]) ? trim($row[1]) : "0";
            $SALE_NAME = isset($row[2]) ? trim($row[2]) : "0";
            $TAKE_NAME = isset($row[3]) ? trim($row[3]) : "0";

/*
            $txt = $AR_CODE . " | " . $AR_NAME . " | " . $SALE_NAME;
            $myfile = fopen("sac_cust_param.txt", "w") or die("Unable to open file!");
            fwrite($myfile, $txt);
            fclose($myfile);
*/

            // Check if the record exists
            $statement = $conn->prepare("SELECT COUNT(*) FROM $table_name WHERE AR_CODE = ?");
            $statement->execute([$AR_CODE]);
            $rowCount = $statement->fetchColumn();

            if ($rowCount >= 1) {
                $sql_update = "UPDATE $table_name SET AR_NAME = :AR_NAME, SALE_NAME = :SALE_NAME , TAKE_NAME = :TAKE_NAME WHERE AR_CODE = :AR_CODE";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bindParam(':AR_NAME', $AR_NAME, PDO::PARAM_STR);
                $stmt_update->bindParam(':SALE_NAME', $SALE_NAME, PDO::PARAM_STR);
                $stmt_update->bindParam(':TAKE_NAME', $TAKE_NAME, PDO::PARAM_STR);
                $stmt_update->bindParam(':AR_CODE', $AR_CODE, PDO::PARAM_STR);
                $stmt_update->execute();
                $updatetedRows++; // นับแถวที่นำเข้าสำเร็จ
                $status = "Y";
            }
        }

        $import_success = "$file_Upload \n\r $upload_status \n\r จำนวนที่ Upload จาก Excel : $totalRows รายการ \n\r";
        echo $import_success;

    } catch (Exception $e) {
        error_log("Error processing file: " . $e->getMessage());
        echo "Error processing file.";
    }
} else {
    echo "Error uploading file.";
}
