<?php
date_default_timezone_set("Asia/Bangkok");
include('db_value_sac.inc');

try
{
    $conn_sac = new PDO("mysql:host=".DB_HOST.";dbname=".DB_NAME.";port=" .DB_PORT,DB_USER, DB_PASS
        ,array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
    $conn_sac->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch (PDOException $e)
{
    echo "Error: " . $e->getMessage();
    exit("Error: " . $e->getMessage());
}