<?php
// export_data_tires_number_process.php

// ตรวจสอบให้แน่ใจว่าไฟล์ connect_db.php มีการตั้งค่าการเชื่อมต่อ PDO ($conn) ที่ถูกต้อง
include '../config/connect_db.php';
require '../vendor/autoload.php'; // โหลด PhpSpreadsheet

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate; // ต้องใช้คลาส Coordinate

// 1. สร้าง object Spreadsheet ใหม่
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// 2. กำหนดชื่อหัวตาราง
$headers = [
    'doc_id' => 'เลขที่เอกสาร',
    'doc_date' => 'วันที่เอกสาร',
    'customer_name' => 'ชื่อลูกค้า',
    'send_date' => 'วันที่ส่ง',
    'tires_brand' => 'ยี่ห้อยาง',
    'tires_size' => 'ขนาดยาง',
    'tires_model' => 'รุ่นยาง',
    'tires_number' => 'เลขที่ยาง',
    'tires_year' => 'ปีผลิต',
    'create_date' => 'วันที่สร้างรายการ'
];
$columnKeys = array_keys($headers);
$headerTitles = array_values($headers);

// 3. กำหนดหัวตารางในแถวแรก (A1, B1, C1, ...)
$colIndex = 1;
foreach ($headerTitles as $title) {
    // ใช้ setCellValue ร่วมกับการแปลงเลขคอลัมน์เป็นตัวอักษร
    $columnLetter = Coordinate::stringFromColumnIndex($colIndex);
    $sheet->setCellValue($columnLetter . '1', $title);
    $colIndex++;
}

// --- 4. การรับและเตรียมข้อมูลจากฟอร์ม ---
$doc_date_start_th = isset($_POST['doc_date_start']) ? trim($_POST['doc_date_start']) : null;
$doc_date_to_th = isset($_POST['doc_date_to']) ? trim($_POST['doc_date_to']) : null;
$customer_name_select = isset($_POST['customer_name_select']) ? trim($_POST['customer_name_select']) : null;


// 5. สร้าง SQL Query โดยเพิ่มเงื่อนไข WHERE clause
$sql = "SELECT " . implode(', ', $columnKeys) . " FROM ims_tires_number";
$whereConditions = [];
$params = [];

// เงื่อนไขวันที่เอกสาร (doc_date)
if (!empty($doc_date_start_th) && !empty($doc_date_to_th)) {
    // ใช้ STR_TO_DATE ใน MySQL เพื่อแปลง DD-MM-YYYY เป็น Date Type ก่อนเปรียบเทียบ
    $whereConditions[] = "STR_TO_DATE(send_date_format, '%d-%m-%Y') BETWEEN STR_TO_DATE(?, '%d-%m-%Y') AND STR_TO_DATE(?, '%d-%m-%Y')";
    $params[] = $doc_date_start_th;
    $params[] = $doc_date_to_th;
}

// เงื่อนไขชื่อลูกค้า
if (!empty($customer_name_select)) {
    // *** 🔑 การแก้ไข: เปลี่ยนจาก '=' เป็น 'LIKE' และเพิ่ม Wildcard '%' ***
    $whereConditions[] = "customer_name LIKE ?";
    // เพิ่ม % ก่อนและหลังค่าค้นหาเพื่อค้นหาแบบบางส่วน
    $params[] = "%" . $customer_name_select . "%";
    // หมายเหตุ: แม้ว่า Select2 จะส่งค่าที่เลือกมาเป็นค่าเต็ม แต่การใช้ LIKE ก็ยังปลอดภัยกว่าเผื่อมีการเปลี่ยนไปใช้ Text Input
    // แต่ถ้า Select2 ถูกใช้ในการเลือกรายการเดียว (single selection) ซึ่งค่าที่ส่งมาเป็นค่าเต็ม การใช้ '=' จะแม่นยำกว่า
    // ในกรณีนี้ ผมจะใช้ LIKE ตามคำขอ แต่ควรระลึกว่า Select2 ในฟอร์ม Export ส่ง "ค่าที่ถูกเลือก" มา
    // (ถ้าผู้ใช้พิมพ์ค้นหาแล้วเลือกรายการนั้น ค่าที่ส่งมาจะเป็นชื่อเต็ม)
    // การใช้ LIKE "%ค่าเต็ม%" อาจจะซ้ำซ้อน แต่ก็ยังคงความสามารถในการค้นหาแบบยืดหยุ่นได้
}

if (count($whereConditions) > 0) {
    $sql .= " WHERE " . implode(' OR ', $whereConditions);
}

$sql .= " ORDER BY doc_id DESC"; // เรียงลำดับข้อมูล

/*
$myfile = fopen("a-param.txt", "w") or die("Unable to open file!");
fwrite($myfile, $sql );
fclose($myfile);
*/

// 6. ดึงข้อมูลจากฐานข้อมูล
try {
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 7. เติมข้อมูลลงใน Spreadsheet
    $rowIndex = 2; // เริ่มต้นที่แถวที่ 2
    foreach ($data as $rowData) {
        $colIndex = 1;
        foreach ($columnKeys as $key) {
            // 🔑 การแก้ไข: เปลี่ยนมาใช้ setCellValue ร่วมกับการแปลงเลขคอลัมน์เป็นตัวอักษร
            $columnLetter = Coordinate::stringFromColumnIndex($colIndex);
            $sheet->setCellValue($columnLetter . $rowIndex, $rowData[$key]);
            $colIndex++;
        }
        $rowIndex++;
    }

    // --- 8. จัดรูปแบบตาราง (Styling) ---
    $lastColumn = $sheet->getHighestColumn();
    $lastRow = $sheet->getHighestRow();
    $fullRange = 'A1:' . $lastColumn . $lastRow;
    $headerRange = 'A1:' . $lastColumn . '1';

    // จัดรูปแบบหัวตาราง
    $sheet->getStyle($headerRange)->applyFromArray([
        'font' => ['bold' => true],
        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ]);

    // จัดรูปแบบข้อมูลทั้งหมด
    $sheet->getStyle($fullRange)->applyFromArray([
        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]]
    ]);

    // ปรับความกว้างของคอลัมน์อัตโนมัติ
    for ($i = 'A'; $i <= $lastColumn; $i++) {
        $sheet->getColumnDimension($i)->setAutoSize(true);
    }

    $sheet->setTitle("Tires_Data");

    // --- 9. การตั้งค่า Header และส่งไฟล์ให้ดาวน์โหลด ---
    $writer = new Xlsx($spreadsheet);
    $filename = 'ims_tires_number_export_' . date('Ymd_His') . '.xlsx';

    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    $writer->save('php://output');
    exit;

} catch (PDOException $e) {
    // ส่งข้อความ Error กลับไปยังผู้ใช้งาน (ถ้าจำเป็น)
    echo "Database error: " . $e->getMessage();
}
?>