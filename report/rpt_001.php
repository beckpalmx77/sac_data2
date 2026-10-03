<?php
require_once('../vendor/tcpdf/etc/tcpdf_include.php');

$f_name = "นายสมชาย ทรัพย์เพิ่ม";

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8');

$pdf->SetCreator('Mindphp');
$pdf->SetAuthor('Mindphp Developer');
$pdf->SetTitle('Mindphp Example 04');
$pdf->SetSubject('Mindphp Example');
$pdf->SetKeywords('Mindphp, TCPDF, PDF, example, guide');

// กำหนดรูปแบบตัวอักษรให้กับส่วนหัวของเอกสาร
// freeserif = ชื่อตัวอักษร
// B = กำหนดให้เป็นตัวหนา
// 12 = ขนาดตัวอักษร
$pdf->setHeaderFont(array('THSarabun', 'B', 12));

$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, 'Mindphp Example 04', 'การใช้คำสั่ง Cell(), Multicell(), WriteHTML(), writeHTMLCell()', array (0, 64, 255), array (0, 64, 128));
$pdf->setFooterData(array (0, 64, 0), array (0, 64, 128));

$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
$pdf->AddPage();

// กำหนดรูปแบบตัวอักษรให้กับเนื้อหา
$pdf->SetFont('THSarabun', '', 14);

$html = '<h3>หัวข้อ writeHTML()</h3>';
$html .= '<table border="1" width="720" cellpadding="10"><tr>';
$html .= '<td width="150"><img src="http://www.mindphp.com/images/info/mindphp.png" width="150" /></td>';
$html .= '<td>';
$html .= '<b>PHP ยินดีต้อนรับสู่ MIND PHP.COM</b>';
$html .= '<p style="font-size: 12px;">PHP ยินดีต้อนรับสู่ MIND PHP.COM (รูปแบบใหม่)   ปรับปรุง Mindphp เป็นรูปแบบใหม่ ได้ใช้ ตัว Convert จาก phpnuke เป็น Joomla 1.5 และได้อัพเดดอย่างต่อเนื่องเป็น Joomla 2.5 ปัจจุบัน ใช้ Joomla 3.6 </p>';
$html .= '</td>';
$html .= '</tr></table>';

// การใช้คำสั่ง writeHTML()


$html .= '               หนังสือสัญญาฉบับนี้ทำขึ้นระหว่าง ' . $f_name . ' บัตรประชาชนเลขที่ _ _ _ _ _ _ _ _ _ _
อายุ ..... ปี อาชีพ ...................... อยู่บ้านเลขที่ ........................................................................ โทรศัพท์ _ _ _ _ _ _ _ _ _ _
ซึ่งต่อไปในสัญญานี้จะเรียกว่า "ผู้จะซื้อ” ฝ่ายหนึ่ง กับ บริษัท เดอะมิวส์ เอสเตท จ ากัด โดย นายสุทธินันท์ สกุลภิญโญ ส านักงาน
เลขที่ 345 หมู่ 3 ต.หัวทะเล อ.เมือง จ.นครราชสีมา โทรศัพท์ 083-1416599 ซึ่งต่อไปนี้จะเรียกว่า “ผู้จะขาย” อีกฝ่ายหนึ่ง ซึ่ง
ทั้งสองฝ่ายตกลงท าสัญญาจะซื้อจะขายที่ดินพร้อมสิ่งปลูกสร้างกัน ดังมีข้อความต่อไปนี้
ข้อ 1. ผู้จะซื้อและผู้จะขายตกลงซื้อขายที่ดินพร้อมสิ่งปลูกสร้าง แปลงที่ _ _ _ บนโฉนดที่ดินเลขที่ _ _ _ _ _ _
เนื้อที่ _ _ ตร.ว. ตามผังสังเขป พร้อมบ้านเดี่ยวแบบ _ _ _ _ _ ขนาด _ _ ตรม. ที่โครงการเดอะมิวส์ ท่าอ่าง ต.ท่าอ่าง
อ.โชคชัย จ.นครราชสีมา และได้ช าระค่าสัญญาจอง เป็นเงิน ……………….. บาท ( ….......................……………………….)
ข้อ 2. ผู้จะซื้อและผู้จะขาย ตกลงราคาที่ดินพร้อมสิ่งปลูกสร้าง รวมเป็นเงินทั้งสิ้น …………………………… บาท
( ..............................................................................)
ข้อ 3. เงินตามข้อ 1. ถือเป็นส่วนหนึ่งขอราคาที่ดินพร้อมสิ่งปลูกสร้าง และผู้จะซื้อจะมาท าสัญญาจะซื้อจะขาย
ที่ดินพร้อมสิ่งปลูกสร้าง ภายใน วันที่ ………………………………… (ภายใน 7 วัน นับจากวันจอง)
หากข้าพเจ้าเพิกเฉยไม่มาท าสัญญาจะซื้อจะขายตามก าหนดเวลาดังกล่าวให้ถือว่าข้าพเจ้าสละสิทธิ์ และยินยอมให้
ผู้รับจองริบเงินมัดจ าตามข้อ 1. ได้ทันทีโดยไม่ต้องบอกกล่าว';

$pdf->writeHTML($html);



// การใช้คำสั่ง writeHTMLCell()
$pdf->writeHTMLCell(50, '', '', 150, 'writeHTMLCell()<br /><img src="http://www.mindphp.com/images/info/mindphp.png" width="150" />', 1);
$pdf->writeHTMLCell(50, '', 145, 150, 'writeHTMLCell()<br /><img src="http://www.mindphp.com/images/info/mindphp.png" width="150" />', 1);
$pdf->writeHTMLCell(50, '', 80, 200, 'writeHTMLCell()<br /><img src="http://www.mindphp.com/images/info/mindphp.png" width="150" />', 1);

$pdf->Output('mindphp04.pdf', 'I');