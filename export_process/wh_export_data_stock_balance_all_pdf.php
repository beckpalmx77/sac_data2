<?php
// =========================================================
// 1. ตั้งค่าพื้นฐาน และ Database
// =========================================================
require_once '../vendor/autoload.php';

// เพิ่มเวลา Time Limit และ Memory Limit (เผื่อข้อมูลเยอะ)
set_time_limit(300);
ini_set('memory_limit', '512M');

date_default_timezone_set('Asia/Bangkok');
include('../config/connect_sqlserver.php');

// =========================================================
// 2. Logic การเตรียม Query (Filter Data)
// =========================================================

// Helper Function
function quoteArray($arr, $conn) {
    return array_map(function ($v) use ($conn) {
        return $conn->quote(trim($v));
    }, $arr);
}

// เก็บเงื่อนไข และ Description สำหรับหัวกระดาษ
$sql_condition = "";
$filter_desc_arr = [];

// --- 2.1 Date Range (ตัดยอด ณ วันที่) ---
// Default: วันปัจจุบัน
$as_of_date_show = date('d/m/Y');

if (!empty($_POST['end_date'])) {
    $end = $conn_sqlsvr->quote($_POST['end_date']);
    $sql_condition .= " AND DOCINFO.DI_DATE <= $end ";

    // แปลงวันที่เพื่อแสดงผล
    $date_obj = date_create($_POST['end_date']);
    $as_of_date_show = date_format($date_obj, "d/m/Y");

    $filter_desc_arr[] = "ข้อมูล ณ วันที่: " . $as_of_date_show;
} else {
    $filter_desc_arr[] = "ข้อมูล ณ วันที่: " . $as_of_date_show;
}

// --- 2.2 ICCAT ---
if (!empty($_POST['icc_codes'])) {
    $in_icc = implode(',', quoteArray($_POST['icc_codes'], $conn_sqlsvr));
    $sql_condition .= " AND ICCAT.ICCAT_CODE IN ($in_icc) ";
    //$filter_desc_arr[] = "หมวดสินค้า: ระบุ"; // เปิดใช้ถ้าต้องการแสดง
}

// สร้างข้อความแสดงเงื่อนไขที่หัวกระดาษ
$filter_display = empty($filter_desc_arr) ? "ทั้งหมด" : implode(', ', $filter_desc_arr);

// =========================================================
// 3. Query Data
// =========================================================

$String_Sql = "
SELECT 
    SM.SKU_CODE,
    SM.SKU_NAME,    
    WH.WH_CODE,
    WH.WH_NAME,
    WL.WL_CODE,
    ICCAT_CODE,
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
WHERE WL.WL_CODE = 'K3' AND SM.SKU_ENABLE = 'Y'
    $sql_condition
GROUP BY 
    SM.SKU_CODE,
    SM.SKU_NAME,
    WH.WH_CODE,
    WH.WH_NAME,
    WL.WL_CODE,
    UQ.UTQ_NAME,
    ICCAT_CODE
HAVING 
    SUM(SKM.SKM_QTY) > 0
ORDER BY 
    WH.WH_CODE ASC,
    WL.WL_CODE ASC,
    SM.SKU_CODE ASC
";

$query = $conn_sqlsvr->prepare($String_Sql);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_ASSOC);

// =========================================================
// 4. ตั้งค่า mPDF (ใช้ Config เดียวกับไฟล์ต้นฉบับ)
// =========================================================

$defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
$fontDirs = $defaultConfig['fontDir'];
$defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
$fontData = $defaultFontConfig['fontdata'];

$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4-L',
    'margin_left' => 10,
    'margin_right' => 10,
    'margin_top' => 40,
    'margin_bottom' => 10,
    'default_font_size' => 10,
    'default_font' => 'prompt',
    'autoScriptToLang' => true,
    'autoLangToFont' => true,

    'fontDir' => array_merge($fontDirs, [
        '../fonts',
    ]),
    'fontdata' => $fontData + [
            'prompt' => [
                'R' => 'Prompt-Regular.ttf',
                'B' => 'Prompt-Bold.ttf',
            ]
        ],
]);

// CSS Styles (ชุดเดียวกับต้นฉบับ)
$css = '
<style>
    body { font-family: "prompt"; line-height: 1.5; }
    table { width: 100%; border-collapse: collapse; }
    
    th { 
        border: 1px solid #333; 
        background-color: #f2f2f2; 
        padding: 8px 5px; 
        font-weight: bold; 
        text-align: center;
        line-height: 1.4;
    }
    
    td { 
        border-bottom: 1px dotted #ccc; 
        border-left: 1px solid #eee;
        border-right: 1px solid #eee;
        padding: 6px 5px; 
        vertical-align: top; 
        line-height: 1.4;
    }
    
    .text-right { text-align: right; }
    .text-center { text-align: center; }
    .text-left { text-align: left; }
    
    .grand-total td {
        background-color: #333;
        color: #fff;
        font-weight: bold;
        padding: 8px;
    }
</style>
';

// Header HTML
$thaiDate = date('d/m/') . (date('Y') + 543);
$printTime = date('H:i:s');

$headerHtml = '
<div style="width:100%;">
    <div style="float:left; width:30%; font-size:12pt; font-weight:bold;">บริษัท สงวนออโต้คาร์ จำกัด</div>
    <div style="float:right; width:30%; text-align:right;">พิมพ์เมื่อ: ' . $thaiDate . ' ' . $printTime . '</div>
    <div style="clear:both;"></div>
    
    <div style="text-align:center; font-size:16pt; font-weight:bold; margin-top:5px;">รายงานยอดคงเหลือ Stock Balance (K3)</div>
    
    <table style="width:100%; margin-top:10px; border:none; font-size:10pt;">
        <tr>            
            <td style="border:none; width:80%;">' . htmlspecialchars($filter_display) . '</td>
            <td style="border:none; width:20%; text-align:right;">หน้า {PAGENO} / {nbpg}</td>
        </tr>
    </table>
</div>
';

$mpdf->SetHTMLHeader($headerHtml);

// =========================================================
// 5. Generate Table HTML
// =========================================================

$html = '
<table>
    <thead>
        <tr>
            <th width="15%">รหัสสินค้า</th>
            <th width="40%">ชื่อสินค้า</th>
            <th width="15%">คลัง / ที่เก็บ</th>
            <th width="10%">หน่วยนับ</th>
            <th width="15%">จำนวนคงเหลือ</th>
        </tr>
    </thead>
    <tbody>
';

$grand_qty = 0;
$hasData = false;
$total_cols = 5;

foreach ($results as $row) {
    $hasData = true;

    $html .= '<tr>';
    $html .= '<td class="text-left">' . htmlspecialchars($row['SKU_CODE']) . '</td>';
    $html .= '<td class="text-left">' . htmlspecialchars($row['SKU_NAME']) . '</td>';
    $html .= '<td class="text-center">' . htmlspecialchars($row['WH_CODE']) . ' / ' . htmlspecialchars($row['WL_CODE']) . '</td>';
    $html .= '<td class="text-center">' . htmlspecialchars($row['UNIT_NAME']) . '</td>';
    $html .= '<td class="text-right">' . number_format($row['SUM_QTY'], 2) . '</td>';
    $html .= '</tr>';

    $grand_qty += $row['SUM_QTY'];
}

if (!$hasData) {
    $html .= '<tr><td colspan="' . $total_cols . '" class="text-center" style="padding:20px;">-- ไม่พบข้อมูลตามเงื่อนไข --</td></tr>';
}

$html .= '
    </tbody>
    <tfoot>
        <tr class="grand-total">
            <td colspan="4" class="text-right">รวมสุทธิ</td>
            <td class="text-right">' . number_format($grand_qty, 2) . '</td>
        </tr>
    </tfoot>
</table>';

// =========================================================
// 6. Output PDF
// =========================================================
$mpdf->WriteHTML($css);
$mpdf->WriteHTML($html);

$filename = "Report_StockBalance_K3_" . date('Ymd_His') . ".pdf";
$mpdf->Output($filename, 'I');
exit();
?>