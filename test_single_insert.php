<?php
include 'config/connect_db.php';
try {
    $conn->beginTransaction();
    echo "Transaction started.\n";
    
    $stmt = $conn->prepare("INSERT INTO ims_data_sale_sac_all (DI_DAY, DI_MONTH_NAME, DI_YEAR, AR_CODE, SKU_CODE, DI_REF, WL_CODE) VALUES ('30', 'มิถุนายน', '2026', 'TEST', 'TEST_SKU', 'TEST_REF', 'TEST_WL')");
    $stmt->execute();
    $id = $conn->lastInsertId();
    echo "Inserted ID: $id\n";
    
    $conn->commit();
    echo "Transaction committed.\n";
    
    // Verify it exists
    $stmt = $conn->prepare("SELECT * FROM ims_data_sale_sac_all WHERE id = ?");
    $stmt->execute([$id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row) {
        echo "Successfully verified row in DB:\n";
        print_r($row);
        
        // Clean up
        $conn->prepare("DELETE FROM ims_data_sale_sac_all WHERE id = ?")->execute([$id]);
        echo "Cleaned up test row.\n";
    } else {
        echo "FAILED TO FIND INSERTED ROW!\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
