<?php
$start_time = microtime(true);

ini_set('display_errors', 1);
error_reporting(~0);

include('../config/connect_db.php');
include('../config/connect_sqlserver.php');
include('../cond_file/query_product_stock.php');

$sql_main = $select_query_stock . $sql_cond_stock . $sql_group_stock . $sql_order_stock;

$create_date = date("Y-m-d H:i:s");
$update_date = date("Y-m-d H:i:s");

$statement = $conn_sqlsvr->query($sql_main);
$results = $statement->fetchAll(PDO::FETCH_ASSOC);

$updated = 0;
$inserted = 0;

if (!empty($results)) {
    // Collect unique SKU_CODEs to target preloading
    $sku_codes = array_filter(array_unique(array_column($results, 'SKU_CODE')));

    // Pre-fetch existing records into memory for target SKU_CODEs only
    $existing_records = array();
    $chunks = array_chunk($sku_codes, 500);
    foreach ($chunks as $chunk) {
        $placeholders = implode(',', array_fill(0, count($chunk), '?'));
        $sql_preload = "SELECT SKU_CODE, ICCAT_CODE, DI_DATE, WL_CODE, WH_CODE, ICCAT_NAME, SKU_NAME, UTQ_QTY, QTY, WL_NAME, WH_NAME FROM ims_product_stock_balance WHERE SKU_CODE IN ($placeholders)";
        $stmt_preload = $conn->prepare($sql_preload);
        $stmt_preload->execute($chunk);
        while ($row = $stmt_preload->fetch(PDO::FETCH_ASSOC)) {
            $key = $row["SKU_CODE"] . "|" . $row["ICCAT_CODE"] . "|" . $row["DI_DATE"] . "|" . $row["WL_CODE"] . "|" . $row["WH_CODE"];
            $existing_records[$key] = [
                'ICCAT_NAME' => (string)$row["ICCAT_NAME"],
                'SKU_NAME'   => (string)$row["SKU_NAME"],
                'UTQ_QTY'    => (string)$row["UTQ_QTY"],
                'QTY'        => (string)$row["QTY"],
                'WL_NAME'    => (string)$row["WL_NAME"],
                'WH_NAME'    => (string)$row["WH_NAME"]
            ];
        }
    }

    $sql_update = " UPDATE ims_product_stock_balance SET ICCAT_NAME=:ICCAT_NAME,SKU_NAME=:SKU_NAME,UTQ_QTY=:UTQ_QTY,QTY=:QTY
        ,WL_NAME=:WL_NAME,WH_NAME=:WH_NAME,update_date=:update_date WHERE SKU_CODE = :SKU_CODE AND DI_DATE = :DI_DATE
         AND ICCAT_CODE=:ICCAT_CODE AND WL_CODE=:WL_CODE AND WH_CODE=:WH_CODE ";
    $stmt_update = $conn->prepare($sql_update);

    $sql_insert = "INSERT INTO ims_product_stock_balance(ICCAT_CODE,ICCAT_NAME,DI_DATE,SKU_CODE,SKU_NAME,WH_CODE,WL_CODE,UTQ_QTY,QTY,WH_NAME,WL_NAME,create_date) 
        VALUES (:ICCAT_CODE,:ICCAT_NAME,:DI_DATE,:SKU_CODE,:SKU_NAME,:WH_CODE,:WL_CODE,:UTQ_QTY,:QTY,:WH_NAME,:WL_NAME,:create_date)";
    $stmt_insert = $conn->prepare($sql_insert);

    // Process DB operations in small batched transactions to prevent long lock wait timeouts
    $batch_size = 1000;
    $result_batches = array_chunk($results, $batch_size);

    foreach ($result_batches as $batch) {
        try {
            $conn->beginTransaction();

            foreach ($batch as $result) {
                $key = $result["SKU_CODE"] . "|" . $result["ICCAT_CODE"] . "|" . $result["DI_DATE"] . "|" . $result["WL_CODE"] . "|" . $result["WH_CODE"];
                $exists = isset($existing_records[$key]);

                if ($exists) {
                    $cur = $existing_records[$key];

                    // Skip UPDATE query execution if values are unchanged
                    if ($cur['ICCAT_NAME'] === (string)$result["ICCAT_NAME"] &&
                        $cur['SKU_NAME'] === (string)$result["SKU_NAME"] &&
                        $cur['UTQ_QTY'] == $result["UTQ_QTY"] &&
                        $cur['QTY'] == $result["QTY"] &&
                        $cur['WL_NAME'] === (string)$result["WL_NAME"] &&
                        $cur['WH_NAME'] === (string)$result["WH_NAME"]) {
                        $updated++;
                        continue;
                    }

                    $stmt_update->execute([
                        ':ICCAT_NAME'  => $result["ICCAT_NAME"],
                        ':SKU_NAME'    => $result["SKU_NAME"],
                        ':UTQ_QTY'     => $result["UTQ_QTY"],
                        ':QTY'         => $result["QTY"],
                        ':WL_NAME'     => $result["WL_NAME"],
                        ':WH_NAME'     => $result["WH_NAME"],
                        ':update_date' => $update_date,
                        ':SKU_CODE'    => $result["SKU_CODE"],
                        ':DI_DATE'     => $result["DI_DATE"],
                        ':ICCAT_CODE'  => $result["ICCAT_CODE"],
                        ':WL_CODE'     => $result["WL_CODE"],
                        ':WH_CODE'     => $result["WH_CODE"]
                    ]);
                    $updated++;
                } else {
                    $stmt_insert->execute([
                        ':ICCAT_CODE'  => $result["ICCAT_CODE"],
                        ':ICCAT_NAME'  => $result["ICCAT_NAME"],
                        ':DI_DATE'     => $result["DI_DATE"],
                        ':SKU_CODE'    => $result["SKU_CODE"],
                        ':SKU_NAME'    => $result["SKU_NAME"],
                        ':WH_CODE'     => $result["WH_CODE"],
                        ':WL_CODE'     => $result["WL_CODE"],
                        ':UTQ_QTY'     => $result["UTQ_QTY"],
                        ':QTY'         => $result["QTY"],
                        ':WH_NAME'     => $result["WH_NAME"],
                        ':WL_NAME'     => $result["WL_NAME"],
                        ':create_date' => $create_date
                    ]);

                    $existing_records[$key] = [
                        'ICCAT_NAME' => (string)$result["ICCAT_NAME"],
                        'SKU_NAME'   => (string)$result["SKU_NAME"],
                        'UTQ_QTY'    => (string)$result["UTQ_QTY"],
                        'QTY'        => (string)$result["QTY"],
                        'WL_NAME'    => (string)$result["WL_NAME"],
                        'WH_NAME'    => (string)$result["WH_NAME"]
                    ];
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
    }
}

$conn_sqlsvr = null;

$execution_time = round(microtime(true) - $start_time, 4);
echo "Updated: " . $updated . " | Inserted: " . $inserted . " | Execution time: " . $execution_time . " seconds\n";
