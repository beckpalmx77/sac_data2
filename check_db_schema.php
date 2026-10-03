<?php
include('config/connect_db.php');

try {
    $stmt = $conn->query("SHOW COLUMNS FROM ims_tires_plan_forecast");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo str_pad("Field", 25) . " | " . str_pad("Type", 20) . " | " . "Default" . "\n";
    echo str_repeat("-", 60) . "\n";

    foreach ($columns as $col) {
        echo str_pad($col['Field'], 25) . " | " . str_pad($col['Type'], 20) . " | " . ($col['Default'] ?? 'NULL') . "\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
