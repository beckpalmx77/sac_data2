<?php
session_start();
error_reporting(0);
include '../config/connect_db.php';

// Number of records fetch
$numberofrecords = 500000;


$status_id = "complete_flag";

if(!isset($_POST['searchTerm'])){

	// Fetch records
	$stmt = $conn->prepare("SELECT * FROM ims_status WHERE status_id LIKE '". $status_id . "' ORDER BY id LIMIT :limit");
	$stmt->bindValue(':limit', (int)$numberofrecords, PDO::PARAM_INT);
	$stmt->execute();
	$custsList = $stmt->fetchAll();

}else{

	$search = $_POST['searchTerm'];// Search text
	
	// Fetch records
    $stmt = $conn->prepare("SELECT * FROM ims_status WHERE status_id LIKE '". $status_id . "' AND status_detail like :status_detail ORDER BY id LIMIT :limit");
	$stmt->bindValue(':status_detail', '%'.$search.'%', PDO::PARAM_STR);
	$stmt->bindValue(':limit', (int)$numberofrecords, PDO::PARAM_INT);
	$stmt->execute();
	$custsList = $stmt->fetchAll();

}
	
$response = array();

// Read Data
foreach($custsList as $cust){
	$response[] = array(
		"id" => $cust['status'],
		"text" => $cust['status_detail'] . " [" . $cust['status'] . "]"
    );
}

echo json_encode($response);
exit();
