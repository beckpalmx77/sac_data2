<?php
include 'config/connect_db.php';
try {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM ims_data_sale_sac_all WHERE DI_DATE = '29-06-2026'");
    $stmt->execute();
    echo "Count of DI_DATE = '29-06-2026': " . $stmt->fetchColumn() . "\n";
    
    $stmt2 = $conn->prepare("SELECT MAX(id) FROM log_import_data");
    $stmt2->execute();
    $max_id = $stmt2->fetchColumn();
    echo "Max ID in log_import_data: " . $max_id . "\n";
    
    if ($max_id) {
        $stmt3 = $conn->prepare("SELECT * FROM log_import_data WHERE id = ?");
        $stmt3->execute([$max_id]);
        $row = $stmt3->fetch(PDO::FETCH_ASSOC);
        print_r($row);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
