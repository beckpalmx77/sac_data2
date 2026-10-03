<?php
include 'config/connect_db.php';
try {
    $stmt = $conn->query("SELECT COUNT(*) FROM log_import_data");
    echo "Total logs: " . $stmt->fetchColumn() . "\n";
    
    $stmt2 = $conn->query("SELECT id, detail2, create_date FROM log_import_data ORDER BY id DESC LIMIT 5");
    $recent = $stmt2->fetchAll(PDO::FETCH_ASSOC);
    print_r($recent);
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
