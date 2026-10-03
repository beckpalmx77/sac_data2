<?php
session_start();
include('config/connect_db.php');

if (!isset($_SESSION['alogin']) || $_SESSION['alogin'] == '') {
    echo "Unauthorized access. Please login first.";
    exit;
}

try {
    $conn->beginTransaction();

    $sql = "UPDATE ims_tires_plan_forecast p
            INNER JOIN (
                SELECT 
                    AR_CODE, 
                    MAX(SALE_NAME) AS SALE_NAME, 
                    MAX(TAKE_NAME) AS TAKE_NAME, 
                    MAX(TRD_AMPHUR) AS TRD_AMPHUR, 
                    MAX(TRD_PROVINCE) AS TRD_PROVINCE
                FROM ims_data_sale_sac_all
                GROUP BY AR_CODE
            ) s ON p.ar_code = s.AR_CODE
            SET 
                p.sale_name = s.SALE_NAME,
                p.take_name = s.TAKE_NAME,
                p.amphur = s.TRD_AMPHUR,
                p.province = s.TRD_PROVINCE";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $affected = $stmt->rowCount();

    $conn->commit();
    echo "<div style='font-family: Arial, sans-serif; padding: 20px; background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; border-radius: 5px; margin: 20px;'>
            <h3><i class='fa fa-check-circle'></i> Synchronization Completed Successfully!</h3>
            <p>อัปเดตข้อมูลรายละเอียดร้านค้า (พนักงานขาย, คนเทค, อำเภอ, จังหวัด) ไปยังตารางวางแผนการขายยางเรียบร้อยแล้ว</p>
            <p><strong>จำนวนรายการที่ได้รับผลกระทบ:</strong> " . number_format($affected) . " แถว</p>
            <a href='forecast_plan_display_new.php' style='display: inline-block; padding: 10px 15px; background-color: #198754; color: white; text-decoration: none; border-radius: 4px; margin-top: 10px;'>กลับไปยังตารางวางแผนการขาย</a>
          </div>";

} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "<div style='font-family: Arial, sans-serif; padding: 20px; background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; border-radius: 5px; margin: 20px;'>
            <h3><i class='fa fa-exclamation-triangle'></i> Error!</h3>
            <p>เกิดข้อผิดพลาดในการประมวลผลข้อมูล</p>
            <p><strong>รายละเอียดข้อผิดพลาด:</strong> " . $e->getMessage() . "</p>
          </div>";
}
?>
