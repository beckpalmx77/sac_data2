<?php
include('../config/connect_sqlserver.php');

header('Content-Type: application/json');

if (!isset($_GET['term']) && !isset($_GET['all'])) {
    echo json_encode([]);
    exit();
}

$searchTerm = isset($_GET['term']) ? trim($_GET['term']) : '';

try {
    $conn_sqlsvr->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    if (!empty($searchTerm)) {
        $sql = "SELECT DISTINCT TOP 20 SLMN_CODE, SLMN_NAME FROM SALESMAN WHERE (SLMN_CODE LIKE '%" . $searchTerm . "%' OR SLMN_NAME LIKE '%" . $searchTerm . "%') ORDER BY SLMN_NAME";
    } else {
        $sql = "SELECT DISTINCT TOP 20 SLMN_CODE, SLMN_NAME FROM SALESMAN ORDER BY SLMN_NAME";
    }
    
    $query = $conn_sqlsvr->query($sql);

    $result = [];
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $result[] = $row['SLMN_CODE'] . ' - ' . $row['SLMN_NAME'];
    }
    
    echo json_encode([
        'results' => $result,
        'pagination' => ['more' => false]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
