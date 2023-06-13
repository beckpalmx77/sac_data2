<?php


$host = 'sac.cckwqocv7kfy.ap-southeast-1.rds.amazonaws.com';
$db = 'sac';
$username = 'sacdbm';
$password = "Sac123456";


$dsn = "pgsql:host=$host;port=5432;dbname=$db;user=$username;password=$password";

try {
    // create a PostgreSQL database connection
    $conn = new PDO($dsn);

    // display a message if connected to the PostgreSQL successfully
    if ($conn) {
        echo "Connected to the <strong>$db . ' ON HOST ' . $host . ' User Name = ' . $username </strong> database successfully!";
    }
} catch (PDOException $e) {
    // report error message
    echo $e->getMessage();
}

/*
$stmt = $conn->query("SELECT * FROM SC_DOCINFO ORDER BY DI_REF DESC LIMIT 10  ");
while ($row = $stmt->fetch()) {
    echo $row['DI_REF']."<br />\n";
}
*/