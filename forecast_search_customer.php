<?php
session_start();
include('config/connect_db.php');

header('Content-Type: application/json');

if (!isset($_SESSION['alogin']) || $_SESSION['alogin'] == '') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$search = $_GET['q'] ?? '';

try {
    // We fetch from the forecast table itself to get related fields like sale_name, team, zone
    // Or we could fetch from ims_customer_ar, but the forecast table already has the mapped data for this system.
    // Fetching from v_customer_salename or similar would be better, but let's use what we know is available and linked to AR_CODE.
    // To ensure we get the most recent or consistent mapping, we'll grab from ims_tires_plan_forecast.
    
    $sql = "SELECT ar_code as id, MAX(ar_name) as text, MAX(sale_name) as sale_name, MAX(take_name) as take_name, 
            MAX(team_name) as team_name, MAX(amphur) as amphur, MAX(province) as province, MAX(zone) as zone
            FROM ims_tires_plan_forecast 
            WHERE ar_code LIKE :search OR ar_name LIKE :search
            GROUP BY ar_code 
            LIMIT 50";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute([':search' => '%' . $search . '%']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format for Select2
    $data = [];
    foreach ($results as $row) {
        $data[] = [
            'id' => $row['id'],
            'text' => $row['id'] . ' - ' . $row['text'],
            'ar_name' => $row['text'],
            'sale_name' => $row['sale_name'],
            'take_name' => $row['take_name'],
            'team_name' => $row['team_name'],
            'amphur' => $row['amphur'],
            'province' => $row['province'],
            'zone' => $row['zone']
        ];
    }

    echo json_encode(['results' => $data]);

} catch (Exception $e) {
    echo json_encode(['results' => [], 'error' => $e->getMessage()]);
}
