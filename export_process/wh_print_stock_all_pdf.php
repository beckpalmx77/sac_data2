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

// Helper Function สำหรับ Quote ค่า String ป้องกัน SQL Injection
function quoteArray($arr, $conn) {
    return array_map(function ($v) use ($conn) {
        return $conn->quote(trim($v));
    }, $arr);
}

// เก็บเงื่อนไข WHERE
$where = [];
$filter_desc_arr = [];

// --- 2.1 Mandatory Conditions (เงื่อนไขบังคับจาก SQL ของคุณ) ---
$where[] = "DOCINFO.DI_ACTIVE = 0";
$where[] = "SKUMASTER.SKU_ENABLE = 'Y'";
// $where[] = "TRANSTKH.TRH_LSTATUS = 1";
$where[] = "WARELOCATION.WL_CODE = 'K3'";        // บังคับคลัง K3
$where[] = "UOFQTY.UTQ_NAME LIKE '%เส้น%'";       // บังคับหน่วยเส้น

// --- 2.2 Date Range (รับจาก Front End) ---
if (!empty($_POST['start_date']) && !empty($_POST['end_date'])) {
    $start = $conn_sqlsvr->quote($_POST['start_date']);
    $end   = $conn_sqlsvr->quote($_POST['end_date']);
    $where[] = "DI_DATE BETWEEN $start AND $end";

    // เก็บ description ไว้โชว์หัวกระดาษ
    $filter_desc_arr[] = "วันที่: " . $_POST['start_date'] . " ถึง " . $_POST['end_date'];
} else {
    // กรณีไม่มีวันที่ส่งมา (กัน Error) ให้ใช้วันที่ปัจจุบัน
    $today = $conn_sqlsvr->quote(date('Y-m-d'));
    $where[] = "DI_DATE BETWEEN $today AND $today";
    $filter_desc_arr[] = "วันที่: " . date('Y-m-d');
}

// --- 2.3 ICCAT (รับจาก Checkbox ที่ Front End) ---
if (!empty($_POST['icc_codes'])) {
    // สร้าง string สำหรับ IN (...)
    $in_icc = implode(',', quoteArray($_POST['icc_codes'], $conn_sqlsvr));
    $where[] = "ICCAT.ICCAT_CODE IN ($in_icc)";

    // Description (ตัดแสดงแค่บางส่วนถ้าเยอะเกิน)
    //$count_icc = count($_POST['icc_codes']);
    //$filter_desc_arr[] = "หมวดสินค้า: เลือก " . $count_icc . " รายการ";
}

// --- 2.4 BRAND (เผื่อเลือกเพิ่ม) ---
if (!empty($_POST['brn_codes'])) {
    $in_brn = implode(',', quoteArray($_POST['brn_codes'], $conn_sqlsvr));
    $where[] = "BRAND.BRN_CODE IN ($in_brn)";
    $filter_desc_arr[] = "ยี่ห้อ: ระบุ";
}

// รวม WHERE Clause
$WHERE_SQL = "WHERE " . implode(' AND ', $where);

// สร้างข้อความแสดงเงื่อนไขที่หัวกระดาษ
$filter_display = empty($filter_desc_arr) ? "ทั้งหมด" : implode(', ', $filter_desc_arr);

// =========================================================
// 3. Query Data
// =========================================================

$String_Sql = "
SELECT 
    DI_DATE,
    SKUMASTER.SKU_CODE, 
    SKUMASTER.SKU_NAME, 
    WAREHOUSE.WH_CODE,
    WARELOCATION.WL_CODE,
    SUM(ISNULL(TRANSTKD.TRD_QTY, 0) + ISNULL(TRANSTKD.TRD_Q_FREE, 0)) AS TRD_QRT_SUM, 
    UOFQTY.UTQ_NAME
FROM DOCINFO WITH (NOLOCK)
    JOIN DOCTYPE WITH (NOLOCK) ON DOCINFO.DI_DT = DOCTYPE.DT_KEY
    JOIN TRANSTKH WITH (NOLOCK) ON DOCINFO.DI_KEY = TRANSTKH.TRH_DI
    JOIN TRANSTKD WITH (NOLOCK) ON TRANSTKH.TRH_KEY = TRANSTKD.TRD_TRH
    JOIN SKUMASTER WITH (NOLOCK) ON TRANSTKD.TRD_SKU = SKUMASTER.SKU_KEY
    JOIN WARELOCATION WITH (NOLOCK) ON TRANSTKD.TRD_WL = WARELOCATION.WL_KEY 
    JOIN WAREHOUSE WITH (NOLOCK) ON WARELOCATION.WL_WH = WAREHOUSE.WH_KEY
    JOIN ICCAT WITH (NOLOCK) ON SKUMASTER.SKU_ICCAT = ICCAT.ICCAT_KEY
    LEFT JOIN BRAND WITH (NOLOCK) ON SKUMASTER.SKU_BRN = BRAND.BRN_KEY
    LEFT JOIN UOFQTY WITH (NOLOCK) ON UOFQTY.UTQ_KEY = SKUMASTER.SKU_S_UTQ
$WHERE_SQL
GROUP BY 
    DI_DATE,
    SKUMASTER.SKU_CODE, 
    SKUMASTER.SKU_NAME, 
    WAREHOUSE.WH_CODE, 
    WARELOCATION.WL_CODE, 
    UOFQTY.UTQ_NAME
ORDER BY 
    DI_DATE ASC, 
    SKUMASTER.SKU_CODE ASC
";

$query = $conn_sqlsvr->prepare($String_Sql);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_ASSOC);

// =========================================================
// 4. ตั้งค่า mPDF
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

// CSS Styles
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
    
    <div style="text-align:center; font-size:16pt; font-weight:bold; margin-top:5px;">รายงานตรวจสอบรายการรับสินค้าเข้า SAC คลัง K3</div>
    
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
            <th width="10%">วันที่</th>
            <th width="20%">รหัสสินค้า</th>
            <th width="40%">ชื่อสินค้า</th>
            <th width="10%">คลัง/ตำแหน่ง</th>   
            <th width="10%">จำนวน</th>
            <th width="10%">หน่วย</th>
        </tr>
    </thead>
    <tbody>
';

$grand_qty = 0;
$hasData = false;
$total_cols = 6;

foreach ($results as $row) {
    $hasData = true;

    // แปลงวันที่เป็น d/m/Y
    $date_obj = date_create($row['DI_DATE']);
    $date_show = date_format($date_obj, "d/m/Y");

    // Print Data
    $html .= '<tr>';
    $html .= '<td class="text-center">' . $date_show . '</td>';
    $html .= '<td>' . htmlspecialchars($row['SKU_CODE']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['SKU_NAME']) . '</td>';
    $html .= '<td class="text-center">' . htmlspecialchars($row['WH_CODE']) . ' / ' . htmlspecialchars($row['WL_CODE']) . '</td>';
    $html .= '<td class="text-right">' . number_format($row['TRD_QRT_SUM'], 2) . '</td>'; // ใช้ตัวแปรใหม่จาก SQL
    $html .= '<td class="text-center">' . htmlspecialchars($row['UTQ_NAME']) . '</td>';
    $html .= '</tr>';

    $grand_qty += $row['TRD_QRT_SUM'];
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
            <td></td>
        </tr>
    </tfoot>
</table>';

// =========================================================
// 6. Output PDF
// =========================================================
$mpdf->WriteHTML($css);
$mpdf->WriteHTML($html);

$filename = "Report_K3_" . date('Ymd_His') . ".pdf";
$mpdf->Output($filename, 'I');
exit();