<?php
include('../config/connect_db.php');

header('Content-Type: application/json');

if (!isset($_GET['term']) || empty($_GET['term'])) {
    echo json_encode([]);
    exit();
}

$searchTerm = "%" . $_GET['term'] . "%";

try {
    $sql = "SELECT customer_id, f_name FROM ims_customer_ar 
            WHERE customer_id LIKE :searchTerm OR f_name LIKE :searchTerm 
            ORDER BY f_name ASC LIMIT 20";
    $query = $conn->prepare($sql);
    $query->execute(['searchTerm' => $searchTerm]);

    $results = [];
    while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $results[] = [
            'id' => $row['customer_id'],
            'value' => $row['f_name'],
            'label' => $row['customer_id'] . " - " . $row['f_name']
        ];
    }

    echo json_encode($results);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
