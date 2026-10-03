<?php
session_start();
error_reporting(0);

// Include necessary files for database connection and PhpSpreadsheet
include '../config/connect_db.php'; // Include database connection
require '../vendor/autoload.php'; // Load PhpSpreadsheet library
include '../util/record_util.php';
include '../util/month_convert_util.php';
include '../util/check_format_number.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

if (isset($_FILES['excelFile']['name']) && $_FILES['excelFile']['error'] == UPLOAD_ERR_OK) {
    $file_Upload = $_FILES['excelFile']['name'];
    $fileTmp = $_FILES['excelFile']['tmp_name'];
    $fileType = mime_content_type($fileTmp);

    $uploadDir = '../uploads/';
    $uploadFile = $uploadDir . basename($file_Upload);

    if ($fileType !== 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet' &&
        $fileType !== 'application/vnd.ms-excel') {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Invalid file type.']);
        exit;
    }

    if (move_uploaded_file($fileTmp, $uploadFile)) {
        $upload_status = "Upload File สำเร็จ";
    } else {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'ผิดพลาด Upload File ไม่สำเร็จ']);
        exit;
    }

    try {
        // 0. Create missing database indexes for high performance lookup
        try { $conn->exec("CREATE INDEX idx_di_ref ON ims_data_sale_sac_all (DI_REF)"); } catch (Exception $e) {}
        try { $conn->exec("CREATE INDEX idx_ar_code ON ims_data_sale_sac_all (AR_CODE)"); } catch (Exception $e) {}
        try { $conn->exec("CREATE INDEX idx_ar_code ON ims_tires_plan_forecast (ar_code)"); } catch (Exception $e) {}

        // 1. High performance Excel reading (Data only, no formatting overhead)
        $reader = IOFactory::createReaderForFile($uploadFile);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($uploadFile);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        $user_id = $_SESSION['user_id'] ?? '';
        $table_name = "ims_data_sale_sac_all";
        $screen_name = "update_sale_take_doc";
        $seq_record = md5(rand());

        // 2. In-memory Deduplication by DI_REF
        $dataByRef = [];
        $totalRows = 0;

        foreach ($rows as $index => $row) {
            if ($index == 0) continue; // Skip header row

            $DI_REF = isset($row[0]) ? trim($row[0]) : "";
            if ($DI_REF === "") continue;

            $totalRows++;
            $dataByRef[$DI_REF] = [
                'AR_NAME'      => isset($row[2]) ? trim($row[2]) : "",
                'SALE_NAME'    => isset($row[3]) ? trim($row[3]) : "",
                'TAKE_NAME'    => isset($row[4]) ? trim($row[4]) : "",
                'TRD_AMPHUR'   => isset($row[5]) ? trim($row[5]) : "",
                'TRD_PROVINCE' => isset($row[6]) ? trim($row[6]) : ""
            ];
        }

        if (empty($dataByRef)) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => true,
                'file_name' => $file_Upload,
                'status' => $upload_status,
                'count' => 0,
                'updated' => 0
            ]);
            exit;
        }

        // 3. Create Temporary Table for Bulk Processing
        $conn->exec("CREATE TEMPORARY TABLE IF NOT EXISTS tmp_sale_import (
            DI_REF VARCHAR(100) PRIMARY KEY,
            AR_NAME VARCHAR(255),
            SALE_NAME VARCHAR(255),
            TAKE_NAME VARCHAR(255),
            TRD_AMPHUR VARCHAR(100),
            TRD_PROVINCE VARCHAR(100)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $conn->exec("TRUNCATE TABLE tmp_sale_import;");

        // Insert deduplicated rows into Temp Table in chunks of 200
        $chunk = [];
        $chunkSize = 200;
        foreach ($dataByRef as $di_ref => $d) {
            $chunk[] = [$di_ref, $d['AR_NAME'], $d['SALE_NAME'], $d['TAKE_NAME'], $d['TRD_AMPHUR'], $d['TRD_PROVINCE']];
            if (count($chunk) >= $chunkSize) {
                insertChunkData($conn, $chunk);
                $chunk = [];
            }
        }
        if (!empty($chunk)) {
            insertChunkData($conn, $chunk);
        }

        // 4. Perform High-Performance JOIN UPDATE
        $sql_update_all = "UPDATE ims_data_sale_sac_all t
                           INNER JOIN tmp_sale_import tmp ON t.DI_REF = tmp.DI_REF
                           SET 
                               t.AR_NAME = tmp.AR_NAME,
                               t.SALE_NAME = tmp.SALE_NAME,
                               t.TAKE_NAME = tmp.TAKE_NAME,
                               t.TRD_AMPHUR = tmp.TRD_AMPHUR,
                               t.TRD_PROVINCE = tmp.TRD_PROVINCE,
                               t.seq_record = :seq_record";
        $stmt_upd = $conn->prepare($sql_update_all);
        $stmt_upd->execute([':seq_record' => $seq_record]);
        $updatedRows = $stmt_upd->rowCount();

        // 5. Optimized Scoped Forecast Sync (targets updated batch only)
        $sql_sync_forecast = "UPDATE ims_tires_plan_forecast p
                              INNER JOIN (
                                  SELECT 
                                      AR_CODE, 
                                      MAX(SALE_NAME) AS SALE_NAME, 
                                      MAX(TAKE_NAME) AS TAKE_NAME, 
                                      MAX(TRD_AMPHUR) AS TRD_AMPHUR, 
                                      MAX(TRD_PROVINCE) AS TRD_PROVINCE
                                  FROM ims_data_sale_sac_all
                                  WHERE seq_record = :seq_record
                                  GROUP BY AR_CODE
                              ) s ON p.ar_code = s.AR_CODE
                              SET 
                                  p.sale_name = s.SALE_NAME,
                                  p.take_name = s.TAKE_NAME,
                                  p.amphur = s.TRD_AMPHUR,
                                  p.province = s.TRD_PROVINCE";
        $stmt_sync = $conn->prepare($sql_sync_forecast);
        $stmt_sync->execute([':seq_record' => $seq_record]);

        // 6. Insert log record
        if ($updatedRows > 0) {
            $sql_insert_log = "INSERT INTO log_import_data (screen_name,total_record,import_record,detail1,detail2,seq_record,create_by) VALUES (?,?,?,?,?,?,?)";
            $stmt_insert_log = $conn->prepare($sql_insert_log);
            $stmt_insert_log->execute([$screen_name, $totalRows, $updatedRows, $updatedRows, $file_Upload, $seq_record, $user_id]);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'file_name' => $file_Upload,
            'status' => $upload_status,
            'count' => $totalRows,
            'updated' => $updatedRows
        ]);

    } catch (Exception $e) {
        error_log("Error processing file: " . $e->getMessage());
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
        'message' => 'Error uploading file.'
    ]);
}

function insertChunkData($conn, $data) {
    $placeholders = [];
    $values = [];
    foreach ($data as $r) {
        $placeholders[] = "(?, ?, ?, ?, ?, ?)";
        array_push($values, $r[0], $r[1], $r[2], $r[3], $r[4], $r[5]);
    }
    $sql = "INSERT INTO tmp_sale_import (DI_REF, AR_NAME, SALE_NAME, TAKE_NAME, TRD_AMPHUR, TRD_PROVINCE) VALUES " . implode(", ", $placeholders);
    $stmt = $conn->prepare($sql);
    $stmt->execute($values);
}
