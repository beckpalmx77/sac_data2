<?php
/**
 * Export Payment Pass Cheque Data to Excel (.xlsx)
 * ดึงข้อมูลการผ่านเช็คจาก MSSQL (SAC) ตามช่วงวันที่ และสร้างไฟล์ Excel (.xlsx)
 */

ini_set('memory_limit', '512M');
set_time_limit(180);
date_default_timezone_set('Asia/Bangkok');

include('../config/connect_sqlserver.php');
require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

// 1. รับค่าวันที่เริ่มต้น - สิ้นสุด
$doc_date_start = !empty($_REQUEST['doc_date_start']) ? $_REQUEST['doc_date_start'] : (!empty($_REQUEST['start_date']) ? $_REQUEST['start_date'] : date('d-m-Y'));
$doc_date_to   = !empty($_REQUEST['doc_date_to']) ? $_REQUEST['doc_date_to'] : (!empty($_REQUEST['end_date']) ? $_REQUEST['end_date'] : date('d-m-Y'));

// แปลงรูปแบบวันที่สำหรับค้นหาใน MSSQL (YYYY/MM/DD)
function convertDateForSql($dateStr) {
    $dateStr = trim((string)$dateStr);
    if (empty($dateStr)) {
        return date('Y/m/d');
    }
    // กรณี dd-mm-yyyy หรือ dd/mm/yyyy
    if (preg_match('/^(\d{1,2})[-\/](\d{1,2})[-\/](\d{4})$/', $dateStr, $matches)) {
        return sprintf('%04d/%02d/%02d', $matches[3], $matches[2], $matches[1]);
    }
    // กรณี yyyy-mm-dd หรือ yyyy/mm/dd
    if (preg_match('/^(\d{4})[-\/](\d{1,2})[-\/](\d{1,2})$/', $dateStr, $matches)) {
        return sprintf('%04d/%02d/%02d', $matches[1], $matches[2], $matches[3]);
    }
    $ts = strtotime($dateStr);
    return $ts !== false ? date('Y/m/d', $ts) : $dateStr;
}

$doc_date_start_sql = convertDateForSql($doc_date_start);
$doc_date_to_sql   = convertDateForSql($doc_date_to);

// 2. Query ข้อมูลจาก MSSQL
$sql = "
SELECT 
  DI_DATE, 
  DI_REF, 
  BNKAC_CODE,
  BNKAC_NAME,
  BANK_INITIAL,
  BANK_T_NAME, 
  CQIN_CHEQUE_NO,
  CQIN_CHEQUE_DD,
  CQIN_OWNER, 
  CQIN_AMT, 
  CQIN_FEE_A,
  DI_ACTIVE,
  BR_CODE,
  DEPT_CODE,
  PRJ_CODE
FROM 
  BRANCH WITH (NOLOCK),
  DEPTTAB WITH (NOLOCK),
  PRJTAB WITH (NOLOCK),
  CHEQUEIN WITH (NOLOCK), 
  BANKFILE WITH (NOLOCK), 
  BANKACCOUNT WITH (NOLOCK), 
  DOCTYPE WITH (NOLOCK), 
  ARFILE WITH (NOLOCK),
  DOCINFO WITH (NOLOCK)
LEFT OUTER JOIN TRANPAYH WITH (NOLOCK)
    ON TPH_DI=DI_KEY 
WHERE (CQIN_DI=DI_KEY)
  AND (CQIN_BANK=BANK_KEY)
  AND (CQIN_BNKAC=BNKAC_KEY)
  AND (CQIN_AR=AR_KEY)
  AND (DI_DT=DT_KEY)
  AND (DT_PROPERTIES=612) 
  AND (TPH_DEPT=DEPT_KEY) 
  AND (TPH_PRJ=PRJ_KEY) 
  AND (TPH_BR=BR_KEY)
  AND (DI_DATE BETWEEN :start_date AND :end_date)
ORDER BY 
  DI_DATE, 
  DI_REF,  
  BANK_INITIAL, 
  CQIN_CHEQUE_NO, 
  CQIN_CHEQUE_DD
";

if (!isset($conn_sqlsvr) || !$conn_sqlsvr) {
    die("เชื่อมต่อฐานข้อมูลล้มเหลว: ไม่พบการเชื่อมต่อ MSSQL");
}

try {
    $stmt = $conn_sqlsvr->prepare($sql);
    $stmt->bindParam(':start_date', $doc_date_start_sql);
    $stmt->bindParam(':end_date', $doc_date_to_sql);
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("เกิดข้อผิดพลาดในการดึงข้อมูล: " . $e->getMessage());
}

// 3. สร้างเอกสาร Excel ด้วย PhpSpreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Payment_Pass_Cheque');

// กำหนดฟอนต์เริ่มต้น
$spreadsheet->getDefaultStyle()->getFont()->setName('Calibri')->setSize(11);

// กำหนดหัวตารางเฉพาะที่ต้องการ (9 คอลัมน์ โดยมี ยอดสุทธิ ต่อท้าย)
$headers = [
    'วันที่เช็ค',
    'เลขที่เอกสาร',
    'รหัสธนาคาร',
    'ชื่อบัญชีธนาคาร',
    'เลขที่เช็ค',
    'สั่งจ่ายโดย',
    'ยอดเงิน',
    'ค่าธรรมเนียม',
    'ยอดสุทธิ'
];

$sheet->fromArray($headers, NULL, 'A1');

// จัดสไตล์ Header
$headerStyle = [
    'font' => [
        'bold' => true,
        'color' => ['rgb' => 'FFFFFF'],
        'size' => 11,
    ],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => '1E3A8A'], // สีน้ำเงินเข้ม
    ],
    'alignment' => [
        'horizontal' => Alignment::HORIZONTAL_CENTER,
        'vertical' => Alignment::VERTICAL_CENTER,
        'wrapText' => true,
    ],
    'borders' => [
        'allBorders' => [
            'borderStyle' => Border::BORDER_THIN,
            'color' => ['rgb' => 'D1D5DB'],
        ],
    ],
];
$sheet->getStyle('A1:I1')->applyFromArray($headerStyle);
$sheet->getRowDimension(1)->setRowHeight(28);

// ฟังก์ชันแปลงวันที่สำหรับแสดงผลใน Excel (dd/mm/yyyy)
function formatExcelDate($dateVal) {
    if (empty($dateVal)) return '';
    if ($dateVal instanceof DateTime) {
        return $dateVal->format('d/m/Y');
    }
    $ts = strtotime($dateVal);
    return $ts !== false ? date('d/m/Y', $ts) : trim((string)$dateVal);
}

// 4. ใส่ข้อมูลลงในตาราง
$rowNumber = 2;

foreach ($rows as $row) {
    $cqinAmt = is_numeric($row['CQIN_AMT']) ? (float)$row['CQIN_AMT'] : 0.0;
    $cqinFee = is_numeric($row['CQIN_FEE_A']) ? (float)$row['CQIN_FEE_A'] : 0.0;
    $cqinNet = $cqinAmt + $cqinFee;

    $sheet->setCellValueExplicit('A' . $rowNumber, formatExcelDate($row['CQIN_CHEQUE_DD']), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('B' . $rowNumber, trim((string)$row['DI_REF']), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('C' . $rowNumber, trim((string)$row['BANK_INITIAL']), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('D' . $rowNumber, trim((string)$row['BNKAC_NAME']), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('E' . $rowNumber, trim((string)$row['CQIN_CHEQUE_NO']), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('F' . $rowNumber, trim((string)$row['CQIN_OWNER']), \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
    $sheet->setCellValueExplicit('G' . $rowNumber, $cqinAmt, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
    $sheet->setCellValueExplicit('H' . $rowNumber, $cqinFee, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);
    $sheet->setCellValueExplicit('I' . $rowNumber, $cqinNet, \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_NUMERIC);

    $rowNumber++;
}

$lastDataRow = $rowNumber - 1;

// จัดรูปแบบตัวเลขและ Alignment ของข้อมูล
if ($lastDataRow >= 2) {
    // กำหนดรูปแบบตัวเลขทศนิยม 2 ตำแหน่งสำหรับ ยอดเงิน, ค่าธรรมเนียม และ ยอดสุทธิ
    $sheet->getStyle('G2:I' . $lastDataRow)->getNumberFormat()->setFormatCode('#,##0.00');

    // จัด Alignment ของแต่ละคอลัมน์
    $sheet->getStyle('A2:A' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('B2:B' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('C2:C' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('D2:D' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getStyle('E2:E' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle('F2:F' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getStyle('G2:I' . $lastDataRow)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

    // เส้นขอบของแถวข้อมูล
    $dataBorderStyle = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
                'color' => ['rgb' => 'E5E7EB'],
            ],
        ],
    ];
    $sheet->getStyle('A2:I' . $lastDataRow)->applyFromArray($dataBorderStyle);

    // แถวสรุปผลรวม (Total Summary Row)
    $totalRow = $rowNumber;
    $sheet->setCellValue('A' . $totalRow, 'รวมทั้งหมด');
    $sheet->mergeCells("A{$totalRow}:F{$totalRow}");
    $sheet->setCellValue('G' . $totalRow, "=SUM(G2:G{$lastDataRow})");
    $sheet->setCellValue('H' . $totalRow, "=SUM(H2:H{$lastDataRow})");
    $sheet->setCellValue('I' . $totalRow, "=SUM(I2:I{$lastDataRow})");

    $sheet->getStyle('G' . $totalRow . ':I' . $totalRow)->getNumberFormat()->setFormatCode('#,##0.00');

    $totalStyle = [
        'font' => ['bold' => true],
        'fill' => [
            'fillType' => Fill::FILL_SOLID,
            'startColor' => ['rgb' => 'F3F4F6'],
        ],
        'alignment' => [
            'vertical' => Alignment::VERTICAL_CENTER,
        ],
        'borders' => [
            'top' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
            'bottom' => ['borderStyle' => Border::BORDER_DOUBLE, 'color' => ['rgb' => '000000']],
        ],
    ];
    $sheet->getStyle("A{$totalRow}:I{$totalRow}")->applyFromArray($totalStyle);
    $sheet->getStyle("A{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("G{$totalRow}:I{$totalRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->getRowDimension($totalRow)->setRowHeight(24);
}

// ปรับความกว้างคอลัมน์อัตโนมัติ (A ถึง I)
foreach (range('A', 'I') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// 5. ส่งออกไฟล์ Excel
$cleanStart = str_replace(['/', '-'], '', $doc_date_start);
$cleanEnd   = str_replace(['/', '-'], '', $doc_date_to);
$filename   = "Payment_Pass_Cheque_{$cleanStart}_{$cleanEnd}_" . date('His') . ".xlsx";

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Cache-Control: max-age=0');
header('Cache-Control: max-age=1');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT');
header('Cache-Control: cache, must-revalidate');
header('Pragma: public');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit();
