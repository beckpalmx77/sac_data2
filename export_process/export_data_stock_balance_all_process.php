<?php
// เพิ่มขีดจำกัดหน่วยความจำและเวลา (เผื่อข้อมูลเยอะ)
ini_set('memory_limit', '512M');
set_time_limit(300);

date_default_timezone_set('Asia/Bangkok');
include('../config/connect_sqlserver.php');

// =========================================================
// 1. Helper Function
// =========================================================
function quoteArray($arr, $conn) {
    return array_map(function ($v) use ($conn) {
        return $conn->quote(trim($v));
    }, $arr);
}

// =========================================================
// 2. รับค่า Filter และสร้าง WHERE SQL
// =========================================================
$where = [];
$filename_suffix = "All";

// --- แก้ไข: รับค่าคลังสินค้า (wh_codes) ---
if (!empty($_POST['wh_codes'])) {
    if (is_array($_POST['wh_codes'])) {
        $in = implode(',', quoteArray($_POST['wh_codes'], $conn_sqlsvr));
        $where[] = "WH.WH_CODE IN ($in)";
        $filename_suffix = "Multi-WH"; // ตั้งชื่อไฟล์แบบกลางๆ
    } else {
        $val = $conn_sqlsvr->quote($_POST['wh_codes']);
        $where[] = "WH.WH_CODE = $val";
        $filename_suffix = $_POST['wh_codes'];
    }
}

// ICCAT
if (!empty($_POST['icc_codes'])) {
    $in = implode(',', quoteArray($_POST['icc_codes'], $conn_sqlsvr));
    $where[] = "ICCAT.ICCAT_CODE IN ($in)";
}

// BRAND
if (!empty($_POST['brn_codes'])) {
    $in = implode(',', quoteArray($_POST['brn_codes'], $conn_sqlsvr));
    $where[] = "BRAND.BRN_CODE IN ($in)";
}

// WARELOCATION
if (!empty($_POST['wl_codes'])) {
    $in = implode(',', quoteArray($_POST['wl_codes'], $conn_sqlsvr));
    $where[] = "WL.WL_CODE IN ($in)";
}

// SKU Enable
$where[] = "SM.SKU_ENABLE = 'Y'";

// รวม WHERE Clause
$WHERE_SQL = "";
if (!empty($where)) {
    $WHERE_SQL = "WHERE " . implode(' AND ', $where);
}

// =========================================================
// 3. ตั้งค่า Header CSV
// =========================================================
$filename = "Stock_Balance_" . $filename_suffix . "_" . date('Ymd-His') . ".csv";

header('Content-type: text/csv; charset=UTF-8');
header('Content-Encoding: UTF-8');
header("Content-Disposition: attachment; filename=\"$filename\"");

// =========================================================
// 4. SQL Query
// =========================================================
$String_Sql = "
SELECT 
    SM.SKU_CODE,
    SM.SKU_NAME,    
    WH.WH_CODE,
    WL.WL_CODE,
    ICCAT.ICCAT_CODE, 
    UQ.UTQ_NAME AS UNIT_NAME,
    SUM(SKM.SKM_QTY) AS SUM_QTY
FROM 
    dbo.SKUMASTER SM WITH (NOLOCK)
    INNER JOIN dbo.ICCAT WITH (NOLOCK) ON SM.SKU_ICCAT = ICCAT.ICCAT_KEY
    INNER JOIN dbo.ICDEPT WITH (NOLOCK) ON SM.SKU_ICDEPT = ICDEPT.ICDEPT_KEY
    INNER JOIN dbo.BRAND WITH (NOLOCK) ON SM.SKU_BRN = BRAND.BRN_KEY
    INNER JOIN dbo.UOFQTY UQ WITH (NOLOCK) ON SM.SKU_S_UTQ = UQ.UTQ_KEY
    INNER JOIN dbo.SKUMOVE SKM WITH (NOLOCK) ON SM.SKU_KEY = SKM.SKM_SKU
    INNER JOIN dbo.WARELOCATION WL WITH (NOLOCK) ON SKM.SKM_WL = WL.WL_KEY
    INNER JOIN dbo.WAREHOUSE WH WITH (NOLOCK) ON WL.WL_WH = WH.WH_KEY
    INNER JOIN dbo.DOCINFO WITH (NOLOCK) ON SKM.SKM_DI = DOCINFO.DI_KEY
    INNER JOIN dbo.DOCTYPE WITH (NOLOCK) ON DOCINFO.DI_DT = DOCTYPE.DT_KEY
    $WHERE_SQL
GROUP BY 
    SM.SKU_CODE,
    SM.SKU_NAME,
    WH.WH_CODE,
    WL.WL_CODE,
    ICCAT.ICCAT_CODE,
    UQ.UTQ_NAME
HAVING 
    SUM(SKM.SKM_QTY) > 0
ORDER BY 
    WH.WH_CODE,
    WL.WL_CODE,
    SM.SKU_CODE
";

// =========================================================
// 5. ดึงข้อมูลและสร้าง Content CSV
// =========================================================
$query = $conn_sqlsvr->prepare($String_Sql);
$query->execute();

// Header Columns
$data = "รหัสสินค้า,สินค้า,หมวด,คลัง,ที่เก็บ,หน่วยนับ,จำนวน\n";

while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    // ใช้ str_replace แทน comma ด้วย ^ ตามรูปแบบเดิมของคุณ
    $data .= str_replace(",", "^", $row['SKU_CODE']) . ",";
    $data .= str_replace(",", "^", $row['SKU_NAME']) . ",";
    $data .= str_replace(",", "^", $row['ICCAT_CODE']) . ","; // เพิ่มหมวด
    $data .= str_replace(",", "^", $row['WH_CODE']) . ",";
    $data .= str_replace(",", "^", $row['WL_CODE']) . ",";
    $data .= str_replace(",", "^", $row['UNIT_NAME']) . ",";
    $data .= $row['SUM_QTY'] . "\n";
}

// =========================================================
// 6. Output (Windows-874 สำหรับ Excel ภาษาไทย)
// =========================================================
echo iconv("utf-8", "windows-874//IGNORE", $data);
exit();
?>