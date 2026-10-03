<?php
include 'config/connect_db.php';

try {
    echo "Active DB Host: " . DB_HOST . "\n";
    $conn->beginTransaction();
    echo "Transaction started on Conn A.\n";
    
    $stmt = $conn->prepare("INSERT INTO ims_data_sale_sac_all (DI_DAY, DI_MONTH_NAME, DI_YEAR, AR_CODE, SKU_CODE, DI_REF, WL_CODE) VALUES ('30', 'มิถุนายน', '2026', 'PERSIST_TEST', 'SKU_PERSIST', 'REF_PERSIST', 'WL_PERSIST')");
    $stmt->execute();
    $id = $conn->lastInsertId();
    echo "Conn A inserted row ID: $id\n";
    
    $conn->commit();
    echo "Conn A committed.\n";
    
    // Close Conn A (destroy PDO object)
    $conn = null;
    echo "Conn A closed.\n";
    
    // Open Conn B
    include('config/db_value.inc');
    $connB = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";port=" .DB_PORT,DB_USER, DB_PASS
        ,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    $connB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Conn B opened.\n";
    
    $stmtB = $connB->prepare("SELECT * FROM ims_data_sale_sac_all WHERE id = ?");
    $stmtB->execute([$id]);
    $rowB = $stmtB->fetch(PDO::FETCH_ASSOC);
    if ($rowB) {
        echo "Conn B immediately sees the row!\n";
    } else {
        echo "Conn B immediately FAILED to see the row!\n";
    }
    
    // Wait 5 seconds
    echo "Waiting 5 seconds...\n";
    sleep(5);
    
    $stmtB->execute([$id]);
    $rowB = $stmtB->fetch(PDO::FETCH_ASSOC);
    if ($rowB) {
        echo "Conn B still sees the row after 5 seconds!\n";
        
        // Clean up
        $connB->prepare("DELETE FROM ims_data_sale_sac_all WHERE id = ?")->execute([$id]);
        echo "Conn B cleaned up the row.\n";
    } else {
        echo "Conn B FAILED to see the row after 5 seconds! (It vanished!)\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
