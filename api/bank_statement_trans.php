<?php
header('Content-Type: application/json; charset=UTF-8');
include('../config/connect_sqlserver.php'); // เชื่อมต่อกับฐานข้อมูล

// รับข้อมูล POST
$input = json_decode(file_get_contents('php://input'), true);
$bank = isset($_POST['bank']) ? $_POST['bank'] : null;
$doc_date_start = isset($_POST['doc_date_start']) ? $_POST['doc_date_start'] : null;
$doc_date_to = isset($_POST['doc_date_to']) ? $_POST['doc_date_to'] : null;

// ตรวจสอบว่ามีค่าที่ต้องการทั้งหมด
if (!$bank || !$doc_date_start || !$doc_date_to) {
    echo json_encode(['error' => 'Missing required parameters']);
    exit();
}

try {
// สร้าง SQL query
    $sql_transactions = "
SELECT 
FORMAT(BANKSTATEMENT.BSTM_RECNL_DD, 'dd/MM/yyyy') AS BSTM_RECNL_DD,
BANKACCOUNT.BNKAC_CODE, 
BANKACCOUNT.BNKAC_NAME,
BANKSTATEMENT.BSTM_CREDIT, 
BANKSTATEMENT.BSTM_DEBIT, 
BANKSTATEMENT.BSTM_REMARK, 
FORMAT(DOCINFO.DI_DATE, 'dd/MM/yyyy') AS DI_DATE, 
DOCINFO.DI_REF,
FORMAT(CHEQUEBOOK.CQBK_CHEQUE_DD, 'dd/MM/yyyy') AS CQBK_CHEQUE_DD,
BANKSTATEMENT.BSTM_CHEQUE_NO,
BANKSTATEMENT.BSTM_RECNL_SEQ,
BANKSTATEMENT.BSTM_SHOW_ORDER,
BANKSTATEMENT.BSTM_LASTUPD,
BANKSTATEMENT.BSTM_KEY
FROM BANKSTATEMENT 
LEFT JOIN BANKACCOUNT ON BANKACCOUNT.BNKAC_KEY = BANKSTATEMENT.BSTM_BNKAC
LEFT JOIN DOCINFO ON DOCINFO.DI_KEY = BANKSTATEMENT.BSTM_DI
LEFT JOIN CHEQUEBOOK ON CHEQUEBOOK.CQBK_REFER_REF = DOCINFO.DI_REF
WHERE DOCINFO.DI_ACTIVE = 0 AND BANKACCOUNT.BNKAC_KEY = :bank   
AND BANKSTATEMENT.BSTM_RECNL_DD BETWEEN :doc_date_start AND :doc_date_to
ORDER BY BANKSTATEMENT.BSTM_RECNL_DD, BANKSTATEMENT.BSTM_RECNL_SEQ, BANKSTATEMENT.BSTM_SHOW_ORDER DESC";

// เตรียมและเรียกใช้ SQL
    $stmt = $conn_sqlsvr->prepare($sql_transactions);
    $stmt->bindParam(':bank', $bank);
    $stmt->bindParam(':doc_date_start', $doc_date_start);
    $stmt->bindParam(':doc_date_to', $doc_date_to);
    $stmt->execute();

// ดึงข้อมูลทั้งหมดและส่งผลลัพธ์เป็น JSON
    $transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode(['data' => $transactions]);

} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

