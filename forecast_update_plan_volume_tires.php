<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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
    $is_admin = in_array($account_type, ['admin', 'sale_sup']);

    // Guard: If not admin/sale_sup, check if any target row is already locked
    if (!$is_admin) {
        $ids = array_filter(array_column($updates, 'id'));
        if (!empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $check_stmt = $conn->prepare("SELECT COUNT(*) FROM ims_tires_plan_forecast_volume_tires WHERE id IN ($placeholders) AND is_locked = 1");
            $check_stmt->execute(array_values($ids));
            if ($check_stmt->fetchColumn() > 0) {
                echo json_encode(['success' => false, 'message' => 'ไม่สามารถบันทึกได้ เนื่องจากข้อมูลประจำเดือนนี้ถูกบันทึกและล็อคแล้ว (กรุณาติดต่อ Admin หรือหัวหน้างานเพื่อปลดล็อค)']);
                exit;
            }
        }
    }

    try {
        $conn->beginTransaction();

        $sql_sku = "UPDATE ims_tires_plan_forecast_volume_tires SET 
                forecast_qty_month_1 = COALESCE(?, forecast_qty_month_1), 
                compare_forecast_month_1 = COALESCE(?, compare_forecast_month_1), 
                compare_sales_percent = COALESCE(?, compare_sales_percent), 
                remark = COALESCE(?, remark),
                responsible_person = COALESCE(?, responsible_person),
                import_date = CURRENT_TIMESTAMP
                WHERE id = ?";
        $stmt_sku = $conn->prepare($sql_sku);

        foreach ($updates as $item) {
            $id = $item['id'] ?? null;
            if (!$id) continue;

            $forecast_qty = isset($item['forecast_qty']) ? $item['forecast_qty'] : ($item['forecast_qty_month_1'] ?? null);
            $compare_1 = isset($item['compare_1']) ? $item['compare_1'] : ($item['compare_forecast_month_1'] ?? null);
            $compare_pct = isset($item['compare_pct']) ? $item['compare_pct'] : ($item['compare_sales_percent'] ?? null);
            $remark = isset($item['remark']) ? $item['remark'] : null;
            $responsible = isset($item['responsible_person']) ? $item['responsible_person'] : null;

            $stmt_sku->execute([
                $forecast_qty,
                $compare_1,
                $compare_pct,
                $remark,
                $responsible,
                $id
            ]);
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
