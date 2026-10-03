<?php
session_start();
error_reporting(0);
include '../config/connect_db.php';

// Number of records fetch
$numberofrecords = 500000;

$manage_team_id = ($_SESSION['manage_team_id'] === '-') ? "'%'" : "'%" . $_SESSION['manage_team_id'] . "%'";

if(!isset($_POST['searchTerm'])){

	// Fetch records
    $sql_sale = "SELECT * FROM v_customer_salename GROUP BY SLMN_NAME  ORDER BY SLMN_NAME LIMIT :limit";
    $stmt = $conn->prepare($sql_sale);
    //$stmt = $conn->prepare("SELECT * FROM v_customer_salename WHERE SLMN_SLT LIKE ". $team_id . " GROUP BY AR_CODE ");
	$stmt->bindValue(':limit', (int)$numberofrecords, PDO::PARAM_INT);
	$stmt->execute();
    $salesList = $stmt->fetchAll();

}else{

	$search = $_POST['searchTerm'];// Search text
	
	// Fetch records
    $sql_sale = "SELECT * FROM v_customer_salename WHERE SLMN_NAME like :SLMN_NAME GROUP BY SLMN_NAME ORDER BY SLMN_NAME LIMIT :limit";
    $stmt = $conn->prepare($sql_sale);
	//$stmt = $conn->prepare("SELECT * FROM v_customer_salename WHERE SLMN_SLT LIKE ". $team_id . " AND AR_NAME like :AR_NAME ORDER BY AR_CODE ");
	$stmt->bindValue(':SLMN_NAME', '%'.$search.'%', PDO::PARAM_STR);
	$stmt->bindValue(':limit', (int)$numberofrecords, PDO::PARAM_INT);
	$stmt->execute();
    $salesList = $stmt->fetchAll();

}

/*
$myfile = fopen("qry_file_mysql_server.txt", "w") or die("Unable to open file!");
fwrite($myfile, $sql_sale);
fclose($myfile);
*/
	
$response = array();

// Read Data
foreach($salesList as $sale){
	$response[] = array(
		"id" => $sale['SLMN_NAME'],
		"text" => $sale['SLMN_NAME']
    );
}

echo json_encode($response);
exit();
