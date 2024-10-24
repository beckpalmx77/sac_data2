<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/css/bootstrap.min.css">

<?php
include("config/connect_sqlserver40_syy.php");

// Query 1: ดึงยอดยกมา
$sql_start_balance = "SELECT * FROM BSTMPERIOD 
                      WHERE BSTMP_BNKAC = 106 
                      AND BSTMP_ST_DATE BETWEEN '2024/08/01' AND '2024/08/31'";

$stmt_start = $conn_sqlsvr->prepare($sql_start_balance);
$stmt_start->execute();
$row_start = $stmt_start->fetch(PDO::FETCH_ASSOC);
$start_balance = 0;

// ถ้ามียอดยกมา
if ($row_start) {
    $start_balance = $row_start['BSTMP_TOWARD'];  // สมมุติว่า column ที่เก็บยอดยกมาคือ BSTMP_START_BALANCE
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
                     
                     WHERE BANKACCOUNT.BNKAC_KEY = 106  
                     AND BANKSTATEMENT.BSTM_RECNL_DD BETWEEN '2024/08/01' AND '2024/08/31' 
                     ORDER BY BANKSTATEMENT.BSTM_RECNL_DD";

$stmt_transactions = $conn_sqlsvr->prepare($sql_transactions);
$stmt_transactions->execute();
$transactions = $stmt_transactions->fetchAll(PDO::FETCH_ASSOC);

// เริ่มแสดงข้อมูล
echo '<table class="table">';
echo '<thead>
        <tr>
            <th>BSTM_RECNL_DD</th>
            <th>BNKAC_NAME</th>
            <th>BSTM_CREDIT</th>
            <th>BSTM_DEBIT</th>
            <th>ยอดคงเหลือ</th> <!-- แสดงยอดคงเหลือ -->
            <th>BSTM_REMARK</th>
            <th>DI_DATE</th>
            <th>DI_REF</th>        
            <th>CQBK_CHEQUE_DD</th>
            <th>BSTM_CHEQUE_NO</th>
        </tr>
      </thead>
      <tbody>';

// แสดงยอดยกมา
$current_balance = $start_balance;  // กำหนดยอดยกมาเป็นยอดเริ่มต้น
echo "<tr class='table-active'>
        <td colspan='6'>ยอดยกมา</td>
        <td></td>
        <td>" . number_format($current_balance, 2) . "</td>
        <td></td>
        <td></td>
      </tr>";

// Loop แสดงรายการธุรกรรม
foreach ($transactions as $transaction) {
    // คำนวณยอดคงเหลือ
    $current_balance = $current_balance - $transaction['BSTM_CREDIT'] + $transaction['BSTM_DEBIT'];

    // แสดงผลในแต่ละบรรทัด
    echo "<tr>            
            <td>{$transaction['BSTM_RECNL_DD']}</td>            
            <td>{$transaction['BNKAC_NAME']}</td>
            <td>" . number_format($transaction['BSTM_CREDIT'], 2) . "</td>
            <td>" . number_format($transaction['BSTM_DEBIT'], 2) . "</td>
            <td>" . number_format($current_balance, 2) . "</td> <!-- แสดงยอดคงเหลือ -->
            <td>{$transaction['BSTM_REMARK']}</td>            
            <td>{$transaction['DI_DATE']}</td>     
            <td>{$transaction['DI_REF']}</td>            
            <td>{$transaction['CQBK_CHEQUE_DD']}</td>
            <td>{$transaction['BSTM_CHEQUE_NO']}</td>
          </tr>";
}

echo "</tbody></table>";
?>
