<?php
// =========================================================
// 1. ตั้งค่าพื้นฐาน และ Database
// =========================================================
require_once '../vendor/autoload.php';

// เพิ่มเวลา Time Limit และ Memory Limit สำหรับประมวลผล PDF
set_time_limit(300);
ini_set('memory_limit', '512M');

date_default_timezone_set('Asia/Bangkok');
include('../config/connect_sqlserver.php'); // ตรวจสอบ Path นี้ให้ถูกต้อง
//include('../config/connect_sqlserver40.php');

// =========================================================
// 2. Logic การดึงข้อมูล (Query Data)
// =========================================================

$stockData = [];
$currentYearStart = date('Y') . '-01-01';
$dateEnd = date('Y-m-d');
$dateFilterSql = " AND (DOCINFO.DI_DATE BETWEEN '$currentYearStart' AND '$dateEnd') ";

// Helper Functions
function getKey($sku, $wh)
{
    return trim($sku) . '|' . trim($wh);
}

function quoteArray($arr, $conn)
{
    return array_map(function ($v) use ($conn) {
        return $conn->quote(trim($v));
    }, $arr);
}

// รับค่า Filter จาก Form
$filter_wh = "";
$filter_iccat = "";
$filter_brand = "";
$in_icc_codes_display = "ทั้งหมด";

if (!empty($_POST['wh_codes'])) {
    $in = implode(',', quoteArray($_POST['wh_codes'], $conn_sqlsvr));
    $filter_wh = " AND WAREHOUSE.WH_CODE IN ($in) ";
}

if (!empty($_POST['icc_codes'])) {
    $in = implode(',', quoteArray($_POST['icc_codes'], $conn_sqlsvr));
    $filter_iccat = " AND ICCAT.ICCAT_CODE IN ($in) ";
    // ดึงชื่อมาโชว์
    preg_match_all("/'([^']+)'/", $in, $matches);
    $in_icc_codes_display = implode(', ', $matches[1]);
}

if (!empty($_POST['brn_codes'])) {
    $in = implode(',', quoteArray($_POST['brn_codes'], $conn_sqlsvr));
    $filter_brand = " AND BRAND.BRN_CODE IN ($in) ";
}

// --- QUERY 1: ยอดคงเหลือ (Balance) ---
$sql_balance = "
    SELECT V.SKU_CODE, V.SKU_NAME, V.WH_CODE, SUM(CAST(V.QTY AS DECIMAL(10,2))) as QTY, UOFQTY.UTQ_NAME, WAREHOUSE.WH_NAME
    FROM v_stock_movement V WITH (NOLOCK)
    LEFT JOIN SKUMASTER WITH (NOLOCK) ON V.SKU_CODE = SKUMASTER.SKU_CODE
    LEFT JOIN ICCAT WITH (NOLOCK) ON SKUMASTER.SKU_ICCAT = ICCAT.ICCAT_KEY
    LEFT JOIN BRAND WITH (NOLOCK) ON SKUMASTER.SKU_BRN = BRAND.BRN_KEY
    LEFT JOIN WAREHOUSE WITH (NOLOCK) ON V.WH_CODE = WAREHOUSE.WH_CODE
    LEFT JOIN UOFQTY WITH (NOLOCK) ON UOFQTY.UTQ_KEY = SKUMASTER.SKU_S_UTQ    
    WHERE SKUMASTER.SKU_ENABLE = 'Y' $filter_wh $filter_iccat $filter_brand
    GROUP BY V.SKU_CODE, V.SKU_NAME, V.WH_CODE, UOFQTY.UTQ_NAME ,WAREHOUSE.WH_NAME
    -- Sort เพื่อให้ Grouping ทำงานได้ถูกต้อง
    ORDER BY V.WH_CODE ASC, V.SKU_CODE ASC 
";

$stmt = $conn_sqlsvr->prepare($sql_balance);
$stmt->execute();
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $k = getKey($row['SKU_CODE'], $row['WH_CODE']);
    if (!isset($stockData[$k])) {
        $stockData[$k] = [
            'sku' => $row['SKU_CODE'],
            'name' => $row['SKU_NAME'],
            'wh' => $row['WH_CODE'],
            'unit' => $row['UTQ_NAME'],
            'bal' => 0, 'ord' => 0, 'recv' => 0, 'res' => 0, 'ship' => 0
        ];
    }
    $stockData[$k]['bal'] = (float)$row['QTY'];
}

// --- FUNCTION: ดึงยอดเคลื่อนไหว (Transactions) ---
function processTransaction($conn, &$dataArr, $typeField, $docProps, $filters, $docCode = null, $extraWhere = "")
{
    list($f_wh, $f_iccat, $f_brand) = $filters;

    $props = is_array($docProps) ? implode(',', $docProps) : $docProps;
    $docCondition = " AND DOCTYPE.DT_PROPERTIES IN ($props) ";
    if ($docCode) $docCondition .= " AND DOCTYPE.DT_DOCCODE = '$docCode' ";

    $sql = "
        SELECT SKUMASTER.SKU_CODE, SKUMASTER.SKU_NAME, WAREHOUSE.WH_CODE,
            SUM(ISNULL(TRANSTKD.TRD_QTY, 0) + ISNULL(TRANSTKD.TRD_Q_FREE, 0)) AS TRD_QRT_SUM      
        FROM DOCINFO WITH (NOLOCK)
            JOIN DOCTYPE WITH (NOLOCK) ON DOCINFO.DI_DT = DOCTYPE.DT_KEY
            JOIN TRANSTKH WITH (NOLOCK) ON DOCINFO.DI_KEY = TRANSTKH.TRH_DI
            JOIN TRANSTKD WITH (NOLOCK) ON TRANSTKH.TRH_KEY = TRANSTKD.TRD_TRH
            JOIN SKUMASTER WITH (NOLOCK) ON TRANSTKD.TRD_SKU = SKUMASTER.SKU_KEY
            JOIN WARELOCATION WITH (NOLOCK) ON TRANSTKD.TRD_WL = WARELOCATION.WL_KEY 
            JOIN WAREHOUSE WITH (NOLOCK) ON WARELOCATION.WL_WH = WAREHOUSE.WH_KEY
            JOIN ICCAT WITH (NOLOCK) ON SKUMASTER.SKU_ICCAT = ICCAT.ICCAT_KEY
            LEFT JOIN BRAND WITH (NOLOCK) ON SKUMASTER.SKU_BRN = BRAND.BRN_KEY
        WHERE DOCINFO.DI_ACTIVE = 0 AND SKUMASTER.SKU_ENABLE = 'Y' $docCondition $f_wh $f_iccat $f_brand $extraWhere
        GROUP BY SKUMASTER.SKU_CODE, SKUMASTER.SKU_NAME, WAREHOUSE.WH_CODE
    ";

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $k = trim($row['SKU_CODE']) . '|' . trim($row['WH_CODE']);
        // สร้าง Row ใหม่กรณีที่ไม่มีใน Balance แต่มีการเคลื่อนไหว
        if (!isset($dataArr[$k])) {
            $dataArr[$k] = [
                'sku' => trim($row['SKU_CODE']),
                'name' => $row['SKU_NAME'],
                'wh' => trim($row['WH_CODE']),
                'unit' => '',
                'bal' => 0, 'ord' => 0, 'recv' => 0, 'res' => 0, 'ship' => 0
            ];
        }
        $dataArr[$k][$typeField] = (float)$row['TRD_QRT_SUM'];
    }
}

$filters = [$filter_wh, $filter_iccat, $filter_brand];
processTransaction($conn_sqlsvr, $stockData, 'ord', 203, $filters, 'OR01', $dateFilterSql); // สั่งซื้อ
processTransaction($conn_sqlsvr, $stockData, 'recv', 303, $filters, '5', $dateFilterSql);    // รับของ
processTransaction($conn_sqlsvr, $stockData, 'res', 207, $filters, null, $dateFilterSql);     // จอง
processTransaction($conn_sqlsvr, $stockData, 'ship', [302, 307], $filters, null, $dateFilterSql); // ตัดจ่าย

// สำคัญ: Sort Array อีกครั้งเพื่อให้มั่นใจว่า WH อยู่กลุ่มเดียวกันก่อนส่งเข้า PDF
usort($stockData, function ($a, $b) {
    return strcmp($a['wh'], $b['wh']);
});

// =========================================================
// 3. สร้าง PDF (mPDF Setup with Custom Font)
// =========================================================

$defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
$fontDirs = $defaultConfig['fontDir'];

$defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
$fontData = $defaultFontConfig['fontdata'];

// สร้าง Instance mPDF
$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4-L', // แนวนอน
    'margin_left' => 10,
    'margin_right' => 10,
    // [แก้ไข] เพิ่มระยะขอบบนเป็น 45 เพื่อป้องกัน Header ทับเนื้อหา
    'margin_top' => 45,
    'margin_bottom' => 10,
    'default_font_size' => 10,
    'default_font' => 'prompt',

    // [แก้ไข] เปิดใช้งาน Script เพื่อแก้ปัญหาสระลอย/หายในภาษาไทย
    'autoScriptToLang' => true,
    'autoLangToFont' => true,

    // ตั้งค่า Path ของ Font
    'fontDir' => array_merge($fontDirs, [
        '../fonts',
    ]),

    // Map ชื่อ Font
    'fontdata' => $fontData + [
            'prompt' => [
                'R' => 'Prompt-Regular.ttf',
                'B' => 'Prompt-Bold.ttf',
                // 'I' => 'Prompt-Italic.ttf',
            ]
        ],
]);

// CSS Styles
$css = '
<style>
    /* [แก้ไข] เพิ่ม line-height เป็น 1.5 เพื่อไม่ให้สระชั้นบนโดนตัด */
    body { font-family: "prompt"; line-height: 1.5; }
    
    table { width: 100%; border-collapse: collapse; }
    
    th { 
        border: 1px solid #333; 
        background-color: #f2f2f2; 
        /* [แก้ไข] เพิ่ม Padding บนล่าง */
        padding: 8px 5px; 
        font-weight: bold; 
        text-align: center;
        line-height: 1.4;
    }
    
    td { 
        border-bottom: 1px dotted #ccc; 
        border-left: 1px solid #eee;
        border-right: 1px solid #eee;
        /* [แก้ไข] เพิ่ม Padding บนล่าง */
        padding: 6px 5px; 
        vertical-align: top; 
        line-height: 1.4;
    }
    
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    
    /* Group Headers */
    .group-header td {
        background-color: #e3f2fd;
        font-weight: bold;
        border-top: 1px solid #999;
        border-bottom: 1px solid #999;
        padding: 8px;
    }
    
    /* Group Subtotals */
    .group-total td {
        background-color: #fff8e1;
        font-weight: bold;
        border-top: 2px solid #333;
        border-bottom: 2px solid #333;
    }
    
    /* Grand Total */
    .grand-total td {
        background-color: #333;
        color: #fff;
        font-weight: bold;
        padding: 8px;
    }
</style>
';

// Header HTML
$thaiDate = date('d/m/', strtotime($dateEnd)) . (date('Y', strtotime($dateEnd)) + 543);
$printTime = date('H:i:s');

$headerHtml = '
<div style="width:100%;">
    <div style="float:left; width:30%; font-size:12pt; font-weight:bold;">บริษัท สงวนออโต้คาร์ จำกัด</div>
    <div style="float:right; width:30%; text-align:right;">พิมพ์เมื่อ: ' . $thaiDate . ' ' . $printTime . '</div>
    <div style="clear:both;"></div>
    
    <div style="text-align:center; font-size:16pt; font-weight:bold; margin-top:5px;">รายงานสรุปยอดสินค้า (คงเหลือ - ค้างรับ  - ค้างส่ง) </div>
    
    <table style="width:100%; margin-top:10px; border:none; font-size:10pt;">
        <tr>
            <td style="border:none; width:15%; font-weight:bold;">ประเภทสินค้า:</td>
            <td style="border:none; width:65%;">' . $in_icc_codes_display . '</td>
            <td style="border:none; width:20%; text-align:right;">หน้า {PAGENO} / {nbpg}</td>
        </tr>
        <tr>
            <td style="border:none; font-weight:bold;">คงเหลือ ณ วันที่ :</td>
            <td style="border:none;">' . $thaiDate . '</td>
            <td style="border:none;"></td>
        </tr>
    </table>
</div>
';

$mpdf->SetHTMLHeader($headerHtml);

// เริ่มสร้างตาราง
$html = '
<table>
    <thead>
        <tr>
            <th width="15%">รหัสสินค้า</th>
            <th width="35%">ชื่อสินค้า</th>
            <th width="8%">หน่วย</th>
            <th width="10%">คงเหลือ</th>
            <th width="10%">ค้างรับ</th>
            <th width="10%">ค้างส่ง</th>
            <th width="12%">สุทธิ</th>
        </tr>
    </thead>
    <tbody>
';

// =========================================================
// 4. วนลูปแสดงผล Grouping Logic
// =========================================================

$current_wh = null;
$sub_bal = 0;
$sub_net = 0;
$grand_bal = 0;
$grand_net = 0;
$hasData = false;

foreach ($stockData as $row) {

    // คำนวณตัวเลข
    $pending_recv = max(0, $row['ord'] - $row['recv']);
    $pending_ship = max(0, $row['res'] - $row['ship']);
    $net_amount = ($row['bal'] + $pending_recv) - $pending_ship;

    // Skip หากยอดเป็น 0 ทั้งหมด
    if ($row['bal'] == 0 && $pending_recv == 0 && $pending_ship == 0) continue;

    $hasData = true;

    // --- Check WH Change (Control Break) ---
    if ($row['wh'] !== $current_wh) {

        // ถ้าไม่ใช่รอบแรก ให้ปริ้นยอดรวมของคลังเก่าก่อน
        if ($current_wh !== null) {
            $html .= '
            <tr class="group-total">
                <td colspan="3" class="text-right">รวมคลัง ' . htmlspecialchars($current_wh) . '</td>
                <td class="text-right">' . number_format($sub_bal, 2) . '</td>
                <td></td>
                <td></td>
                <td class="text-right">' . number_format($sub_net, 2) . '</td>
            </tr>';
        }

        // Reset Subtotal และตั้งค่าคลังใหม่
        $current_wh = $row['wh'];
        $sub_bal = 0;
        $sub_net = 0;

        // Print Group Header
        $html .= '
        <tr class="group-header">
            <td colspan="7">คลังสินค้า: ' . htmlspecialchars($current_wh) . '</td>
        </tr>';
    }

    // --- Print Data Row ---
    $html .= '<tr>';
    $html .= '<td>' . htmlspecialchars($row['sku']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['name']) . '</td>';
    $html .= '<td class="text-center">' . htmlspecialchars($row['unit']) . '</td>';
    $html .= '<td class="text-right">' . number_format($row['bal'], 2) . '</td>';
    $html .= '<td class="text-right">' . ($pending_recv > 0 ? number_format($pending_recv, 2) : '-') . '</td>';
    $html .= '<td class="text-right">' . ($pending_ship > 0 ? number_format($pending_ship, 2) : '-') . '</td>';

    // Highlight สีแดงถ้าสุทธิติดลบ
    $netStyle = ($net_amount < 0) ? 'color:red; font-weight:bold;' : '';
    $html .= '<td class="text-right" style="' . $netStyle . '">' . number_format($net_amount, 2) . '</td>';
    $html .= '</tr>';

    // Accumulate Totals
    $sub_bal += $row['bal'];
    $sub_net += $net_amount;

    $grand_bal += $row['bal'];
    $grand_net += $net_amount;
}

// --- End Loop Cleanup ---

// ปริ้นยอดรวมคลังสุดท้าย (ถ้ามีข้อมูล)
if ($current_wh !== null) {
    $html .= '
    <tr class="group-total">
        <td colspan="3" class="text-right">รวมคลัง ' . htmlspecialchars($current_wh) . '</td>
        <td class="text-right">' . number_format($sub_bal, 2) . '</td>
        <td></td>
        <td></td>
        <td class="text-right">' . number_format($sub_net, 2) . '</td>
    </tr>';
}

if (!$hasData) {
    $html .= '<tr><td colspan="7" class="text-center" style="padding:20px;">-- ไม่พบข้อมูลตามเงื่อนไข --</td></tr>';
}

// --- Grand Total Footer ---
$html .= '
    </tbody>
    <tfoot>
        <tr class="grand-total">
            <td colspan="3" class="text-right">รวมสุทธิทุกคลัง</td>
            <td class="text-right">' . number_format($grand_bal, 2) . '</td>
            <td></td>
            <td></td>
            <td class="text-right">' . number_format($grand_net, 2) . '</td>
        </tr>
    </tfoot>
</table>';

// =========================================================
// 5. Output PDF
// =========================================================
$mpdf->WriteHTML($css);
$mpdf->WriteHTML($html);

$filename = "Stock_Report_" . date('Ymd_His') . ".pdf";
$mpdf->Output($filename, 'I'); // I = Inline, D = Download
exit();
?>