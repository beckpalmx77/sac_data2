<?php
// Include the autoloader from composer to load PhpSpreadsheet classes
require_once __DIR__ . '/vendor/autoload.php';

// Include your database connection file
// Make sure this file establishes a PDO connection named $conn
include "config/connect_db.php";

use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Generates a new unique DOC_ID based on the current year, month, and a sequential number.
 *
 * @param PDO $conn The PDO database connection object.
 * @return string The newly generated DOC_ID.
 */
function generateDocId($conn) {
    $prefix = "PL";
    $year = date('Y');
    $month = date('m');
    $docIdPrefix = "{$prefix}-{$year}-{$month}-";

    // Query to find the latest DOC_ID for the current month
    $sql = "SELECT DOC_ID FROM ims_sale_logistic_plan WHERE DOC_ID LIKE ? ORDER BY DOC_ID DESC LIMIT 1";
    $stmt = $conn->prepare($sql);
    $searchPattern = $docIdPrefix . "%";
    $stmt->execute([$searchPattern]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        $lastDocId = $result['DOC_ID'];
        $lastNumber = (int) substr($lastDocId, -4);
        $newNumber = $lastNumber + 1;
    } else {
        $newNumber = 1;
    }

    return $docIdPrefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
}

// Check if a file was uploaded and if there are any upload errors
if (empty($_FILES['excelFile']['name']) || $_FILES['excelFile']['error'] !== UPLOAD_ERR_OK) {
    // Check for specific upload errors for a more helpful message
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE   => 'ขนาดไฟล์ที่อัปโหลดเกินกว่าขีดจำกัดที่กำหนดใน php.ini',
        UPLOAD_ERR_FORM_SIZE  => 'ขนาดไฟล์ที่อัปโหลดเกินกว่าขีดจำกัดที่กำหนดในฟอร์ม HTML',
        UPLOAD_ERR_PARTIAL    => 'ไฟล์ถูกอัปโหลดไม่สมบูรณ์',
        UPLOAD_ERR_NO_FILE    => 'ไม่พบไฟล์ที่อัปโหลด',
        UPLOAD_ERR_NO_TMP_DIR => 'ไม่พบโฟลเดอร์ชั่วคราว',
        UPLOAD_ERR_CANT_WRITE => 'ไม่สามารถเขียนไฟล์ลงดิสก์ได้',
        UPLOAD_ERR_EXTENSION  => 'การอัปโหลดไฟล์ถูกหยุดโดยส่วนขยาย PHP',
    ];
    $errorMessage = $errorMessages[$_FILES['excelFile']['error']] ?? 'เกิดข้อผิดพลาดในการอัปโหลดไฟล์';
    die("<h1>เกิดข้อผิดพลาดในการอัปโหลดไฟล์</h1><p>" . htmlspecialchars($errorMessage) . "</p>");
}

$inputFileName = $_FILES['excelFile']['tmp_name'];

try {
    // Load the Excel file and read all data into an array
    $spreadsheet = IOFactory::load($inputFileName);
    $data = $spreadsheet->getActiveSheet()->toArray(null, true, true, true);

    // Check if the data array is empty or has only a header row
    if (count($data) <= 1) {
        die("<h1>ข้อผิดพลาด</h1><p>ไฟล์ Excel ว่างเปล่าหรือไม่มีข้อมูล</p>");
    }

    // Begin a database transaction for data integrity
    $conn->beginTransaction();

    // Get header row and create a mapping from header name to column index
    // Assuming the first row (index 1 in the array) contains the headers.
    $headerRow = array_map('trim', array_map('strtoupper', $data[1]));
    $headerMap = array_flip($headerRow);

    // Ensure the required columns exist
    $requiredColumns = ['PROVINCE', 'AMPHUR', 'CUSTOMER_NAME', 'BRAND', 'SKU_CODE', 'TARGET'];
    foreach ($requiredColumns as $col) {
        if (!isset($headerMap[$col])) {
            throw new Exception("ไม่พบคอลัมน์ที่จำเป็น: " . $col);
        }
    }

    // Generate the new DOC_ID
    $docId = generateDocId($conn);
    $docDate = date('Y-m-d');
    $createDate = date('Y-m-d H:i:s');
    $updateDate = date('Y-m-d H:i:s');

    // --- INSERT INTO HEADER TABLE: ims_sale_logistic_plan ---
    // Get header data from the first data row (row 2 in Excel, which is array index 2)
    $firstDataRow = $data[2];
    $province = $firstDataRow[$headerMap['PROVINCE']] ?? null;
    $amphur = $firstDataRow[$headerMap['AMPHUR']] ?? null;
    $customerName = $firstDataRow[$headerMap['CUSTOMER_NAME']] ?? null;

    $sqlHeader = "INSERT INTO ims_sale_logistic_plan (
       DOC_ID, DOC_DATE, CUSTOMER_NAME, PROVINCE, AMPHUR
    ) VALUES (?, ?, ?, ?, ?)";

    $stmtHeader = $conn->prepare($sqlHeader);
    $stmtHeader->execute([
        $docId, $docDate, $customerName, $province, $amphur
    ]);

    // --- INSERT INTO DETAIL TABLE: ims_sale_logistic_plan_detail ---
    $sqlDetail = "INSERT INTO ims_sale_logistic_plan_detail (
        line_no, DOC_ID, DOC_DATE, PROVINCE, AMPHUR, CUSTOMER_NAME, BRAND, SKU_CODE, target, create_date, update_date
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmtDetail = $conn->prepare($sqlDetail);

    // Loop through each data row starting from the second data row (index 3 in array)
    for ($rowNum = 2; $rowNum <= count($data); $rowNum++) {
        $row = $data[$rowNum];

        // Skip empty rows
        $isRowEmpty = true;
        foreach ($row as $cell) {
            if (!empty($cell)) {
                $isRowEmpty = false;
                break;
            }
        }
        if ($isRowEmpty) {
            continue;
        }

        $lineNo = $rowNum - 1;

        // Map data from Excel using the header map
        $province = $row[$headerMap['PROVINCE']] ?? null;
        $amphur = $row[$headerMap['AMPHUR']] ?? null;
        $customerName = $row[$headerMap['CUSTOMER_NAME']] ?? null;
        $brand = $row[$headerMap['BRAND']] ?? null;
        $skuCode = $row[$headerMap['SKU_CODE']] ?? null;
        $target = $row[$headerMap['TARGET']] ?? 0.00; // Use TARGET instead of target
        $target = ($target === '' || $target === null) ? 0.00 : (double) $target;

        // Bind parameters and execute
        $stmtDetail->execute([
            $lineNo, $docId, $docDate, $province, $amphur, $customerName, $brand, $skuCode, $target, $createDate, $updateDate
        ]);
    }

    // If all inserts are successful, commit the transaction
    $conn->commit();
    echo "<h1>บันทึกข้อมูลสำเร็จ!</h1>";
    echo "<p>DOC_ID: " . htmlspecialchars($docId) . "</p>";

} catch (Exception $e) {
    // If any error occurs, roll back the transaction
    $conn->rollBack();
    echo "<h1>การบันทึกข้อมูลล้มเหลว</h1>";
    echo "<p>เกิดข้อผิดพลาด: " . htmlspecialchars($e->getMessage()) . "</p>";
}

