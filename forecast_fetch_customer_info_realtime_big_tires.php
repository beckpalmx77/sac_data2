<?php
session_start();
include('config/connect_db.php');

header('Content-Type: application/json');

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
                MAX(p.target_qty_month) as total_target,
                IFNULL(act.actual_qty, 0) as total_target2,
                SUM(p.forecast_qty_month_1) as total_forecast,
                SUM(p.avg_3_month) as total_avg3m,
                SUM(p.avg_per_month_year) as total_avg12m
            FROM ims_tires_plan_forecast_realtime_big_tirs p
            LEFT JOIN (
                SELECT AR_CODE, SUM(CAST(TRD_QTY AS DECIMAL(10,2))) as actual_qty
                FROM ims_product_sale_sac
                WHERE DI_MONTH = :act_month AND DI_YEAR = :year
                GROUP BY AR_CODE
            ) act ON p.ar_code = act.AR_CODE
            WHERE p.forcast_month = :forcast_month 
            AND p.forcast_year = :year 
            AND p.ar_name = :ar_name
            GROUP BY p.ar_code";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':forcast_month' => $month,
        ':act_month' => strval(intval($month)),
        ':year' => $year,
        ':ar_name' => $ar_name
    ]);
    
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        $data['total_target'] = floatval($data['total_target']);
        $data['total_target2'] = floatval($data['total_target2']);
        $data['total_forecast'] = floatval($data['total_forecast']);
        $data['total_avg3m'] = floatval($data['total_avg3m']);
        $data['total_avg12m'] = floatval($data['total_avg12m']);
        
        echo json_encode(['success' => true, 'data' => $data]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Customer not found']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
