<?php
include("config/connect_sqlserver40_syy.php");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $bank = $_POST['bank'];

    // Query ยอดยกมา
    $sql_start_balance = "SELECT * FROM BSTMPERIOD 
                          WHERE BSTMP_BNKAC = :bank 
                          AND BSTMP_ST_DATE BETWEEN :start_date AND :end_date";
    $stmt_start = $conn_sqlsvr->prepare($sql_start_balance);
    $stmt_start->execute([':bank' => $bank, ':start_date' => $start_date, ':end_date' => $end_date]);
    $row_start = $stmt_start->fetch(PDO::FETCH_ASSOC);
    $start_balance = 0;

    if ($row_start) {
        $start_balance = $row_start['BSTMP_TOWARD'];
    }

    // Query รายการธุรกรรม
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

    // แสดงยอดยกมา
    $current_balance = $start_balance;
    echo "<tr>
            <td colspan='6'>ยอดยกมา</td>
            <td></td>
            <td>" . number_format($current_balance, 2) . "</td>
            <td></td>
            <td></td>
          </tr>";

    // แสดงรายการธุรกรรม
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
}
?>
