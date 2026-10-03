<?php
include('../config/connect_db.php');
require('../vendor/fpdf/fpdf.php'); // เรียกใช้ FPDF

// กำหนดชื่อไฟล์ PDF
$filename = "movement-stock-" . date('m-d-Y-H:i:s', time()) . ".pdf";
date_default_timezone_set('Asia/Bangkok');

// รับค่าวันที่จาก form
$doc_date_start = $_POST["doc_date_start"];
$doc_date_to = $_POST["doc_date_to"];

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

$select_query_wh_movement = $sql_get . " AND vo.doc_date BETWEEN '$doc_date_start' AND '$doc_date_to' "
    . " ORDER BY vo.doc_id ";

$query = $conn->prepare($select_query_wh_movement);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

// สร้างไฟล์ PDF ด้วย FPDF
$pdf = new FPDF();
$pdf->AddPage();

// เพิ่มฟอนต์ภาษาไทย
$pdf->AddFont('Prompt','','Prompt-regular.php');
$pdf->SetFont('Prompt', '', 14); // ใช้ฟอนต์ภาษาไทย ขนาด 14

// เพิ่มหัวตาราง
$pdf->Cell(40, 10, iconv('UTF-8', 'cp874', 'วันที่'));
$pdf->Cell(30, 10, iconv('UTF-8', 'cp874', 'รหัสสินค้า'));
$pdf->Cell(50, 10, iconv('UTF-8', 'cp874', 'รายละเอียด'));
$pdf->Cell(20, 10, iconv('UTF-8', 'cp874', 'จำนวน'));
$pdf->Cell(20, 10, iconv('UTF-8', 'cp874', 'คลังปี'));
$pdf->Cell(20, 10, iconv('UTF-8', 'cp874', 'สัปดาห์'));
$pdf->Cell(20, 10, iconv('UTF-8', 'cp874', 'ตำแหน่ง'));
$pdf->Ln();

// เพิ่มข้อมูลจากฐานข้อมูล
if ($query->rowCount() >= 1) {
    foreach ($results as $result) {
        $pdf->Cell(40, 10, iconv('UTF-8', 'cp874', $result->doc_date));
        $pdf->Cell(30, 10, iconv('UTF-8', 'cp874', $result->product_id));
        $pdf->Cell(50, 10, iconv('UTF-8', 'cp874', $result->product_name));
        $pdf->Cell(20, 10, iconv('UTF-8', 'cp874', $result->qty));
        $pdf->Cell(20, 10, iconv('UTF-8', 'cp874', $result->wh_org));
        $pdf->Cell(20, 10, iconv('UTF-8', 'cp874', $result->wh_week_id));
        $pdf->Cell(20, 10, iconv('UTF-8', 'cp874', $result->location_org));
        $pdf->Ln();
    }
}

// ตั้งค่าชื่อไฟล์และพิมพ์ออก PDF
$pdf->Output('D', $filename);
exit();
?>
