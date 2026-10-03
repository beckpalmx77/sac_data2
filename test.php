<? phpinfo(); ?>

<!--?php

$serverName = "192.168.88.13";
$userName = "SYY";
$userPassword = "39122222";
$dbName = "SAC";

$conn = new PDO("sqlsrv:server=$serverName ; Database = $dbName", $userName, $userPassword);

$sql = "SELECT * FROM ADDRBOOK";

$stmt = $conn->prepare($sql);
$stmt->execute();

while($result = $stmt->fetch( PDO::FETCH_ASSOC ))
{
    echo $result["ADDB_KEY"] . "\n";
}