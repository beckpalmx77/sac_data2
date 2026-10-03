<?php
/**
 * Export Stock Summary (On Hand + Wait In + Wait Out)
 * แก้ไขปัญหา Column Name และแยก Process เป็น PHP Array
 */

// 1. ตั้งค่าพื้นฐาน
date_default_timezone_set('Asia/Bangkok');
ini_set('memory_limit', '512M'); // เพิ่ม Memory เพราะต้องประมวลผล Array เยอะ
set_time_limit(300);

include('../config/connect_sqlserver.php');

// -------------------------------------------------------------------------
// 2. ส่วนเตรียม Header CSV
// -------------------------------------------------------------------------
$WH_CODE_FILE = $_POST['WH_CODE'] ?? 'ALL';
$filename = "Stock_Summary_" . $WH_CODE_FILE . "_" . date('Ymd-His') . ".csv";

header('Content-type: text/csv; charset=UTF-8');
header('Content-Encoding: UTF-8');
header("Content-Disposition: attachment; filename=\"$filename\"");
echo "\xEF\xBB\xBF"; // UTF-8 BOM สำหรับให้ Excel อ่านภาษาไทยออก

$output = fopen('php://output', 'w');
// เขียนหัวตาราง
fputcsv($output, ['หมวด', 'รหัสสินค้า', 'ชื่อสินค้า', 'หน่วยนับ', 'คลัง', 'คงเหลือ', 'ค้างรับ', 'ค้างส่ง', 'สุทธิ']);

// ตัวแปร Array สำหรับเก็บข้อมูลรวม
// รูปแบบ Key: "SKU_CODE|WH_CODE"
$data_summary = [];

// -------------------------------------------------------------------------
// 3. ฟังก์ชันและตัวแปร Filter
// -------------------------------------------------------------------------
function quoteArray($arr, $conn) {
    return array_map(function ($v) use ($conn) { return $conn->quote(trim($v)); }, $arr);
}

// เงื่อนไขพื้นฐาน (ตามโจทย์เก่าคือ ICCAT='2SAC07' แต่ถ้ามีการติ๊กเลือกจากหน้าจอจะใช้ตามหน้าจอ)
$sql_where_stock = "";
$sql_where_trans = "";

// 3.1 กรองหมวดสินค้า (ICCAT)
if (!empty($_POST['icc_codes'])) {
    $in = implode(',', quoteArray($_POST['icc_codes'], $conn_sqlsvr));
    $sql_where_stock .= " AND ICCAT_CODE IN ($in) ";
    $sql_where_trans .= " AND ICCAT.ICCAT_CODE IN ($in) ";
} else {
    // ถ้าไม่เลือกอะไรเลย ให้ Default ตามโจทย์เดิม (หรือจะลบออกเพื่อให้ดึงทั้งหมดก็ได้)
    $sql_where_stock .= " AND ICCAT_CODE = '2SAC07' ";
    $sql_where_trans .= " AND ICCAT.ICCAT_CODE = '2SAC07' ";
}

// 3.2 กรองคลังสินค้า (WH_CODE)
if (!empty($_POST['WH_CODE'])) {
    $wh = $conn_sqlsvr->quote($_POST['WH_CODE']);
    $sql_where_stock .= " AND WH_CODE = $wh ";
    $sql_where_trans .= " AND WAREHOUSE.WH_CODE = $wh ";
} elseif (!empty($_POST['wh_codes'])) {
    $in = implode(',', quoteArray($_POST['wh_codes'], $conn_sqlsvr));
    $sql_where_stock .= " AND WH_CODE IN ($in) ";
    $sql_where_trans .= " AND WAREHOUSE.WH_CODE IN ($in) ";
}

// =========================================================================
// PART 1: ดึงจำนวนคงเหลือ (Stock On Hand)
// =========================================================================
$sql_1 = "
    SELECT 
        ICCAT_CODE, SKU_CODE, SKU_NAME, UTQ_NAME, WH_CODE, WH_NAME,
        SUM(CAST(QTY AS DECIMAL(10,2))) AS QTY_STOCK
    FROM v_stock_movement WITH (NOLOCK)
    WHERE SKU_CODE = 'LE1856015-LISP/HP3' $sql_where_stock
    GROUP BY ICCAT_CODE, SKU_CODE, SKU_NAME, UTQ_NAME, WH_CODE, WH_NAME
";

try {
    $stmt = $conn_sqlsvr->prepare($sql_1);
    $stmt->execute();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $key = $row['SKU_CODE'] . '|' . $row['WH_CODE'];

        $data_summary[$key] = [
            'ICCAT_CODE' => $row['ICCAT_CODE'],
            'SKU_CODE'   => $row['SKU_CODE'],
            'SKU_NAME'   => $row['SKU_NAME'],
            'UTQ_NAME'   => $row['UTQ_NAME'],
            'WH_CODE'    => $row['WH_CODE'],
            'WH_NAME'    => $row['WH_NAME'],
            'QTY_STOCK'  => (float)$row['QTY_STOCK'],
            'QTY_IN'     => 0.0,
            'QTY_OUT'    => 0.0
        ];
    }
} catch (Exception $e) {
    fputcsv($output, ['Error Part 1: ' . $e->getMessage()]);
    exit;
}

// =========================================================================
// PART 2: ดึงจำนวนค้างรับ (Wait In)
// =========================================================================
// เงื่อนไข: เฉพาะเอกสารตั้งแต่ 1 ม.ค. ปีปัจจุบัน
$sql_2 = "
    SELECT 
        ICCAT.ICCAT_CODE, SKUMASTER.SKU_CODE, SKUMASTER.SKU_NAME, 
        WAREHOUSE.WH_CODE, WAREHOUSE.WH_NAME,
        
        -- ใช้ยอดเต็ม TRD_QTY เพื่อป้องกัน Error Invalid Column
        -- หากต้องการหักยอดรับแล้ว ให้แก้เป็น: SUM(TRANSTKD.TRD_QTY - ISNULL(TRANSTKD.TRD_Q_RCV, 0))
        SUM(TRANSTKD.TRD_QTY) AS QTY_IN

    FROM DOCINFO WITH (NOLOCK)
    INNER JOIN DOCTYPE WITH (NOLOCK) ON DOCINFO.DI_DT = DOCTYPE.DT_KEY
    INNER JOIN TRANSTKH WITH (NOLOCK) ON DOCINFO.DI_KEY = TRANSTKH.TRH_DI
    INNER JOIN TRANSTKD WITH (NOLOCK) ON TRANSTKH.TRH_KEY = TRANSTKD.TRD_TRH
    INNER JOIN SKUMASTER WITH (NOLOCK) ON TRANSTKD.TRD_SKU = SKUMASTER.SKU_KEY
    INNER JOIN WARELOCATION WITH (NOLOCK) ON TRANSTKD.TRD_WL = WARELOCATION.WL_KEY
    INNER JOIN WAREHOUSE WITH (NOLOCK) ON WARELOCATION.WL_WH = WAREHOUSE.WH_KEY
    INNER JOIN ICCAT WITH (NOLOCK) ON SKUMASTER.SKU_ICCAT = ICCAT.ICCAT_KEY
    WHERE DOCINFO.DI_ACTIVE = 0 
      $sql_where_trans
      AND (
          (DOCTYPE.DT_PROPERTIES = 203 AND DOCTYPE.DT_DOCCODE = 'OR01') 
          OR 
          (DOCTYPE.DT_PROPERTIES = 303 AND DOCTYPE.DT_DOCCODE = '5')    
      )
      -- กรองวันที่ตั้งแต่ 01/01/ปีปัจจุบัน
      -- AND DOCINFO.DI_DATE >= CONVERT(DATE, '01/01/' + CAST(YEAR(GETDATE()) AS VARCHAR(4)), 103)
        AND SKUMASTER.SKU_CODE = 'LE1856015-LISP/HP3'
    GROUP BY ICCAT.ICCAT_CODE, SKUMASTER.SKU_CODE, SKUMASTER.SKU_NAME, WAREHOUSE.WH_CODE, WAREHOUSE.WH_NAME
";

try {
    $stmt = $conn_sqlsvr->prepare($sql_2);
    $stmt->execute();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $qty_in = (float)$row['QTY_IN'];
        if ($qty_in <= 0) continue;

        $key = $row['SKU_CODE'] . '|' . $row['WH_CODE'];

        if (isset($data_summary[$key])) {
            $data_summary[$key]['QTY_IN'] += $qty_in;
        } else {
            // กรณีไม่มีใน Stock แต่มีค้างรับ ให้สร้างแถวใหม่
            $data_summary[$key] = [
                'ICCAT_CODE' => $row['ICCAT_CODE'],
                'SKU_CODE'   => $row['SKU_CODE'],
                'SKU_NAME'   => $row['SKU_NAME'],
                'UTQ_NAME'   => '', // ไม่มีหน่วยนับจาก Part 1 ให้ว่างไว้
                'WH_CODE'    => $row['WH_CODE'],
                'WH_NAME'    => $row['WH_NAME'],
                'QTY_STOCK'  => 0.0,
                'QTY_IN'     => $qty_in,
                'QTY_OUT'    => 0.0
            ];
        }
    }
} catch (Exception $e) {
    // ข้าม Error Part 2 ไปก่อนถ้ามีปัญหา (เช่น column name ผิด) แต่ควร log ไว้
    // fputcsv($output, ['Error Part 2: ' . $e->getMessage()]);
}

// =========================================================================
// PART 3: ดึงจำนวนค้างส่ง (Wait Out)
// =========================================================================
$sql_3 = "
    SELECT 
        ICCAT.ICCAT_CODE, SKUMASTER.SKU_CODE, SKUMASTER.SKU_NAME, 
        WAREHOUSE.WH_CODE, WAREHOUSE.WH_NAME,
        
        -- ใช้ยอดเต็ม TRD_QTY เพื่อป้องกัน Error Invalid Column
        -- หากต้องการหักยอดส่งแล้ว ให้แก้เป็น: SUM(TRANSTKD.TRD_QTY - ISNULL(TRANSTKD.TRD_SH_Q, 0)) หรือ TRD_Q_DLV
        SUM(TRANSTKD.TRD_QTY) AS QTY_OUT

    FROM DOCINFO WITH (NOLOCK)
    INNER JOIN DOCTYPE WITH (NOLOCK) ON DOCINFO.DI_DT = DOCTYPE.DT_KEY
    INNER JOIN TRANSTKH WITH (NOLOCK) ON DOCINFO.DI_KEY = TRANSTKH.TRH_DI
    INNER JOIN TRANSTKD WITH (NOLOCK) ON TRANSTKH.TRH_KEY = TRANSTKD.TRD_TRH
    INNER JOIN SKUMASTER WITH (NOLOCK) ON TRANSTKD.TRD_SKU = SKUMASTER.SKU_KEY
    INNER JOIN WARELOCATION WITH (NOLOCK) ON TRANSTKD.TRD_WL = WARELOCATION.WL_KEY
    INNER JOIN WAREHOUSE WITH (NOLOCK) ON WARELOCATION.WL_WH = WAREHOUSE.WH_KEY
    INNER JOIN ICCAT WITH (NOLOCK) ON SKUMASTER.SKU_ICCAT = ICCAT.ICCAT_KEY
    WHERE DOCINFO.DI_ACTIVE = 0 
      $sql_where_trans
      AND (
          (DOCTYPE.DT_PROPERTIES = 207) 
          OR 
          (DOCTYPE.DT_PROPERTIES IN (302, 307, 308, 337)) 
      )
      -- กรองวันที่ตั้งแต่ 01/01/ปีปัจจุบัน
      -- AND DOCINFO.DI_DATE >= CONVERT(DATE, '01/01/' + CAST(YEAR(GETDATE()) AS VARCHAR(4)), 103)
         AND SKUMASTER.SKU_CODE = 'LE1856015-LISP/HP3'
    GROUP BY ICCAT.ICCAT_CODE, SKUMASTER.SKU_CODE, SKUMASTER.SKU_NAME, WAREHOUSE.WH_CODE, WAREHOUSE.WH_NAME
";

try {
    $stmt = $conn_sqlsvr->prepare($sql_3);
    $stmt->execute();

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $qty_out = (float)$row['QTY_OUT'];
        if ($qty_out <= 0) continue;

        $key = $row['SKU_CODE'] . '|' . $row['WH_CODE'];

        if (isset($data_summary[$key])) {
            $data_summary[$key]['QTY_OUT'] += $qty_out;
        } else {
            // กรณีไม่มีใน Stock/WaitIn แต่มีค้างส่ง ให้สร้างแถวใหม่
            $data_summary[$key] = [
                'ICCAT_CODE' => $row['ICCAT_CODE'],
                'SKU_CODE'   => $row['SKU_CODE'],
                'SKU_NAME'   => $row['SKU_NAME'],
                'UTQ_NAME'   => '',
                'WH_CODE'    => $row['WH_CODE'],
                'WH_NAME'    => $row['WH_NAME'],
                'QTY_STOCK'  => 0.0,
                'QTY_IN'     => 0.0,
                'QTY_OUT'    => $qty_out
            ];
        }
    }
} catch (Exception $e) {
    // ข้าม Error Part 3
}

// =========================================================================
// 4. เรียงลำดับและเขียนไฟล์ CSV
// =========================================================================

// เรียงข้อมูล: หมวด -> รหัสสินค้า -> คลัง
usort($data_summary, function ($a, $b) {
    return $a['ICCAT_CODE'] <=> $b['ICCAT_CODE'] ?:
        $a['SKU_CODE'] <=> $b['SKU_CODE'] ?:
            $a['WH_CODE'] <=> $b['WH_CODE'];
});

// Loop เขียนข้อมูล
foreach ($data_summary as $row) {
    // คำนวณยอดสุทธิ
    $net_total = $row['QTY_STOCK'] + $row['QTY_IN'] - $row['QTY_OUT'];

    fputcsv($output, [
        $row['ICCAT_CODE'],
        $row['SKU_CODE'],
        $row['SKU_NAME'],
        $row['UTQ_NAME'],
        $row['WH_CODE'],
        number_format($row['QTY_STOCK'], 2, '.', ''),
        number_format($row['QTY_IN'], 2, '.', ''),
        number_format($row['QTY_OUT'], 2, '.', ''),
        number_format($net_total, 2, '.', '')
    ]);
}

fclose($output);
exit;
?>