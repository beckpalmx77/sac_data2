<?php
date_default_timezone_set('Asia/Bangkok');

// -----------------------------
// ตั้งชื่อไฟล์ CSV
// -----------------------------
$filename = "Data_DUE_BILL-" . date('Y-m-d_H-i-s', time()) . ".csv";

@header('Content-type: text/csv; charset=UTF-8');
@header('Content-Encoding: UTF-8');
@header("Content-Disposition: attachment; filename=" . $filename);

include('../config/connect_db.php');

// ----------------------------------------------------------------------------------
// ส่วนที่ 1: Base SQL
// ----------------------------------------------------------------------------------
$sql_due_date = "
SELECT 
    v_ims_document_bill.*,
    b.DI_REF AS BILL_DI_REF,
    b.DI_DATE_STR AS BILL_DI_DATE,
    b.TPA_REFER_REF,
    b.TPA_REFER_DATE_STR,
    b.ARD_BIL_DA_STR AS BILL_ARD_BIL_DA,
    b.ARD_DUE_DA_STR AS BILL_ARD_DUE_DA,
    b.ARD_A_SV,
    b.ARD_A_VAT,
    b.ARD_A_AMT
FROM v_ims_document_bill
INNER JOIN v_ims_document_bill_load b 
    ON b.TPA_REFER_REF = v_ims_document_bill.DI_REF
WHERE v_ims_document_bill.DI_ACTIVE = 0
";

// ----------------------------------------------------------------------------------
// ส่วนที่ 2: แปลงวันที่จาก POST (DD/MM/YYYY → YYYY-MM-DD)
// ----------------------------------------------------------------------------------
$doc_date_start_sql = substr($_POST['doc_date_start'], 6, 4) . "-" .
    substr($_POST['doc_date_start'], 3, 2) . "-" .
    substr($_POST['doc_date_start'], 0, 2);

$doc_date_to_sql = substr($_POST['doc_date_to'], 6, 4) . "-" .
    substr($_POST['doc_date_to'], 3, 2) . "-" .
    substr($_POST['doc_date_to'], 0, 2);

// ----------------------------------------------------------------------------------
// ส่วนที่ 3: เงื่อนไขช่วงวันที่ (BETWEEN)
// ----------------------------------------------------------------------------------
$String_Sql = $sql_due_date . "
    AND b.DI_DATE_STR IS NOT NULL
    AND b.DI_DATE_STR <> ''
    AND STR_TO_DATE(TRIM(v_ims_document_bill.DI_DATE_STR), '%d-%m-%Y')
        BETWEEN STR_TO_DATE('" . $doc_date_start_sql . "', '%Y-%m-%d')
        AND STR_TO_DATE('" . $doc_date_to_sql . "', '%Y-%m-%d')
";

// ----------------------------------------------------------------------------------
// Debug: บันทึก SQL ที่ใช้งานจริง
// ----------------------------------------------------------------------------------
/*
$my_file = fopen("a-sac_str1.txt", "w") or die("Unable to open file!");
fwrite($my_file, $String_Sql . "\n");
fclose($my_file);
*/

// ----------------------------------------------------------------------------------
// ส่วนที่ 4: สร้าง CSV
// ----------------------------------------------------------------------------------
function check_null_and_escape($value) {
    return str_replace(",", "^", (isset($value) && $value !== null) ? $value : '');
}

// หัวตาราง
$data = "เลขที่เอกสาร,วันที่เอกสาร,วันที่ Due,ชื่อลูกค้า,จำนวนเงิน,Sale Code, Sale Name,หมายเหตุ\n";

try {
    $query = $conn->prepare($String_Sql);
    $query->execute();

    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $data .= check_null_and_escape($row['DI_REF']) . ",";
        $data .= check_null_and_escape($row['DI_DATE_STR']) . ",";
        $data .= check_null_and_escape($row['ARD_BIL_DA_STR']) . ",";
        $data .= check_null_and_escape($row['AR_NAME']) . ",";
        $data .= check_null_and_escape($row['DI_AMOUNT']) . ",";
        $data .= check_null_and_escape($row['AR_SLMNCODE']) . ",";
        $data .= check_null_and_escape($row['SLMN_NAME']) . ",";
        $data .= check_null_and_escape($row['AR_REMARK']) . "\n";
    }
} catch (PDOException $e) {
    die("Database query failed: " . $e->getMessage());
}

// ----------------------------------------------------------------------------------
// ส่วนที่ 5: แปลง encoding และแสดงผล CSV
// ----------------------------------------------------------------------------------
$data = iconv("utf-8", "tis-620", $data);
echo $data;

exit();