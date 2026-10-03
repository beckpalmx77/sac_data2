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

$month = $_POST['month'] ?? '';
$year = $_POST['year'] ?? '';
$ar_name = $_POST['ar_name'] ?? '';

if (empty($month) || empty($year) || empty($ar_name)) {
    echo json_encode(['success' => false, 'message' => 'Incomplete parameters']);
    exit;
}

try {
    $sql = "SELECT 
                MAX(p.ar_code) as ar_code,
                MAX(p.ar_name) as ar_name,
                MAX(p.sale_name) as sale_name,
                MAX(p.take_name) as take_name,
                MAX(p.team_name) as team_name,
                MAX(p.province) as province,
                MAX(p.amphur) as amphur,
                MAX(p.zone) as zone,
                SUM(p.forecast_qty_month_1) as total_forecast,
                SUM(p.forecast_qty_month_1 * p.volume) as total_volume,
                SUM(p.avg_3_month) as total_avg3m,
                IFNULL(act.actual_qty, 0) as total_actual
            FROM ims_tires_plan_forecast_volume_tires p
            LEFT JOIN (
                SELECT AR_CODE, SUM(CAST(TRD_QTY AS DECIMAL(10,2))) as actual_qty
                FROM ims_product_sale_sac
                WHERE DI_MONTH = :act_month AND DI_YEAR = :year
                GROUP BY AR_CODE
            ) act ON p.ar_code = act.AR_CODE
            WHERE (p.forcast_month = :forcast_month OR p.forcast_month = :forcast_month_padded)
            AND p.forcast_year = :year 
            AND p.ar_name = :ar_name
            GROUP BY p.ar_code";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':forcast_month' => strval(intval($month)),
        ':forcast_month_padded' => str_pad((string)$month, 2, '0', STR_PAD_LEFT),
        ':act_month' => strval(intval($month)),
        ':year' => $year,
        ':ar_name' => $ar_name
    ]);
    
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        $data['total_forecast'] = floatval($data['total_forecast']);
        $data['total_volume'] = floatval($data['total_volume']);
        $data['total_avg3m'] = floatval($data['total_avg3m']);
        $data['total_actual'] = floatval($data['total_actual']);
        echo json_encode(['success' => true, 'data' => $data]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No data found']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
