<?php
include('config/connect_db.php');

try {
    echo "Checking for view v_customer_data_sale_all_zone...\n";
    $stmt = $conn->query("SHOW COLUMNS FROM v_customer_data_sale_all_zone");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo str_pad("Field", 25) . " | " . "Type" . "\n";
    echo str_repeat("-", 40) . "\n";
    foreach ($columns as $col) {
        echo str_pad($col['Field'], 25) . " | " . $col['Type'] . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
