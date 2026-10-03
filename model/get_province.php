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
        $sql = "SELECT DISTINCT TOP 20 ADDB_PROVINCE FROM ADDRBOOK WHERE ADDB_PROVINCE IS NOT NULL AND ADDB_PROVINCE <> '' AND ADDB_PROVINCE LIKE '%" . $searchTerm . "%' ORDER BY ADDB_PROVINCE";
    } else {
        $sql = "SELECT DISTINCT TOP 20 ADDB_PROVINCE FROM ADDRBOOK WHERE ADDB_PROVINCE IS NOT NULL AND ADDB_PROVINCE <> '' ORDER BY ADDB_PROVINCE";
    }
    
    $query = $conn_sqlsvr->query($sql);

    $result = [];
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $result[] = $row['ADDB_PROVINCE'];
    }
    
    echo json_encode([
        'results' => $result,
        'pagination' => ['more' => false]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
