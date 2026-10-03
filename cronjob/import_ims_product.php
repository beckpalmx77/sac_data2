<?php

$start_time = microtime(true);

ini_set('display_errors', 1);
error_reporting(~0);

include("../config/connect_sqlserver.php");
include("../config/connect_db.php");
include("../cond_file/query-product-price-main.php");

$sql_sqlsvr = $select_query . $sql_cond . $sql_order;

$stmt_sqlsvr = $conn_sqlsvr->prepare($sql_sqlsvr);
$stmt_sqlsvr->execute();

$inserted_count = 0;
$updated_count = 0;

// Pre-fetch existing record keys into memory to eliminate N+1 SELECT queries
$existing_keys = array();
$sql_preload = "SELECT product_id, product_key, price_code FROM ims_product";
$stmt_preload = $conn->query($sql_preload);
if ($stmt_preload) {
    while ($row = $stmt_preload->fetch(PDO::FETCH_ASSOC)) {
        $key = $row["product_id"] . "|" . $row["product_key"] . "|" . $row["price_code"];
        $existing_keys[$key] = true;
    }
}

// Move PDO::prepare statements OUTSIDE the while loop so they are prepared ONCE
$sql_check = "SELECT COUNT(*) FROM ims_product WHERE product_id = :product_id AND product_key = :product_key AND price_code = :price_code";
$stmt_check = $conn->prepare($sql_check);

$sql_update = "UPDATE ims_product SET name_t=:name_t , brand_id=:brand_id , pgroup_id=:pgroup_id , price=:price , unit_name=:unit_name"
    . " WHERE product_id = :product_id AND product_key = :product_key AND price_code = :price_code";
$stmt_update = $conn->prepare($sql_update);

$sql_insert = "INSERT INTO ims_product(product_key,product_id,pgroup_id,name_t,brand_id,price_code,price,unit_name) 
        VALUES (:product_key,:product_id,:pgroup_id,:name_t,:brand_id,:price_code,:price,:unit_name)";
$stmt_insert = $conn->prepare($sql_insert);

$conn->beginTransaction();
try {
    while ($result_sqlsvr = $stmt_sqlsvr->fetch(PDO::FETCH_ASSOC)) {
        $composite = $result_sqlsvr["SKU_CODE"] . "|" . $result_sqlsvr["SKU_KEY"] . "|" . $result_sqlsvr["ARPRB_CODE"];

        $exists = isset($existing_keys[$composite]);
        if (!$exists) {
            $stmt_check->execute([
                ':product_id' => $result_sqlsvr["SKU_CODE"],
                ':product_key' => $result_sqlsvr["SKU_KEY"],
                ':price_code' => $result_sqlsvr["ARPRB_CODE"]
            ]);
            $exists = (bool)$stmt_check->fetchColumn();
        }

        if ($exists) {
            $stmt_update->execute([
                ':name_t' => $result_sqlsvr["SKU_NAME"],
                ':brand_id' => $result_sqlsvr["BRN_CODE"],
                ':pgroup_id' => $result_sqlsvr["ICCAT_CODE"],
                ':price' => $result_sqlsvr["ARPLU_U_PRC"],
                ':unit_name' => $result_sqlsvr["UTQ_NAME"],
                ':product_id' => $result_sqlsvr["SKU_CODE"],
                ':product_key' => $result_sqlsvr["SKU_KEY"],
                ':price_code' => $result_sqlsvr["ARPRB_CODE"]
            ]);
            $updated_count++;
        } else {
            $stmt_insert->execute([
                ':product_key' => $result_sqlsvr["SKU_KEY"],
                ':product_id' => $result_sqlsvr["SKU_CODE"],
                ':pgroup_id' => $result_sqlsvr["ICCAT_CODE"],
                ':name_t' => $result_sqlsvr["SKU_NAME"],
                ':brand_id' => $result_sqlsvr["BRN_CODE"],
                ':price_code' => $result_sqlsvr["ARPRB_CODE"],
                ':price' => $result_sqlsvr["ARPLU_U_PRC"],
                ':unit_name' => $result_sqlsvr["UTQ_NAME"]
            ]);
            $existing_keys[$composite] = true;
            $inserted_count++;
        }
    }

    $conn->commit();
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "Error during import: " . $e->getMessage() . "\n";
}

$conn_sqlsvr = null;

$end_time = microtime(true);
$execution_time = round($end_time - $start_time, 4);

echo "Import Completed | Updated: $updated_count | Inserted: $inserted_count | Execution Time: {$execution_time} seconds\n";
