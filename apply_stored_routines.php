<?php
/**
 * Script to automatically apply the Stored Procedures and Functions migrations.
 * Run this script via CLI or browser to register them in the database.
 */

// Show error messages
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include('config/connect_db.php');

$file_path = __DIR__ . '/db/migration_stored_routines.sql';

if (!file_exists($file_path)) {
    die("Error: migration SQL file not found at $file_path\n");
}

try {
    $sql = file_get_contents($file_path);
    
    // Clean up SQL: Remove line comments (--)
    $sql = preg_replace('/^[ \t]*--.*\r?\n/m', '', $sql);
    
    // Clean up SQL: Remove inline comments
    $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);

    // Remove DELIMITER statements since PDO doesn't use standard client delimiters
    $sql = preg_replace('/^[ \t]*DELIMITER\s+\S+[ \t]*\r?\n?/mi', '', $sql);

    // Split SQL queries by "//" separator
    $queries = explode('//', $sql);
    
    $success_count = 0;
    
    foreach ($queries as $query) {
        $query = trim($query);
        if (empty($query)) {
            continue;
        }
        
        try {
            $conn->exec($query);
            $success_count++;
        } catch (PDOException $ex) {
            echo "Failed executing query block:\n" . htmlspecialchars($query) . "\nError: " . $ex->getMessage() . "\n\n";
        }
    }
    
    echo "Database setup completed! Applied $success_count query blocks successfully.\n";

} catch (Exception $e) {
    echo "Fatal Error: " . $e->getMessage() . "\n";
}
