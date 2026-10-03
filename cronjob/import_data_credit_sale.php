<?php
$start_time = microtime(true);

ini_set('display_errors', 1);
error_reporting(~0);

include ("../config/connect_sqlserver.php");
include ("../config/connect_db.php");

include ("../cond_file/doc_info_credit_sale.php");

$doc_id_prefix = 'BKSV%';
$year = date("Y");
$month = date("m");

// Pre-fetch existing record keys into memory to eliminate N+1 SELECT queries
$existing_keys = array();
$sql_preload = "SELECT DI_KEY FROM ims_price_approve_header";
$stmt_preload = $conn->query($sql_preload);
if ($stmt_preload) {
    while ($row = $stmt_preload->fetch(PDO::FETCH_ASSOC)) {
        $existing_keys[$row["DI_KEY"]] = true;
    }
}

// Move PDO::prepare statements OUTSIDE the loop so they are prepared ONCE
$sql_check = "SELECT 1 FROM ims_price_approve_header WHERE DI_KEY = :DI_KEY LIMIT 1";
$stmt_check = $conn->prepare($sql_check);

$sql_update = "UPDATE ims_price_approve_header SET doc_no=:doc_no, doc_date=:doc_date, customer_id=:customer_id, customer_name=:customer_name WHERE DI_KEY = :DI_KEY";
$stmt_update = $conn->prepare($sql_update);

$sql_insert = "INSERT INTO ims_price_approve_header(DI_KEY,doc_no,doc_date,customer_id,customer_name) VALUES (:DI_KEY,:doc_no,:doc_date,:customer_id,:customer_name)";
$stmt_insert = $conn->prepare($sql_insert);

$sql_sqlsvr = $select_query . $sql_cond . " AND DI_REF like '" . $doc_id_prefix . "'"
            . " AND YEAR(DI_DATE) = " . $year
            . " AND MONTH(DI_DATE) = " . $month
            . $sql_order ;

//$myfile = fopen("qry_file1.txt", "w") or die("Unable to open file!");
//fwrite($myfile, $sql_sqlsvr);
//fclose($myfile);

$stmt_sqlsvr = $conn_sqlsvr->prepare($sql_sqlsvr);
$stmt_sqlsvr->execute();

$updated = 0;
$inserted = 0;

try {
    $conn->beginTransaction();

    while ($result_sqlsvr = $stmt_sqlsvr->fetch(PDO::FETCH_ASSOC)) {
        $di_key = $result_sqlsvr["DI_KEY"];
        $exists = isset($existing_keys[$di_key]);
        if (!$exists) {
            $stmt_check->execute([':DI_KEY' => $di_key]);
            $exists = (bool)$stmt_check->fetchColumn();
        }

        $doc_date = substr($result_sqlsvr["DI_DATE"],8,2) . "/" . substr($result_sqlsvr["DI_DATE"],5,2) . "/" . strval(intval(substr($result_sqlsvr["DI_DATE"],0,4))+543);

        if ($exists) {
            $stmt_update->execute([
                ':doc_no' => $result_sqlsvr["DI_REF"],
                ':doc_date' => $doc_date,
                ':customer_id' => $result_sqlsvr["AR_CODE"],
                ':customer_name' => $result_sqlsvr["AR_NAME"],
                ':DI_KEY' => $di_key
            ]);
            $updated++;
        } else {
            $stmt_insert->execute([
                ':DI_KEY' => $di_key,
                ':doc_no' => $result_sqlsvr["DI_REF"],
                ':doc_date' => $doc_date,
                ':customer_id' => $result_sqlsvr["AR_CODE"],
                ':customer_name' => $result_sqlsvr["AR_NAME"]
            ]);

            $existing_keys[$di_key] = true;
            $inserted++;
        }
    }

    $conn->commit();
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "Error: " . $e->getMessage() . "\n";
}

$conn_sqlsvr=null;

$execution_time = round(microtime(true) - $start_time, 4);
echo "Updated: " . $updated . " | Inserted: " . $inserted . " | Execution time: " . $execution_time . " seconds\n";


