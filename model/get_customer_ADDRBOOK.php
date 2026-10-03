<?php
include('../config/connect_sqlserver.php');

header('Content-Type: application/json');

if (!isset($_GET['term']) || empty($_GET['term'])) {
    echo json_encode([]);
    exit();
}

$searchTerm = trim($_GET['term']);
$searchTerm = preg_replace('/[%_]/', '\\\$0', $searchTerm);
$page = isset($_GET['page']) ? intval($_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

try {
    $conn_sqlsvr->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = "SELECT DISTINCT TOP " . ($limit + 1) . " ADDB_COMPANY FROM ADDRBOOK WHERE ADDB_COMPANY LIKE '%" . $searchTerm . "%' ORDER BY ADDB_COMPANY";
    $query = $conn_sqlsvr->query($sql);

    $result = [];
    $count = 0;
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        if ($count >= $offset && $count < $offset + $limit) {
            $result[] = $row['ADDB_COMPANY'];
        }
        $count++;
    }

    $hasMore = $count > $offset + $limit;
    
    echo json_encode([
        'results' => $result,
        'pagination' => [
            'more' => $hasMore
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}