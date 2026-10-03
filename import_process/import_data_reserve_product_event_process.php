<?php
include '../config/connect_db.php';
require '../vendor/autoload.php';
include '../util/record_util.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

// สร้างค่า seq_record และ DOC_ID
$str = rand();
$seq_record = md5($str);
$DOC_DATE = date("d-m-Y");
$cond = " WHERE DOC_DATE = '" . $DOC_DATE . "'";
$run_no = LAST_DOCUMENT_COND($conn, "ims_sale_logistic_plan", $cond);
$DOC_ID = "PL-" . $DOC_DATE . "-" . sprintf('%06s', $run_no);

$line_no = 0;
$status = "N";

// กำหนดค่าตัวแปรที่ขาดหายไป
$table_name = "ims_sale_logistic_plan_detail"; // ตัวอย่างชื่อตาราง
$file_Upload = "filename.xlsx"; // ตัวอย่างชื่อไฟล์ที่อัปโหลด
$user_id = "user123"; // ตัวอย่าง user id

if (isset($_FILES['excelFile']['name']) && $_FILES['excelFile']['error'] == UPLOAD_ERR_OK) {
    $fileTmp = $_FILES['excelFile']['tmp_name'];
    $fileName = $_FILES['excelFile']['name'];

    try {
        $spreadsheet = IOFactory::load($fileTmp);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();
    } catch (\PhpOffice\PhpSpreadsheet\Reader\Exception $e) {
        die('Error loading file: ' . $e->getMessage());
    }

    if (count($rows) < 2) {
        die("ไฟล์ Excel ไม่มีข้อมูลที่ต้องการนำเข้า.");
    }

    $first_data_row = $rows[1];
    $MONTH = ($first_data_row[0] === "" || $first_data_row[0] === null) ? "-" : trim($first_data_row[0]);
    $YEAR = ($first_data_row[1] === "" || $first_data_row[1] === null) ? "-" : trim($first_data_row[1]);
    $PROVINCE = ($first_data_row[2] === "" || $first_data_row[2] === null) ? "-" : trim($first_data_row[2]);
    $AMPHUR = ($first_data_row[3] === "" || $first_data_row[3] === null) ? "-" : trim($first_data_row[3]);
    $CUSTOMER_NAME = ($first_data_row[4] === "" || $first_data_row[4] === null) ? "0" : $first_data_row[4];

    // ตรวจสอบข้อมูลซ้ำในตาราง ims_sale_logistic_plan (Header)
    $stmt_check_header = $conn->prepare("SELECT COUNT(*) FROM ims_sale_logistic_plan 
        WHERE DOC_ID = ? AND DOC_DATE = ? AND MONTH = ? AND YEAR = ? AND CUSTOMER_NAME = ? AND AMPHUR = ? AND PROVINCE = ? AND seq_record = ?");
    $stmt_check_header->execute([$DOC_ID, $DOC_DATE, $MONTH, $YEAR, $CUSTOMER_NAME, $AMPHUR, $PROVINCE, $seq_record]);

    if ($stmt_check_header->fetchColumn() > 0) {
        die("พบข้อมูลซ้ำสำหรับเอกสารนี้แล้ว ไม่สามารถนำเข้าได้.");
    }

    // Insert Header into ims_sale_logistic_plan
    $stmt_header = $conn->prepare("INSERT INTO ims_sale_logistic_plan 
        (DOC_ID, DOC_DATE, MONTH, YEAR, CUSTOMER_NAME, AMPHUR, PROVINCE, seq_record) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt_header->execute([$DOC_ID, $DOC_DATE, $MONTH, $YEAR, $CUSTOMER_NAME, $AMPHUR, $PROVINCE, $seq_record]);

    $importedRows = 0;
    $duplicateRows = 0;

    foreach ($rows as $index => $row) {
        if ($index == 0) continue; // ข้ามแถวหัวตาราง

        $MONTH = ($row[0] === "" || $row[0] === null) ? "-" : trim($row[0]);
        $YEAR = ($row[1] === "" || $row[1] === null) ? "-" : trim($row[1]);
        $PROVINCE = ($row[2] === "" || $row[2] === null) ? "-" : trim($row[2]);
        $AMPHUR = ($row[3] === "" || $row[3] === null) ? "-" : trim($row[3]);
        $CUSTOMER_NAME = ($row[4] === "" || $row[4] === null) ? "0" : $row[4];
        $BRAND = ($row[5] === "" || $row[5] === null) ? "-" : $row[5];
        $SKU_CODE = ($row[6] === "" || $row[6] === null) ? "-" : trim($row[6]);
        $target = ($row[7] === "" || $row[7] === null) ? "-" : trim($row[7]);

        // ตรวจสอบข้อมูลซ้ำใน ims_sale_logistic_plan_detail
        $statement = $conn->prepare("SELECT COUNT(*) FROM ims_sale_logistic_plan_detail 
        WHERE MONTH = ? AND YEAR = ? AND DOC_ID = ? AND DOC_DATE = ? AND SKU_CODE = ? AND BRAND = ? AND CUSTOMER_NAME = ? AND PROVINCE = ? AND AMPHUR = ? AND seq_record = ?");
        $statement->execute([$MONTH, $YEAR, $DOC_ID, $DOC_DATE, $SKU_CODE, $BRAND, $CUSTOMER_NAME, $PROVINCE, $AMPHUR, $seq_record]);

        if ($statement->fetchColumn() == 0) {
            $line_no++;
            // Insert ข้อมูลใหม่ในตาราง detail
            $stmt_logistic_plan = $conn->prepare("INSERT INTO ims_sale_logistic_plan_detail 
            (MONTH,YEAR,DOC_ID,DOC_DATE,line_no,PROVINCE,AMPHUR,CUSTOMER_NAME,BRAND,SKU_CODE,target,seq_record) 
            VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt_logistic_plan->execute([$MONTH, $YEAR, $DOC_ID, $DOC_DATE, $line_no, $PROVINCE, $AMPHUR, $CUSTOMER_NAME, $BRAND, $SKU_CODE, $target, $seq_record]);
            $importedRows++;
            $status = "Y";
        } else {
            $duplicateRows++;
        }
    }

    $total_rows = count($rows) - 1;

    $import_success = "$fileName \n\r $status \n\r จำนวนที่ Upload จาก Excel : $total_rows รายการ \n\r นำเข้าใหม่สำเร็จ: $importedRows รายการ \n\r มีข้อมูลซ้ำ: $duplicateRows รายการ";

    // Insert log_import_data
    if ($importedRows > 0) {
        $sql_insert_log = "INSERT INTO log_import_data (screen_name, total_record, import_record, detail1, detail2, seq_record, create_by) VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt_insert_log = $conn->prepare($sql_insert_log);
        $stmt_insert_log->execute([$table_name, $total_rows, $importedRows, $import_success, $fileName, $seq_record, $user_id]);
    }

    echo $import_success;

} else {
    echo "ไม่พบไฟล์ที่อัปโหลด หรือเกิดข้อผิดพลาดในการอัปโหลดไฟล์.";
}