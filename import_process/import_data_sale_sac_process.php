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
    $fileTmp = $_FILES['excelFile']['tmp_name'];
    $fileType = mime_content_type($fileTmp); // ตรวจสอบ MIME type

    if ($fileType !== 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' &&
        $fileType !== 'application/vnd.ms-excel') {
        echo "Invalid file type.";
        exit;
    }

    try {
        // Load the spreadsheet
        $spreadsheet = IOFactory::load($fileTmp);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        $user_id = $_SESSION['user_id'];
        $table_name = "ims_data_sale_sac_all";
        $importedRows = 0;
        $duplicateRows = 0;
        $totalRows = 0;

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

            // นับจำนวนแถวทั้งหมด (ไม่รวมแถวว่าง)
            $totalRows++;

            // Map data from Excel row to your table structure
            $data = [];
            for ($i = 0; $i <= 33; $i++) {
                // ตรวจสอบว่า cell ใน Excel มีค่าเป็น null หรือว่างเปล่าหรือไม่
                $value = isset($row[$i]) && trim($row[$i]) !== '' ? $row[$i] : "0";

                // Replace commas and check for #N/A
                $data[$i] = str_replace(["#", ","], "", ($value === "#N/A" ? "0" : $value));
            }

            // กำหนดตัวแปรต่าง ๆ ตามลำดับคอลัมน์ในฐานข้อมูล
            $DI_DAY = $data[0];
            $DI_MONTH_NAME = $data[1];
            $DI_YEAR = $data[2];
            $AR_CODE = $data[3];
            $SKU_CODE = $data[4];
            $SKU_NAME = $data[5];
            $DETAIL = ($data[6] === "0" ? "-" : $data[6]);
            $BRAND = ($data[7] === "0" ? "-" : $data[7]);
            $DI_REF = $data[8];
            $AR_NAME = $data[9];
            $SALE_NAME = $data[10];
            $TAKE_NAME = $data[11];
            $TRD_QTY = $data[12];
            $TRD_PRC = $data[13];
            $TRD_DISCOUNT = $data[14];
            $TRD_TOTAL_PRICE = $data[15];
            $TRD_VAT = $data[16];
            $TRD_AMOUNT_PRICE = $data[17];
            $TRD_PER_POINT = $data[18];
            $TRD_TOTAL_POINT = $data[19];
            $WL_CODE = ($data[20] === "0" ? "-" : $data[20]);
            $TRD_Q_FREE = $data[21];
            $TRD_AMPHUR = ($data[22] === "0" ? "-" : $data[22]);
            $TRD_PROVINCE = ($data[23] === "0" ? "-" : $data[23]);
            $TRD_MARK = ($data[24] === "0" ? "-" : $data[24]);
            $TRD_U_POINT = $data[25];
            $TRD_R_POINT = $data[26];
            $TRD_S_POINT = $data[27];
            $TRD_T_POINT = $data[28];
            $TRD_COMPARE = ($data[29] === "0" ? "-" : $data[29]);
            $TRD_SHOP = ($data[30] === "0" ? "-" : $data[30]);
            $TRD_BY_MOBAPP = ($data[31] === "0" ? "-" : $data[31]);
            $TRD_YEAR_OLD = ($data[32] === "0" ? "-" : $data[32]);
            $SKU_CAT = ($data[33] === "0" ? "-" : $data[33]);

            $DI_MONTH = convertMonthToNumberSingle($DI_MONTH_NAME);
            $DI_DATE = formatNumber($DI_DAY, 2) . "-" . formatNumber($DI_MONTH, 2) . "-" . $DI_YEAR;

            $statement = $conn->prepare("SELECT COUNT(*) FROM " . $table_name . " WHERE DI_DAY = ? AND DI_MONTH_NAME = ? 
            AND DI_YEAR = ? AND DI_REF = ? AND AR_CODE = ? AND SKU_CODE = ? 
            AND WL_CODE = ? AND TRD_QTY = ?");
            $statement->execute([$DI_DAY, $DI_MONTH_NAME, $DI_YEAR, $DI_REF, $AR_CODE, $SKU_CODE, $WL_CODE, $TRD_QTY]);
            $row = $statement->fetchColumn();
            if ($row === 0) {
                // Insert new record
                $sql_insert = "INSERT INTO $table_name (DI_DAY, DI_MONTH_NAME, DI_YEAR, AR_CODE, SKU_CODE, SKU_NAME, DETAIL
        , BRAND, DI_REF, AR_NAME, SALE_NAME, TAKE_NAME, TRD_QTY, TRD_PRC, TRD_DISCOUNT, TRD_TOTAL_PRICE, TRD_VAT, TRD_AMOUNT_PRICE
        , TRD_PER_POINT, TRD_TOTAL_POINT, WL_CODE, TRD_Q_FREE, TRD_AMPHUR, TRD_PROVINCE, TRD_MARK, TRD_U_POINT, TRD_R_POINT
        , TRD_S_POINT, TRD_T_POINT, TRD_COMPARE, TRD_SHOP, TRD_BY_MOBAPP, TRD_YEAR_OLD, SKU_CAT,DI_MONTH,seq_record,DI_DATE) 
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
                $stmt_insert = $conn->prepare($sql_insert);
                $stmt_insert->execute([$DI_DAY, $DI_MONTH_NAME, $DI_YEAR, $AR_CODE, $SKU_CODE, $SKU_NAME, $DETAIL,
                    $BRAND, $DI_REF, $AR_NAME, $SALE_NAME, $TAKE_NAME, $TRD_QTY, $TRD_PRC,
                    $TRD_DISCOUNT, $TRD_TOTAL_PRICE, $TRD_VAT, $TRD_AMOUNT_PRICE, $TRD_PER_POINT,
                    $TRD_TOTAL_POINT, $WL_CODE, $TRD_Q_FREE, $TRD_AMPHUR, $TRD_PROVINCE, $TRD_MARK,
                    $TRD_U_POINT, $TRD_R_POINT, $TRD_S_POINT, $TRD_T_POINT, $TRD_COMPARE, $TRD_SHOP,
                    $TRD_BY_MOBAPP, $TRD_YEAR_OLD, $SKU_CAT, $DI_MONTH, $seq_record, $DI_DATE]);

                $importedRows++; // นับแถวที่นำเข้าสำเร็จ
            } else {
                $duplicateRows++; // นับแถวที่ซ้ำ
            }
        }

        //echo "Import completed. Total rows: $totalRows, Imported rows: $importedRows, Duplicated rows: $duplicateRows.";
        $import_success = "จำนวนที่ Upload จาก Excel : $totalRows รายการ \n\r นำเข้าสำเร็จ: $importedRows รายการ";

        $sql_insert_log = "INSERT INTO log_import_data (screen_name,total_record,import_record,detail1,seq_record,create_by) VALUES (?,?,?,?,?,?)";
        $stmt_insert_log = $conn->prepare($sql_insert_log);
        $stmt_insert_log->execute([$table_name, $totalRows, $importedRows, $import_success, $seq_record, $user_id]);

        echo $import_success;

    } catch (Exception $e) {
        error_log("Error processing file: " . $e->getMessage());
        echo "Error processing file.";
    }
} else {
    echo "Error uploading file.";
}

