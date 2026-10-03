<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Remove execution limits
set_time_limit(0);
ini_set('memory_limit', '2048M');
ini_set('upload_max_filesize', '128M');
ini_set('post_max_size', '128M');

// Include files
include __DIR__ . '/../config/connect_db.php';
require __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\IOFactory;

header('Content-Type: application/json; charset=UTF-8');

if (!isset($_FILES['excelFile']['name']) || $_FILES['excelFile']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'ไม่พบไฟล์ที่อัปโหลด หรือเกิดข้อผิดพลาดในการส่งไฟล์']);
    exit;
}

$log_id = 0;

try {
    $file_Upload = $_FILES['excelFile']['name'];
    $fileTmp = $_FILES['excelFile']['tmp_name'];

    // Validate MIME type
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $fileType = finfo_file($finfo, $fileTmp);
    finfo_close($finfo);

    $allowedTypes = [
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'application/vnd.ms-excel',
        'application/octet-stream',
        'application/zip',
        'application/x-zip'
    ];

    if (!in_array($fileType, $allowedTypes)) {
        throw new Exception("รูปแบบไฟล์ไม่ถูกต้อง ($fileType) กรุณาเลือกไฟล์ .xlsx หรือ .xls");
    }

    // Read Excel
    $inputFileType = IOFactory::identify($fileTmp);
    $reader = IOFactory::createReader($inputFileType);
    $reader->setReadDataOnly(true);
    $spreadsheet = $reader->load($fileTmp);
    $worksheet = $spreadsheet->getActiveSheet();
    $rows = $worksheet->toArray();

    if (empty($rows) || count($rows) < 2) {
        throw new Exception("ไฟล์ Excel ไม่มีข้อมูลสำหรับการอัปเดต");
    }

    // Detect Header Indices for 3 main fields (SKU_CODE, BRAND, SKU_CAT)
    $headerRow = $rows[0];
    $idx_sku_code = -1;
    $idx_brand    = -1;
    $idx_sku_cat  = -1;
    $idx_sku_name = -1;
    $idx_detail   = -1;

    foreach ($headerRow as $cIdx => $hVal) {
        $hVal = trim((string)$hVal);
        if (strpos($hVal, 'รหัสสินค้า') !== false || stripos($hVal, 'sku_code') !== false) {
            $idx_sku_code = $cIdx;
        } elseif (strpos($hVal, 'ยี่ห้อ') !== false || stripos($hVal, 'brand') !== false) {
            $idx_brand = $cIdx;
        } elseif (strpos($hVal, 'ประเภท') !== false || stripos($hVal, 'sku_cat') !== false) {
            $idx_sku_cat = $cIdx;
        } elseif (strpos($hVal, 'ชื่อสินค้า') !== false || stripos($hVal, 'sku_name') !== false) {
            $idx_sku_name = $cIdx;
        } elseif (strpos($hVal, 'รายละเอียด') !== false || stripos($hVal, 'detail') !== false) {
            $idx_detail = $cIdx;
        }
    }

    // Default 3-column layout: Col 0 = SKU_CODE, Col 1 = BRAND, Col 2 = SKU_CAT
    if ($idx_sku_code === -1) $idx_sku_code = 0;
    if ($idx_brand === -1)    $idx_brand = 1;
    if ($idx_sku_cat === -1)  $idx_sku_cat = 2;

    // Config variables
    $user_id = $_SESSION['user_id'] ?? $_SESSION['alogin'] ?? 'SYSTEM';
    $table_name = "ims_data_sale_sac_all";
    $updatetedRows = 0;
    $totalRows = 0;
    $screen_name = "update_brand_sku_cat";

    $seq_record = md5(uniqid(rand(), true));

    // [STEP 1] Insert Log Start
    $sql_log_start = "INSERT INTO log_import_data 
                      (screen_name, detail2, seq_record, create_by, create_date) 
                      VALUES (?, ?, ?, ?, NOW())";
    $stmt_log_start = $conn->prepare($sql_log_start);
    $stmt_log_start->execute([$screen_name, $file_Upload, $seq_record, $user_id]);
    $log_id = $conn->lastInsertId();

    // Prepare Update Statement on ims_data_sale_sac_all
    $sql_update = "UPDATE {$table_name} 
                   SET BRAND = :BRAND, 
                       SKU_CAT = :SKU_CAT, 
                       DETAIL = :DETAIL, 
                       SKU_NAME = CASE WHEN :SKU_NAME_CHECK != '' THEN :SKU_NAME ELSE SKU_NAME END,
                       seq_record = :seq_record, 
                       update_date = NOW() 
                   WHERE SKU_CODE = :SKU_CODE";
    $stmt_update = $conn->prepare($sql_update);

    // Transaction
    $conn->beginTransaction();

    for ($i = 1; $i < count($rows); $i++) {
        $row = $rows[$i];
        $sku_code = isset($row[$idx_sku_code]) ? trim((string)$row[$idx_sku_code]) : '';

        // Skip blank SKU_CODE
        if ($sku_code === '' || $sku_code === '-') {
            continue;
        }

        $totalRows++;

        $sku_name = ($idx_sku_name !== -1 && isset($row[$idx_sku_name])) ? trim((string)$row[$idx_sku_name]) : '';
        $brand    = ($idx_brand !== -1 && isset($row[$idx_brand])) ? trim((string)$row[$idx_brand]) : '';
        $sku_cat  = ($idx_sku_cat !== -1 && isset($row[$idx_sku_cat])) ? trim((string)$row[$idx_sku_cat]) : '';
        $detail   = ($idx_detail !== -1 && isset($row[$idx_detail])) ? trim((string)$row[$idx_detail]) : '';

        // If detail is empty, sync with sku_cat
        if ($detail === '' && $sku_cat !== '') {
            $detail = $sku_cat;
        }

        $params = [
            ':BRAND'          => $brand,
            ':SKU_CAT'        => $sku_cat,
            ':DETAIL'         => $detail,
            ':SKU_NAME_CHECK' => $sku_name,
            ':SKU_NAME'       => $sku_name,
            ':seq_record'     => $seq_record,
            ':SKU_CODE'       => $sku_code
        ];

        $stmt_update->execute($params);

        if ($stmt_update->rowCount() > 0) {
            $updatetedRows += $stmt_update->rowCount();
        }
    }

    $conn->commit();

    // [STEP 2] Optional Sync to ims_product if table exists
    try {
        $sql_sync_prod = "UPDATE ims_product p
                          INNER JOIN (
                              SELECT SKU_CODE, MAX(BRAND) AS BRAND, MAX(SKU_CAT) AS SKU_CAT, MAX(DETAIL) AS DETAIL
                              FROM ims_data_sale_sac_all
                              WHERE seq_record = :seq_record
                              GROUP BY SKU_CODE
                          ) s ON p.SKU_CODE = s.SKU_CODE
                          SET p.brand = s.BRAND, p.sku_cat = s.SKU_CAT";
        $stmt_sync_prod = $conn->prepare($sql_sync_prod);
        $stmt_sync_prod->execute([':seq_record' => $seq_record]);
    } catch (Exception $ex) {
        // Suppress sync errors if columns do not match
    }

    // [STEP 3] Update Log End
    if ($log_id > 0) {
        $sql_log_end = "UPDATE log_import_data SET 
                        total_record = ?, 
                        import_record = ?, 
                        detail1 = ?, 
                        update_date = NOW() 
                        WHERE id = ?";
        $stmt_log_end = $conn->prepare($sql_log_end);
        $stmt_log_end->execute([$totalRows, $updatetedRows, "Updated BRAND, SKU_CAT (3 Fields)", $log_id]);
    }

    echo json_encode([
        'success'   => true,
        'file_name' => $file_Upload,
        'count'     => $totalRows,
        'updated'   => $updatetedRows,
        'message'   => 'นำเข้าและอัปเดตข้อมูลสำเร็จ'
    ]);

} catch (Exception $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()
    ]);
}
