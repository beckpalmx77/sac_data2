<?php
include '../config/connect_db.php';
require '../vendor/autoload.php';
include '../util/record_util.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// --- 1. การกำหนดค่าเอกสารเริ่มต้น ---
$current_date = date("d-m-Y");
$doc_user_id = "IMTN01";
$doc_id = "";

if (isset($_FILES['excelFile']['name']) && $_FILES['excelFile']['error'] == UPLOAD_ERR_OK) {
    $fileTmp = $_FILES['excelFile']['tmp_name'];

    try {
        $spreadsheet = IOFactory::load($fileTmp);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        // --- 3. สร้างเลขที่เอกสาร (Doc ID) ---
        $cond = " WHERE DATE(create_date) = CURDATE() AND doc_user_id = '" . $doc_user_id . "'";
        $run_no = LAST_DOCUMENT_NUMBER($conn, "doc_id", "ims_tires_number", $cond);
        $doc_id = $doc_user_id . "-" . date("Ymd") . "-" . sprintf('%06s', $run_no);
        $importedRows = 0;
        $duplicateRows = 0;

        // --- 4. วนลูปเพื่ออ่านและนำเข้าข้อมูล ---
        foreach ($rows as $index => $row) {
            if ($index == 0) continue; // ข้ามแถวหัวตาราง

            // *** 🔑 doc_date ใช้ current_date เสมอ ***
            $excel_doc_date = $current_date;

            $customer_name = (isset($row[1]) && $row[1] !== "" && $row[1] !== null) ? trim($row[1]) : null;

            // *** 🔑 send_date ใช้ข้อมูลจาก Excel Index 2 โดยตรง ไม่มีการแปลงวันที่ ***
            $send_date_excel = (isset($row[2]) && $row[2] !== "" && $row[2] !== null) ? trim($row[2]) : null;

            $tires_brand = (isset($row[3]) && $row[3] !== "" && $row[3] !== null) ? trim($row[3]) : null;
            $tires_size = (isset($row[4]) && $row[4] !== "" && $row[4] !== null) ? trim($row[4]) : null;
            $tires_model = (isset($row[5]) && $row[5] !== "" && $row[5] !== null) ? trim($row[5]) : null;
            $tires_number = (isset($row[6]) && $row[6] !== "" && $row[6] !== null) ? trim($row[6]) : null;
            $tires_year = (isset($row[7]) && $row[7] !== "" && $row[7] !== null) ? trim($row[7]) : null;

            // ตรวจสอบความสมบูรณ์ของข้อมูลที่จำเป็น
            if (empty($tires_number) || empty($tires_brand)) {
                $duplicateRows++;
                continue;
            }

            // --- 5. ตรวจสอบการซ้ำซ้อน (Duplicate Check) ด้วย 7 ฟิลด์ (ใช้ค่า send_date จาก Excel โดยตรง) ---
            $sql_check = "SELECT COUNT(*) FROM ims_tires_number 
                          WHERE customer_name = ? 
                          AND send_date = ? 
                          AND tires_brand = ? 
                          AND tires_size = ? 
                          AND tires_model = ? 
                          AND tires_number = ? 
                          AND tires_year = ?";

            $statement = $conn->prepare($sql_check);
            $statement->execute([
                $customer_name,
                $send_date_excel, // ใช้ค่าจาก Excel
                $tires_brand,
                $tires_size,
                $tires_model,
                $tires_number,
                $tires_year
            ]);

            if ($statement->fetchColumn() == 0) {
                // --- 6. เตรียมและดำเนินการ INSERT ข้อมูลใหม่ (ใช้ค่า doc_date ปัจจุบัน และ send_date จาก Excel) ---
                $stmt_insert = $conn->prepare("
                    INSERT INTO ims_tires_number 
                        (doc_id, doc_date, customer_name, send_date, tires_brand, tires_size, tires_model, tires_number, tires_year, doc_user_id) 
                    VALUES 
                        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $insert_success = $stmt_insert->execute([
                    $doc_id,
                    $excel_doc_date, // ใช้ current_date
                    $customer_name,
                    $send_date_excel, // ใช้ค่าจาก Excel
                    $tires_brand,
                    $tires_size,
                    $tires_model,
                    $tires_number,
                    $tires_year,
                    $doc_user_id
                ]);

                if ($insert_success) {
                    $importedRows++;
                }
            } else {
                $duplicateRows++;
            }
        }

        // --- 7. แสดงผลลัพธ์ ---
        echo "Imported: $importedRows, Duplicates/Skipped: $duplicateRows. Document ID: $doc_id";

    } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
        echo "Error loading spreadsheet file: " . $e->getMessage();
    } catch (\PDOException $e) {
        // หากเกิด Error ตรงนี้ สาเหตุหลักมักมาจากค่าของ doc_date หรือ send_date ไม่ตรงกับ Type ใน MySQL
        echo "Database error: " . $e->getMessage() . " (SQLSTATE: " . $e->getCode() . ")";
    }

} else {
    echo "No file uploaded or error in file upload. Error code: " . (isset($_FILES['excelFile']['error']) ? $_FILES['excelFile']['error'] : 'Unknown');
}