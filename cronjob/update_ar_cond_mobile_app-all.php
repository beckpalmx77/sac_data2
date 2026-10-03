<?php
// ตั้งค่า Timezone ให้ตรงกับประเทศไทย
date_default_timezone_set('Asia/Bangkok');

require_once '../config/connect_sqlserver.php'; // $conn_sqlsvr
require_once '../config/connect_pg_db.php';     // $conn

// --------------------------------------------------------
// ตั้งค่าระบบเก็บ Log
// --------------------------------------------------------
$log_dir = __DIR__ . '/logs';
if (!file_exists($log_dir)) {
    mkdir($log_dir, 0777, true);
}
$log_file = $log_dir . '/sync_price_' . date('Y-m-d') . '.log';

function writeLog($message) {
    global $log_file;
    $timestamp = date('Y-m-d H:i:s');
    $log_msg = "[$timestamp] " . $message . PHP_EOL;
    echo $log_msg;
    //file_put_contents($log_file, $log_msg, FILE_APPEND);
}

//writeLog("=== เริ่มต้นการซิงค์ข้อมูลกลุ่มราคาลูกค้า ===");

try {
    // --------------------------------------------------------
    // 1. ดึงข้อมูลจาก MSSQL
    // --------------------------------------------------------
    $sql_select_ms = "
        SELECT f.AR_CODE, f.AR_NAME, p.ARPRB_KEY 
        FROM ARFILE f
        LEFT JOIN ARCONDITION c ON f.AR_KEY = c.ARCD_AR AND c.ARCD_DEFAULT = 'Y'
        LEFT JOIN ARPRICETAB p ON c.ARCD_ARPRBCODE = p.ARPRB_CODE
        WHERE f.AR_CODE LIKE '%SAC6102-020%' 
        ORDER BY f.AR_CODE
    ";

    $stmt_ms = $conn_sqlsvr->query($sql_select_ms);
    $results = $stmt_ms->fetchAll(PDO::FETCH_ASSOC);

    $total_source = count($results);
    //writeLog("พบข้อมูลต้นทาง (MSSQL) จำนวน: {$total_source} รายการ");

    if ($total_source == 0) {
        //writeLog("=== สิ้นสุดการทำงาน (ไม่มีข้อมูล) ===" . PHP_EOL);
        exit;
    }

    // --------------------------------------------------------
    // 2. เตรียมคำสั่ง PostgreSQL
    // --------------------------------------------------------
    $sql_get_pg_id = "SELECT id FROM sac_price_groups WHERE key = :arprb_key";
    $stmt_get_pg_id = $conn->prepare($sql_get_pg_id);

    $sql_check_pg = "
        SELECT p.price_group_id, p.is_default 
        FROM sac_customer_price_group p
        INNER JOIN sac_customers c ON p.customer_id = c.id
        WHERE c.code = :ar_code
    ";
    $stmt_check = $conn->prepare($sql_check_pg);

    $sql_update_pg = "
        UPDATE sac_customer_price_group p
        SET price_group_id = :price_group_id, is_default = 't'
        FROM sac_customers c
        WHERE p.customer_id = c.id AND c.code = :ar_code
    ";
    $stmt_update = $conn->prepare($sql_update_pg);

    $conn->beginTransaction();
    $update_count = 0;
    $skip_count = 0;

    // --------------------------------------------------------
    // 3. วนลูปตรวจสอบและอัปเดตข้อมูลจริง
    // --------------------------------------------------------
    foreach ($results as $row) {
        $ar_code = $row['AR_CODE'];
        $ar_name = $row['AR_NAME'];
        // $ar_name = iconv('TIS-620', 'UTF-8', $row['AR_NAME']); // เปิดใช้ถ้าภาษาไทยอ่านไม่ออก

        $arprb_key = $row['ARPRB_KEY'];

        if (empty($arprb_key)) {
            //writeLog("SKIP    | {$ar_code} | {$ar_name} | ไม่มี ARPRB_KEY จากต้นทาง");
            $skip_count++;
            continue;
        }

        // แปลง ARPRB_KEY เป็น ID ของ PostgreSQL
        $stmt_get_pg_id->bindParam(':arprb_key', $arprb_key);
        $stmt_get_pg_id->execute();
        $pg_group_data = $stmt_get_pg_id->fetch(PDO::FETCH_ASSOC);

        if (!$pg_group_data) {
            //writeLog("ERROR   | {$ar_code} | {$ar_name} | ไม่พบ Key '{$arprb_key}' ใน sac_price_groups");
            $skip_count++;
            continue;
        }

        $new_price_group_id = $pg_group_data['id'];

        // เช็คค่าเดิมใน PostgreSQL
        $stmt_check->bindParam(':ar_code', $ar_code);
        $stmt_check->execute();
        $old_data = $stmt_check->fetch(PDO::FETCH_ASSOC);

        // ตรวจสอบค่าเดิมเพื่อนำไปแสดงผล
        $old_price_text = "[ไม่มีข้อมูลเดิม]";

        if ($old_data) {
            $old_price_text = "[ID เดิม: " . $old_data['price_group_id'] . "]";

            // เช็คว่าข้อมูลปัจจุบันตรงกับค่าใหม่ที่จะอัปเดตหรือยัง
            if ($old_data['price_group_id'] == $new_price_group_id && $old_data['is_default'] == 't') {
                //writeLog("EQUAL   | {$ar_code} | {$ar_name} | ข้อมูลตรงกันอยู่แล้ว {$old_price_text}");
                $skip_count++;
                continue;
            }
        }

        // --------------------------------------------------------
        // 3.4 ดำเนินการอัปเดตข้อมูลลง Database
        // --------------------------------------------------------
        $stmt_update->bindParam(':price_group_id', $new_price_group_id);
        $stmt_update->bindParam(':ar_code', $ar_code);
        $stmt_update->execute();

        if ($stmt_update->rowCount() > 0) {
            //writeLog("OK      | {$ar_code} | {$ar_name} | อัปเดตสำเร็จ {$old_price_text} -> เป็น [ID ใหม่: {$new_price_group_id}]");
            $update_count++;
        } else {
            //writeLog("FAIL    | {$ar_code} | {$ar_name} | ไม่พบรหัสลูกค้านี้ในตาราง price_group บน PostgreSQL");
            $skip_count++;
        }
    }

    $conn->commit();
    //writeLog("สรุปผลการทำงาน: อัปเดตข้อมูลสำเร็จ {$update_count} รายการ | ข้าม/ไม่มีการเปลี่ยนแปลง {$skip_count} รายการ");
    //writeLog("=== สิ้นสุดการทำงานอย่างสมบูรณ์ ===" . PHP_EOL);

} catch (PDOException $e) {
    if (isset($conn) && $conn->inTransaction()) {
        $conn->rollBack();
    }
    //writeLog("CRITICAL ERROR: " . $e->getMessage());
    //writeLog("=== สิ้นสุดการทำงาน (มีข้อผิดพลาด) ===" . PHP_EOL);
}