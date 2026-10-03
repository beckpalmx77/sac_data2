<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include(__DIR__ . '/../config/connect_db.php');

header('Content-Type: application/json; charset=UTF-8');
date_default_timezone_set('Asia/Bangkok');

if (!isset($_SESSION['alogin']) || $_SESSION['alogin'] == '') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized access']);
    exit;
}

try {
    $sql = "
        SELECT DISTINCT TRIM(s.SALE_NAME) AS SALE_NAME 
        FROM ims_data_sale_sac_all s
        WHERE s.SALE_NAME IS NOT NULL AND TRIM(s.SALE_NAME) != '' AND TRIM(s.SALE_NAME) != '-'
          AND s.SALE_NAME NOT LIKE '%BTC%' 
          AND s.SALE_NAME NOT LIKE '%RQ%'
          AND s.SALE_NAME NOT LIKE '%ส่วนกลาง%' 
          AND s.SALE_NAME NOT LIKE '%READY%QUICK%'
          AND s.BRAND IN ('VB', 'ATLAS', 'AT', 'BS/FS/DT', 'BS/FS', 'BS', '')
          AND (s.SKU_CAT IN ('TBR/LTR (ยางใหญ่)', 'LSR', 'TBB/LTB (ยางผ้าใบ)', 'TBR') OR TRIM(s.SKU_CAT) IN ('TBR/LTR (ยางใหญ่)', 'LSR', 'TBB/LTB (ยางผ้าใบ)', 'TBR'))
          AND s.DI_REF NOT LIKE 'DS%'
        ORDER BY s.SALE_NAME ASC
    ";
    $stmt = $conn->query($sql);
    $sales_list = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo json_encode([
        'status' => 'success',
        'data' => $sales_list,
        'count' => count($sales_list)
    ]);
} catch (Exception $e) {
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
}
