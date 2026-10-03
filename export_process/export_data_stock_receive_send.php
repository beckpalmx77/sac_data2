<?php
// เพิ่มเวลาให้ Script รันได้นานขึ้น
set_time_limit(300);
ini_set('memory_limit', '512M');

date_default_timezone_set('Asia/Bangkok');
include('../config/connect_sqlserver.php');
//include('../config/connect_sqlserver40.php');

// ---------------------------------------------------------
// ตั้งค่า Header CSV และแก้ปัญหาภาษาไทย (UTF-8 with BOM)
// ---------------------------------------------------------
$WH_CODE_REQ = $_POST['WH_CODE'] ?? 'ALL';
$filename = "Stock_Balance_" . $WH_CODE_REQ . "_" . date('Ymd-His') . ".csv";

header('Content-Type: text/csv; charset=UTF-8');
header("Content-Disposition: attachment; filename=\"$filename\"");

// เขียน BOM (Byte Order Mark) บังคับ Excel ให้อ่านไทยถูก
echo "\xEF\xBB\xBF";

// ---------------------------------------------------------
// ส่วนเตรียมตัวแปรและ Filter
// ---------------------------------------------------------

$stockData = [];

// กำหนดวันเริ่มต้นปีปัจจุบัน (เช่น 2026-01-01)
$currentYearStart = date('Y') . '-01-01';
// เงื่อนไข SQL สำหรับกรองวันที่
// $dateFilterSql = " AND DOCINFO.DI_DATE >= '$currentYearStart' ";

// ---------------------------------------------------------
// แก้ไข: กำหนดช่วงเวลา ย้อนหลัง 3 เดือน ถึง ปัจจุบัน
// ---------------------------------------------------------

// วันที่ปัจจุบัน (เช่น 2026-05-22)
$dateEnd = date('Y-m-d');

// ย้อนหลัง 3 เดือนจากปัจจุบัน (เช่น 2026-02-22)
$dateStart = date('Y-m-d', strtotime('-3 months'));

// สร้างเงื่อนไข SQL (ใช้ BETWEEN เพื่อครอบคลุมช่วงเวลา)
$dateFilterSql = " AND (DOCINFO.DI_DATE BETWEEN '$currentYearStart' AND '$dateEnd') ";

// ฟังก์ชันสำหรับสร้าง Key
function getKey($sku, $wh)
{
    return trim($sku) . '|' . trim($wh);
}

// ฟังก์ชัน Escape ค่า
function quoteArray($arr, $conn)
{
    return array_map(function ($v) use ($conn) {
        return $conn->quote(trim($v));
    }, $arr);
}

// สร้างเงื่อนไข WHERE แยกตามตารางที่ต้อง Join
$filter_wh = "";
$filter_iccat = "";
$filter_brand = "";

// 1. กรองคลัง
if (!empty($_POST['wh_codes'])) {
    // กรณีรับเป็น Array Checkbox
    $in = implode(',', quoteArray($_POST['wh_codes'], $conn_sqlsvr));
    $in_wh_codes = $in;
    $filter_wh = " AND WAREHOUSE.WH_CODE IN ($in) ";
} else {
    $in_wh_codes = "ทั้งหมด";
}

/*
elseif (!empty($_POST['WH_CODE'])) {
    // กรณีรับเป็น Dropdown เดียว
    $val = $conn_sqlsvr->quote($_POST['WH_CODE']);
    $filter_wh = " AND WAREHOUSE.WH_CODE IN ($val) ";
}
*/

// 2. กรองหมวดสินค้า
if (!empty($_POST['icc_codes'])) {
    $in = implode(',', quoteArray($_POST['icc_codes'], $conn_sqlsvr));
    $in_icc_codes = $in;
    $filter_iccat = " AND ICCAT.ICCAT_CODE IN ($in) ";
} else {
    $in_icc_codes = "ทั้งหมด";
}

// 3. กรองยี่ห้อ
if (!empty($_POST['brn_codes'])) {
    $in = implode(',', quoteArray($_POST['brn_codes'], $conn_sqlsvr));
    $in_brn_codes = $in;
    $filter_brand = " AND BRAND.BRN_CODE IN ($in) ";
} else {
    $in_brn_codes = "ทั้งหมด";
}

// ---------------------------------------------------------
// STEP 1: ดึงยอดคงเหลือ (Balance)
// ---------------------------------------------------------
$sql_balance = "
    SELECT 
        V.SKU_CODE, 
        V.SKU_NAME, 
        V.WH_CODE, 
        SUM(CAST(V.QTY AS DECIMAL(10,2))) as QTY,
        UOFQTY.UTQ_NAME
    FROM v_stock_movement V WITH (NOLOCK)
    LEFT JOIN SKUMASTER WITH (NOLOCK) ON V.SKU_CODE = SKUMASTER.SKU_CODE
    LEFT JOIN ICCAT WITH (NOLOCK) ON SKUMASTER.SKU_ICCAT = ICCAT.ICCAT_KEY
    LEFT JOIN BRAND WITH (NOLOCK) ON SKUMASTER.SKU_BRN = BRAND.BRN_KEY
    LEFT JOIN WAREHOUSE WITH (NOLOCK) ON V.WH_CODE = WAREHOUSE.WH_CODE
    LEFT JOIN UOFQTY WITH (NOLOCK) ON UOFQTY.UTQ_KEY = SKUMASTER.SKU_S_UTQ    
    WHERE SKUMASTER.SKU_ENABLE = 'Y'
    $filter_wh 
    $filter_iccat 
    $filter_brand
    GROUP BY V.SKU_CODE, V.SKU_NAME, V.WH_CODE,UOFQTY.UTQ_NAME
    ORDER BY V.WH_CODE , V.SKU_CODE, V.SKU_NAME, UOFQTY.UTQ_NAME
";

// *** DEBUG: บันทึก SQL ลงไฟล์ debug_sql_balance.txt ***
// file_put_contents('debug_sql_balance.txt', $sql_balance . " | " . $in);

$stmt = $conn_sqlsvr->prepare($sql_balance);
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $k = getKey($row['SKU_CODE'], $row['WH_CODE']);

    if (!isset($stockData[$k])) {
        $stockData[$k] = [
            'sku' => $row['SKU_CODE'],
            'name' => $row['SKU_NAME'],
            'wh' => $row['WH_CODE'],
            'unit' => $row['UTQ_NAME'], // เก็บหน่วยนับ
            'bal' => 0, 'ord' => 0, 'recv' => 0, 'res' => 0, 'ship' => 0
        ];
    }
    $stockData[$k]['bal'] = (float)$row['QTY'];
}

// ---------------------------------------------------------
// ฟังก์ชันกลางสำหรับรัน SQL Transaction
// *** อัปเดต: เพิ่ม $extraWhere สำหรับใส่เงื่อนไขวันที่ ***
// ---------------------------------------------------------
function processTransaction($conn, &$dataArr, $typeField, $docProps, $filters, $docCode = null, $extraWhere = "")
{

    list($f_wh, $f_iccat, $f_brand) = $filters;

    $docCondition = "";
    if (is_array($docProps)) {
        $propsList = implode(',', $docProps);
        $docCondition = " AND DOCTYPE.DT_PROPERTIES IN ($propsList) ";
    } else {
        $docCondition = " AND DOCTYPE.DT_PROPERTIES = $docProps ";
    }

    if ($docCode) {
        $docCondition .= " AND DOCTYPE.DT_DOCCODE = '$docCode' ";
    }

    $sql = "
        SELECT 
            SKUMASTER.SKU_CODE,
            SKUMASTER.SKU_NAME,
            WAREHOUSE.WH_CODE,
            -- 1. ยอดปกติ
            SUM(TRANSTKD.TRD_QTY) AS TOTAL_QTY,
            -- 2. ยอดของแถม
            SUM(TRANSTKD.TRD_Q_FREE) AS TRD_Q_FREE,
            -- 3. ยอดรวม (ปกติ + ของแถม) แก้ไขตรงนี้ครับ
            SUM(ISNULL(TRANSTKD.TRD_QTY, 0) + ISNULL(TRANSTKD.TRD_Q_FREE, 0)) AS TRD_QRT_SUM      
        FROM
            DOCINFO WITH (NOLOCK)
            JOIN DOCTYPE WITH (NOLOCK) ON DOCINFO.DI_DT = DOCTYPE.DT_KEY
            JOIN TRANSTKH WITH (NOLOCK) ON DOCINFO.DI_KEY = TRANSTKH.TRH_DI
            JOIN TRANSTKD WITH (NOLOCK) ON TRANSTKH.TRH_KEY = TRANSTKD.TRD_TRH
            JOIN SKUMASTER WITH (NOLOCK) ON TRANSTKD.TRD_SKU = SKUMASTER.SKU_KEY
            JOIN WARELOCATION WITH (NOLOCK) ON TRANSTKD.TRD_WL = WARELOCATION.WL_KEY 
            JOIN WAREHOUSE WITH (NOLOCK) ON WARELOCATION.WL_WH = WAREHOUSE.WH_KEY
            JOIN ICCAT WITH (NOLOCK) ON SKUMASTER.SKU_ICCAT = ICCAT.ICCAT_KEY
            LEFT JOIN BRAND WITH (NOLOCK) ON SKUMASTER.SKU_BRN = BRAND.BRN_KEY
        WHERE 
            DOCINFO.DI_ACTIVE = 0 
            $docCondition
            $f_wh
            $f_iccat
            $f_brand
            $extraWhere
        GROUP BY 
            SKUMASTER.SKU_CODE,
            SKUMASTER.SKU_NAME,
            WAREHOUSE.WH_CODE
        ORDER BY 
            WAREHOUSE.WH_CODE,       
            SKUMASTER.SKU_CODE,
            SKUMASTER.SKU_NAME                 
    ";

    // *** DEBUG: บันทึก SQL ลงไฟล์แยกตามประเภท (ord, recv, res, ship) ***
    // file_put_contents("debug_sql_{$typeField}.txt", $sql);

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $sku = trim($row['SKU_CODE']);
        $wh = trim($row['WH_CODE']);
        $k = $sku . '|' . $wh;

        if (!isset($dataArr[$k])) {
            $dataArr[$k] = [
                'sku' => $sku,
                'name' => $row['SKU_NAME'],
                'wh' => $wh,
                'unit' => '', // กรณีไม่มีใน Master ให้เป็นค่าว่างไว้ก่อน
                'bal' => 0, 'ord' => 0, 'recv' => 0, 'res' => 0, 'ship' => 0
            ];
        }
        $dataArr[$k][$typeField] = (float)$row['TRD_QRT_SUM'];
    }
}

$filters = [$filter_wh, $filter_iccat, $filter_brand];

// ---------------------------------------------------------
// เรียกใช้ฟังก์ชัน (ใส่เงื่อนไขวันที่เฉพาะ Step 4, 5)
// ---------------------------------------------------------

// STEP 2: สั่งซื้อ (ไม่กรองวันที่)
processTransaction($conn_sqlsvr, $stockData, 'ord', 203, $filters, 'OR01', $dateFilterSql);

// STEP 3: รับเข้า (ไม่กรองวันที่)
processTransaction($conn_sqlsvr, $stockData, 'recv', 303, $filters, '5', $dateFilterSql);

// STEP 4: จองสินค้า (*** กรองวันที่ปีปัจจุบัน ***)
processTransaction($conn_sqlsvr, $stockData, 'res', 207, $filters, null, $dateFilterSql);

// STEP 5: ส่งสินค้า (*** กรองวันที่ปีปัจจุบัน ***)
processTransaction($conn_sqlsvr, $stockData, 'ship', [302, 307], $filters, null, $dateFilterSql);


// ---------------------------------------------------------
// STEP 6: แสดงผล CSV
// ---------------------------------------------------------
// เพิ่ม Header ให้ครบตามข้อมูลที่จะแสดง
//echo "รหัสสินค้า,ชื่อสินค้า,คลัง,คงเหลือ,สั่งซื้อ,รับเข้า,ค้างรับ,จองสินค้า,ส่งสินค้า,ค้างส่ง\n";

// แปลงวันที่เป็น Timestamp
$timestamp = strtotime($dateEnd);

// จัดรูปแบบ: วัน/เดือน/ (ปี ค.ศ. + 543)
$thaiDate = date('d/m/', $timestamp) . (date('Y', $timestamp) + 543);

preg_match_all("/'([^']+)'/", $in_icc_codes, $matches);

// นำผลลัพธ์มาเชื่อมกันด้วย ,
$result = implode(' - ', $matches[1]);

echo "รายงาน ยอดคงเหลือ - ยอดค้างรับ - ยอดค้างส่ง\n";
echo "ประเภทสินค้า : $result\n";
echo "คงเหลือ ณ วันที่ $thaiDate\n";
echo "รหัสสินค้า,ชื่อสินค้า,คลัง,หน่วยนับ,จำนวนคงเหลือ,จำนวนค้างรับ,จำนวนค้างส่ง,จํานวนสุทธิ\n";

function toUTF8($str)
{
    return iconv('Windows-874', 'UTF-8//IGNORE', trim($str));
}

foreach ($stockData as $row) {
    // ถ้าคงเหลือน้อยกว่า 1 ให้ข้าม (ตามเงื่อนไขเดิม)
    if ($row['bal'] < 1) {
        continue;
    }

    $sku = str_replace([",", "\n", "\r"], " ", ((string)$row['sku']));
    $name = str_replace([",", "\n", "\r"], " ", ((string)$row['name']));
    $wh = str_replace([",", "\n", "\r"], " ", ((string)$row['wh']));
    $unit = str_replace([",", "\n", "\r"], " ", ((string)$row['unit']));

    // คำนวณค้างรับ และ ค้างส่ง (ห้ามติดลบ)
    $pending_recv = max(0, $row['ord'] - $row['recv']);
    $pending_ship = max(0, $row['res'] - $row['ship']);

    echo $sku . ",";
    echo $name . ",";
    echo $wh . ",";
    echo $unit . ",";
    echo $row['bal'] . ",";  // คงเหลือ

    //echo $row['ord'] . ",";  // สั่งซื้อ
    //echo $row['recv'] . ","; // รับเข้า
    echo $pending_recv . ","; // ค้างรับ (New!)

    //echo $row['res'] . ",";  // จองสินค้า
    //echo $row['ship'] . ","; // ส่งสินค้า
    echo $pending_ship . ","; // ค้างรับ (New!)
    echo ($row['bal'] + $pending_recv) - $pending_ship . "\n"; // จํานวนสุทธิ (New!)
}

exit();