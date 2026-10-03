<?php
session_start();
error_reporting(0);
include '../config/connect_db.php';

// Number of records fetch
$numberofrecords = 500000;

$search = $_POST['searchTerm'];// Search text

// Fetch records
$stmt = $conn->prepare("SELECT * FROM v_sac_customer_arname WHERE 1 AND AR_NAME like :AR_NAME ORDER BY AR_NAME LIMIT :limit");
$stmt->bindValue(':AR_NAME', '%' . $search . '%', PDO::PARAM_STR);
$stmt->bindValue(':limit', (int)$numberofrecords, PDO::PARAM_INT);
$stmt->execute();
$custsList = $stmt->fetchAll();

$response = array();

// Read Data
foreach ($custsList as $cust) {
    $response[] = array(
        "id" => $cust['AR_CODE'],
        "text" => $cust['AR_NAME'] . " [" . $cust['AR_CODE'] . "]"
    );
}

echo json_encode($response);
exit();
