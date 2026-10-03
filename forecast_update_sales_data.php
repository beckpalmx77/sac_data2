<?php
session_start();
include('config/connect_db.php');

if (!isset($_SESSION['alogin']) || $_SESSION['alogin'] == '') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$month = $_POST['month'] ?? '';
$year = $_POST['year'] ?? '';

if (empty($month) || empty($year)) {
    echo json_encode(['success' => false, 'message' => 'Missing month or year']);
    exit;
}

try {
    $conn->beginTransaction();

    // 1. Calculate months back
    $m1 = $month - 1; $y1 = $year; if ($m1 <= 0) { $m1 += 12; $y1--; }
    $m2 = $month - 2; $y2 = $year; if ($m2 <= 0) { $m2 += 12; $y2--; }
    $m3 = $month - 3; $y3 = $year; if ($m3 <= 0) { $m3 += 12; $y3--; }
    $ly = $year - 1;

    // 2. Perform a massive update using JOIN with subqueries
    $sql = "UPDATE ims_tires_plan_forecast p
    LEFT JOIN (
        SELECT AR_CODE, SKU_CODE, 
            SUM(CASE WHEN DI_MONTH = :m1 AND DI_YEAR = :y1 THEN CAST(TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as s1,
            SUM(CASE WHEN DI_MONTH = :m2 AND DI_YEAR = :y2 THEN CAST(TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as s2,
            SUM(CASE WHEN DI_MONTH = :m3 AND DI_YEAR = :y3 THEN CAST(TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) as s3,
            SUM(CASE WHEN DI_YEAR = :ly THEN CAST(TRD_QTY AS DECIMAL(10,2)) ELSE 0 END) / 12 as avg_ly
        FROM ims_data_sale_sac_all
        WHERE (DI_YEAR = :y1 AND DI_MONTH = :m1) 
           OR (DI_YEAR = :y2 AND DI_MONTH = :m2) 
           OR (DI_YEAR = :y3 AND DI_MONTH = :m3)
           OR (DI_YEAR = :ly)
        GROUP BY AR_CODE, SKU_CODE
    ) s ON p.ar_code = s.AR_CODE AND p.sku_code = s.SKU_CODE
    SET 
        p.sales_month_1 = IFNULL(s.s1, 0),
        p.sales_month_2 = IFNULL(s.s2, 0),
        p.sales_month_3 = IFNULL(s.s3, 0),
        p.avg_per_month_year = IFNULL(s.avg_ly, 0),
        p.avg_3_month = (IFNULL(s.s1, 0) + IFNULL(s.s2, 0) + IFNULL(s.s3, 0)) / 3,
        p.compare_forecast_month_1 = p.forecast_qty_month_1 - ((IFNULL(s.s1, 0) + IFNULL(s.s2, 0) + IFNULL(s.s3, 0)) / 3),
        p.compare_sales_percent = CASE 
            WHEN (IFNULL(s.s1, 0) + IFNULL(s.s2, 0) + IFNULL(s.s3, 0)) / 3 = 0 THEN 0 
            ELSE ((p.forecast_qty_month_1 - ((IFNULL(s.s1, 0) + IFNULL(s.s2, 0) + IFNULL(s.s3, 0)) / 3)) / ((IFNULL(s.s1, 0) + IFNULL(s.s2, 0) + IFNULL(s.s3, 0)) / 3)) * 100
        END,
        p.load_time = p.load_time + 1
    WHERE p.forcast_month = :month AND p.forcast_year = :year";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':m1' => $m1, ':y1' => $y1,
        ':m2' => $m2, ':y2' => $y2,
        ':m3' => $m3, ':y3' => $y3,
        ':ly' => $ly,
        ':month' => $month,
        ':year' => $year
    ]);

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'ปรับปรุงข้อมูลยอดขายเรียบร้อยแล้ว']);

} catch (Exception $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
