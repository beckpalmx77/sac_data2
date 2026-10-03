<?php
date_default_timezone_set('Asia/Bangkok');

// -----------------------------
// ตั้งชื่อไฟล์ CSV
// -----------------------------
$filename = "Cheque_Receive_Report-" . date('Y-m-d_H-i-s', time()) . ".csv";

@header('Content-type: text/csv; charset=UTF-8');
@header('Content-Encoding: UTF-8');
@header("Content-Disposition: attachment; filename=" . $filename);

include('../config/connect_sqlserver.php'); // $conn_sqlsvr

// ----------------------------------------------------------------------------------
// ส่วนที่ 1: รับเงื่อนไขวันที่จาก POST และแปลงรูปแบบ (DD-MM-YYYY → YYYY/MM/DD)
// ----------------------------------------------------------------------------------
$doc_date_start = isset($_POST['doc_date_start']) ? $_POST['doc_date_start'] : "";
$doc_date_to = isset($_POST['doc_date_to']) ? $_POST['doc_date_to'] : "";

$doc_date_start_sql = "";
$doc_date_to_sql = "";

if (!empty($doc_date_start) && !empty($doc_date_to)) {
    $doc_date_start_sql = substr($doc_date_start, 6, 4) . "/" . substr($doc_date_start, 3, 2) . "/" . substr($doc_date_start, 0, 2);
    $doc_date_to_sql = substr($doc_date_to, 6, 4) . "/" . substr($doc_date_to, 3, 2) . "/" . substr($doc_date_to, 0, 2);
} else {
    $doc_date_start_sql = date("Y/m/d");
    $doc_date_to_sql = date("Y/m/d");
}

// ----------------------------------------------------------------------------------
// ส่วนที่ 2: SQL Query (SQL Server)
// ----------------------------------------------------------------------------------
$String_Sql = "
SELECT 
  DI_DATE, 
  DI_REF, 
  BNKAC_CODE,
  BNKAC_NAME,
  BANK_INITIAL,
  BANK_T_NAME, 
  CQIN_CHEQUE_NO,
  CQIN_CHEQUE_DD,
  CQIN_OWNER, 
  CQIN_AMT, 
  CQIN_FEE_A,
  DI_ACTIVE,
  BR_CODE,
  DEPT_CODE,
  PRJ_CODE
FROM 
  BRANCH,
  DEPTTAB,
  PRJTAB,
  CHEQUEIN, 
  BANKFILE, 
  BANKACCOUNT, 
  DOCTYPE, 
  ARFILE,
  DOCINFO
LEFT OUTER JOIN TRANPAYH
    ON TPH_DI=DI_KEY 
WHERE (CQIN_DI=DI_KEY)
  AND (CQIN_BANK=BANK_KEY)
  AND (CQIN_BNKAC=BNKAC_KEY)
  AND (CQIN_AR=AR_KEY)
  AND (DI_DT=DT_KEY)
  AND (DT_PROPERTIES=612) 
  AND (TPH_DEPT=DEPT_KEY) 
  AND (TPH_PRJ=PRJ_KEY) 
  AND (TPH_BR=BR_KEY)
  AND (DI_DATE BETWEEN '" . $doc_date_start_sql . "' AND '" . $doc_date_to_sql . "')
ORDER BY 
  DI_DATE, 
  DI_REF,  
  BANK_INITIAL, 
  CQIN_CHEQUE_NO, 
  CQIN_CHEQUE_DD
";

// ----------------------------------------------------------------------------------
// ส่วนที่ 3: Helper Functions สำหรับทำความสะอาดข้อมูล
// ----------------------------------------------------------------------------------
function check_null_and_escape($value) {
    if (isset($value) && $value !== null) {
        // แทนที่เครื่องหมายจุลภาคด้วย ^ เพื่อไม่ให้โครงสร้าง CSV เพี้ยน
        return str_replace(",", "^", trim((string)$value));
    }
    return '';
}

function format_date($date_val) {
    if (empty($date_val)) {
        return "";
    }
    if ($date_val instanceof DateTime) {
        return $date_val->format('d-m-Y');
    }
    $timestamp = strtotime($date_val);
    if ($timestamp === false) {
        return $date_val;
    }
    return date('d-m-Y', $timestamp);
}

// ----------------------------------------------------------------------------------
// ส่วนที่ 4: สร้างเนื้อหา CSV (Header & Body)
// ----------------------------------------------------------------------------------
// กำหนดหัวตารางของ CSV
$data = "วันที่เอกสาร,เลขที่เอกสาร,รหัสบัญชีธนาคาร,ชื่อบัญชีธนาคาร,อักษรย่อธนาคาร,ชื่อธนาคาร,เลขที่เช็ค,วันที่เช็ค,สั่งจ่ายโดย,ยอดเงินเช็ค,ค่าธรรมเนียม,สถานะเอกสาร,รหัสสาขา,รหัสแผนก,รหัสโครงการ\n";

try {
    $query = $conn_sqlsvr->prepare($String_Sql);
    $query->execute();

    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $data .= check_null_and_escape(format_date($row['DI_DATE'])) . ",";
        $data .= check_null_and_escape($row['DI_REF']) . ",";
        $data .= check_null_and_escape($row['BNKAC_CODE']) . ",";
        $data .= check_null_and_escape($row['BNKAC_NAME']) . ",";
        $data .= check_null_and_escape($row['BANK_INITIAL']) . ",";
        $data .= check_null_and_escape($row['BANK_T_NAME']) . ",";
        $data .= check_null_and_escape($row['CQIN_CHEQUE_NO']) . ",";
        $data .= check_null_and_escape(format_date($row['CQIN_CHEQUE_DD'])) . ",";
        $data .= check_null_and_escape($row['CQIN_OWNER']) . ",";
        $data .= check_null_and_escape($row['CQIN_AMT']) . ",";
        $data .= check_null_and_escape($row['CQIN_FEE_A']) . ",";
        $data .= check_null_and_escape($row['DI_ACTIVE']) . ",";
        $data .= check_null_and_escape($row['BR_CODE']) . ",";
        $data .= check_null_and_escape($row['DEPT_CODE']) . ",";
        $data .= check_null_and_escape($row['PRJ_CODE']) . "\n";
    }
} catch (PDOException $e) {
    die("Database query failed: " . $e->getMessage());
}

// แปลงเป็น TIS-620 เพื่อให้สามารถเปิดกับ Microsoft Excel ภาษาไทยรุ่นเก่าได้โดยตรง
$data = iconv("utf-8", "tis-620//IGNORE", $data);
echo $data;

exit();
?>
