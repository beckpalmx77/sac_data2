<?php
include 'config/connect_db.php';

try {
    // Connection A (already established as $conn)
    echo "Conn A active. Starting transaction...\n";
    $conn->beginTransaction();
    
    $stmt = $conn->prepare("INSERT INTO ims_data_sale_sac_all (DI_DAY, DI_MONTH_NAME, DI_YEAR, AR_CODE, SKU_CODE, DI_REF, WL_CODE) VALUES ('30', 'มิถุนายน', '2026', 'TEST_A', 'TEST_SKU_A', 'TEST_REF_A', 'TEST_WL_A')");
    $stmt->execute();
    $id = $conn->lastInsertId();
    echo "Conn A inserted row ID: $id\n";
    
    $conn->commit();
    echo "Conn A committed.\n";
    
    // Now open Connection B
    echo "Opening Conn B...\n";
    include('config/db_value.inc');
    $connB = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";port=" .DB_PORT,DB_USER, DB_PASS
        ,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    $connB->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmtB = $connB->prepare("SELECT * FROM ims_data_sale_sac_all WHERE id = ?");
    $stmtB->execute([$id]);
    $rowB = $stmtB->fetch(PDO::FETCH_ASSOC);
    if ($rowB) {
        echo "Conn B successfully saw the row!\n";
        
        // Clean up
        $connB->prepare("DELETE FROM ims_data_sale_sac_all WHERE id = ?")->execute([$id]);
        echo "Conn B cleaned up the row.\n";
    } else {
        echo "Conn B FAILED to see the row!\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
