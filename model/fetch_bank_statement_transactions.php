<?php
include("config/connect_sqlserver40_syy.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $bank = $_POST['bank'];

    // Query ข้อมูลตามวันที่และธนาคาร
    $sql_transactions = "SELECT                             
                                FORMAT(BANKSTATEMENT.BSTM_RECNL_DD, 'dd/MM/yyyy') AS BSTM_RECNL_DD,
                                BANKACCOUNT.BNKAC_CODE, 
                                BANKACCOUNT.BNKAC_NAME,
                                BANKSTATEMENT.BSTM_CREDIT, 
                                BANKSTATEMENT.BSTM_DEBIT, 
                                BANKSTATEMENT.BSTM_REMARK, 
                                FORMAT(DOCINFO.DI_DATE, 'dd/MM/yyyy') AS DI_DATE, 
                                DOCINFO.DI_REF,    
                                FORMAT(CHEQUEBOOK.CQBK_CHEQUE_DD, 'dd/MM/yyyy') AS CQBK_CHEQUE_DD,
                                BANKSTATEMENT.BSTM_CHEQUE_NO 
                         FROM BANKSTATEMENT 
                         LEFT JOIN BANKACCOUNT ON BANKACCOUNT.BNKAC_KEY = BANKSTATEMENT.BSTM_BNKAC
                         LEFT JOIN DOCINFO ON DOCINFO.DI_KEY = BANKSTATEMENT.BSTM_DI
                         LEFT JOIN CHEQUEBOOK ON CHEQUEBOOK.CQBK_REFER_REF = DOCINFO.DI_REF
                         WHERE BANKACCOUNT.BNKAC_KEY = :bank  
                         AND BANKSTATEMENT.BSTM_RECNL_DD BETWEEN :start_date AND :end_date 
                         ORDER BY BANKSTATEMENT.BSTM_RECNL_DD";

    $stmt_transactions = $conn_sqlsvr->prepare($sql_transactions);
    $stmt_transactions->execute([
        ':bank' => $bank,
        ':start_date' => $start_date,
        ':end_date' => $end_date,
    ]);
    $transactions = $stmt_transactions->fetchAll(PDO::FETCH_ASSOC);

    // แสดงข้อมูลในตาราง
    foreach ($transactions as $transaction) {
        echo "<tr>            
                <td>{$transaction['BSTM_RECNL_DD']}</td>            
                <td>{$transaction['BNKAC_NAME']}</td>
                <td>" . number_format($transaction['BSTM_CREDIT'], 2) . "</td>
                <td>" . number_format($transaction['BSTM_DEBIT'], 2) . "</td>
                <td>{$transaction['BSTM_REMARK']}</td>            
                <td>{$transaction['DI_DATE']}</td>     
                <td>{$transaction['DI_REF']}</td>            
                <td>{$transaction['CQBK_CHEQUE_DD']}</td>
                <td>{$transaction['BSTM_CHEQUE_NO']}</td>
              </tr>";
    }

    // Export to Excel
    if (isset($_POST['export_excel'])) {
        exportToExcel($transactions);
    }
}

// ฟังก์ชัน Export to Excel
function exportToExcel($data)
{
    // ต้องใช้ PhpSpreadsheet สำหรับการ export เป็น Excel
    require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // กำหนดหัวคอลัมน์
    $sheet->setCellValue('A1', 'BSTM_RECNL_DD');
    $sheet->setCellValue('B1', 'BNKAC_NAME');
    $sheet->setCellValue('C1', 'BSTM_CREDIT');
    $sheet->setCellValue('D1', 'BSTM_DEBIT');
    $sheet->setCellValue('E1', 'ยอดคงเหลือ');
    $sheet->setCellValue('F1', 'BSTM_REMARK');
    $sheet->setCellValue('G1', 'DI_DATE');
    $sheet->setCellValue('H1', 'DI_REF');
    $sheet->setCellValue('I1', 'CQBK_CHEQUE_DD');
    $sheet->setCellValue('J1', 'BSTM_CHEQUE_NO');

    // ใส่ข้อมูล
    $row = 2;
    foreach ($data as $transaction) {
        $sheet->setCellValue('A' . $row, $transaction['BSTM_RECNL_DD']);
        $sheet->setCellValue('B' . $row, $transaction['BNKAC_NAME']);
        $sheet->setCellValue('C' . $row, number_format($transaction['BSTM_CREDIT'], 2));
        $sheet->setCellValue('D' . $row, number_format($transaction['BSTM_DEBIT'], 2));
        $sheet->setCellValue('F' . $row, $transaction['BSTM_REMARK']);
        $sheet->setCellValue('G' . $row, $transaction['DI_DATE']);
        $sheet->setCellValue('H' . $row, $transaction['DI_REF']);
        $sheet->setCellValue('I' . $row, $transaction['CQBK_CHEQUE_DD']);
        $sheet->setCellValue('J' . $row, $transaction['BSTM_CHEQUE_NO']);
        $row++;
    }

    // เขียนไฟล์ Excel
    $writer = new Xlsx($spreadsheet);
    $filename = 'bank_transactions.xlsx';

    // ตั้งค่า Header เพื่อดาวน์โหลดไฟล์ Excel
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $writer->save('php://output');
}
?>
