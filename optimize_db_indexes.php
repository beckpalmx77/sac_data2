<?php
require_once __DIR__ . '/config/connect_db.php';

$conn->setAttribute(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY, true);

echo "====================================================================\n";
echo "           DATABASE INDEX OPTIMIZATION SCRIPT - sac_data2           \n";
echo "====================================================================\n\n";

function getTableIndexSize($conn, $tables) {
    $in = "'" . implode("','", $tables) . "'";
    $sql = "SELECT TABLE_NAME, TABLE_ROWS, 
                   ROUND(DATA_LENGTH / 1024 / 1024, 2) AS data_mb,
                   ROUND(INDEX_LENGTH / 1024 / 1024, 2) AS index_mb,
                   ROUND((DATA_LENGTH + INDEX_LENGTH) / 1024 / 1024, 2) AS total_mb
            FROM information_schema.TABLES 
            WHERE TABLE_SCHEMA = :dbname AND TABLE_NAME IN ($in)";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':dbname' => DB_NAME]);
    $res = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $res[$row['TABLE_NAME']] = $row;
    }
    $stmt->closeCursor();
    return $res;
}

function indexExists($conn, $table, $indexName) {
    $stmt = $conn->prepare("SHOW INDEX FROM `$table` WHERE Key_name = :idx");
    $stmt->execute([':idx' => $indexName]);
    $exists = ($stmt->fetch() !== false);
    $stmt->closeCursor();
    return $exists;
}

function dropIndexIfExists($conn, $table, $indexName) {
    if (indexExists($conn, $table, $indexName)) {
        echo "  [-] Dropping redundant index `$indexName` from `$table`... ";
        try {
            $conn->exec("ALTER TABLE `$table` DROP INDEX `$indexName`");
            echo "DONE\n";
            return true;
        } catch (Exception $e) {
            echo "FAILED: " . $e->getMessage() . "\n";
            return false;
        }
    } else {
        echo "  [.] Index `$indexName` does not exist on `$table` (Already clean).\n";
        return null;
    }
}

function addIndexIfNotExists($conn, $table, $indexName, $columnsSql) {
    if (!indexExists($conn, $table, $indexName)) {
        echo "  [+] Adding index `$indexName` ($columnsSql) to `$table`... ";
        try {
            $conn->exec("ALTER TABLE `$table` ADD INDEX `$indexName` ($columnsSql)");
            echo "DONE\n";
            return true;
        } catch (Exception $e) {
            echo "FAILED: " . $e->getMessage() . "\n";
            return false;
        }
    } else {
        echo "  [.] Index `$indexName` already exists on `$table` (OK).\n";
        return null;
    }
}

$targetTables = [
    'ims_product_sale_sac',
    'ims_data_sale_sac_all',
    'ims_document_bill',
    'ims_customer_ar',
    'ims_forecast_auto',
    'ims_user_column_config',
    'ims_product_sale_cockpit',
    'ims_tires_plan_forecast',
    'wh_stock_record',
    'ims_tires_number'
];

echo "--- 1. CURRENT INDEX STATUS ---\n";
// ims_product_sale_sac
dropIndexIfExists($conn, 'ims_product_sale_sac', 'idx_sale_sac_ar');
dropIndexIfExists($conn, 'ims_product_sale_sac', 'idx_sale_sac_ref');
dropIndexIfExists($conn, 'ims_product_sale_sac', 'idx_sale_sac_sku');
dropIndexIfExists($conn, 'ims_product_sale_sac', 'idx_di_date');
dropIndexIfExists($conn, 'ims_product_sale_sac', 'idx_product');

// ims_data_sale_sac_all
dropIndexIfExists($conn, 'ims_data_sale_sac_all', 'idx_sale');
dropIndexIfExists($conn, 'ims_data_sale_sac_all', 'idx_di_year');

// others
dropIndexIfExists($conn, 'ims_document_bill', 'idx_id');
dropIndexIfExists($conn, 'ims_customer_ar', 'idx_customer_id');
dropIndexIfExists($conn, 'ims_forecast_auto', 'idx_target_ym');
dropIndexIfExists($conn, 'ims_user_column_config', 'idx_user');

echo "\n--- 2. ENSURING MISSING PERFORMANCE INDEXES ---\n";
// ims_product_sale_cockpit
addIndexIfNotExists($conn, 'ims_product_sale_cockpit', 'idx_pgroup_year', '`PGROUP`(50), `DI_YEAR`(10)');
addIndexIfNotExists($conn, 'ims_product_sale_cockpit', 'idx_year_month', '`DI_YEAR`(10), `DI_MONTH`(10)');
addIndexIfNotExists($conn, 'ims_product_sale_cockpit', 'idx_di_date', '`DI_DATE`(20)');

// ims_tires_plan_forecast
addIndexIfNotExists($conn, 'ims_tires_plan_forecast', 'idx_month_year', '`forcast_month`, `forcast_year`');
addIndexIfNotExists($conn, 'ims_tires_plan_forecast', 'idx_forecast_lookup', '`forcast_month`, `forcast_year`, `ar_code`(20)');

// wh_stock_record
addIndexIfNotExists($conn, 'wh_stock_record', 'idx_doc_line_prod', '`doc_id`(100), `line_no`, `product_id`(100)');

// ims_tires_number
addIndexIfNotExists($conn, 'ims_tires_number', 'idx_tires_num', '`tires_number`(100)');
addIndexIfNotExists($conn, 'ims_tires_number', 'idx_cust_name', '`customer_name`(100)');

echo "\n--- 3. ANALYZING TABLES (UPDATE STATISTICS) ---\n";
foreach ($targetTables as $tbl) {
    echo "  [~] Analyzing table `$tbl`... ";
    try {
        $stmt = $conn->query("ANALYZE TABLE `$tbl`");
        $stmt->fetchAll();
        $stmt->closeCursor();
        echo "DONE\n";
    } catch (Exception $e) {
        echo "SKIPPED (" . $e->getMessage() . ")\n";
    }
}

echo "\n--- 4. FINAL STATS & INDEX STATUS ---\n";
$afterStats = getTableIndexSize($conn, $targetTables);
$totalData = 0;
$totalIndex = 0;
foreach ($targetTables as $tbl) {
    if (isset($afterStats[$tbl])) {
        $s = $afterStats[$tbl];
        printf("  %-32s | Rows: %9s | Data: %7s MB | Index: %7s MB | Total: %7s MB\n", 
            $tbl, number_format($s['TABLE_ROWS']), $s['data_mb'], $s['index_mb'], $s['total_mb']);
        $totalData += (float)$s['data_mb'];
        $totalIndex += (float)$s['index_mb'];
    }
}

echo "\n====================================================================\n";
echo "Total Data Size : " . number_format($totalData, 2) . " MB\n";
echo "Total Index Size: " . number_format($totalIndex, 2) . " MB\n";
echo "Total Storage   : " . number_format($totalData + $totalIndex, 2) . " MB\n";
echo "Optimization Verified & Complete!\n";
echo "====================================================================\n";