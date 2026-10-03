<?php
// 1. ตั้งค่าการเชื่อมต่อ MSSQL Server และ PostgreSQL
$ms_host = "ไอพี_MSSQL"; $ms_db = "ชื่อฐานข้อมูล_MSSQL"; $ms_user = "ชื่อผู้ใช้_MSSQL"; $ms_pass = "รหัสผ่าน_MSSQL";
$pg_host = "ไอพี_POSTGRES"; $pg_db = "ชื่อฐานข้อมูล_POSTGRES"; $pg_user = "ชื่อผู้ใช้_POSTGRES"; $pg_pass = "รหัสผ่าน_POSTGRES";

try {
    $ms_conn = new PDO("sqlsrv:Server=$ms_host;Database=$ms_db", $ms_user, $ms_pass);
    $ms_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $pg_conn = new PDO("pgsql:host=$pg_host;dbname=$pg_db", $pg_user, $pg_pass);
    $pg_conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // --------------------------------------------------------
    // ขั้นตอนที่ 1: ดึงข้อมูลจาก MSSQL
    // --------------------------------------------------------
    $sql_select_ms = "
        SELECT f.AR_KEY, f.AR_CODE, f.AR_NAME, c.ARCD_ARPRBCODE 
        FROM ARFILE f
        LEFT JOIN ARCONDITION c ON f.AR_KEY = c.ARCD_AR AND c.ARCD_DEFAULT = 'Y'
        WHERE f.AR_CODE LIKE '%SAC%' ORDER BY f.AR_CODE
    ";

    $stmt_ms = $ms_conn->query($sql_select_ms);
    $results = $stmt_ms->fetchAll(PDO::FETCH_ASSOC);

    echo "<h3>รายงานการอัปเดตข้อมูล (จำนวน " . count($results) . " รายการ)</h3>";
    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr style='background-color:#f2f2f2;'>
            <th>AR_CODE</th>
            <th>ค่าเดิม (PostgreSQL)</th>
            <th>ค่าใหม่ (จาก MSSQL)</th>
            <th>สถานะ</th>
          </tr>";

    // --------------------------------------------------------
    // ขั้นตอนที่ 2: เตรียมคำสั่ง เช็คค่าเดิม และ อัปเดตค่าใหม่
    // --------------------------------------------------------

    // คำสั่งสำหรับเช็คค่าปัจจุบันใน PostgreSQL
    $sql_check_pg = "
        SELECT p.price_group_id, p.is_default 
        FROM sac_customer_price_group p
        INNER JOIN sac_customers c ON p.customer_id = c.id
        WHERE c.code = :ar_code
    ";
    $stmt_check = $pg_conn->prepare($sql_check_pg);

    // คำสั่งสำหรับอัปเดตข้อมูล
    $sql_update_pg = "
        UPDATE sac_customer_price_group p
        SET price_group_id = :price_group_id, is_default = 't'
        FROM sac_customers c
        WHERE p.customer_id = c.id AND c.code = :ar_code
    ";
    $stmt_update = $pg_conn->prepare($sql_update_pg);

    $pg_conn->beginTransaction();
    $update_count = 0;

    // --------------------------------------------------------
    // ขั้นตอนที่ 3: วนลูปตรวจสอบและอัปเดต
    // --------------------------------------------------------
    foreach ($results as $row) {
        $ar_code = $row['AR_CODE'];
        $new_price_group_id = $row['ARCD_ARPRBCODE'];

        // ข้ามรายการที่ไม่มีรหัสกลุ่มราคา
        if (empty($new_price_group_id)) {
            echo "<tr><td>$ar_code</td><td colspan='2' align='center'>- ไม่มี ARCD_ARPRBCODE จากต้นทาง -</td><td style='color:orange;'>ข้าม (Skip)</td></tr>";
            continue;
        }

        // 3.1 หาค่าเดิมจาก PostgreSQL ก่อนอัปเดต
        $stmt_check->bindParam(':ar_code', $ar_code);
        $stmt_check->execute();
        $old_data = $stmt_check->fetch(PDO::FETCH_ASSOC);

        $old_text = "ไม่พบข้อมูลในตาราง price_group";
        if ($old_data) {
            $old_text = "Group ID: " . $old_data['price_group_id'] . " | Default: " . $old_data['is_default'];
        }

        // 3.2 ทำการอัปเดตค่าใหม่ลงไป
        $stmt_update->bindParam(':price_group_id', $new_price_group_id);
        $stmt_update->bindParam(':ar_code', $ar_code);
        $stmt_update->execute();

        // 3.3 แสดงผลลัพธ์
        $new_text = "Group ID: " . $new_price_group_id . " | Default: t";

        if ($stmt_update->rowCount() > 0) {
            $status = "<span style='color:green;'>อัปเดตสำเร็จ</span>";
            $update_count++;
        } else {
            $status = "<span style='color:red;'>ไม่พบรหัสลูกค้านี้ให้รอัปเดต</span>";
        }

        echo "<tr>
                <td>$ar_code</td>
                <td>$old_text</td>
                <td>$new_text</td>
                <td>$status</td>
              </tr>";
    }

    $pg_conn->commit();
    echo "</table>";
    echo "<br><b>อัปเดตข้อมูลสำเร็จรวมทั้งหมด: " . $update_count . " รายการ</b>";

} catch (PDOException $e) {
    if (isset($pg_conn) && $pg_conn->inTransaction()) {
        $pg_conn->rollBack();
    }
    echo "เกิดข้อผิดพลาด: " . $e->getMessage();
}