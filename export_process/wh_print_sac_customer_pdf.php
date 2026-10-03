<?php
require_once '../vendor/autoload.php';

set_time_limit(300);
ini_set('memory_limit', '512M');
date_default_timezone_set('Asia/Bangkok');
include('../config/connect_db.php');

// Helper Function
function quoteArray($arr, $conn) {
    return array_map(function ($v) use ($conn) {
        return "N" . $conn->quote(trim($v));
    }, $arr);
}

// 1. Prepare SQL & Filter
$where = [];
$where[] = "AR_CODE LIKE 'SAC%'"; // Default Condition

$filter_desc = "ทั้งหมด";

if (!empty($_POST['provinces'])) {
    $in = implode(',', quoteArray($_POST['provinces'], $conn));
    $where[] = "TRD_PROVINCE IN ($in)";

    // Description Check
    if(count($_POST['provinces']) > 5) {
        $filter_desc = "เลือก " . count($_POST['provinces']) . " จังหวัด";
    } else {
        $filter_desc = implode(', ', $_POST['provinces']);
    }
}

$WHERE_SQL = "WHERE " . implode(' AND ', $where);

$String_Sql = "
SELECT AR_CODE, AR_NAME, TRD_AMPHUR, SALE_NAME, TRD_PROVINCE 
FROM ims_data_sale_sac_all
$WHERE_SQL
GROUP BY AR_CODE, AR_NAME, TRD_AMPHUR, SALE_NAME, TRD_PROVINCE 
ORDER BY TRD_PROVINCE ASC, TRD_AMPHUR ASC, AR_CODE ASC
";

$query = $conn->prepare($String_Sql);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_ASSOC);

// 2. Set PDF
$defaultConfig = (new Mpdf\Config\ConfigVariables())->getDefaults();
$fontDirs = $defaultConfig['fontDir'];
$defaultFontConfig = (new Mpdf\Config\FontVariables())->getDefaults();
$fontData = $defaultFontConfig['fontdata'];

$mpdf = new \Mpdf\Mpdf([
    'mode' => 'utf-8',
    'format' => 'A4', // แนวตั้งก็น่าจะพอสำหรับข้อมูลนี้ ถ้าคอลัมน์เยอะให้ใช้ A4-L
    'margin_top' => 40,
    'default_font' => 'prompt',
    'fontDir' => array_merge($fontDirs, ['../fonts']),
    'fontdata' => $fontData + ['prompt' => ['R' => 'Prompt-Regular.ttf', 'B' => 'Prompt-Bold.ttf']]
]);

// Styles
$css = '
<style>
    body { font-family: "prompt"; }
    table { width: 100%; border-collapse: collapse; font-size: 10pt; }
    th { border: 1px solid #333; background-color: #f2f2f2; padding: 6px; font-weight: bold; text-align: center; }
    td { border: 1px solid #ccc; padding: 5px; vertical-align: top; }
    .text-center { text-align: center; }
</style>
';

// Header
$headerHtml = '
<div style="width:100%;">
    <div style="text-align:right; font-size:10pt;">พิมพ์เมื่อ: ' . date('d/m/') . (date('Y') + 543) . '</div>
    <div style="text-align:center; font-size:16pt; font-weight:bold;">รายงานข้อมูลลูกค้า SAC แยกตามจังหวัด</div>
    <div style="font-size:11pt; font-weight:bold; margin-top:5px;">จังหวัด: ' . htmlspecialchars($filter_desc) . '</div>
</div>';

$mpdf->SetHTMLHeader($headerHtml);
$mpdf->SetFooter('หน้า {PAGENO} / {nbpg}');

// 3. Generate HTML Table
$html = '
<table>
    <thead>
        <tr>
            <th width="15%">รหัสลูกค้า</th>
            <th width="35%">ชื่อลูกค้า</th>
            <th width="15%">อำเภอ</th>
            <th width="15%">จังหวัด</th>            
            <th width="20%">พนักงานขาย</th>
        </tr>
    </thead>
    <tbody>
';

if (count($results) > 0) {
    foreach ($results as $row) {
        $html .= '<tr>';
        $html .= '<td class="text-center">' . htmlspecialchars($row['AR_CODE']) . '</td>';
        $html .= '<td>' . htmlspecialchars($row['AR_NAME']) . '</td>';
        $html .= '<td class="text-center">' . htmlspecialchars($row['TRD_AMPHUR']) . '</td>';
        $html .= '<td class="text-center">' . htmlspecialchars($row['TRD_PROVINCE']) . '</td>';
        $html .= '<td class="text-center">' . htmlspecialchars($row['SALE_NAME']) . '</td>';
        $html .= '</tr>';
    }
} else {
    $html .= '<tr><td colspan="5" class="text-center">-- ไม่พบข้อมูลตามเงื่อนไข --</td></tr>';
}

$html .= '</tbody></table>';

$mpdf->WriteHTML($css);
$mpdf->WriteHTML($html);
$mpdf->Output("SAC_Customer_" . date('Ymd') . ".pdf", 'I');
?>