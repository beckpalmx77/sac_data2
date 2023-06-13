<?php


$host = 'sac.cckwqocv7kfy.ap-southeast-1.rds.amazonaws.com';
$db = 'MADB_PRODUCT_2564';
$username = 'sac';
$password = "l;ovvF9h8kiN";

$dsn = "pgsql:host=$host;port=5432;dbname=$db;user=$username;password=$password";

try {
    // create a PostgreSQL database connection
    $conn = new PDO($dsn);

    // display a message if connected to the PostgreSQL successfully
    if ($conn) {
        echo "Connected to the <strong>$db . ' ON HOST ' . $host  </strong> database successfully!";
    }
} catch (PDOException $e) {
    // report error message
    echo $e->getMessage();
}