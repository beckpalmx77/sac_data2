<?php

ini_set('display_errors', 1);
error_reporting(~0);

$start_time = microtime(true);

include("../config/connect_sqlserver.php");
include("../config/connect_db.php");

$day = substr(date("Y/m/d"),8,2);
$month = substr(date("Y/m/d"),5,2);
$year = substr(date("Y/m/d"),0,4);

echo $year . " | " . $month . " | " . $day . "\n\r";

$sql_sqlsvr = "SELECT DOCINFO.DI_KEY,DOCINFO.DI_REF,DI_DATE,DOCINFO.DI_ACTIVE FROM DOCINFO 
WHERE DOCINFO.DI_ACTIVE = 1 AND DI_DATE >= DATEADD(month, -2, GETDATE()) AND DI_DATE <= GETDATE();";

$stmt_sqlsvr = $conn_sqlsvr->prepare($sql_sqlsvr);
$stmt_sqlsvr->execute();

$sql_delete = "DELETE FROM ims_product_sale_sac WHERE DI_KEY = :DI_KEY AND DI_REF = :DI_REF";
$stmt_delete = $conn->prepare($sql_delete);

$conn->beginTransaction();

$count_delete = 0;

try {
    while ($result_sqlsvr = $stmt_sqlsvr->fetch(PDO::FETCH_ASSOC)) {
        $stmt_delete->execute([
            ':DI_KEY' => $result_sqlsvr["DI_KEY"],
            ':DI_REF' => $result_sqlsvr["DI_REF"]
        ]);
        $count_delete++;
    }

    $conn->commit();
    echo "Delete process completed successfully.\n";
    echo "Deleted: " . $count_delete . " rows.\n";

} catch (Exception $e) {
    $conn->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}

$conn_sqlsvr = null;
$conn = null;

echo "Execution time: " . (microtime(true) - $start_time) . " seconds\n";