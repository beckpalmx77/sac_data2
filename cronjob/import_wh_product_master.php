<?php

$start_time = microtime(true);

ini_set('display_errors', 1);
error_reporting(~0);

date_default_timezone_set("Asia/Bangkok");

require_once __DIR__ . "/../config/connect_sqlserver.php";
require_once __DIR__ . "/../config/connect_db.php";

$sql_sql_svr = "SELECT 
    ICCAT.ICCAT_KEY,
    ICCAT.ICCAT_CODE,
    ICCAT.ICCAT_NAME,
    SKUMASTER.SKU_CODE,
    SKUMASTER.SKU_NAME,
    GOODSMASTER.GOODS_SKU,    
    UOFQTY.UTQ_KEY,
    UOFQTY.UTQ_NAME,
    BRAND.BRN_CODE,
    BRAND.BRN_NAME,
    ICCAT_CODE
FROM ICCAT
LEFT JOIN SKUMASTER ON ICCAT.ICCAT_KEY = SKUMASTER.SKU_ICCAT
LEFT JOIN GOODSMASTER ON GOODSMASTER.GOODS_SKU = SKUMASTER.SKU_KEY 
LEFT JOIN UOFQTY ON UOFQTY.UTQ_KEY = GOODSMASTER.GOODS_UTQ    
LEFT JOIN BRAND ON BRAND.BRN_KEY = SKUMASTER.SKU_BRN
WHERE 
    ICCAT.ICCAT_CODE IN (
        '1SAC01', '2SAC01', '4SAC01', '3SAC01', '5SAC02', '1SAC07', '5SAC01', '1SAC06', '6SAC08', '1SAC02',
        '1SAC03', '1SAC04', '1SAC05', '1SAC09', '1SAC08', '1SAC10', '1SAC11', '1SAC12', '1SAC13', '1SAC14',
        '2SAC09', '2SAC04', '2SAC13', '2SAC14', '2SAC12', '2SAC02', '2SAC03', '2SAC10', '2SAC15', '2SAC06',
        '2SAC05', '2SAC07', '2SAC08', '2SAC11', '3SAC02', '3SAC06', '3SAC03', '3SAC04', '4SAC02', '4SAC03',
        '4SAC04', '4SAC06', '3SAC05', '4SAC05', '6SAC001', '7SAC001', '2SAC16', '2SAC17', '2SAC18', '6SAC002', '1SAC15',
        '2SAC19', '2SAC20', '2SAC21' 
    ) ORDER BY SKUMASTER.SKU_CODE ;";

$stmt_sql_svr = $conn_sqlsvr->prepare($sql_sql_svr);
$stmt_sql_svr->execute();

// Pre-fetch existing record keys into PHP Memory lookup array to eliminate N+1 queries
$existing_products = array();
$stmt_check_pm = $conn->query("SELECT product_id FROM wh_product_master");
while ($prow = $stmt_check_pm->fetch(PDO::FETCH_ASSOC)) {
    if ($prow['product_id'] !== null) {
        $existing_products[$prow['product_id']] = true;
    }
}

// Move PDO prepare statements OUTSIDE the while loop
$sql_check = "SELECT COUNT(*) FROM wh_product_master WHERE product_id = :product_id";
$stmt_check = $conn->prepare($sql_check);

$sql_update = "UPDATE wh_product_master SET product_name=:product_name,unit_id=:unit_id,unit_name=:unit_name,brand_code=:brand_code,brand_name=:brand_name,iccat_code=:iccat_code 
WHERE product_id = :product_id";
$query_update = $conn->prepare($sql_update);

$sql_insert = "INSERT INTO wh_product_master(product_id,product_name,unit_id,unit_name,brand_code,brand_name,iccat_code) 
VALUES (:product_id,:product_name,:unit_id,:unit_name,:brand_code,:brand_name,:iccat_code)";
$query_insert = $conn->prepare($sql_insert);

$inserted_count = 0;
$updated_count = 0;

// Wrap transaction around the loop
$conn->beginTransaction();
try {
    while ($result_sql_svr = $stmt_sql_svr->fetch(PDO::FETCH_ASSOC)) {
        $sku_code = $result_sql_svr["SKU_CODE"];

        if ($sku_code !== null) {
            $exists = isset($existing_products[$sku_code]);
            if (!$exists) {
                $stmt_check->execute([':product_id' => $sku_code]);
                $exists = (bool)$stmt_check->fetchColumn();
            }

            if ($exists) {
                $query_update->execute([
                    ':product_name' => $result_sql_svr["SKU_NAME"],
                    ':unit_id' => $result_sql_svr["UTQ_KEY"],
                    ':unit_name' => $result_sql_svr["UTQ_NAME"],
                    ':brand_code' => $result_sql_svr["BRN_CODE"],
                    ':brand_name' => $result_sql_svr["BRN_NAME"],
                    ':iccat_code' => $result_sql_svr["ICCAT_CODE"],
                    ':product_id' => $sku_code
                ]);
                $updated_count++;
            } else {
                $query_insert->execute([
                    ':product_id' => $sku_code,
                    ':product_name' => $result_sql_svr["SKU_NAME"],
                    ':unit_id' => $result_sql_svr["UTQ_KEY"],
                    ':unit_name' => $result_sql_svr["UTQ_NAME"],
                    ':brand_code' => $result_sql_svr["BRN_CODE"],
                    ':brand_name' => $result_sql_svr["BRN_NAME"],
                    ':iccat_code' => $result_sql_svr["ICCAT_CODE"]
                ]);
                $existing_products[$sku_code] = true;
                $inserted_count++;
            }
        }
    }
    $conn->commit();
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "Error during import: " . $e->getMessage() . "\n";
    exit(1);
}

$conn_sqlsvr = null;

$end_time = microtime(true);
$execution_time = round($end_time - $start_time, 4);

echo "Import Completed | Updated: $updated_count | Inserted: $inserted_count | Execution Time: {$execution_time} seconds\n";
