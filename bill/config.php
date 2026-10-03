<?php

$host = "localhost"; /* Host name */
$port = "3307"; /* Host name */
$user = "myadmin"; /* User */
$password = "myadmin"; /* Password */
$dbname = "empdbs"; /* Database name */

$con = mysqli_connect($host, $user, $password,$dbname,$port);
// Check connection
if (!$con) {
 die("Connection failed: " . mysqli_connect_error());
}