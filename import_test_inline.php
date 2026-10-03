<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'config/connect_db.php';

try {
    $stmt = $conn->query("SELECT COUNT(*) FROM ims_data_sale_sac_all");
    echo "Count before: " . $stmt->fetchColumn() . "\n";
    
    // Let's run a test insert with commit
    $conn->beginTransaction();
    $stmt_ins = $conn->prepare("INSERT INTO ims_data_sale_sac_all (DI_DAY, DI_MONTH_NAME, DI_YEAR, AR_CODE, SKU_CODE, DI_REF, WL_CODE) VALUES ('30', 'มิถุนายน', '2026', 'TEST', 'TEST_SKU', 'TEST_REF', 'TEST_WL')");
    $stmt_ins->execute();
    $id = $conn->lastInsertId();
    $conn->commit();
    echo "Committed insert ID: $id\n";
    
    $stmt = $conn->query("SELECT COUNT(*) FROM ims_data_sale_sac_all");
    echo "Count after: " . $stmt->fetchColumn() . "\n";
    
    // Clean up
    $conn->prepare("DELETE FROM ims_data_sale_sac_all WHERE id = ?")->execute([$id]);
    echo "Cleaned up.\n";
    
    $stmt = $conn->query("SELECT COUNT(*) FROM ims_data_sale_sac_all");
    echo "Count after clean: " . $stmt->fetchColumn() . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
