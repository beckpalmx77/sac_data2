<?php
session_start();
error_reporting(0);
include '../config/connect_db.php';

// Number of records fetch
$numberofrecords = 500000;

$manage_team_id = ($_SESSION['manage_team_id'] === '-') ? "'%'" : "'%" . $_SESSION['manage_team_id'] . "%'";

if(!isset($_POST['searchTerm'])){

	// Fetch records
	$stmt = $conn->prepare("SELECT * FROM v_customer_salename WHERE SLMN_SLT LIKE ". $manage_team_id . " GROUP BY AR_CODE LIMIT :limit");
    //$stmt = $conn->prepare("SELECT * FROM v_customer_salename WHERE SLMN_SLT LIKE ". $team_id . " GROUP BY AR_CODE ");
	$stmt->bindValue(':limit', (int)$numberofrecords, PDO::PARAM_INT);
	$stmt->execute();
	$custsList = $stmt->fetchAll();

}else{

	$search = $_POST['searchTerm'];// Search text
	
	// Fetch records
    $stmt = $conn->prepare("SELECT * FROM v_customer_salename WHERE SLMN_SLT LIKE ". $manage_team_id . " AND AR_NAME like :AR_NAME ORDER BY AR_CODE LIMIT :limit");
	//$stmt = $conn->prepare("SELECT * FROM v_customer_salename WHERE SLMN_SLT LIKE ". $team_id . " AND AR_NAME like :AR_NAME ORDER BY AR_CODE ");
	$stmt->bindValue(':AR_NAME', '%'.$search.'%', PDO::PARAM_STR);
	$stmt->bindValue(':limit', (int)$numberofrecords, PDO::PARAM_INT);
	$stmt->execute();
	$custsList = $stmt->fetchAll();

}
	
$response = array();

// Read Data
foreach($custsList as $cust){
	$response[] = array(
		"id" => $cust['AR_CODE'],
		"text" => $cust['AR_NAME'] . " [" . $cust['AR_CODE'] . "]"
    );
}

echo json_encode($response);
exit();
