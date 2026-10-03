<?php
session_start();
include('config/connect_db.php');

// ตรวจสอบสิทธิ์การเข้าใช้งาน
if (!isset($_SESSION['alogin']) || $_SESSION['alogin'] == '') {
    echo "Unauthorized access. Please login first.";
    exit;
}

try {
    // เริ่มต้น Transaction เพื่อความปลอดภัยของข้อมูล
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

    // บันทึกการทำรายการสำเร็จ
    $conn->commit();

    echo "<div style='font-family: Prompt, Arial, sans-serif; padding: 25px; background-color: #d1e7dd; color: #0f5132; border: 1px solid #badbcc; border-radius: 8px; margin: 30px auto; max-width: 600px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);'>
            <h3 style='margin-top: 0;'><i class='fa fa-check-circle'></i> การซิงก์ข้อมูลสำเร็จ (Sync Address Completed)!</h3>
            <p>ปรับปรุงข้อมูลที่อยู่ลูกค้า (อำเภอ, จังหวัด) ในตารางวางแผน <code>ims_tires_plan_forecast</code> จากข้อมูลอ้างอิงของระบบเรียบร้อยแล้ว</p>
            <p><strong>จำนวนรายการที่ทำการแก้ไข:</strong> " . number_format($affected) . " แถว</p>
            <a href='forecast_plan_display_new.php' style='display: inline-block; padding: 10px 20px; background-color: #198754; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top: 10px; transition: background 0.2s;'>กลับไปยังตารางแผนงาน</a>
          </div>";

} catch (Exception $e) {
    // กรณีเกิดข้อผิดพลาด ให้ทำการ Rollback ข้อมูลกลับคืน
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "<div style='font-family: Prompt, Arial, sans-serif; padding: 25px; background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7; border-radius: 8px; margin: 30px auto; max-width: 600px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);'>
            <h3 style='margin-top: 0;'><i class='fa fa-exclamation-triangle'></i> เกิดข้อผิดพลาด (Sync Failed)</h3>
            <p>ระบบไม่สามารถประมวลผลการปรับปรุงข้อมูลที่อยู่ลูกค้าได้</p>
            <hr style='border: 0; border-top: 1px solid #f5c2c7; margin: 15px 0;'>
            <p><strong>รายละเอียดข้อผิดพลาด:</strong> " . htmlspecialchars($e->getMessage()) . "</p>
            <a href='forecast_plan_display_new.php' style='display: inline-block; padding: 10px 20px; background-color: #dc3545; color: white; text-decoration: none; border-radius: 5px; font-weight: bold; margin-top: 10px;'>กลับไปยังตารางแผนงาน</a>
          </div>";
}
?>
