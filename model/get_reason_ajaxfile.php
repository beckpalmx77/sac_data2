<?php
session_start();
error_reporting(0);
include '../config/connect_db.php';

// Number of records fetch
$numberofrecords = 500000;

$manage_team_id = ($_SESSION['manage_team_id'] === '-') ? "'%'" : "'%" . $_SESSION['manage_team_id'] . "%'";

if(!isset($_POST['searchTerm'])){

	// Fetch records
    $sql_reason = "SELECT * FROM ims_reason ORDER BY id  LIMIT :limit";
    $stmt = $conn->prepare($sql_reason);
	$stmt->bindValue(':limit', (int)$numberofrecords, PDO::PARAM_INT);
	$stmt->execute();
    $reasonList = $stmt->fetchAll();

}else{

	$search = $_POST['searchTerm'];// Search text
	
	// Fetch records
    $sql_reason = "SELECT * FROM ims_reason WHERE detail like :detail ORDER BY id LIMIT :limit";
    $stmt = $conn->prepare($sql_reason);
	$stmt->bindValue(':detail', '%'.$search.'%', PDO::PARAM_STR);
	$stmt->bindValue(':limit', (int)$numberofrecords, PDO::PARAM_INT);
	$stmt->execute();
    $reasonList = $stmt->fetchAll();

}

/*
$myfile = fopen("qry_file_mysql_server.txt", "w") or die("Unable to open file!");
fwrite($myfile, $sql_reason);
fclose($myfile);
*/

	
$response = array();

// Read Data
foreach($reasonList as $reason){
	$response[] = array(
		"id" => $reason['detail'],
		"text" => $reason['detail']
    );
}

echo json_encode($response);
exit();
