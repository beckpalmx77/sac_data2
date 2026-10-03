<?php
//Connect MSSQL
$serverName = '192.168.88.40';
$userName = 'SYY';
$userPassword = '39122222';
$dbName = 'SAC';

try{
    $conn = new PDO("sqlsrv:server=$serverName ; Database = $dbName", $userName, $userPassword);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}
catch(Exception $e){
    die(print_r($e->getMessage()));
}

//การ query และแสดงข้อมูล จัดเรียงตามฟิวด์ field1 แบบมากไปน้อย เริ่มที่เรคคอร์ดที่ 0-100
$query = " SELECT * FROM  DOCTYPE
ORDER BY DT_DOCCODE DESC ";
$getRes = $conn->prepare($query);
$getRes->execute();

while($row = $getRes->fetch( PDO::FETCH_ASSOC ))
{
    echo $row['DT_DOCCODE']. " - " ;
    echo $row['DT_THAIDESC']."\n\r";
}
