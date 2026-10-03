<?php
// =========================================================
// 1. ตั้งค่าพื้นฐาน และ Database
// =========================================================
require_once '../vendor/autoload.php';

// เพิ่มเวลา Time Limit และ Memory Limit
set_time_limit(300);
ini_set('memory_limit', '512M');

date_default_timezone_set('Asia/Bangkok');
include('../config/connect_sqlserver.php');

// =========================================================
// 2. Logic การเตรียม Query (Filter Data)
// =========================================================

// Helper Function สำหรับ Quote ค่า Array เพื่อป้องกัน SQL Injection
function quoteArray($arr, $conn)
{
    return array_map(function ($v) use ($conn) {
        return $conn->quote(trim($v));
    }, $arr);
}

$where = [];
$filter_desc_arr = [];

// --- [แก้ไข] รับค่า WH_CODE ให้ตรงกับหน้าจอ (name="wh_codes[]") ---
if (!empty($_POST['wh_codes'])) {
    // ตรวจสอบว่าเป็น Array หรือไม่ (ปกติ checkbox จะส่งมาเป็น array)
    if (is_array($_POST['wh_codes'])) {
        $in = implode(',', quoteArray($_POST['wh_codes'], $conn_sqlsvr));
        $where[] = "WH.WH_CODE IN ($in)";
        $filter_desc_arr[] = "คลัง: " . implode(',', $_POST['wh_codes']);
    } else {
        // กรณีส่งมาค่าเดียว
        $val = $conn_sqlsvr->quote($_POST['wh_codes']);
        $where[] = "WH.WH_CODE = $val";
        $filter_desc_arr[] = "คลัง: " . $_POST['wh_codes'];
    }
}

// ICCAT
if (!empty($_POST['icc_codes'])) {
    $in = implode(',', quoteArray($_POST['icc_codes'], $conn_sqlsvr));
    $where[] = "ICCAT.ICCAT_CODE IN ($in)";

    // ดึงชื่อหมวดมาแสดงใน Header (Optional)
    // preg_match_all("/'([^']+)'/", $in, $matches);
    // $filter_desc_arr[] = "หมวด: " . implode(',', $matches[1]);
    $filter_desc_arr[] = "ระบุหมวดสินค้า";
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

// SKU Enable Check
$where[] = "SM.SKU_ENABLE = 'Y'";

// รวม WHERE Clause
$WHERE_SQL = "";
if (!empty($where)) {
    $WHERE_SQL = "WHERE " . implode(' AND ', $where);
}

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
    WH.WH_NAME,
    WL.WL_CODE,
    UQ.UTQ_NAME
HAVING 
    SUM(SKM.SKM_QTY) > 0
ORDER BY 
    WH.WH_CODE ASC,
    WL.WL_CODE ASC,
    SM.SKU_CODE ASC
";

// Debug Query (ถ้าต้องการดู Query ให้ uncomment บรรทัดล่าง)
// file_put_contents("debug_query.txt", $String_Sql);

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
    'margin_top' => 45,
    'margin_bottom' => 10,
    'default_font_size' => 10,
    'default_font' => 'prompt',
    'autoScriptToLang' => true,
    'autoLangToFont' => true,

    'fontDir' => array_merge($fontDirs, [
        '../fonts', // ตรวจสอบ path font ให้ถูกต้อง
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
    
    <div style="text-align:center; font-size:16pt; font-weight:bold; margin-top:5px;">รายงานตรวจสอบยอดสินค้าคงเหลือ</div>
    
    <table style="width:100%; margin-top:10px; border:none; font-size:10pt;">
        <tr>
            <td style="border:none; width:15%; font-weight:bold;">เงื่อนไข:</td>
            <td style="border:none; width:65%;">' . htmlspecialchars($filter_display) . '</td>
            <td style="border:none; width:20%; text-align:right;">หน้า {PAGENO} / {nbpg}</td>
        </tr>
        <tr>
            <td style="border:none; font-weight:bold;">ข้อมูล ณ วันที่ :</td>
            <td style="border:none;">' . $thaiDate . '</td>
            <td style="border:none;"></td>
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
            <th width="20%">รหัสสินค้า</th>
            <th width="45%">ชื่อสินค้า</th>
            <th width="10%">คลัง</th>          
            <th width="10%">ตำแหน่งเก็บ</th>   
            <th width="5%">หน่วย</th>
            <th width="10%">คงเหลือ</th>
        </tr>
    </thead>
    <tbody>
';

$current_wh = null;
$sub_qty = 0;
$grand_qty = 0;
$hasData = false;

// จำนวนคอลัมน์ทั้งหมด (เพื่อใช้กำหนด colspan)
// รหัส(1) + ชื่อ(1) + คลัง(1) + ตำแหน่ง(1) + หน่วย(1) + คงเหลือ(1) = 6 คอลัมน์
$total_cols = 6;
$cols_before_total = 5; // จำนวนคอลัมน์ก่อนถึงช่องรวมเงิน

foreach ($results as $row) {
    $hasData = true;

    // --- Check WH Change (Grouping) ---
    if ($row['WH_CODE'] !== $current_wh) {

        // ปิดยอดกลุ่มเก่า
        if ($current_wh !== null) {
            $html .= '
            <tr class="group-total">
                <td colspan="' . $cols_before_total . '" class="text-right">รวมคลัง ' . htmlspecialchars($current_wh) . '</td>
                <td class="text-right">' . number_format($sub_qty, 2) . '</td>
            </tr>';
        }

        // เริ่มกลุ่มใหม่
        $current_wh = $row['WH_CODE'];
        $current_wh_name = $row['WH_NAME']; // ใช้หากต้องการแสดงชื่อเต็ม
        $sub_qty = 0;

        $html .= '
        <tr class="group-header">
            <td colspan="' . $total_cols . '">คลังสินค้า: ' . htmlspecialchars($current_wh) . ' - ' . htmlspecialchars($current_wh_name) . '</td>
        </tr>';
    }

    // Print Data
    $html .= '<tr>';
    $html .= '<td>' . htmlspecialchars($row['SKU_CODE']) . '</td>';
    $html .= '<td>' . htmlspecialchars($row['SKU_NAME']) . '</td>';
    $html .= '<td class="text-center">' . htmlspecialchars($row['WH_CODE']) . '</td>';
    $html .= '<td class="text-center">' . htmlspecialchars($row['WL_CODE']) . '</td>';
    $html .= '<td class="text-center">' . htmlspecialchars($row['UNIT_NAME']) . '</td>';
    $html .= '<td class="text-right">' . number_format($row['SUM_QTY'], 2) . '</td>';
    $html .= '</tr>';

    $sub_qty += $row['SUM_QTY'];
    $grand_qty += $row['SUM_QTY'];
}

// ปิดยอดกลุ่มสุดท้าย (ถ้ามีข้อมูล)
if ($current_wh !== null) {
    $html .= '
    <tr class="group-total">
        <td colspan="' . $cols_before_total . '" class="text-right">รวมคลัง ' . htmlspecialchars($current_wh) . '</td>
        <td class="text-right">' . number_format($sub_qty, 2) . '</td>
    </tr>';
}

if (!$hasData) {
    $html .= '<tr><td colspan="' . $total_cols . '" class="text-center" style="padding:20px;">-- ไม่พบข้อมูลตามเงื่อนไข --</td></tr>';
}

$html .= '
    </tbody>
    <tfoot>
        <tr class="grand-total">
            <td colspan="' . $cols_before_total . '" class="text-right">รวมสุทธิทุกคลัง</td>
            <td class="text-right">' . number_format($grand_qty, 2) . '</td>
        </tr>
    </tfoot>
</table>';

// =========================================================
// 6. Output PDF
// =========================================================
$mpdf->WriteHTML($css);
$mpdf->WriteHTML($html);

$filename = "Stock_Location_Report_" . date('Ymd_His') . ".pdf";
$mpdf->Output($filename, 'I');
exit();
?>