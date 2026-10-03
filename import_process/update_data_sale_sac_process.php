<?php
session_start();
error_reporting(0);

// Include necessary files for database connection and PhpSpreadsheet
include '../config/connect_db.php'; // Include your database connection
require '../vendor/autoload.php'; // Load PhpSpreadsheet library
include '../util/record_util.php'; // Include any utility files
include '../util/month_convert_util.php'; // Include any utility files
include '../util/check_format_number.php'; // Include any utility files


try {

    $table_name = "ims_data_sale_sac_all";
    $str = rand();
    $seq_record = md5($str);
    $row_count = 0;
    $importedRows = 0;
    $user_id = "System";
    $file_Upload = "temp_file.txt";

    // อ่านข้อมูลจากตาราง
    $stmt = $conn->query("SELECT id,DI_DAY, DI_MONTH_NAME, DI_YEAR FROM " . $table_name ." WHERE 1 AND DI_DATE = '-' ORDER BY id DESC ");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($rows as $row) {

        $row_count++;

        $DI_DAY = $row['DI_DAY'];
        $DI_MONTH_NAME = $row['DI_MONTH_NAME'];
        $DI_YEAR = $row['DI_YEAR'];
        $id = $row['id'];

        // แปลงชื่อเดือนเป็นหมายเลข
        $DI_MONTH = convertMonthToNumberSingle(trim($DI_MONTH_NAME));
        // สร้างวันที่ในรูปแบบ dd-mm-yyyy
        $DI_DATE = formatNumber($DI_DAY, 2) . "-" . formatNumber($DI_MONTH, 2) . "-" . $DI_YEAR;

        // อัปเดตข้อมูลในตาราง
        $updateStmt = $conn->prepare("UPDATE " . $table_name . " SET DI_DATE = :di_date , DI_MONTH = :di_month , seq_record = :seq_record WHERE id = :id ");
        $updateStmt->bindParam(':di_date', $DI_DATE);
        $updateStmt->bindParam(':di_month', $DI_MONTH);
        $updateStmt->bindParam(':seq_record', $seq_record);
        $updateStmt->bindParam(':id', $id);
        $updateStmt->execute();

        echo "วันที่อัปเดตเรียบร้อยแล้ว: " .$id . " | " . $DI_DATE . "\n\r";
    }

    //echo "Import completed. Total rows: $totalRows, Imported rows: $importedRows, Duplicated rows: $duplicateRows.";
    $import_success = "แก้ไขรายการทั้งหมด";

    $sql_insert_log = "INSERT INTO log_import_data (screen_name,total_record,import_record,detail1,detail2,seq_record,create_by) VALUES (?,?,?,?,?,?,?)";
    $stmt_insert_log = $conn->prepare($sql_insert_log);
    $stmt_insert_log->execute([$table_name, $row_count, $importedRows, $import_success, $file_Upload, $seq_record, $user_id]);

    echo $import_success;

} catch (PDOException $e) {
    echo "เกิดข้อผิดพลาดในการเชื่อมต่อฐานข้อมูล: " . $e->getMessage();
}