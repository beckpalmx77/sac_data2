<?php
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
// 2. รับค่า Filter และเตรียมเงื่อนไข SQL
// =========================================================

$sql_condition = "";
$date_suffix = date('Ymd');
$as_of_date_show = date('d/m/Y'); // Default เป็นวันปัจจุบัน

// 2.1 Date Range (ยอดคงเหลือ ณ วันที่)
if (!empty($_POST['end_date'])) {
    $end = $conn_sqlsvr->quote($_POST['end_date']);
    $sql_condition .= " AND DOCINFO.DI_DATE <= $end ";

    // ตั้งชื่อไฟล์และตัวแปรแสดงผล
    $date_suffix = str_replace('-', '', $_POST['end_date']);
    $as_of_date_show = date_format(date_create($_POST['end_date']), "d/m/Y");
}

// 2.2 Warehouse (คลังสินค้า) - แก้ไขให้รับ wh_codes[]
if (!empty($_POST['wh_codes'])) {
    if (is_array($_POST['wh_codes'])) {
        $in = implode(',', quoteArray($_POST['wh_codes'], $conn_sqlsvr));
        $sql_condition .= " AND WH.WH_CODE IN ($in) ";
    } else {
        $val = $conn_sqlsvr->quote($_POST['wh_codes']);
        $sql_condition .= " AND WH.WH_CODE = $val ";
    }
}

// 2.3 ICCAT (หมวดสินค้า)
if (!empty($_POST['icc_codes'])) {
    $in = implode(',', quoteArray($_POST['icc_codes'], $conn_sqlsvr));
    $sql_condition .= " AND ICCAT.ICCAT_CODE IN ($in) ";
}

// 2.4 Brand (ยี่ห้อ) - เพิ่มส่วนนี้
if (!empty($_POST['brn_codes'])) {
    $in = implode(',', quoteArray($_POST['brn_codes'], $conn_sqlsvr));
    $sql_condition .= " AND BRAND.BRN_CODE IN ($in) ";
}

// 2.5 Location (ตำแหน่งเก็บ) - เพิ่มส่วนนี้
if (!empty($_POST['wl_codes'])) {
    $in = implode(',', quoteArray($_POST['wl_codes'], $conn_sqlsvr));
    $sql_condition .= " AND WL.WL_CODE IN ($in) ";
}

// =========================================================
// 3. ตั้งค่า Header CSV
// =========================================================
$filename = "Stock_Balance_" . $date_suffix . "_" . date('His') . ".csv";

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
    WH.WH_NAME,
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
WHERE SM.SKU_ENABLE = 'Y' 
    $sql_condition
GROUP BY 
    SM.SKU_CODE,
    SM.SKU_NAME,
    WH.WH_CODE,
    WH.WH_NAME,
    WL.WL_CODE,
    UQ.UTQ_NAME,
    ICCAT.ICCAT_CODE
HAVING 
    SUM(SKM.SKM_QTY) > 0
ORDER BY 
    WH.WH_CODE ASC,
    WL.WL_CODE ASC,
    SM.SKU_CODE ASC
";

// =========================================================
// 5. ประมวลผลและสร้าง CSV
// =========================================================
$query = $conn_sqlsvr->prepare($String_Sql);
$query->execute();

// สร้าง Header Column
$csv_content = "ยอดคงเหลือ ณ วันที่,รหัสสินค้า,ชื่อสินค้า,หมวด,คลัง,ที่เก็บ,หน่วยนับ,จำนวน\n";

while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
    // Clean Data (ป้องกัน Comma ทำ csv เพี้ยน)
    $sku_code = str_replace(",", " ", $row['SKU_CODE']);
    $sku_name = str_replace(",", " ", $row['SKU_NAME']);
    $iccat    = str_replace(",", " ", $row['ICCAT_CODE']);
    $wh_code  = str_replace(",", " ", $row['WH_CODE']);
    $wl_code  = str_replace(",", " ", $row['WL_CODE']);
    $utq_name = str_replace(",", " ", $row['UNIT_NAME']);
    $qty      = $row['SUM_QTY'];

    // ต่อ String
    $csv_content .= "$as_of_date_show,$sku_code,$sku_name,$iccat,$wh_code,$wl_code,$utq_name,$qty\n";
}

// =========================================================
// 6. Output (แปลงเป็น Windows-874 สำหรับ Excel ภาษาไทย)
// =========================================================
echo iconv("utf-8", "windows-874//IGNORE", $csv_content);
exit();
?>