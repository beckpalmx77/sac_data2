<?php
session_start();
include('config/connect_db.php');

header('Content-Type: application/json; charset=utf-8');

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

    $account_type = $_SESSION['account_type'] ?? '';
    $user_login = $_SESSION['alogin'] ?? '';
    $is_admin = in_array($account_type, ['admin', 'sale_sup']);

    // Guard: If not admin/sale_sup, check if any target row is already locked
    if (!$is_admin) {
        $ids = array_filter(array_column($updates, 'id'));
        if (!empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $check_stmt = $conn->prepare("SELECT COUNT(*) FROM ims_tires_plan_forecast_realtime WHERE id IN ($placeholders) AND is_locked = 1");
            $check_stmt->execute(array_values($ids));
            if ($check_stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'ไม่สามารถบันทึกได้ เนื่องจากข้อมูลประจำเดือนนี้ถูกบันทึกและล็อคแล้ว (กรุณาติดต่อ Admin เพื่อปลดล็อค)']);
                exit;
            }
        }
    }

    try {
        $conn->beginTransaction();

        // 1. Prepare statement for SKU-level data (updates data without locking)
        $sql_sku = "UPDATE ims_tires_plan_forecast_realtime SET 
                target_qty_month = COALESCE(?, target_qty_month),
                forecast_qty_month_1 = COALESCE(?, forecast_qty_month_1), 
                compare_forecast_month_1 = COALESCE(?, compare_forecast_month_1), 
                compare_sales_percent = COALESCE(?, compare_sales_percent), 
                remark = COALESCE(?, remark),
                responsible_person = COALESCE(?, responsible_person),
                target_qty_month_2 = COALESCE(?, target_qty_month_2),
                forecast_qty_month_2 = COALESCE(?, forecast_qty_month_2),
                compare_forecast_month_2 = COALESCE(?, compare_forecast_month_2),
                compare_sales_percent_2 = COALESCE(?, compare_sales_percent_2),
                import_date = CURRENT_TIMESTAMP
                WHERE id = ?";
        $stmt_sku = $conn->prepare($sql_sku);

        // 2. Prepare statement to update Shop-level Target for all SKUs of the same shop/month/year
        $sql_shop_target = "UPDATE ims_tires_plan_forecast_realtime t1
            JOIN ims_tires_plan_forecast_realtime t2 ON t1.ar_code = t2.ar_code 
                AND t1.forcast_month = t2.forcast_month 
                AND t1.forcast_year = t2.forcast_year
            SET t1.target_qty_month = COALESCE(?, t1.target_qty_month), 
                t1.target_qty_month_2 = COALESCE(?, t1.target_qty_month_2)
            WHERE t2.id = ?";
        $stmt_shop = $conn->prepare($sql_shop_target);

        foreach ($updates as $item) {
            $id = $item['id'] ?? null;
            if (!$id) continue;

            $target_qty = isset($item['target_qty']) ? $item['target_qty'] : null;
            $forecast_qty = isset($item['forecast_qty']) ? $item['forecast_qty'] : ($item['forecast_qty_month_1'] ?? null);
            $compare_1 = isset($item['compare_1']) ? $item['compare_1'] : ($item['compare_forecast_month_1'] ?? null);
            $compare_pct = isset($item['compare_pct']) ? $item['compare_pct'] : ($item['compare_sales_percent'] ?? null);
            $remark = isset($item['remark']) ? $item['remark'] : null;
            $responsible = isset($item['responsible_person']) ? $item['responsible_person'] : null;
            $target_qty_2 = isset($item['target_qty_2']) ? $item['target_qty_2'] : ($item['target_qty_month_2'] ?? null);
            $forecast_qty_2 = isset($item['forecast_qty_2']) ? $item['forecast_qty_2'] : ($item['forecast_qty_month_2'] ?? null);
            $compare_2 = isset($item['compare_2']) ? $item['compare_2'] : ($item['compare_forecast_month_2'] ?? null);
            $compare_pct_2 = isset($item['compare_pct_2']) ? $item['compare_pct_2'] : ($item['compare_sales_percent_2'] ?? null);

            // Update SKU-specific data
            $stmt_sku->execute([
                $target_qty,
                $forecast_qty,
                $compare_1,
                $compare_pct,
                $remark,
                $responsible,
                $target_qty_2,
                $forecast_qty_2,
                $compare_2,
                $compare_pct_2,
                $id
            ]);

            // Update Shop-level target globally for this customer (if target_qty is explicitly sent)
            if ($target_qty !== null || $target_qty_2 !== null) {
                $stmt_shop->execute([
                    $target_qty,
                    $target_qty_2,
                    $id
                ]);
            }
        }

        $conn->commit();
        echo json_encode(['success' => true, 'message' => 'บันทึกข้อมูลเรียบร้อยแล้ว']);

    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
}
