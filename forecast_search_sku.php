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
    // Fetch unique SKU and Brand from the forecast table (simplest to ensure consistency)
    $sql = "SELECT sku_code as id, MAX(brand_code) as brand_code
            FROM ims_tires_plan_forecast 
            WHERE sku_code LIKE :search
            GROUP BY sku_code 
            LIMIT 50";
            
    $stmt = $conn->prepare($sql);
    $stmt->execute([':search' => '%' . $search . '%']);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Format for Select2
    $data = [];
    foreach ($results as $row) {
        $data[] = [
            'id' => $row['id'],
            'text' => $row['id'] . ' (' . $row['brand_code'] . ')',
            'brand_code' => $row['brand_code']
        ];
    }

    echo json_encode(['results' => $data]);

} catch (Exception $e) {
    echo json_encode(['results' => [], 'error' => $e->getMessage()]);
}
