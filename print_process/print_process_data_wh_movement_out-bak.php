<?php
include('../config/connect_db.php');
require_once '../vendor/autoload.php'; // เรียกใช้ mPDF ผ่าน autoload ของ Composer

// กำหนดชื่อไฟล์ PDF
$filename = "movement-stock-" . date('m-d-Y-H:i:s', time()) . ".pdf";
date_default_timezone_set('Asia/Bangkok');

// รับค่าวันที่จากฟอร์ม
$doc_date_start = $_POST["doc_date_start"];
$doc_date_to = $_POST["doc_date_to"];

// แปลงรูปแบบวันที่
$start_date_formatted = DateTime::createFromFormat('d-m-Y', $doc_date_start)->format('Y-m-d');
$end_date_formatted = DateTime::createFromFormat('d-m-Y', $doc_date_to)->format('Y-m-d');

// SQL Query เพื่อดึงข้อมูล
$sql_get = "SELECT 
    vo.id,vo.doc_date,vo.doc_id, vo.line_no,vo.product_id,vo.product_name,vo.wh_org,vo.wh_week_id,vo.location_org,
    vo.sale_take,vo.customer_name,vo.car_no,vo.doc_user_id,vo.location_to,
    vo.qty,vb.total_qty,vo.create_by,vo.create_date 
FROM 
    v_wh_stock_movement_out vo
LEFT JOIN 
    v_wh_stock_balance vb 
ON 
    vb.product_id = vo.product_id 
    AND vb.wh = vo.wh_org 
    AND vb.wh_week_id = vo.wh_week_id 
    AND vb.location = vo.location_org 
WHERE 1 ";

$select_query_wh_movement = $sql_get . " AND vo.doc_date BETWEEN '$start_date_formatted' AND '$end_date_formatted' "
    . " ORDER BY vo.doc_id ";

$query = $conn->prepare($select_query_wh_movement);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

// เริ่มการสร้าง PDF ด้วย mPDF
$mpdf = new \Mpdf\Mpdf();
$html = '<h1>Stock Movement Report</h1>';
$html .= '<table border="1" style="border-collapse: collapse; width: 100%;">';
$html .= '<thead>
            <tr>
                <th>วันที่</th>
                <th>รหัสสินค้า</th>
                <th>รายละเอียด</th>
                <th>จำนวน</th>
                <th>คลังปี</th>
                <th>สัปดาห์</th>
                <th>ตำแหน่ง</th>
                <th>เลขที่เอกสาร</th>
                <th>รถคันที่</th>
                <th>เทค</th>
                <th>Supplier/ลูกค้า</th>
                <th>ยอดคงเหลือ</th>
            </tr>
          </thead>';
$html .= '<tbody>';

// เพิ่มข้อมูลจากฐานข้อมูล
if ($query->rowCount() >= 1) {
    foreach ($results as $result) {
        $html .= '<tr>';
        $html .= '<td>' . $result->doc_date . '</td>';
        $html .= '<td>' . $result->product_id . '</td>';
        $html .= '<td>' . $result->product_name . '</td>';
        $html .= '<td>' . $result->qty . '</td>';
        $html .= '<td>' . $result->wh_org . '</td>';
        $html .= '<td>' . $result->wh_week_id . '</td>';
        $html .= '<td>' . $result->location_org . '</td>';
        $html .= '<td>' . $result->doc_id . '</td>';
        $html .= '<td>' . $result->car_no . '</td>';
        $html .= '<td>' . $result->sale_take . '</td>';
        $html .= '<td>' . $result->customer_name . '</td>';
        $html .= '<td>' . $result->total_qty . '</td>';
        $html .= '</tr>';
    }
}

$html .= '</tbody></table>';

// เพิ่ม HTML ลงใน mPDF
$mpdf->WriteHTML($html);

// ตั้งค่าชื่อไฟล์และพิมพ์ออก PDF
$mpdf->Output($filename, 'D'); // 'D' หมายถึงการดาวน์โหลดไฟล์ PDF
exit();
