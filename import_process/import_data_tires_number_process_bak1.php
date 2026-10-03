<?php
include '../config/connect_db.php';
require '../vendor/autoload.php';
include '../util/record_util.php'; // สมมติว่าไฟล์นี้มีฟังก์ชัน LAST_DOCUMENT_NUMBER

use PhpOffice\PhpSpreadsheet\IOFactory;

function convertThaiDateToStandard($thai_date_str)
{
    if (empty($thai_date_str) || $thai_date_str === '-') {
        return null;
    }

    // 1. สร้าง Array สำหรับ mapping ชื่อเดือนไทย (เต็ม/ย่อ) และอังกฤษ (เต็ม/ย่อ) ไปยังตัวเลขเดือน
    $month_mappings = [
        // ชื่อเดือนภาษาไทย (แบบเต็ม)
        'มกราคม' => '01', 'กุมภาพันธ์' => '02', 'มีนาคม' => '03',
        'เมษายน' => '04', 'พฤษภาคม' => '05', 'มิถุนายน' => '06',
        'กรกฎาคม' => '07', 'สิงหาคม' => '08', 'กันยายน' => '09',
        'ตุลาคม' => '10', 'พฤศจิกายน' => '11', 'ธันวาคม' => '12',

        // *** 🔑 การแก้ไข: เพิ่มชื่อเดือนย่อภาษาไทย (3 ตัวอักษร) ***
        'ม.ค.' => '01', 'ก.พ.' => '02', 'มี.ค.' => '03',
        'เม.ย.' => '04', 'พ.ค.' => '05', 'มิ.ย.' => '06',
        'ก.ค.' => '07', 'ส.ค.' => '08', 'ก.ย.' => '09',
        'ต.ค.' => '10', 'พ.ย.' => '11', 'ธ.ค.' => '12',

        // ชื่อเดือนภาษาอังกฤษ (แบบเต็ม)
        'January' => '01', 'February' => '02', 'March' => '03',
        'April' => '04', 'May' => '05', 'June' => '06',
        'July' => '07', 'August' => '08', 'September' => '09',
        'October' => '10', 'November' => '11', 'December' => '12',

        // ชื่อเดือนภาษาอังกฤษ (แบบเต็ม)
        'january' => '01', 'february' => '02', 'march' => '03',
        'april' => '04', 'may' => '05', 'june' => '06',
        'july' => '07', 'august' => '08', 'september' => '09',
        'october' => '10', 'november' => '11', 'december' => '12',

        // ชื่อเดือนภาษาอังกฤษ (แบบย่อ - 3 ตัวอักษร)
        'Jan' => '01', 'Feb' => '02', 'Mar' => '03',
        'Apr' => '04', 'May' => '05', 'Jun' => '06',
        'Jul' => '07', 'Aug' => '08', 'Sep' => '09',
        'Oct' => '10', 'Nov' => '11', 'Dec' => '12',

        // ชื่อเดือนภาษาอังกฤษ (แบบย่อ - 3 ตัวอักษร)
        'jan' => '01', 'feb' => '02', 'mar' => '03',
        'apr' => '04', 'may' => '05', 'jun' => '06',
        'jul' => '07', 'aug' => '08', 'sep' => '09',
        'oct' => '10', 'nov' => '11', 'dec' => '12',

    ];

    // 2. แยกส่วนประกอบของวันที่
    // ใช้ preg_split เพื่อแยกด้วยเครื่องหมายขีด (-) หรือทับ (/) และรวมถึงการตัดช่องว่าง
    $parts = preg_split('/[ -\/]/', $thai_date_str);

    // ตรวจสอบความถูกต้องของ format (ต้องมี 3 ส่วน)
    if (count($parts) !== 3) {
        // หาก format ไม่ตรง อาจเป็นวันที่รูปแบบอื่น (เช่น 01-01-2025) ให้ลองใช้ค่าเดิม
        return $thai_date_str;
    }

    $day = trim($parts[0]);
    $month_name = ucwords(trim($parts[1])); // แปลงเป็นรูปแบบขึ้นต้นด้วยตัวพิมพ์ใหญ่ เช่น 'Jan' หรือ 'January'
    $thai_year = trim($parts[2]);

    // 3. แปลงชื่อเดือนเป็นตัวเลขเดือน
    if (!isset($month_mappings[$month_name])) {
        // หากชื่อเดือนไม่ตรง ให้ใช้ค่าเดิม (อาจเป็นรูปแบบ 01-10-2568)
        return $thai_date_str;
    }
    $month = $month_mappings[$month_name];

    // 4. แปลงปี พ.ศ. เป็น ค.ศ. (ปี ค.ศ. = ปี พ.ศ. - 543)
    // ตรวจสอบก่อนว่าปีเป็น ค.ศ. (น้อยกว่า 3000) หรือ พ.ศ. (มากกว่า 3000)
    if ((int)$thai_year > 3000) {
        $gregorian_year = (int)$thai_year - 543;
    } elseif ((int)$thai_year > 2500) { // กรณีปีเป็น พ.ศ. 4 หลัก
        $gregorian_year = (int)$thai_year - 543;
    } else { // กรณีเป็น ค.ศ. อยู่แล้ว (เช่น 2024)
        $gregorian_year = (int)$thai_year;
    }


    // 5. ประกอบวันที่ในรูปแบบ DD-MM-YYYY (ค.ศ.)
    $formatted_day = sprintf('%02d', $day);

    return "{$formatted_day}-{$month}-{$gregorian_year}";
}

// --- 1. การกำหนดค่าเอกสารเริ่มต้น ---
$current_date = date("d-m-Y");
$doc_user_id = "IMTN01"; // รหัสผู้สร้างเอกสาร
$doc_id = "";

if (isset($_FILES['excelFile']['name']) && $_FILES['excelFile']['error'] == UPLOAD_ERR_OK) {
    $fileTmp = $_FILES['excelFile']['tmp_name'];

    try {
        $spreadsheet = IOFactory::load($fileTmp);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        // --- 3. สร้างเลขที่เอกสาร (Doc ID) สำหรับชุดการนำเข้า ---
        $cond = " WHERE DATE(create_date) = CURDATE() AND doc_user_id = '" . $doc_user_id . "'";
        $run_no = LAST_DOCUMENT_NUMBER($conn, "doc_id", "ims_tires_number", $cond);
        $doc_id = $doc_user_id . "-" . date("Ymd") . "-" . sprintf('%06s', $run_no);
        $importedRows = 0;
        $duplicateRows = 0;

        // --- 4. วนลูปเพื่ออ่านและนำเข้าข้อมูล ---
        foreach ($rows as $index => $row) {
            if ($index == 0) continue; // ข้ามแถวหัวตาราง

            // doc_date ใช้ current_date เสมอ
            $excel_doc_date = $current_date;

            $customer_name = (isset($row[1]) && $row[1] !== "" && $row[1] !== null) ? trim($row[1]) : null;

            // send_date ใช้ข้อมูลจาก Excel Index 2
            $send_date_excel = (isset($row[2]) && $row[2] !== "" && $row[2] !== null) ? trim($row[2]) : null;
            // *** 🔑 การเปลี่ยนแปลง: $send_date_processed จะมี 0 นำหน้าวันแล้ว ***
            $send_date_processed = convertThaiDateToStandard($send_date_excel);
            // *** ----------------------------------------------------------------- ***

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

            // --- 5. ตรวจสอบการซ้ำซ้อน (Duplicate Check) ด้วย 7 ฟิลด์ ---
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
                $send_date_processed, // ใช้ค่าที่แปลงแล้วในการตรวจสอบซ้ำ
                $tires_brand,
                $tires_size,
                $tires_model,
                $tires_number,
                $tires_year
            ]);

            if ($statement->fetchColumn() == 0) {
                // --- 6. เตรียมและดำเนินการ INSERT ข้อมูลใหม่ ---
                $stmt_insert = $conn->prepare("
                    INSERT INTO ims_tires_number 
                        (doc_id, doc_date, send_date, send_date_format, customer_name, tires_brand, tires_size, tires_model, tires_number, tires_year, doc_user_id) 
                    VALUES 
                        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");

                $insert_success = $stmt_insert->execute([
                    $doc_id,
                    $excel_doc_date,
                    $send_date_excel,
                    $send_date_processed, // ใช้ค่าที่แปลงแล้วในการ INSERT
                    $customer_name,
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
        echo "Database error: " . $e->getMessage() . " (SQLSTATE: " . $e->getCode() . ")";
    }

} else {
    echo "No file uploaded or error in file upload. Error code: " . (isset($_FILES['excelFile']['error']) ? $_FILES['excelFile']['error'] : 'Unknown');
}
?>