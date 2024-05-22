<?php
include "../../config/connect_pg_db.php";

$statement = $conn->query('SELECT DI_REF, DI_DATE, DI_UPD_DATE '
    . 'FROM SC_DOCINFO '
    . 'ORDER BY DI_UPD_DATE DESC LIMIT 10');

$results = $statement->fetchAll();
$statement->execute();

foreach ($results as $result) {

    echo $result['DI_DATE'] . " | " . $result['DI_REF'] . " | " . $result['DI_UPD_DATE'] ;

}


