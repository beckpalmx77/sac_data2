<?php
include("../config/connect_sqlserver40_syy.php");

// ดึงค่าจาก POST
$start_date = $_POST['start_date'];
$end_date = $_POST['end_date'];
$bnkac_key = $_POST['bnkac_key'];

// Query 1: ดึงยอดยกมา
$sql_start_balance = "SELECT * FROM BSTMPERIOD 
                      WHERE BSTMP_BNKAC = ? 
                      AND BSTMP_ST_DATE BETWEEN ? AND ?";
$stmt_start = $conn_sqlsvr->prepare($sql_start_balance);
$stmt_start->execute([$bnkac_key, $start_date, $end_date]);
$row_start = $stmt_start->fetch(PDO::FETCH_ASSOC);
$start_balance = 0;

if ($row_start) {
    $start_balance = $row_start['BSTMP_TOWARD']; // Column ที่เก็บยอดยกมา
}

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
                     WHERE BANKACCOUNT.BNKAC_KEY = " . $bnkac_key . "  
                     AND BANKSTATEMENT.BSTM_RECNL_DD BETWEEN '" . $start_date . "' AND '" . $end_date . "' 
                     ORDER BY BANKSTATEMENT.BSTM_RECNL_DD";


//$txt = $sql_transactions . " | " . $start_date . " | " . $end_date . " | " . $bnkac_key;
$txt = $sql_transactions;
$myfile = fopen("a-bank-param.txt", "w") or die("Unable to open file!");
fwrite($myfile, $txt);
fclose($myfile);

$stmt_transactions = $conn_sqlsvr->prepare($sql_transactions);
$stmt_transactions->execute();
$transactions = $stmt_transactions->fetchAll(PDO::FETCH_ASSOC);

$data = [];
$current_balance = $start_balance;  // กำหนดยอดยกมาเป็นยอดเริ่มต้น

// ยอดยกมา
$data[] = [
    'BSTM_RECNL_DD' => 'ยอดยกมา',
    'BNKAC_NAME' => '',
    'BSTM_CREDIT' => '',
    'BSTM_DEBIT' => '',
    'ยอดคงเหลือ' => number_format($current_balance, 2),
    'BSTM_REMARK' => '',
    'DI_DATE' => '',
    'DI_REF' => '',
    'CQBK_CHEQUE_DD' => '',
    'BSTM_CHEQUE_NO' => ''
];

// รายการธุรกรรม
foreach ($transactions as $transaction) {
    $current_balance = $current_balance - $transaction['BSTM_CREDIT'] + $transaction['BSTM_DEBIT'];

    $data[] = [
        'BSTM_RECNL_DD' => $transaction['BSTM_RECNL_DD'],
        'BNKAC_NAME' => $transaction['BNKAC_NAME'],
        'BSTM_CREDIT' => number_format($transaction['BSTM_CREDIT'], 2),
        'BSTM_DEBIT' => number_format($transaction['BSTM_DEBIT'], 2),
        'ยอดคงเหลือ' => number_format($current_balance, 2),
        'BSTM_REMARK' => $transaction['BSTM_REMARK'],
        'DI_DATE' => $transaction['DI_DATE'],
        'DI_REF' => $transaction['DI_REF'],
        'CQBK_CHEQUE_DD' => $transaction['CQBK_CHEQUE_DD'],
        'BSTM_CHEQUE_NO' => $transaction['BSTM_CHEQUE_NO']
    ];
}

// ส่งข้อมูลไปที่ DataTables
echo json_encode([
    "data" => $data
]);

