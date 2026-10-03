<?php
include 'config/connect_db.php';
try {
    $seq = '3d7781d4bc0b784a329e71ec26a8f158';
    $stmt = $conn->prepare("SELECT COUNT(*) FROM ims_data_sale_sac_all WHERE seq_record = ?");
    $stmt->execute([$seq]);
    echo "Count for seq_record '$seq': " . $stmt->fetchColumn() . "\n";
    
    // Check if the log is there
    $stmt2 = $conn->prepare("SELECT * FROM log_import_data WHERE seq_record = ?");
    $stmt2->execute([$seq]);
    $log = $stmt2->fetch(PDO::FETCH_ASSOC);
    if ($log) {
        echo "Log found:\n";
        print_r($log);
    } else {
        echo "Log NOT found!\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
