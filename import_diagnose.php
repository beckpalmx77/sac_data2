<?php
include 'config/connect_db.php';
try {
    echo "--- DB Variables ---\n";
    $vars = [
        'hostname',
        'port',
        'version',
        'socket',
        'datadir'
    ];
    foreach ($vars as $v) {
        $stmt = $conn->prepare("SHOW VARIABLES LIKE ?");
        $stmt->execute([$v]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            echo $row['Variable_name'] . ": " . $row['Value'] . "\n";
        }
    }
    
    $stmt = $conn->query("SELECT DATABASE()");
    echo "Current DB: " . $stmt->fetchColumn() . "\n";
    
    $stmt = $conn->query("SELECT @@port");
    echo "@@port: " . $stmt->fetchColumn() . "\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
