<?php
session_start();
error_reporting(0);

include '../config/connect_db.php';
require '../vendor/autoload.php';
include '../util/record_util.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

function convertThaiDateToStandard($thai_date_str)
{
    if (empty($thai_date_str) || $thai_date_str === '-') {
        return null;
    }

    $clean_date_str = str_replace(',', '', $thai_date_str);
    $clean_date_str = trim($clean_date_str);

    $month_mappings = [
        'มกราคม' => '01', 'กุมภาพันธ์' => '02', 'มีนาคม' => '03',
        'เมษายน' => '04', 'พฤษภาคม' => '05', 'มิถุนายน' => '06',
        'กรกฎาคม' => '07', 'สิงหาคม' => '08', 'กันยายน' => '09',
        'ตุลาคม' => '10', 'พฤศจิกายน' => '11', 'ธันวาคม' => '12',

        'ม.ค.' => '01', 'ก.พ.' => '02', 'มี.ค.' => '03',
        'เม.ย.' => '04', 'พ.ค.' => '05', 'มิ.ย.' => '06',
        'ก.ค.' => '07', 'ส.ค.' => '08', 'ก.ย.' => '09',
        'ต.ค.' => '10', 'พ.ย.' => '11', 'ธ.ค.' => '12',

        'January' => '01', 'February' => '02', 'March' => '03',
        'April' => '04', 'May' => '05', 'June' => '06',
        'July' => '07', 'August' => '08', 'September' => '09',
        'October' => '10', 'November' => '11', 'December' => '12',

        'Jan' => '01', 'Feb' => '02', 'Mar' => '03',
        'Apr' => '04', 'Jun' => '06', 'Jul' => '07',
        'Aug' => '08', 'Sep' => '09', 'Oct' => '10',
        'Nov' => '11', 'Dec' => '12'
    ];

    foreach ($month_mappings as $key => $val) {
        $month_mappings[strtolower($key)] = $val;
    }

    $parts = preg_split('/[ -\/]+/', $clean_date_str, -1, PREG_SPLIT_NO_EMPTY);
    $count = count($parts);

    $day_raw = "";
    $month_raw = "";
    $year_raw = "";

    if ($count === 4) {
        $month_raw = $parts[1];
        $day_raw   = $parts[2];
        $year_raw  = $parts[3];
    } elseif ($count === 3) {
        if (is_numeric($parts[0])) {
            $day_raw   = $parts[0];
            $month_raw = $parts[1];
        } else {
            $month_raw = $parts[0];
            $day_raw   = $parts[1];
        }
        $year_raw = $parts[2];
    } else {
        return $thai_date_str;
    }

    $day = trim($day_raw);
    $month_name_check = $month_raw;

    $month = null;
    if (isset($month_mappings[$month_name_check])) {
        $month = $month_mappings[$month_name_check];
    } elseif (isset($month_mappings[ucwords($month_name_check)])) {
        $month = $month_mappings[ucwords($month_name_check)];
    } elseif (isset($month_mappings[strtolower($month_name_check)])) {
        $month = $month_mappings[strtolower($month_name_check)];
    }

    if (!$month) {
        return $thai_date_str;
    }

    $thai_year = trim($year_raw);
    if ((int)$thai_year > 3000) {
        $gregorian_year = (int)$thai_year - 543;
    } elseif ((int)$thai_year > 2500) {
        $gregorian_year = (int)$thai_year - 543;
    } else {
        $gregorian_year = (int)$thai_year;
    }

    $formatted_day = sprintf('%02d', $day);
    return "{$formatted_day}-{$month}-{$gregorian_year}";
}

$current_date = date("d-m-Y");
$doc_user_id = "IMTN01";
$doc_id = "";

if (isset($_FILES['excelFile']['name']) && $_FILES['excelFile']['error'] == UPLOAD_ERR_OK) {
    $fileTmp = $_FILES['excelFile']['tmp_name'];

    try {
        // Guarantee Database Indexes
        try { $conn->exec("CREATE INDEX idx_tires_num ON ims_tires_number (tires_number)"); } catch (Exception $e) {}
        try { $conn->exec("CREATE INDEX idx_customer_name ON ims_tires_number (customer_name)"); } catch (Exception $e) {}
        try { $conn->exec("CREATE INDEX idx_doc_id ON ims_tires_number (doc_id)"); } catch (Exception $e) {}

        // Use ReadDataOnly Reader
        $reader = IOFactory::createReaderForFile($fileTmp);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($fileTmp);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        // Generate Doc ID
        $cond = " WHERE DATE(create_date) = CURDATE() AND doc_user_id = '" . $doc_user_id . "'";
        $run_no = function_exists('LAST_DOCUMENT_NUMBER') ? LAST_DOCUMENT_NUMBER($conn, "doc_id", "ims_tires_number", $cond) : 1;
        $doc_id = $doc_user_id . "-" . date("Ymd") . "-" . sprintf('%06s', $run_no);

        $importedRows = 0;
        $duplicateRows = 0;

        // Fetch existing tires_number in bulk into memory
        $stmt_exist = $conn->query("SELECT customer_name, send_date_format, tires_brand, tires_size, tires_model, tires_number, tires_year FROM ims_tires_number");
        $existing_records = [];
        while ($r = $stmt_exist->fetch(PDO::FETCH_ASSOC)) {
            $key = implode('|', [
                trim($r['customer_name']),
                trim($r['send_date_format']),
                trim($r['tires_brand']),
                trim($r['tires_size']),
                trim($r['tires_model']),
                trim($r['tires_number']),
                trim($r['tires_year'])
            ]);
            $existing_records[$key] = true;
        }

        // Prepare Insert Statement
        $stmt_insert = $conn->prepare("
            INSERT INTO ims_tires_number 
                (doc_id, doc_date, send_date, send_date_format, customer_name, tires_brand, tires_size, tires_model, tires_number, tires_year, doc_user_id) 
            VALUES 
                (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $conn->beginTransaction();

        foreach ($rows as $index => $row) {
            if ($index == 0) continue;

            $customer_name = (isset($row[1]) && $row[1] !== "") ? trim($row[1]) : null;
            $send_date_excel = (isset($row[2]) && $row[2] !== "") ? trim($row[2]) : null;
            $send_date_processed = convertThaiDateToStandard($send_date_excel);

            $tires_brand = (isset($row[3]) && $row[3] !== "") ? trim($row[3]) : null;
            $tires_size = (isset($row[4]) && $row[4] !== "") ? trim($row[4]) : null;
            $tires_model = (isset($row[5]) && $row[5] !== "") ? trim($row[5]) : null;
            $tires_number = (isset($row[6]) && $row[6] !== "") ? trim($row[6]) : null;
            $tires_year = (isset($row[7]) && $row[7] !== "") ? trim($row[7]) : null;

            if (empty($tires_number) || empty($tires_brand)) {
                $duplicateRows++;
                continue;
            }

            $check_key = implode('|', [
                $customer_name,
                $send_date_processed,
                $tires_brand,
                $tires_size,
                $tires_model,
                $tires_number,
                $tires_year
            ]);

            if (!isset($existing_records[$check_key])) {
                $stmt_insert->execute([
                    $doc_id,
                    $current_date,
                    $send_date_excel,
                    $send_date_processed,
                    $customer_name,
                    $tires_brand,
                    $tires_size,
                    $tires_model,
                    $tires_number,
                    $tires_year,
                    $doc_user_id
                ]);
                $existing_records[$check_key] = true;
                $importedRows++;
            } else {
                $duplicateRows++;
            }
        }

        $conn->commit();

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'doc_id' => $doc_id,
            'count' => count($rows) - 1,
            'imported' => $importedRows,
            'duplicates' => $duplicateRows,
            'message' => "Imported: $importedRows, Duplicates/Skipped: $duplicateRows. Document ID: $doc_id"
        ]);

    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => false,
            'message' => 'Error processing file: ' . $e->getMessage()
        ]);
    }

} else {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'No file uploaded or upload error.'
    ]);
}