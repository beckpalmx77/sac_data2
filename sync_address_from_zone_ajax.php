<?php
session_start();
header('Content-Type: application/json');
include('config/connect_db.php');

// ตรวจสอบสิทธิ์การเข้าใช้งาน
if (!isset($_SESSION['alogin']) || $_SESSION['alogin'] == '') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access. Please login first.']);
    exit;
}

try {
    $conn->beginTransaction();

    // 1. SQL Query สำหรับอัปเดตข้อมูลที่อยู่
    $sql = "UPDATE ims_tires_plan_forecast p
            INNER JOIN v_customer_data_sale_all_zone v ON p.ar_code = v.AR_CODE
            SET 
                p.amphur = v.TRD_AMPHUR,
                p.province = v.TRD_PROVINCE
            WHERE (v.TRD_AMPHUR IS NOT NULL AND v.TRD_AMPHUR != '')
               OR (v.TRD_PROVINCE IS NOT NULL AND v.TRD_PROVINCE != '')";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $affected_addr = $stmt->rowCount();

    // 2. SQL Query สำหรับอัปเดตข้อมูลพนักงานขาย และ คนเทค
    $sql_sale = "UPDATE ims_tires_plan_forecast p
                 INNER JOIN v_sale_cust_sac v ON p.ar_code = v.AR_CODE
                 SET 
                     p.sale_name = v.SALE_NAME,
                     p.take_name = v.TAKE_NAME
                 WHERE (v.SALE_NAME IS NOT NULL AND v.SALE_NAME != '')
                    OR (v.TAKE_NAME IS NOT NULL AND v.TAKE_NAME != '')";

    $stmt_sale = $conn->prepare($sql_sale);
    $stmt_sale->execute();
    $affected_sale = $stmt_sale->rowCount();

    $affected = max($affected_addr, $affected_sale);

    $conn->commit();

    echo json_encode([
        'success' => true,
        'affected_rows' => $affected
    ]);

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>
