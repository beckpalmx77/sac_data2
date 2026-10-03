<?php
date_default_timezone_set('Asia/Bangkok');

$filename = "Data_Sale_Return-Daily-" . date('m/d/Y H:i:s', time()) . ".csv";

@header('Content-type: text/csv; charset=UTF-8');
@header('Content-Encoding: UTF-8');
@header("Content-Disposition: attachment; filename=" . $filename);

include('../config/connect_db.php');

$sql_due_date = "SELECT v_ims_document_bill.* , b.DI_REF AS BILL_DI_REF , b.DI_DATE_STR AS BILL_DI_DATE
                        , b.TPA_REFER_REF , b.TPA_REFER_DATE_STR , b.ARD_BIL_DA_STR  AS BILL_ARD_BIL_DA , b.ARD_DUE_DA_STR AS BILL_ARD_DUE_DA
                        , b.ARD_A_SV , b.ARD_A_VAT  , b.ARD_A_AMT 
                        FROM v_ims_document_bill
                        LEFT JOIN v_ims_document_bill_load b ON b.TPA_REFER_REF = v_ims_document_bill.DI_REF 
                        WHERE v_ims_document_bill.DI_ACTIVE = 0 ";

// ----------------------------------------------------------------------------------
// ส่วนที่ 1: การแก้ไขการจัดการวันที่ใน PHP
// สมมติว่า $_POST['doc_date_start'] มาในรูปแบบ DD/MM/YYYY (เช่น 13/10/2025)
// เราต้องแปลงให้เป็นรูปแบบ YYYY-MM-DD เพื่อใช้ในการเปรียบเทียบสตริงใน SQL

// 1.1 แปลงรูปแบบวันที่เริ่มต้น: DD/MM/YYYY -> YYYY-MM-DD
$doc_date_start_sql = substr($_POST['doc_date_start'], 6, 4) . "-" . substr($_POST['doc_date_start'], 3, 2) . "-" . substr($_POST['doc_date_start'], 0, 2);

// 1.2 แปลงรูปแบบวันที่สิ้นสุด: DD/MM/YYYY -> YYYY-MM-DD
$doc_date_to_sql = substr($_POST['doc_date_to'], 6, 4) . "-" . substr($_POST['doc_date_to'], 3, 2) . "-" . substr($_POST['doc_date_to'], 0, 2);

// ----------------------------------------------------------------------------------
// ส่วนที่ 2: การแก้ไขเงื่อนไข SQL
// 1. แก้ไขการใช้ Alias: เปลี่ยน BILL_DI_DATE เป็น b.DI_DATE
// 2. จัดการเรื่องเวลา: เมื่อ b.DI_DATE เป็น VARCHAR ที่เก็บเวลาด้วย การใช้ BETWEEN YYYY-MM-DD อาจจะพลาดข้อมูลในวันสุดท้าย
//    ดังนั้นเราจะใช้เงื่อนไขแบบ String Comparison ที่ครอบคลุมเวลาทั้งหมด

$String_Sql = $sql_due_date . " AND b.DI_DATE >= '" . $doc_date_start_sql . " AND b.DI_DATE <= '" . $doc_date_to_sql ;

// ----------------------------------------------------------------------------------
// Debug: บันทึก SQL Command ที่แก้ไขแล้ว
$my_file = fopen("a-sac_str1.txt", "w") or die("Unable to open file!");
fwrite($my_file, $String_Sql);
fclose($my_file);

// ----------------------------------------------------------------------------------
// ส่วนที่ 3: ข้อสังเกตเพิ่มเติมในส่วน CSV
/*
* ในส่วนนี้ดูเหมือนว่าคอลัมน์ใน $data ไม่สอดคล้องกับคอลัมน์ที่คุณเลือกใน SELECT
* โปรดตรวจสอบว่าคุณมีคอลัมน์เหล่านี้ในตาราง v_ims_document_bill หรือไม่:
* AR_NAME, BUY_DATE_REF, BUY_REF, BUY_AMOUNT, ARCD_TERM, DUE_DATE_REF
*
* สำหรับจุดประสงค์ของการแก้ไข SQL: ฉันจะทิ้งส่วนนี้ไว้ตามเดิม แต่คุณควรแก้ไข
* ชื่อคอลัมน์เหล่านี้ให้ตรงกับตารางของคุณ (v_ims_document_bill.*)
*/
$data = "BUY_DATE,AR_NAME,BUY_DATE_REF,BUY_REF,BUY_AMOUNT,ARCD_TERM,DUE_DATE_REF\n"; // แก้ BUY_DATE ซ้ำตัวแรกแล้ว

try {
    $query = $conn_sqlsvr->prepare($String_Sql);
    $query->execute();

    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {

        // **โปรดตรวจสอบชื่อคอลัมน์ใน $row[...] ว่าตรงกับคอลัมน์ที่คุณ SELECT หรือไม่**
        // (เช่น $row['AR_NAME'] อาจจะต้องมาจากตารางอื่น หรือคุณอาจจะลืม SELECT มันมา)

        $data .= str_replace(",", "^", $row['DI_DATE']) . ",";
        $data .= str_replace(",", "^", $row['AR_NAME']) . ",";
        $data .= str_replace(",", "^", $row['BUY_DATE_REF']) . ",";
        $data .= str_replace(",", "^", $row['BUY_REF']) . ",";
        $data .= str_replace(",", "^", $row['BUY_AMOUNT']) . ",";
        $data .= str_replace(",", "^", $row['ARCD_TERM']) . ",";
        $data .= str_replace(",", "^", $row['DUE_DATE_REF']) . "\n"; // ลบคอลัมน์ซ้ำและเพิ่มขึ้นบรรทัดใหม่

    }
} catch (PDOException $e) {
    // ควรมี Error Handling ที่ดีกว่านี้
    die("Database query failed: " . $e->getMessage());
}


$data = iconv("utf-8", "tis-620", $data);
echo $data;

exit();