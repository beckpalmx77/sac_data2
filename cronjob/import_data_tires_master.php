<?php

$start_time = microtime(true);

ini_set('display_errors', 1);
error_reporting(~0);

include ("../config/connect_sqlserver.php");
include ("../config/connect_db.php");

include ("../cond_file/doc_info_customer_ar.php");

$sql_mysql= " SELECT * FROM ims_tires_master_main order by id ";

$stmt_mysql = $conn->prepare($sql_mysql);
$stmt_mysql->execute();

// Pre-fetch existing record keys into PHP Memory lookup array to eliminate N+1 queries
$existing_tires = array();
$stmt_check_tires = $conn->query("SELECT brand, class, tires_code, detail FROM ims_tires_master");
while ($trow = $stmt_check_tires->fetch(PDO::FETCH_ASSOC)) {
    $composite = $trow['brand'] . '|' . $trow['class'] . '|' . $trow['tires_code'] . '|' . $trow['detail'];
    $existing_tires[$composite] = true;
}

// Move PDO prepare statements OUTSIDE the while loop
$sql_find = "SELECT COUNT(*) FROM ims_tires_master WHERE brand = :brand AND class = :class AND tires_code = :tires_code AND detail = :detail";
$stmt_check = $conn->prepare($sql_find);

$sql_update = "UPDATE ims_tires_master SET brand=:brand, class=:class, tires_code=:tires_code, detail=:detail
WHERE brand=:tires_brand AND class=:tires_class AND tires_code=:tires_code_where AND detail=:tires_detail";
$query_update = $conn->prepare($sql_update);

$sql_insert = "INSERT INTO ims_tires_master(brand, class, tires_code, detail) VALUES (:brand, :class, :tires_code, :detail)";
$query_insert = $conn->prepare($sql_insert);

$inserted_count = 0;
$updated_count = 0;

// Wrap transaction around the loop
$conn->beginTransaction();
try {
    while ($result_mysql = $stmt_mysql->fetch(PDO::FETCH_ASSOC)) {
        $composite = $result_mysql["tires_brand"] . '|' . $result_mysql["tires_class"] . '|' . $result_mysql["tires_code"] . '|' . $result_mysql["tires_detail"];

        $exists = isset($existing_tires[$composite]);
        if (!$exists) {
            $stmt_check->execute([
                ':brand' => $result_mysql["tires_brand"],
                ':class' => $result_mysql["tires_class"],
                ':tires_code' => $result_mysql["tires_code"],
                ':detail' => $result_mysql["tires_detail"]
            ]);
            $exists = (bool)$stmt_check->fetchColumn();
        }

        if ($exists) {
            $query_update->execute([
                ':brand' => $result_mysql["tires_brand"],
                ':class' => $result_mysql["tires_class"],
                ':tires_code' => $result_mysql["tires_code"],
                ':detail' => $result_mysql["tires_detail"],
                ':tires_brand' => $result_mysql["tires_brand"],
                ':tires_class' => $result_mysql["tires_class"],
                ':tires_code_where' => $result_mysql["tires_code"],
                ':tires_detail' => $result_mysql["tires_detail"]
            ]);
            $updated_count++;
        } else {
            $query_insert->execute([
                ':brand' => $result_mysql["tires_brand"],
                ':class' => $result_mysql["tires_class"],
                ':tires_code' => $result_mysql["tires_code"],
                ':detail' => $result_mysql["tires_detail"]
            ]);
            $existing_tires[$composite] = true;
            $inserted_count++;
        }
    }
    $conn->commit();
} catch (Exception $e) {
    $conn->rollBack();
    echo "Error during import: " . $e->getMessage() . "\n";
}

$conn_sqlsvr = null;

$end_time = microtime(true);
$execution_time = round($end_time - $start_time, 4);

echo "Import Completed | Updated: $updated_count | Inserted: $inserted_count | Execution Time: {$execution_time} seconds\n";

