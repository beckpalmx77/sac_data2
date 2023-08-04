<?php

/*
$host = 'db4free.net:3306';
$dbuser = "dbadmin007";
$dbpassword = "@AdmSup2023";
$dbname = "sportmgr";

$conn = mysqli_connect($host,$dbuser,$dbpassword,$dbname);
if($conn){
    mysqli_query($conn,'SET NAMES uff8');
    echo "Connection - successful </br>";
}
else {
    echo "Connection - failed </br>" . mysqli_connect_error();
}
*/

$order_id  = '8531';
//$api_url = 'http://192.168.88.241:5000/order/' . $order_id ;
$api_url = 'http://192.168.88.241:5000/orders';

// Read JSON file
$json_data = file_get_contents($api_url);

// Decode JSON data into PHP array
$response_data = json_decode($json_data);

// All user data exists in 'data' object
$order_data = $response_data->data;

// Cut long data into small & select only first 10 records

//$order_data = array_slice($order_data, 0, 9);
$order_data = array_slice($order_data,0);

// Print data if need to debug
print_r($order_data);

// Traverse array and display user data
foreach ($order_data as $order) {
	echo "id : ".$order->code_id . " code: ".$order->customer_code . " name: ".$order->customer_name . "\n\r";

}

$get_data = callAPI('GET', 'http://192.168.88.241:5000/order/30', false);
$response = json_decode($get_data, true);
$errors = $response['response']['errors'];
$data = $response['response']['data'][0];

echo $data;

