<?php
include "../config/connect_pg_db.php";
$stmt = $conn->query("SELECT * FROM SC_DOCINFO ORDER BY DI_REF DESC LIMIT 10  ");
while ($row = $stmt->fetch()) {
    echo $row['DI_REF']."<br />\n";
}