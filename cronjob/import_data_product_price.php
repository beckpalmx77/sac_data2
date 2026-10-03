<?php
$start_time = microtime(true);

ini_set('display_errors', 1);
error_reporting(~0);

include("../config/connect_sqlserver.php");
include("../config/connect_db.php");
include("../cond_file/query-product-price-main.php");

$sql_sqlsvr = $select_query . $sql_cond . $sql_order;

//$myfile = fopen("sqlqry_file1.txt", "w") or die("Unable to open file!");
//fwrite($myfile, $sql_sqlsvr);
//fclose($myfile);

$stmt_sqlsvr = $conn_sqlsvr->prepare($sql_sqlsvr);
$stmt_sqlsvr->execute();

$updated = 0;
$inserted = 0;

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
$sql_check = "SELECT 1 FROM ims_product WHERE product_id = :product_id AND product_key = :product_key AND price_code = :price_code LIMIT 1";
$stmt_check = $conn->prepare($sql_check);

$sql_update = "UPDATE ims_product SET name_t=:name_t , brand_id=:brand_id , pgroup_id=:pgroup_id , price=:price , unit_name=:unit_name"
    . " WHERE product_id = :product_id AND product_key = :product_key AND price_code = :price_code";
$stmt_update = $conn->prepare($sql_update);

$sql_insert = "INSERT INTO ims_product(product_key,product_id,pgroup_id,name_t,brand_id,price_code,price,unit_name) 
        VALUES (:product_key,:product_id,:pgroup_id,:name_t,:brand_id,:price_code,:price,:unit_name)";
$stmt_insert = $conn->prepare($sql_insert);

try {
    $conn->beginTransaction();

    while ($result_sqlsvr = $stmt_sqlsvr->fetch(PDO::FETCH_ASSOC)) {
        $key = $result_sqlsvr["SKU_CODE"] . "|" . $result_sqlsvr["SKU_KEY"] . "|" . $result_sqlsvr["ARPRB_CODE"];
        $exists = isset($existing_keys[$key]);
        if (!$exists) {
            $stmt_check->execute([
                ':product_id' => $result_sqlsvr["SKU_CODE"],
                ':product_key' => $result_sqlsvr["SKU_KEY"],
                ':price_code' => $result_sqlsvr["ARPRB_CODE"]
            ]);
            $exists = (bool)$stmt_check->fetchColumn();
        }

        if ($exists) {
            $stmt_update->bindParam(':name_t', $result_sqlsvr["SKU_NAME"], PDO::PARAM_STR);
            $stmt_update->bindParam(':brand_id', $result_sqlsvr["BRN_CODE"], PDO::PARAM_STR);
            $stmt_update->bindParam(':pgroup_id', $result_sqlsvr["ICCAT_CODE"], PDO::PARAM_STR);
            $stmt_update->bindParam(':price', $result_sqlsvr["ARPLU_U_PRC"], PDO::PARAM_STR);
            $stmt_update->bindParam(':unit_name', $result_sqlsvr["UTQ_NAME"], PDO::PARAM_STR);
            $stmt_update->bindParam(':product_id', $result_sqlsvr["SKU_CODE"], PDO::PARAM_STR);
            $stmt_update->bindParam(':product_key', $result_sqlsvr["SKU_KEY"], PDO::PARAM_STR);
            $stmt_update->bindParam(':price_code', $result_sqlsvr["ARPRB_CODE"], PDO::PARAM_STR);
            $stmt_update->execute();
            $updated++;
        } else {
            $stmt_insert->bindParam(':product_key', $result_sqlsvr["SKU_KEY"], PDO::PARAM_STR);
            $stmt_insert->bindParam(':product_id', $result_sqlsvr["SKU_CODE"], PDO::PARAM_STR);
            $stmt_insert->bindParam(':pgroup_id', $result_sqlsvr["ICCAT_CODE"], PDO::PARAM_STR);
            $stmt_insert->bindParam(':name_t', $result_sqlsvr["SKU_NAME"], PDO::PARAM_STR);
            $stmt_insert->bindParam(':brand_id', $result_sqlsvr["BRN_CODE"], PDO::PARAM_STR);
            $stmt_insert->bindParam(':price_code', $result_sqlsvr["ARPRB_CODE"], PDO::PARAM_STR);
            $stmt_insert->bindParam(':price', $result_sqlsvr["ARPLU_U_PRC"], PDO::PARAM_STR);
            $stmt_insert->bindParam(':unit_name', $result_sqlsvr["UTQ_NAME"], PDO::PARAM_STR);
            $stmt_insert->execute();

            $existing_keys[$key] = true;
            $inserted++;
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
echo "Updated: " . $updated . " | Inserted: " . $inserted . " | Execution time: " . $execution_time . " seconds\n";


