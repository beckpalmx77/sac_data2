<?php
header('Content-Type: application/json; charset=utf-8');
include('../config/connect_db.php');

$filter_type = $_POST['filter_type'] ?? $_GET['filter_type'] ?? 'all';

try {
    if ($filter_type === 'sale') {
        $sql = "SELECT DISTINCT SALE_NAME AS name 
                FROM ims_data_sale_sac_all 
                WHERE SALE_NAME IS NOT NULL AND SALE_NAME != '' AND SALE_NAME != '-'
                ORDER BY SALE_NAME ASC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
    } elseif ($filter_type === 'take') {
        $sql = "SELECT DISTINCT TAKE_NAME AS name 
                FROM ims_data_sale_sac_all 
                WHERE TAKE_NAME IS NOT NULL AND TAKE_NAME != '' AND TAKE_NAME != '-'
                ORDER BY TAKE_NAME ASC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
    } else {
        $sql = "SELECT DISTINCT name FROM (
                    SELECT SALE_NAME AS name FROM ims_data_sale_sac_all WHERE SALE_NAME IS NOT NULL AND SALE_NAME != '' AND SALE_NAME != '-'
                    UNION
                    SELECT TAKE_NAME AS name FROM ims_data_sale_sac_all WHERE TAKE_NAME IS NOT NULL AND TAKE_NAME != '' AND TAKE_NAME != '-'
                ) t ORDER BY name ASC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
    }

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $data = [];
    foreach ($rows as $r) {
        if (!empty(trim($r['name']))) {
            $data[] = [
                'id' => $r['name'],
                'text' => $r['name']
            ];
        }
    }

    echo json_encode(['status' => 'success', 'data' => $data], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()], JSON_UNESCAPED_UNICODE);
}
