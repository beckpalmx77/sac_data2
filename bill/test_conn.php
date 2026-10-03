<?php
$con=mysqli_connect("localhost:3307","myadmin","myadmin","sac_data2");
// Check connection
if (mysqli_connect_errno())
{
    echo "ไม่สามารถเชื่อมต่อกับฐานข้อมูล MySQL ได้:   " . mysqli_connect_error();
}

$port = $_SERVER['SERVER_PORT'];

echo $port;

// Perform queries
mysqli_query($con,"SELECT * FROM ims_product");

mysqli_close($con);
?>
