<?php
include("../config/connect_sqlserver.php");

isset( $_POST['doc_date_start'] ) ? $doc_date_start = $_POST['doc_date_start'] : $doc_date_start = "";
isset( $_POST['doc_date_to'] ) ? $doc_date_to = $_POST['doc_date_to'] : $doc_date_to = "";
isset( $_POST['BANK'] ) ? $bank = $_POST['BANK'] : $bank = "";

// แปลงวันที่ให้อยู่ในรูปแบบ yyyy/mm/dd
$doc_date_start = substr($_POST['doc_date_start'], 6, 4) . "/" . substr($_POST['doc_date_start'], 3, 2) . "/" . substr($_POST['doc_date_start'], 0, 2);
$doc_date_to = substr($_POST['doc_date_to'], 6, 4) . "/" . substr($_POST['doc_date_to'], 3, 2) . "/" . substr($_POST['doc_date_to'], 0, 2);

// ดึงชื่อธนาคารจากฐานข้อมูล
$sql_bank_name = "SELECT BNKAC_NAME FROM BANKACCOUNT WHERE BNKAC_KEY = :bank";
$stmt_bank = $conn_sqlsvr->prepare($sql_bank_name);
$stmt_bank->bindParam(':bank', $bank, PDO::PARAM_INT);
$stmt_bank->execute();
$bank_record = $stmt_bank->fetch(PDO::FETCH_ASSOC);
$bank_name = $bank_record['BNKAC_NAME'];

$year_month = substr($_POST['doc_date_start'], 6, 4) . "/" . substr($_POST['doc_date_start'], 3, 2);

$filename = "bank_statement_" . $year_month . "_".  date('Y-m-d_H-i-s') . ".csv";

header('Content-Type: text/csv; charset=UTF-8');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');
echo "\xEF\xBB\xBF"; // เพิ่ม BOM สำหรับ UTF-8
date_default_timezone_set('Asia/Bangkok');

// เปิดไฟล์ CSV
$output = fopen('php://output', 'w');

// เพิ่มหัวข้อรายงาน
fputcsv($output, ['รายงานความเคลื่อนไหวบัญชีธนาคาร (BANK STATEMENT)']);
fputcsv($output, ['ธนาคาร: ' . $bank_name]);  // แสดงชื่อธนาคาร
fputcsv($output, ['วันที่: ' . $_POST['doc_date_start'] . ' ถึง ' . $_POST['doc_date_to']]);  // แสดงช่วงวันที่
fputcsv($output, []);  // เพิ่มบรรทัดว่าง

// Query 1: ดึงยอดยกมา
$sql_start_balance = "SELECT * FROM BSTMPERIOD 
                      WHERE BSTMP_BNKAC = " . $bank . "   
                      AND BSTMP_ST_DATE BETWEEN '" . $doc_date_start . "' AND '" . $doc_date_to . "'";

$stmt_start = $conn_sqlsvr->prepare($sql_start_balance);
$stmt_start->execute();
$row_start = $stmt_start->fetch(PDO::FETCH_ASSOC);
$start_balance = 0;

// ถ้ามียอดยกมา
if ($row_start) {
    $start_balance = $row_start['BSTMP_TOWARD'];  // สมมุติว่า column ที่เก็บยอดยกมาคือ BSTMP_START_BALANCE
}

// เขียนหัวตาราง
fputcsv($output, [
    'ลำดับที่',
    'วันที่',
    'ธนาคาร',
    'เครดิต',
    'เดบิต',
    'ยอดคงเหลือ',
    'หมายเหตุ',
    'วันที่เอกสาร',
    'เลขที่เอกสาร',
    'วันที่เช๊ค',
    'หมายเลขเช๊ค'
]);

// แสดงยอดยกมา
$current_balance = $start_balance;  // กำหนดยอดยกมาเป็นยอดเริ่มต้น
fputcsv($output, ['', '', '', 'ยอดยกมา', number_format($current_balance, 2), '', '', '', '', '']);

// Query 2: ดึงรายการธุรกรรม
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
                     WHERE BANKACCOUNT.BNKAC_KEY = " . $bank . "   
                     AND BANKSTATEMENT.BSTM_RECNL_DD BETWEEN '" . $doc_date_start . "' AND '" . $doc_date_to . "' 
                     ORDER BY BANKSTATEMENT.BSTM_RECNL_DD,BANKSTATEMENT.BSTM_RECNL_SEQ ";

$myfile = fopen("abank-param.txt", "w") or die("Unable to open file!");
fwrite($myfile, $sql_transactions);
fclose($myfile);

$stmt_transactions = $conn_sqlsvr->prepare($sql_transactions);
$stmt_transactions->execute();
$transactions = $stmt_transactions->fetchAll(PDO::FETCH_ASSOC);

$index = 1; // เริ่มลำดับที่ 1

// Loop แสดงรายการธุรกรรม
foreach ($transactions as $transaction) {
    // คำนวณยอดคงเหลือ
    $current_balance = $current_balance - $transaction['BSTM_CREDIT'] + $transaction['BSTM_DEBIT'];

    // แสดงผลในแต่ละบรรทัด
    fputcsv($output, [
        $index++,  // เพิ่มลำดับที่
        $transaction['BSTM_RECNL_DD'],
        $transaction['BNKAC_NAME'],
        number_format($transaction['BSTM_CREDIT'], 2),
        number_format($transaction['BSTM_DEBIT'], 2),
        number_format($current_balance, 2),
        trim($transaction['BSTM_REMARK']),
        $transaction['DI_DATE'],
        $transaction['DI_REF'],
        $transaction['CQBK_CHEQUE_DD'],
        $transaction['BSTM_CHEQUE_NO']
    ]);
}

fclose($output);
exit();
