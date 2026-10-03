<?php
session_start();
include('config/connect_db.php');

header('Content-Type: application/json');

if (!isset($_SESSION['alogin']) || $_SESSION['alogin'] == '') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['updates'])) {
    $updates = json_decode($_POST['updates'], true);

    if (empty($updates)) {
        echo json_encode(['success' => false, 'message' => 'ไม่มีข้อมูลให้บันทึก']);
        exit;
    }

    try {
        $conn->beginTransaction();

        // 1. Prepare statements
        $sql_sku = "UPDATE ims_tires_plan_forecast SET 
                forecast_qty_month_1 = ?, 
                compare_forecast_month_1 = ?, 
                compare_sales_percent = ?, 
                remark = ?,
                forecast_qty_month_2 = ?,
                compare_forecast_month_2 = ?,
                compare_sales_percent_2 = ?,
                import_date = CURRENT_TIMESTAMP
                WHERE id = ?";
        $stmt_sku = $conn->prepare($sql_sku);

        // 2. SQL to update Shop-level Target for all SKUs of the same shop/month/year
        $sql_shop_target = "UPDATE ims_tires_plan_forecast t1
            JOIN ims_tires_plan_forecast t2 ON t1.ar_code = t2.ar_code 
                AND t1.forcast_month = t2.forcast_month 
                AND t1.forcast_year = t2.forcast_year
            SET t1.target_qty_month = ?, 
                t1.target_qty_month_2 = ?
            WHERE t2.id = ?";
        $stmt_shop = $conn->prepare($sql_shop_target);

        foreach ($updates as $item) {
            // Update SKU-specific data
            $stmt_sku->execute([
                $item['forecast_qty'],
                $item['compare_1'],
                $item['compare_pct'],
                $item['remark'],
                $item['forecast_qty_month_2'] ?? $item['forecast_qty_2'], // Support both old/new naming
                $item['compare_2'],
                $item['compare_pct_2'],
                $item['id']
            ]);

            // Update Shop-level target globally for this customer (only if target_qty is explicitly sent)
            if (isset($item['target_qty'])) {
                $stmt_shop->execute([
                    $item['target_qty'],
                    $item['target_qty_month_2'] ?? $item['target_qty_2'] ?? 0,
                    $item['id']
                ]);
            }
        }

        $conn->commit();
        echo json_encode(['success' => true]);

    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
