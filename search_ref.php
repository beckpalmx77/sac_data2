<?php
include 'config/connect_db.php';
try {
    $stmt = $conn->prepare("SELECT * FROM ims_data_sale_sac_all WHERE DI_REF = 'IV01256906/1265'");
    $stmt->execute();
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (empty($rows)) {
        echo "No rows found for IV01256906/1265\n";
    } else {
        echo "Found " . count($rows) . " rows:\n";
        print_r($rows);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
