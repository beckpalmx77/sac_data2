<?php

$start_time = microtime(true);

ini_set('display_errors', 1);
error_reporting(~0);

include("../config/connect_sqlserver.php");
include("../config/connect_db.php");

include("../cond_file/query-product-stock-balance.php");

$sql_sqlsvr = $select_query;

$stmt_sqlsvr = $conn_sqlsvr->prepare($sql_sqlsvr);
$stmt_sqlsvr->execute();

// Pre-fetch existing record keys into PHP Memory lookup array to eliminate N+1 queries
$existing_keys = array();
$stmt_check_keys = $conn->query("SELECT ICCAT_CODE, SKU_CODE, WH_CODE, WL_CODE, SKM_LOT_NO, SKM_SERIAL FROM ims_product_stock_balance");
while ($krow = $stmt_check_keys->fetch(PDO::FETCH_ASSOC)) {
    $composite = $krow['ICCAT_CODE'] . '|' . $krow['SKU_CODE'] . '|' . $krow['WH_CODE'] . '|' . $krow['WL_CODE'] . '|' . $krow['SKM_LOT_NO'] . '|' . $krow['SKM_SERIAL'];
    $existing_keys[$composite] = true;
}

// Move PDO prepare statements OUTSIDE the while loop
$sql_find = "SELECT COUNT(*) FROM ims_product_stock_balance WHERE ICCAT_CODE = :ICCAT_CODE AND SKU_CODE = :SKU_CODE AND WH_CODE = :WH_CODE AND WL_CODE = :WL_CODE AND SKM_LOT_NO = :SKM_LOT_NO AND SKM_SERIAL = :SKM_SERIAL";
$stmt_check = $conn->prepare($sql_find);

$sql_update = " UPDATE ims_product_stock_balance SET ICCAT_NAME=:ICCAT_NAME,DI_DATE=:DI_DATE,SKU_NAME=:SKU_NAME,UTQ_NAME=:UTQ_NAME,UTQ_QTY=:UTQ_QTY,QTY=:QTY,STOCK_COST=:STOCK_COST,AC_COST=:AC_COST,STD_COST=:STD_COST 
WHERE ICCAT_CODE = :ICCAT_CODE AND SKU_CODE = :SKU_CODE AND WH_CODE = :WH_CODE AND WL_CODE = :WL_CODE AND SKM_LOT_NO = :SKM_LOT_NO AND SKM_SERIAL = :SKM_SERIAL";
$query_update = $conn->prepare($sql_update);

$sql_insert = "INSERT INTO ims_product_stock_balance(ICCAT_CODE,ICCAT_NAME,DI_DATE,SKU_CODE,SKU_NAME,WH_CODE,WL_CODE,SKM_LOT_NO,SKM_SERIAL,UTQ_NAME,UTQ_QTY,QTY,STOCK_COST,AC_COST,STD_COST)
VALUES (:ICCAT_CODE,:ICCAT_NAME,:DI_DATE,:SKU_CODE,:SKU_NAME,:WH_CODE,:WL_CODE,:SKM_LOT_NO,:SKM_SERIAL,:UTQ_NAME,:UTQ_QTY,:QTY,:STOCK_COST,:AC_COST,:STD_COST)";
$query_insert = $conn->prepare($sql_insert);

$inserted_count = 0;
$updated_count = 0;

// Wrap transaction around the loop
$conn->beginTransaction();
try {
    while ($result_sqlsvr = $stmt_sqlsvr->fetch(PDO::FETCH_ASSOC)) {
        $composite = $result_sqlsvr["ICCAT_CODE"] . '|' . $result_sqlsvr["SKU_CODE"] . '|' . $result_sqlsvr["WH_CODE"] . '|' . $result_sqlsvr["WL_CODE"] . '|' . $result_sqlsvr["SKM_LOT_NO"] . '|' . $result_sqlsvr["SKM_SERIAL"];

        $exists = isset($existing_keys[$composite]);
        if (!$exists) {
            $stmt_check->execute([
                ':ICCAT_CODE' => $result_sqlsvr["ICCAT_CODE"],
                ':SKU_CODE' => $result_sqlsvr["SKU_CODE"],
                ':WH_CODE' => $result_sqlsvr["WH_CODE"],
                ':WL_CODE' => $result_sqlsvr["WL_CODE"],
                ':SKM_LOT_NO' => $result_sqlsvr["SKM_LOT_NO"],
                ':SKM_SERIAL' => $result_sqlsvr["SKM_SERIAL"]
            ]);
            $exists = (bool)$stmt_check->fetchColumn();
        }

        if ($exists) {
            $query_update->execute([
                ':ICCAT_NAME' => $result_sqlsvr["ICCAT_NAME"],
                ':DI_DATE' => $result_sqlsvr["DI_DATE"],
                ':SKU_NAME' => $result_sqlsvr["SKU_NAME"],
                ':UTQ_NAME' => $result_sqlsvr["UTQ_NAME"],
                ':UTQ_QTY' => $result_sqlsvr["UTQ_QTY"],
                ':QTY' => $result_sqlsvr["QTY"],
                ':STOCK_COST' => $result_sqlsvr["STOCK_COST"],
                ':AC_COST' => $result_sqlsvr["AC_COST"],
                ':STD_COST' => $result_sqlsvr["STD_COST"],
                ':ICCAT_CODE' => $result_sqlsvr["ICCAT_CODE"],
                ':SKU_CODE' => $result_sqlsvr["SKU_CODE"],
                ':WH_CODE' => $result_sqlsvr["WH_CODE"],
                ':WL_CODE' => $result_sqlsvr["WL_CODE"],
                ':SKM_LOT_NO' => $result_sqlsvr["SKM_LOT_NO"],
                ':SKM_SERIAL' => $result_sqlsvr["SKM_SERIAL"]
            ]);
            $updated_count++;
        } else {
            $query_insert->execute([
                ':ICCAT_CODE' => $result_sqlsvr["ICCAT_CODE"],
                ':ICCAT_NAME' => $result_sqlsvr["ICCAT_NAME"],
                ':DI_DATE' => $result_sqlsvr["DI_DATE"],
                ':SKU_CODE' => $result_sqlsvr["SKU_CODE"],
                ':SKU_NAME' => $result_sqlsvr["SKU_NAME"],
                ':WH_CODE' => $result_sqlsvr["WH_CODE"],
                ':WL_CODE' => $result_sqlsvr["WL_CODE"],
                ':SKM_LOT_NO' => $result_sqlsvr["SKM_LOT_NO"],
                ':SKM_SERIAL' => $result_sqlsvr["SKM_SERIAL"],
                ':UTQ_NAME' => $result_sqlsvr["UTQ_NAME"],
                ':UTQ_QTY' => $result_sqlsvr["UTQ_QTY"],
                ':QTY' => $result_sqlsvr["QTY"],
                ':STOCK_COST' => $result_sqlsvr["STOCK_COST"],
                ':AC_COST' => $result_sqlsvr["AC_COST"],
                ':STD_COST' => $result_sqlsvr["STD_COST"]
            ]);
            $existing_keys[$composite] = true;
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

