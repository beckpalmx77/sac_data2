<?php
session_start();
error_reporting(0);
date_default_timezone_set("Asia/Bangkok");
include('../config/connect_db.php');
include('../config/lang.php');
include('../util/record_util.php');
include('../util/getdata_field.php');

if (isset($_POST["action"]) && $_POST["action"] === 'GET_DATA') {
    $customer_id = $_POST["customer_id"];
    // ตรวจสอบค่า customer_id
    if (!empty($customer_id)) {
        $return_arr = array();
        $sql_get = "SELECT * FROM ims_customer_ar WHERE customer_id = '" . $customer_id . "'";
        $statement = $conn->prepare($sql_get);
        $statement->execute();
        $results = $statement->fetchAll(PDO::FETCH_ASSOC);

        foreach ($results as $result) {
            $return_arr[] = array(
                "id" => $result['id'],
                "customer_id" => $result['customer_id'],
                "province" => $result['province'],
                "phone" => $result['phone'],
                "contact_name" => $result['contact_name']
            );
        }
        echo json_encode($return_arr);
    } else {
        echo json_encode([]);
    }
}
?>
