<?php
require_once __DIR__ . '/../config/connect_db.php';

set_time_limit(600);
ini_set('memory_limit', '1024M');

$tables = [
    [
        'table' => 'ims_reserve_product_sac',
        'keys' => ['DI_KEY', 'TRD_SEQ']
    ],
    [
        'table' => 'ims_product_sale_sac',
        'keys' => ['DI_KEY', 'DI_REF', 'DT_DOCCODE', 'TRD_SEQ']
    ],
    [
        'table' => 'ims_product_stock_balance',
        'keys' => ['SKU_CODE', 'WH_CODE', 'WL_CODE', 'DI_DATE']
    ],
    [
        'table' => 'ims_product_receive_sac',
        'keys' => ['DI_REF', 'TRD_SEQ']
    ],
    [
        'table' => 'ims_data_sale_sac_all',
        'keys' => ['DI_REF', 'TRD_SEQ']
    ],
    [
        'table' => 'ims_tires_master',
        'keys' => ['tires_code']
    ],
    [
        'table' => 'ims_product_sale_cockpit',
        'keys' => ['DI_KEY', 'DI_REF', 'DT_DOCCODE', 'TRD_SEQ']
    ],
    [
        'table' => 'wh_stock_movement_out',
        'keys' => ['doc_id', 'line_no', 'seq_record']
    ]
];

echo "========================================================\n";
echo "    INDEX-ACCELERATED DUPLICATE DELETION (KEEP MAX ID)  \n";
echo "========================================================\n\n";

$total_deleted_overall = 0;

foreach ($tables as $t_info) {
    $table = $t_info['table'];
    $keys = $t_info['keys'];

    // Check if table exists
    $check = $conn->query("SHOW TABLES LIKE '$table'")->fetch();
    if (!$check) {
        echo "Table [$table] does not exist. Skipping.\n";
        continue;
    }

    $count_before = (int)$conn->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
    $cols_sql = "`" . implode("`, `", $keys) . "`";

    echo "Processing [$table] (Current rows: $count_before)...\n";

    try {
        // Step 1: Add temporary index for ultra-fast JOIN
        try {
            $conn->exec("ALTER TABLE `$table` ADD INDEX `idx_dup_clean` ($cols_sql)");
            echo "  - Temporary index created.\n";
        } catch (Exception $idx_err) {
            // Index might already exist
        }

        // Step 2: Construct ON join condition
        $on_parts = [];
        foreach ($keys as $k) {
            $on_parts[] = "t1.`$k` = t2.`$k`";
        }
        $on_sql = implode(" AND ", $on_parts);

        $delete_sql = "DELETE t1 FROM `$table` t1 
                       JOIN `$table` t2 ON $on_sql 
                       WHERE t1.`id` < t2.`id`";

        $affected = $conn->exec($delete_sql);

        // Step 3: Drop temporary index
        try {
            $conn->exec("ALTER TABLE `$table` DROP INDEX `idx_dup_clean`");
            echo "  - Temporary index removed.\n";
        } catch (Exception $idx_err) {
            // Ignore
        }

        $count_after = (int)$conn->query("SELECT COUNT(*) FROM `$table`")->fetchColumn();
        $total_deleted_overall += $affected;

        echo "  ✅ Deleted: $affected rows | Remaining: $count_after rows\n";
    } catch (Exception $e) {
        echo "  ❌ Error in [$table]: " . $e->getMessage() . "\n";
    }

    echo "--------------------------------------------------------\n";
}

echo "\n========================================================\n";
echo "SUCCESS: Deleted total $total_deleted_overall duplicate rows across all tables.\n";
echo "========================================================\n";
