<?php


$host = 'sac.cckwqocv7kfy.ap-southeast-1.rds.amazonaws.com';
$db = 'sac';
$username = 'sacdbm';
$password = "Sac123456";
<<<<<<< HEAD
//$username = 'sac';
//$password = "l;ovvF9h8kiN";
=======

>>>>>>> bd30a9146bc9fbace6d6526d39a7428bead7982f

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

$table_name = "SC_DOCINFO";
$field_sort = "DI_REF";

$sql_Select = "SELECT * FROM " .  $table_name . " ORDER BY " . $field_sort . " LIMIT 10 " ;

$stmt = $conn->query($sql_Select);
while ($row = $stmt->fetch()) {
    echo $row['0']."<br />\n";
}
