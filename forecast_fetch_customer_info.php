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
                MAX(ar_code) as ar_code,
                MAX(ar_name) as ar_name,
                MAX(sale_name) as sale_name,
                MAX(take_name) as take_name,
                MAX(team_name) as team_name,
                MAX(province) as province,
                MAX(amphur) as amphur,
                MAX(zone) as zone,
                MAX(target_qty_month) as total_target,
                MAX(target_qty_month_2) as total_target2,
                SUM(forecast_qty_month_1) as total_forecast,
                SUM(forecast_qty_month_2) as total_forecast2,
                SUM(avg_3_month) as total_avg3m,
                SUM(avg_per_month_year) as total_avg12m
            FROM ims_tires_plan_forecast
            WHERE forcast_month = :month 
            AND forcast_year = :year 
            AND ar_name = :ar_name
            GROUP BY ar_code";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':month' => $month,
        ':year' => $year,
        ':ar_name' => $ar_name
    ]);
    
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        echo json_encode(['success' => true, 'data' => $data]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Customer not found']);
    }

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
