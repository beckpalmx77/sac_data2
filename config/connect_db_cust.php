<?php
date_default_timezone_set("Asia/Bangkok");
include('db_value_cust.inc');

try
{
    $conn_cust = new PDO("mysql:host=".DB_HOST_CUST.";dbname=".DB_NAME_CUST.";port=" .DB_PORT_CUST,DB_USER_CUST, DB_PASS_CUST
        ,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    $conn_cust->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e)
{
    echo "Error: " . $e->getMessage();
    exit("Error: " . $e->getMessage());
}