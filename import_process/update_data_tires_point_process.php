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
        $table_name = "ims_sac_tires_point";
        $insertRows = 0;
        $updatetedRows = 0;
        $duplicateRows = 0;
        $totalRows = 0;
        $screen_name = "update_sac_tires_point";
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
            $SKU_CODE = isset($row[0]) && trim($row[0]) !== "-" && trim($row[0]) !== "" ? trim($row[0]) : "-";
            $SKU_NAME = isset($row[1]) && trim($row[1]) !== "-" && trim($row[1]) !== "" ? trim($row[1]) : "-";
            $BRAND = isset($row[2]) && trim($row[2]) !== "-" && trim($row[2]) !== "" ? strtoupper(trim($row[2])) : "0";
            $SKU_CAT = isset($row[3]) && trim($row[3]) !== "-" && trim($row[3]) !== "" ? trim($row[3]) : "-";
            $TIRES_EDGE = isset($row[4]) && trim($row[4]) !== "-" && trim($row[4]) !== "" ? trim($row[4]) : "-";
            $TRD_U_POINT = isset($row[5]) && is_numeric(trim($row[5])) && trim($row[5]) !== "-" ? trim($row[5]) : "0";
            $TRD_S_POINT = isset($row[6]) && is_numeric(trim($row[6])) && trim($row[6]) !== "-" ? trim($row[6]) : "0";

            // เขียนข้อมูลลงไฟล์ txt
/*
            $txt .= $SKU_CODE . " | " . $SKU_NAME . " | " . $BRAND . " | " . $SKU_CAT . " | " . $TIRES_EDGE . " | "
                . $TRD_U_POINT . " | " . $TRD_S_POINT . " Total Row = " . $totalRows . "\n\r";
            $myfile = fopen("sac_tires_point_param.txt", "a") or die("Unable to open file!"); // เปลี่ยนเป็น append
            fwrite($myfile, $txt);
            fclose($myfile);
*/

            // Check if the record exists
            $statement = $conn->prepare("SELECT COUNT(*) FROM $table_name WHERE SKU_CODE = ?");
            $statement->execute([$SKU_CODE]);
            $rowCount = $statement->fetchColumn();

            if ($rowCount >= 1) {
                // คำสั่ง SQL ถูกต้องหลังจากแก้ไข
                $sql_update = "UPDATE $table_name SET SKU_NAME = :SKU_NAME , BRAND = :BRAND , SKU_CAT = :SKU_CAT , TIRES_EDGE =:TIRES_EDGE,
                TRD_U_POINT = :TRD_U_POINT , TRD_S_POINT = :TRD_S_POINT 
                WHERE SKU_CODE = :SKU_CODE";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bindParam(':SKU_NAME', $SKU_NAME, PDO::PARAM_STR);
                $stmt_update->bindParam(':BRAND', $BRAND, PDO::PARAM_STR);
                $stmt_update->bindParam(':SKU_CAT', $SKU_CAT, PDO::PARAM_STR);
                $stmt_update->bindParam(':TIRES_EDGE', $TIRES_EDGE, PDO::PARAM_STR);
                $stmt_update->bindParam(':TRD_U_POINT', $TRD_U_POINT, PDO::PARAM_STR);
                $stmt_update->bindParam(':TRD_S_POINT', $TRD_S_POINT, PDO::PARAM_STR);
                $stmt_update->bindParam(':SKU_CODE', $SKU_CODE, PDO::PARAM_STR);
                $stmt_update->execute();
                $updatetedRows++; // นับแถวที่นำเข้าสำเร็จ
                $status = "U";
            } else {
                $sql_update = "INSERT INTO $table_name (SKU_CODE,SKU_NAME,BRAND,SKU_CAT,TIRES_EDGE,TRD_U_POINT,TRD_S_POINT) 
                VALUES (:SKU_CODE,:SKU_NAME,:BRAND,:SKU_CAT,:TIRES_EDGE,:TRD_U_POINT,:TRD_S_POINT)";
                $stmt_update = $conn->prepare($sql_update);
                $stmt_update->bindParam(':SKU_CODE', $SKU_CODE, PDO::PARAM_STR);
                $stmt_update->bindParam(':SKU_NAME', $SKU_NAME, PDO::PARAM_STR);
                $stmt_update->bindParam(':BRAND', $BRAND, PDO::PARAM_STR);
                $stmt_update->bindParam(':SKU_CAT', $SKU_CAT, PDO::PARAM_STR);
                $stmt_update->bindParam(':TIRES_EDGE', $TIRES_EDGE, PDO::PARAM_STR);
                $stmt_update->bindParam(':TRD_U_POINT', $TRD_U_POINT, PDO::PARAM_STR);
                $stmt_update->bindParam(':TRD_S_POINT', $TRD_S_POINT, PDO::PARAM_STR);
                $stmt_update->execute();
                $insertRows++; // นับแถวที่นำเข้าสำเร็จ
                $status = "I";
            }
        }

        if ($status === 'U') {
            $import_success = "$file_Upload \n\r $upload_status \n\r จำนวนที่ Upload จาก Excel : $totalRows รายการ Update สำเร็จ $updatetedRows\n\r";
        } else {
            $import_success = "$file_Upload \n\r $upload_status \n\r จำนวนที่ Insert จาก Excel : $totalRows รายการ insert สำเร็จ $insertRows \n\r";
        }

        echo $import_success;

    } catch (Exception $e) {
        echo "Error processing file: " . $e->getMessage();
        error_log("Error processing file: " . $e->getMessage());
    }
} else {
    echo "Error uploading file.";
}
