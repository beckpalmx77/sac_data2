<?php

$start_time = microtime(true);

ini_set('display_errors', 1);
error_reporting(~0);

include ("../config/connect_sqlserver.php");
include ("../config/connect_db.php");

include ("../cond_file/doc_info_customer_ar.php");

$sql_mysql = " SELECT * FROM v_customer_ar ";

// Pre-fetch existing AR_CODEs into PHP Memory lookup array
$existing_arcodes = array_flip($conn->query("SELECT AR_CODE FROM ims_customer_arcode")->fetchAll(PDO::FETCH_COLUMN));

// Prepared check statement ONCE outside loop
$sql_check = "SELECT 1 FROM ims_customer_arcode WHERE AR_CODE = :AR_CODE LIMIT 1";
$stmt_check = $conn->prepare($sql_check);

// Prepare UPDATE and INSERT statements ONCE outside loop
$sql_update = "UPDATE ims_customer_arcode SET AR_NAME=:AR_NAME WHERE AR_CODE = :AR_CODE";
$stmt_update = $conn->prepare($sql_update);

$sql_insert = "INSERT INTO ims_customer_arcode(AR_CODE,AR_NAME) VALUES (:AR_CODE,:AR_NAME)";
$stmt_insert = $conn->prepare($sql_insert);

$stmt_mysql = $conn->prepare($sql_mysql);
$stmt_mysql->execute();

$inserted_count = 0;
$updated_count = 0;

$conn->beginTransaction();
try {
    while ($result_mysql = $stmt_mysql->fetch(PDO::FETCH_ASSOC)) {
        $ar_code = $result_mysql["AR_CODE"];
        $exists = isset($existing_arcodes[$ar_code]);
        if (!$exists) {
            $stmt_check->execute([':AR_CODE' => $ar_code]);
            $exists = (bool)$stmt_check->fetchColumn();
        }

        if ($exists) {
            $stmt_update->execute([
                ':AR_NAME' => $result_mysql["AR_NAME"],
                ':AR_CODE' => $ar_code
            ]);
            $updated_count++;
        } else {
            $stmt_insert->execute([
                ':AR_CODE' => $ar_code,
                ':AR_NAME' => $result_mysql["AR_NAME"]
            ]);
            $existing_arcodes[$ar_code] = true;
            $inserted_count++;
        }
    }
    $conn->commit();
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "Error: " . $e->getMessage() . "\n";
}

$conn_sqlsvr = null;

$execution_time = round(microtime(true) - $start_time, 4);
echo "Import Customer AR Code Summary:\n";
echo "Inserted: $inserted_count\n";
echo "Updated: $updated_count\n";
echo "Execution Time: {$execution_time} seconds\n";


