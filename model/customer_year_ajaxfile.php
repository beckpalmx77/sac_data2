<?php
session_start();
error_reporting(0);
include '../config/connect_db.php';

// Number of records fetch
$numberofrecords = 500000;

$manage_team_id = ($_SESSION['manage_team_id'] === '-') ? "'%'" : "'%" . $_SESSION['manage_team_id'] . "%'";

$year = $_POST["year"];
$customer_id = $_POST["AR_CODE"];

//$myfile = fopen("param_post_mysql.txt", "w") or die("Unable to open file!");
//fwrite($myfile, $year . $customer_id);
//fclose($myfile);

if(!isset($_POST['searchTerm'])){

	// Fetch records
	$stmt = $conn->prepare("SELECT DI_YEAR , AR_CODE FROM ims_product_sale_sac WHERE AR_CODE LIKE '". $customer_id . "' GROUP BY DI_YEAR , AR_CODE LIMIT :limit");
    //$stmt = $conn->prepare("SELECT * FROM v_customer_salename WHERE SLMN_SLT LIKE ". $team_id . " GROUP BY AR_CODE ");
	$stmt->bindValue(':limit', (int)$numberofrecords, PDO::PARAM_INT);
	$stmt->execute();
	$custsList = $stmt->fetchAll();

}else{

	$search = $_POST['searchTerm'];// Search text
	
	// Fetch records
    $stmt = $conn->prepare("SELECT DI_YEAR , AR_CODE FROM ims_product_sale_sac WHERE AR_CODE LIKE '". $customer_id . "' AND DI_YEAR like :DI_YEAR ORDER BY DI_YEAR , AR_CODE LIMIT :limit");
	//$stmt = $conn->prepare("SELECT * FROM v_customer_salename WHERE SLMN_SLT LIKE ". $team_id . " AND AR_NAME like :AR_NAME ORDER BY AR_CODE ");
	$stmt->bindValue(':DI_YEAR', '%'.$search.'%', PDO::PARAM_STR);
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
