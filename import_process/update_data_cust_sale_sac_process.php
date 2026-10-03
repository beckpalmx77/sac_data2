<?php
session_start();
error_reporting(0);

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

    if (!move_uploaded_file($fileTmp, $uploadFile)) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => false, 'message' => 'Upload file failed.']);
        exit;
    }

    try {
        // Guarantee database indexes
        try { $conn->exec("CREATE INDEX idx_ar_cat_brand ON ims_data_sale_sac_all (AR_CODE, SKU_CAT, BRAND)"); } catch (Exception $e) {}
        try { $conn->exec("CREATE INDEX idx_ar_code ON ims_data_sale_sac_all (AR_CODE)"); } catch (Exception $e) {}
        try { $conn->exec("CREATE INDEX idx_ar_code ON ims_tires_plan_forecast (ar_code)"); } catch (Exception $e) {}

        $reader = IOFactory::createReaderForFile($uploadFile);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($uploadFile);
        $rows = $spreadsheet->getActiveSheet()->toArray();

        $user_id = $_SESSION['user_id'];
        $table_name = "ims_data_sale_sac_all";
        $totalRows = 0;
        $screen_name = "update_data_cust_sale";
        $seq_record = md5(rand());

        // In-memory deduplication
        $dataByCompositeKey = [];
        foreach ($rows as $index => $row) {
            if ($index == 0) continue;

            $isEmptyRow = true;
            foreach ($row as $cell) {
                if (trim($cell) !== '') {
                    $isEmptyRow = false;
                    break;
                }
            }
            if ($isEmptyRow) continue;

            $totalRows++;

            $AR_CODE = isset($row[0]) ? trim($row[0]) : "";
            if ($AR_CODE === "") continue;

            $SKU_CAT = isset($row[6]) ? trim($row[6]) : "";
            $BRAND = isset($row[7]) ? trim($row[7]) : "";

            $composite_key = "{$AR_CODE}|{$SKU_CAT}|{$BRAND}";

            $dataByCompositeKey[$composite_key] = [
                'AR_CODE'      => $AR_CODE,
                'AR_NAME'      => isset($row[1]) ? trim($row[1]) : "",
                'SALE_NAME'    => isset($row[2]) ? trim($row[2]) : "",
                'TAKE_NAME'    => isset($row[3]) ? trim($row[3]) : "",
                'TRD_AMPHUR'   => isset($row[4]) ? trim($row[4]) : "",
                'TRD_PROVINCE' => isset($row[5]) ? trim($row[5]) : "",
                'SKU_CAT'      => $SKU_CAT,
                'BRAND'        => $BRAND
            ];
        }

        // Create Temporary Table
        $conn->exec("CREATE TEMPORARY TABLE IF NOT EXISTS tmp_cust_sale_update (
            AR_CODE VARCHAR(100),
            AR_NAME VARCHAR(255),
            SALE_NAME VARCHAR(255),
            TAKE_NAME VARCHAR(255),
            TRD_AMPHUR VARCHAR(255),
            TRD_PROVINCE VARCHAR(255),
            SKU_CAT VARCHAR(100),
            BRAND VARCHAR(100),
            PRIMARY KEY (AR_CODE, SKU_CAT, BRAND)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8;");

        $conn->exec("TRUNCATE TABLE tmp_cust_sale_update;");

        // Multi-row chunk insert into Temp Table
        $chunk = [];
        $chunkSize = 200;
        foreach ($dataByCompositeKey as $d) {
            $chunk[] = [$d['AR_CODE'], $d['AR_NAME'], $d['SALE_NAME'], $d['TAKE_NAME'], $d['TRD_AMPHUR'], $d['TRD_PROVINCE'], $d['SKU_CAT'], $d['BRAND']];
            if (count($chunk) >= $chunkSize) {
                insertChunkCustSaleData($conn, $chunk);
                $chunk = [];
            }
        }
        if (!empty($chunk)) {
            insertChunkCustSaleData($conn, $chunk);
        }

        $conn->beginTransaction();

        // Single Bulk JOIN UPDATE
        $sql_update = "UPDATE ims_data_sale_sac_all t
                       INNER JOIN tmp_cust_sale_update tmp 
                          ON t.AR_CODE = tmp.AR_CODE 
                         AND t.SKU_CAT = tmp.SKU_CAT 
                         AND t.BRAND = tmp.BRAND
                       SET 
                           t.AR_NAME = tmp.AR_NAME,
                           t.SALE_NAME = tmp.SALE_NAME,
                           t.TAKE_NAME = tmp.TAKE_NAME,
                           t.TRD_AMPHUR = tmp.TRD_AMPHUR,
                           t.TRD_PROVINCE = tmp.TRD_PROVINCE,
                           t.seq_record = :seq_record";
        $stmt_update = $conn->prepare($sql_update);
        $stmt_update->execute([':seq_record' => $seq_record]);
        $updatedRows = $stmt_update->rowCount();

        // Scoped Sync to ims_tires_plan_forecast
        $sql_sync_forecast = "UPDATE ims_tires_plan_forecast p
                              INNER JOIN (
                                  SELECT AR_CODE, MAX(AR_NAME) AS AR_NAME, MAX(SALE_NAME) AS SALE_NAME, MAX(TAKE_NAME) AS TAKE_NAME, MAX(TRD_AMPHUR) AS TRD_AMPHUR, MAX(TRD_PROVINCE) AS TRD_PROVINCE
                                  FROM tmp_cust_sale_update
                                  GROUP BY AR_CODE
                              ) s ON p.ar_code = s.AR_CODE
                              SET 
                                  p.ar_name = s.AR_NAME,
                                  p.sale_name = s.SALE_NAME,
                                  p.take_name = s.TAKE_NAME,
                                  p.amphur = s.TRD_AMPHUR,
                                  p.province = s.TRD_PROVINCE";
        $stmt_sync = $conn->prepare($sql_sync_forecast);
        $stmt_sync->execute();

        $conn->commit();

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success' => true,
            'file_name' => $file_Upload,
            'status' => 'Upload File สำเร็จ',
            'count' => $totalRows,
            'updated' => $updatedRows
        ]);

    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
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

function insertChunkCustSaleData($conn, $data) {
    $placeholders = [];
    $values = [];
    foreach ($data as $r) {
        $placeholders[] = "(?, ?, ?, ?, ?, ?, ?, ?)";
        array_push($values, $r[0], $r[1], $r[2], $r[3], $r[4], $r[5], $r[6], $r[7]);
    }
    $sql = "INSERT INTO tmp_cust_sale_update (AR_CODE, AR_NAME, SALE_NAME, TAKE_NAME, TRD_AMPHUR, TRD_PROVINCE, SKU_CAT, BRAND) VALUES " . implode(", ", $placeholders);
    $stmt = $conn->prepare($sql);
    $stmt->execute($values);
}
