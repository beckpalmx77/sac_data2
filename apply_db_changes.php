<?php
include('config/connect_db.php');

try {
    // 1. Check if load_time column exists
    $stmt = $conn->query("SHOW COLUMNS FROM ims_tires_plan_forecast LIKE 'load_time'");
    $exists = $stmt->fetch();

    if (!$exists) {
        $conn->exec("ALTER TABLE ims_tires_plan_forecast ADD COLUMN load_time INT DEFAULT 0 AFTER import_date");
        echo "Successfully added 'load_time' column.\n";
    } else {
        echo "'load_time' column already exists.\n";
    }

    // 2. Ensure all sales columns are DECIMAL for accuracy
    $columns = ['sales_month_1', 'sales_month_2', 'sales_month_3', 'avg_3_month', 'avg_per_month_year', 'target_qty_month', 'forecast_qty_month_1', 'compare_forecast_month_1', 'compare_sales_percent'];
    foreach ($columns as $col) {
        $conn->exec("ALTER TABLE ims_tires_plan_forecast MODIFY COLUMN $col DECIMAL(10,2) DEFAULT 0");
    }
    echo "Successfully updated column types to DECIMAL.\n";

    // 3. Check and add idx_forecast_lookup index
    $stmt_idx1 = $conn->query("SHOW INDEX FROM ims_tires_plan_forecast WHERE Key_name = 'idx_forecast_lookup'");
    if (!$stmt_idx1->fetch()) {
        $conn->exec("ALTER TABLE ims_tires_plan_forecast ADD INDEX idx_forecast_lookup (forcast_month, forcast_year, ar_code(20), sku_code(30), sale_name(50))");
        echo "Successfully created index 'idx_forecast_lookup'.\n";
    } else {
        echo "Index 'idx_forecast_lookup' already exists.\n";
    }

    // 4. Check and add idx_month_year index
    $stmt_idx2 = $conn->query("SHOW INDEX FROM ims_tires_plan_forecast WHERE Key_name = 'idx_month_year'");
    if (!$stmt_idx2->fetch()) {
        $conn->exec("ALTER TABLE ims_tires_plan_forecast ADD INDEX idx_month_year (forcast_month, forcast_year)");
        echo "Successfully created index 'idx_month_year'.\n";
    } else {
        echo "Index 'idx_month_year' already exists.\n";
    }

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

