<?php

ini_set('display_errors', 1);
error_reporting(~0);

$start_time = microtime(true);

include("../config/connect_db.php");

$cond_date = " STR_TO_DATE(DI_DATE,'%d/%m/%Y') BETWEEN 
STR_TO_DATE(". "'" . date("d/m/Y", strtotime("yesterday")) . "'" . ",'%d/%m/%Y') AND 
STR_TO_DATE(". "'" . date("d/m/Y") . "'" . ",'%d/%m/%Y') ";

$sql_main = " SELECT DI_DATE, BRANCH FROM ims_product_sale_cockpit WHERE " . $cond_date
            . " GROUP BY DI_DATE, BRANCH "
            . " ORDER BY STR_TO_DATE(DI_DATE,'%d/%m/%Y') ";

// Prepared Statement Outside Loop
$sql_ins = " INSERT INTO ims_report_product_sale (DI_DATE, SALE_CODE) VALUES (:DI_DATE, :SALE_CODE) ";
$stmt_ins = $conn->prepare($sql_ins);

// Pre-fetch existing record keys
$sql_existing = "SELECT CONCAT(DI_DATE, '|', SALE_CODE) FROM ims_report_product_sale";
$stmt_existing = $conn->prepare($sql_existing);
$stmt_existing->execute();
$existing_keys = array_flip($stmt_existing->fetchAll(PDO::FETCH_COLUMN));

$stmt_main = $conn->prepare($sql_main);
$stmt_main->execute();

$conn->beginTransaction();

$count_ins = 0;

try {
    while ($result_main = $stmt_main->fetch(PDO::FETCH_ASSOC)) {
        $key = $result_main["DI_DATE"] . '|' . $result_main["BRANCH"];
        if (!isset($existing_keys[$key])) {
            $stmt_ins->execute([
                ':DI_DATE'   => $result_main["DI_DATE"],
                ':SALE_CODE' => $result_main["BRANCH"]
            ]);
            $existing_keys[$key] = true;
            $count_ins++;
        }
    }

    $conn->commit();
    echo "Process product sale report completed successfully.\n";
    echo "Inserted: " . $count_ins . " records.\n";

} catch (Exception $e) {
    $conn->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}

$conn = null;

echo "Execution time: " . (microtime(true) - $start_time) . " seconds\n";
