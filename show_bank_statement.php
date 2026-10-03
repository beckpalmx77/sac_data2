<?php
include("includes/Header.php");
include("config/connect_sqlserver.php");

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

// Query 1: ดึงยอดยกมา
$sql_start_balance = "SELECT * FROM BSTMPERIOD 
 WHERE BSTMP_BNKAC = " . $bank . "   
 AND BSTMP_ST_DATE BETWEEN '" . $doc_date_start . "' AND '" . $doc_date_to . "'";

$stmt_start = $conn_sqlsvr->prepare($sql_start_balance);
$stmt_start->execute();
$row_start = $stmt_start->fetch(PDO::FETCH_ASSOC);
$start_balance = 0;

if ($row_start) {
    $start_balance = $row_start['BSTMP_TOWARD'];
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
BANKSTATEMENT.BSTM_CHEQUE_NO,
BANKSTATEMENT.BSTM_RECNL_SEQ,
BANKSTATEMENT.BSTM_SHOW_ORDER,
BANKSTATEMENT.BSTM_LASTUPD,
BANKSTATEMENT.BSTM_KEY
FROM BANKSTATEMENT 
LEFT JOIN BANKACCOUNT ON BANKACCOUNT.BNKAC_KEY = BANKSTATEMENT.BSTM_BNKAC
LEFT JOIN DOCINFO ON DOCINFO.DI_KEY = BANKSTATEMENT.BSTM_DI
LEFT JOIN CHEQUEBOOK ON CHEQUEBOOK.CQBK_REFER_REF = DOCINFO.DI_REF
WHERE DOCINFO.DI_ACTIVE = 0 AND BANKACCOUNT.BNKAC_KEY = " . $bank . "   
AND BANKSTATEMENT.BSTM_RECNL_DD BETWEEN '" . $doc_date_start . "' AND '" . $doc_date_to . "'
ORDER BY BANKSTATEMENT.BSTM_RECNL_DD,BANKSTATEMENT.BSTM_RECNL_SEQ,BANKSTATEMENT.BSTM_SHOW_ORDER DESC";

$stmt_transactions = $conn_sqlsvr->prepare($sql_transactions);
$stmt_transactions->execute();
$transactions = $stmt_transactions->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Table</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>
    <script src="js/subscript.js"></script>
</head>
<body id="page-top">

<div class="container mt-5">
    <!--h2 class="mb-4">รายการความเคลื่อนไหวบัญชีธนาคาร (BANK Statement)</h2-->
    <h5 class="mb-4">รายงานความเคลื่อนไหวบัญชีธนาคาร <?php echo $bank_name ?> วันที่ <?php echo $_POST['doc_date_start'] . " - " . $_POST['doc_date_to']?>  </h5>
    <table class="table table-striped table-bordered">
        <thead class="table-primary">
        <tr>
            <th>BSTM_RECNL_DD</th>
            <th>BNKAC_NAME</th>
            <th>BSTM_CREDIT</th>
            <th>BSTM_DEBIT</th>
            <th>ยอดคงเหลือ</th>
            <th>BSTM_REMARK</th>
            <th>DI_DATE</th>
            <th>DI_REF</th>
            <th>CQBK_CHEQUE_DD</th>
            <th>BSTM_CHEQUE_NO</th>
        </tr>
        </thead>
        <tbody>
        <!-- แสดงยอดยกมา -->
        <?php
        $current_balance = $start_balance;
        echo "<tr>
                    <td colspan='4' class='text-end'><strong>ยอดยกมา</strong></td>
                    <td><strong>" . number_format($current_balance, 2) . "</strong></td>
                    <td colspan='5'></td>
                  </tr>";

        // Loop แสดงรายการธุรกรรม
        foreach ($transactions as $transaction) {
            $current_balance = $current_balance - $transaction['BSTM_CREDIT'] + $transaction['BSTM_DEBIT'];
            echo "<tr>
                        <td>{$transaction['BSTM_RECNL_DD']}</td>
                        <td>{$transaction['BNKAC_NAME']}</td>
                        <td>" . number_format($transaction['BSTM_CREDIT'], 2) . "</td>
                        <td>" . number_format($transaction['BSTM_DEBIT'], 2) . "</td>
                        <td>" . number_format($current_balance, 2) . "</td>
                        <td>{$transaction['BSTM_REMARK']}</td>
                        <td>{$transaction['DI_DATE']}</td>
                        <td>{$transaction['DI_REF']}</td>
                        <td>{$transaction['CQBK_CHEQUE_DD']}</td>
                        <td>{$transaction['BSTM_CHEQUE_NO']}</td>
                      </tr>";
        }
        ?>
        </tbody>
    </table>
</div>

<style>
    .scroll-to-bottom {
        position: fixed;
        /*bottom: 40px;
        right: 40px;*/
        right: 1rem;
        bottom: 5rem;
        width: 2.75rem;
        height: 2.75rem;
        display: none;
        color: #fff;
        background: rgba(90, 92, 105, .5);
        padding: 10px;
        border-radius: 50%;
        text-align: center;
        cursor: pointer;
        font-size: 18px;
    }
</style>


<!-- Scroll to top -->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<!-- ปุ่ม Scroll to Bottom -->
<a class="scroll-to-bottom rounded" href="#bottom">
    <i class="fas fa-angle-down"></i>
</a>

<!-- สร้าง anchor ที่ตำแหน่งล่างสุดของหน้า -->
<div id="bottom"></div>

</body>
</html>
