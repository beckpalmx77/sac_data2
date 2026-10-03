<?php
session_start();
// เปิด Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ปลดล็อกข้อจำกัด
set_time_limit(0);
ini_set('memory_limit', '2048M');
ini_set('upload_max_filesize', '128M');
ini_set('post_max_size', '128M');

// Include files
include '../config/connect_db.php';
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_FILES['excelFile']['name']) && $_FILES['excelFile']['error'] == UPLOAD_ERR_OK) {
    // เตรียมตัวแปรสำหรับ Log ID
    $log_id = 0;

    try {
        $file_Upload = $_FILES['excelFile']['name'];
        $fileTmp = $_FILES['excelFile']['tmp_name'];

        // ตรวจสอบ MIME type
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $fileType = finfo_file($finfo, $fileTmp);
        finfo_close($finfo);

        $allowedTypes = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel',
            'application/octet-stream'
        ];

        if (!in_array($fileType, $allowedTypes)) {
            throw new Exception("รูปแบบไฟล์ไม่ถูกต้อง ($fileType)");
        }

        // อ่านไฟล์ Excel (Load)
        $inputFileType = IOFactory::identify($fileTmp);
        $reader = IOFactory::createReader($inputFileType);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($fileTmp);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        // Config ตัวแปร
        $user_id = $_SESSION['user_id'] ?? 'SYSTEM';
        $table_name = "ims_data_sale_sac_all";
        $updatetedRows = 0;
        $totalRows = 0;
        $screen_name = "update_sale_take_doc_2";

        $str = rand();
        $seq_record = md5($str);

        // Validate Header (Row 0)
        if (!isset($rows[0]) || trim($rows[0][0]) != 'รหัสลูกค้า') {
            throw new Exception("รูปแบบไฟล์ Excel ไม่ถูกต้อง (Header ไม่ตรง)");
        }

        // ---------------------------------------------------------
        // [STEP 1] Insert Log เริ่มต้น (เก็บ Start Timestamp)
        // ---------------------------------------------------------
        // หมายเหตุ: สมมติว่า PK ของตารางนี้ชื่อ 'id'
        $sql_log_start = "INSERT INTO log_import_data 
                          (screen_name, detail2, seq_record, create_by) 
                          VALUES (?, ?, ?, ?)";
        $stmt_log_start = $conn->prepare($sql_log_start);
        // ใส่ detail2 เป็นชื่อไฟล์, ยังไม่ใส่จำนวน record
        $stmt_log_start->execute([$screen_name, $file_Upload, $seq_record, $user_id]);

        // เก็บ ID ล่าสุดไว้เพื่อนำมา Update ตอนจบ
        $log_id = $conn->lastInsertId();

        // ---------------------------------------------------------
        // เตรียม SQL Update ข้อมูลสินค้า
        // ---------------------------------------------------------
        $sql_update = "UPDATE $table_name SET AR_NAME = :AR_NAME, SALE_NAME = :SALE_NAME, TAKE_NAME = :TAKE_NAME, TRD_AMPHUR = :TRD_AMPHUR, TRD_PROVINCE = :TRD_PROVINCE, seq_record = :seq_record 
                    WHERE AR_CODE = :AR_CODE";

        $stmt_update = $conn->prepare($sql_update);

        // --- เริ่ม Transaction ---
        $conn->beginTransaction();

        foreach ($rows as $index => $row) {
            if ($index == 0) continue; // ข้าม Header

            $AR_CODE = isset($row[0]) ? trim($row[0]) : "";

            if ($AR_CODE === "") continue;

            $totalRows++;

            $params = [
                ':AR_NAME'      => isset($row[1]) ? trim($row[1]) : "",
                ':SALE_NAME'    => isset($row[2]) ? trim($row[2]) : "",
                ':TAKE_NAME'    => isset($row[4]) ? trim($row[4]) : "",
                ':TRD_AMPHUR'   => isset($row[5]) ? trim($row[5]) : "",
                ':TRD_PROVINCE' => isset($row[6]) ? trim($row[6]) : "",
                ':seq_record'   => $seq_record,
                ':AR_CODE'      => $AR_CODE
            ];

            $stmt_update->execute($params);

            if ($stmt_update->rowCount() > 0) {
                $updatetedRows++;
            }
        }

        // --- Commit ทีเดียว ---
        $conn->commit();

        // Sync the imported/updated data to ims_tires_plan_forecast table
        $sql_sync_forecast = "UPDATE ims_tires_plan_forecast p
                              INNER JOIN (
                                  SELECT 
                                      AR_CODE, 
                                      MAX(SALE_NAME) AS SALE_NAME, 
                                      MAX(TAKE_NAME) AS TAKE_NAME, 
                                      MAX(TRD_AMPHUR) AS TRD_AMPHUR, 
                                      MAX(TRD_PROVINCE) AS TRD_PROVINCE
                                  FROM ims_data_sale_sac_all
                                  GROUP BY AR_CODE
                              ) s ON p.ar_code = s.AR_CODE
                              SET 
                                  p.sale_name = s.SALE_NAME,
                                  p.take_name = s.TAKE_NAME,
                                  p.amphur = s.TRD_AMPHUR,
                                  p.province = s.TRD_PROVINCE";
        $stmt_sync = $conn->prepare($sql_sync_forecast);
        $stmt_sync->execute();

        // ---------------------------------------------------------
        // [STEP 2] Update Log ตอนจบ (เก็บ End Timestamp และจำนวน Record)
        // ---------------------------------------------------------
        if ($log_id > 0) {
            // อัปเดตแถวเดิมด้วย ID ที่เก็บไว้
            $sql_log_end = "UPDATE log_import_data SET 
                            total_record = ?, 
                            import_record = ?, 
                            detail1 = ?, 
                            update_date = NOW() 
                            WHERE id = ?"; // เปลี่ยน id เป็นชื่อ Primary Key จริงของคุณ
            $stmt_log_end = $conn->prepare($sql_log_end);
            $stmt_log_end->execute([$totalRows, $updatetedRows, $updatetedRows, $log_id]);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'file_name' => $file_Upload,
            'count' => $totalRows,
            'updated' => $updatetedRows
        ]);

    } catch (Exception $e) {
        if (isset($conn) && $conn->inTransaction()) {
            $conn->rollBack();
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ]);
    }
} else {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'Error uploading file.'
    ]);
}