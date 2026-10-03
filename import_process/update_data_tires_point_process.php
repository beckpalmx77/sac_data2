<?php
session_start();
error_reporting(0);

// Include necessary files for database connection and PhpSpreadsheet
include '../config/connect_db.php';
require '../vendor/autoload.php';
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
        // 0. Ensure missing database indexes exist for high-speed operation
        try { $conn->exec("CREATE INDEX idx_sku_code ON ims_sac_tires_point (SKU_CODE)"); } catch (Exception $e) {}
        try { $conn->exec("CREATE INDEX idx_sku_code ON ims_data_sale_sac_all (SKU_CODE)"); } catch (Exception $e) {}

        // 1. Light-weight Excel Reading (Data only)
        $reader = IOFactory::createReaderForFile($uploadFile);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($uploadFile);
        $worksheet = $spreadsheet->getActiveSheet();
        $rows = $worksheet->toArray();

        $user_id = $_SESSION['user_id'] ?? '';
        $table_name = "ims_sac_tires_point";
        $screen_name = "update_sac_tires_point";
        $seq_record = md5(rand());

        // 2. In-memory Deduplication by SKU_CODE
        $dataBySku = [];
        $totalRows = 0;

        foreach ($rows as $index => $row) {
            if ($index == 0) continue; // Skip header row

            $SKU_CODE = isset($row[0]) && trim($row[0]) !== "-" && trim($row[0]) !== "" ? trim($row[0]) : "";
            if ($SKU_CODE === "") continue;

            $totalRows++;
            $SKU_NAME = isset($row[1]) && trim($row[1]) !== "-" && trim($row[1]) !== "" ? trim($row[1]) : "-";
            $BRAND = isset($row[2]) && trim($row[2]) !== "-" && trim($row[2]) !== "" ? strtoupper(trim($row[2])) : "-";
            $SKU_CAT = isset($row[3]) && trim($row[3]) !== "-" && trim($row[3]) !== "" ? trim($row[3]) : "-";
            $TIRES_EDGE = isset($row[4]) && trim($row[4]) !== "-" && trim($row[4]) !== "" ? trim($row[4]) : "-";
            $TRD_U_POINT = isset($row[5]) && trim($row[5]) !== "" && trim($row[5]) !== "-" && is_numeric(trim($row[5])) ? trim($row[5]) : 0;
            $TRD_S_POINT = isset($row[6]) && trim($row[6]) !== "" && trim($row[6]) !== "-" && is_numeric(trim($row[6])) ? trim($row[6]) : 0;

            $dataBySku[$SKU_CODE] = [
                'SKU_NAME'   => $SKU_NAME,
                'BRAND'      => $BRAND,
                'SKU_CAT'    => $SKU_CAT,
                'TIRES_EDGE' => $TIRES_EDGE,
                'TRD_U_POINT'=> $TRD_U_POINT,
                'TRD_S_POINT'=> $TRD_S_POINT
            ];
        }

        if (empty($dataBySku)) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode([
                'success' => true,
                'file_name' => $file_Upload,
                'status' => $upload_status,
                'count' => 0,
                'updated' => 0,
                'inserted' => 0,
                'transaction_updated' => 0
            ]);
            exit;
        }

        // 3. Create Temporary Table for Bulk Processing
        $conn->exec("CREATE TEMPORARY TABLE IF NOT EXISTS tmp_tires_point_import (
            SKU_CODE VARCHAR(100) PRIMARY KEY,
            SKU_NAME VARCHAR(255),
            BRAND VARCHAR(100),
            SKU_CAT VARCHAR(100),
            TIRES_EDGE VARCHAR(100),
            TRD_U_POINT DECIMAL(10,2),
            TRD_S_POINT DECIMAL(10,2)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $conn->exec("TRUNCATE TABLE tmp_tires_point_import;");

        // Insert deduplicated rows into Temp Table in chunks of 200
        $chunk = [];
        $chunkSize = 200;
        foreach ($dataBySku as $sku_code => $d) {
            $chunk[] = [$sku_code, $d['SKU_NAME'], $d['BRAND'], $d['SKU_CAT'], $d['TIRES_EDGE'], $d['TRD_U_POINT'], $d['TRD_S_POINT']];
            if (count($chunk) >= $chunkSize) {
                insertChunkData($conn, $chunk);
                $chunk = [];
            }
        }
        if (!empty($chunk)) {
            insertChunkData($conn, $chunk);
        }

        // Check how many SKUs already exist in master table
        $existingSkus = [];
        $stmt_check = $conn->prepare("SELECT SKU_CODE FROM ims_sac_tires_point WHERE SKU_CODE IN (SELECT SKU_CODE FROM tmp_tires_point_import)");
        $stmt_check->execute();
        $existingSkus = $stmt_check->fetchAll(PDO::FETCH_COLUMN);

        $existingCount = count($existingSkus);
        $totalImportCount = count($dataBySku);
        $updatedCount = min($totalImportCount, $existingCount);
        $insertedCount = max(0, $totalImportCount - $existingCount);

        // 4. Bulk UPSERT to ims_sac_tires_point
        $sql_upsert_master = "INSERT INTO ims_sac_tires_point (SKU_CODE, SKU_NAME, BRAND, SKU_CAT, TIRES_EDGE, TRD_U_POINT, TRD_S_POINT)
                              SELECT SKU_CODE, SKU_NAME, BRAND, SKU_CAT, TIRES_EDGE, TRD_U_POINT, TRD_S_POINT FROM tmp_tires_point_import
                              ON DUPLICATE KEY UPDATE 
                                  SKU_NAME = VALUES(SKU_NAME),
                                  BRAND = VALUES(BRAND),
                                  SKU_CAT = VALUES(SKU_CAT),
                                  TIRES_EDGE = VALUES(TIRES_EDGE),
                                  TRD_U_POINT = VALUES(TRD_U_POINT),
                                  TRD_S_POINT = VALUES(TRD_S_POINT)";
        $stmt_upsert = $conn->prepare($sql_upsert_master);
        $stmt_upsert->execute();

        // 5. Bulk JOIN UPDATE to ims_data_sale_sac_all
        $sql_update_sale_all = "UPDATE ims_data_sale_sac_all t
                                INNER JOIN tmp_tires_point_import tmp ON t.SKU_CODE = tmp.SKU_CODE
                                SET 
                                    t.SKU_NAME = tmp.SKU_NAME,
                                    t.BRAND = tmp.BRAND,
                                    t.SKU_CAT = tmp.SKU_CAT,
                                    t.TIRES_EDGE = tmp.TIRES_EDGE";
        $stmt_sale_upd = $conn->prepare($sql_update_sale_all);
        $stmt_sale_upd->execute();
        $sale_updated_count = $stmt_sale_upd->rowCount();

        // 6. Insert log record
        if ($totalRows > 0) {
            $log_detail = "Import Excel: {$totalRows} รายการ, Update: {$updatedCount}, Insert: {$insertedCount}, Trans Updated: {$sale_updated_count}";
            $sql_insert_log = "INSERT INTO log_import_data (screen_name,total_record,import_record,detail1,detail2,seq_record,create_by) VALUES (?,?,?,?,?,?,?)";
            $stmt_insert_log = $conn->prepare($sql_insert_log);
            $stmt_insert_log->execute([$screen_name, $totalRows, $totalImportCount, $log_detail, $file_Upload, $seq_record, $user_id]);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'file_name' => $file_Upload,
            'status' => $upload_status,
            'count' => $totalRows,
            'updated' => $updatedCount,
            'inserted' => $insertedCount,
            'transaction_updated' => $sale_updated_count
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
        $placeholders[] = "(?, ?, ?, ?, ?, ?, ?)";
        array_push($values, $r[0], $r[1], $r[2], $r[3], $r[4], $r[5], $r[6]);
    }
    $sql = "INSERT INTO tmp_tires_point_import (SKU_CODE, SKU_NAME, BRAND, SKU_CAT, TIRES_EDGE, TRD_U_POINT, TRD_S_POINT) VALUES " . implode(", ", $placeholders);
    $stmt = $conn->prepare($sql);
    $stmt->execute($values);
}
