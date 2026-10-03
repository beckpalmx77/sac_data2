<?php
session_start();
error_reporting(0);
include '../config/connect_db.php';

if ($_POST["action"] === 'GET_DATA') {

    $year = $_POST["year"];
    $customer_id = $_POST["AR_CODE"];


    // Fetch records
    $stmt = $conn->prepare("SELECT count(*) as rec_num FROM ims_product_sale_sac WHERE AR_CODE LIKE '" . $customer_id . "' AND DI_YEAR = " . $year);
    $stmt->execute();
    $RecList = $stmt->fetchAll();


    $response = array();

// Read Data
    foreach ($RecList as $Rec) {
        $response[] = array(
            "rec_num" => $Rec['rec_num']
        );
    }

    echo json_encode($response);
    exit();

}

