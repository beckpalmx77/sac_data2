<?php
session_start();
include('config/connect_db.php');

header('Content-Type: application/json');

if (!isset($_SESSION['alogin']) || $_SESSION['alogin'] == '') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

$month = $_POST['month'] ?? '';
$year = $_POST['year'] ?? '';
$ar_code = $_POST['ar_code'] ?? '';
$ar_name = $_POST['ar_name'] ?? '';
$sale_name = $_POST['sale_name'] ?? '';
$take_name = $_POST['take_name'] ?? '';
$team_name = $_POST['team_name'] ?? '';
$brand_code = $_POST['brand_code'] ?? '';
$product_type = $_POST['product_type'] ?? '';
$sku_code = $_POST['sku_code'] ?? '';
$sku_name = $_POST['sku_name'] ?? '';
$province = $_POST['province'] ?? '';
$amphur = $_POST['amphur'] ?? '';
$zone = $_POST['zone'] ?? '';

$target_qty = $_POST['target_qty'] ?? 0;
$forecast_qty = $_POST['forecast_qty'] ?? 0;
$target_qty_2 = $_POST['target_qty_2'] ?? 0;
$forecast_qty_2 = floatval($_POST['forecast_qty_2'] ?? 0);
$responsible_person = trim($_POST['responsible_person'] ?? '');

if (empty($month) || empty($year) || empty($ar_code) || empty($ar_name) || empty($sku_code) || empty($sale_name)) {
    echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน']);
    exit;
}

try {
    $conn->beginTransaction();

    // 1. Check for duplicates in ims_tires_plan_forecast_realtime_big_tirs
    $sql_check = "SELECT id FROM ims_tires_plan_forecast_realtime_big_tirs 
                  WHERE forcast_month = ? AND forcast_year = ? AND ar_code = ? AND sku_code = ? AND sale_name = ?";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->execute([$month, $year, $ar_code, $sku_code, $sale_name]);
    
    if ($stmt_check->fetch()) {
        $conn->rollBack();
        echo json_encode(['success' => false, 'message' => 'ข้อมูลลูกค้ารายนี้และรหัสสินค้านี้ มีอยู่แล้วในระบบประจำเดือนที่ระบุ']);
        exit;
    }

    // 2. Determine target for the whole shop if other SKUs exist for this shop/month/year
    $sql_shop_target = "SELECT target_qty_month, target_qty_month_2 FROM ims_tires_plan_forecast_realtime_big_tirs 
                        WHERE forcast_month = ? AND forcast_year = ? AND ar_code = ? LIMIT 1";
    $stmt_shop_target = $conn->prepare($sql_shop_target);
    $stmt_shop_target->execute([$month, $year, $ar_code]);
    $existing_shop = $stmt_shop_target->fetch(PDO::FETCH_ASSOC);

    if ($existing_shop) {
        $target_qty = $existing_shop['target_qty_month'];
        $target_qty_2 = $existing_shop['target_qty_month_2'];
    }

    // 3. Insert new record
    $sql_ins = "INSERT INTO ims_tires_plan_forecast_realtime_big_tirs (
        forcast_month, forcast_year, ar_code, ar_name, sale_name, take_name, team_name, brand_code, product_type, sku_code, sku_name, 
        amphur, province, zone, target_qty_month, forecast_qty_month_1, target_qty_month_2, forecast_qty_month_2, responsible_person,
        load_time, avg_per_month_year, max_qty_year, min_qty_year, sales_month_1, sales_month_2, sales_month_3, avg_3_month
    ) VALUES (
        ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 
        ?, ?, ?, ?, ?, ?, ?, ?,
        0, 0, 0, 0, 0, 0, 0, 0
    )";
    $stmt_ins = $conn->prepare($sql_ins);
    $stmt_ins->execute([
        $month, $year, $ar_code, $ar_name, $sale_name, $take_name, $team_name, $brand_code, $product_type, $sku_code, $sku_name,
        $amphur, $province, $zone, $target_qty, $forecast_qty, $target_qty_2, $forecast_qty_2, $responsible_person
    ]);

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'เพิ่มข้อมูลรายการใหม่เรียบร้อยแล้ว']);

} catch (Exception $e) {
    if ($conn->inTransaction()) $conn->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
