<?php

ini_set('display_errors', 1);
error_reporting(~0);

$start_time = microtime(true);

include("../config/connect_sqlserver40.php");
include("../config/connect_db.php");

include('../cond_file/doc_info_sale_daily_sac_all.php');
include('../util/month_util.php');

$DT_DOCCODE_MINUS = "IS";

$str_doc1 = array_flip(array("DS02", "IS01", "IS02", "IV01"));
$str_doc2 = array_flip(array("2"));
$str_doc3 = array_flip(array("CCS6", "CCS7", "DDS5", "IC5", "IC6", "IIS5", "IIS6", "IV3"));

$str_group1 = array_flip(array("1SAC01", "1SAC02", "1SAC03", "1SAC04", "1SAC05", "1SAC06", "1SAC07", "1SAC08", "1SAC09", "1SAC10", "1SAC11", "1SAC12", "1SAC13", "1SAC14", "2SAC01", "2SAC02", "2SAC03", "2SAC04", "2SAC05", "2SAC06", "2SAC07", "2SAC08", "2SAC09", "2SAC10", "2SAC11", "2SAC12", "2SAC13", "2SAC14", "2SAC15", "3SAC01", "3SAC02", "3SAC03", "3SAC04", "3SAC05", "3SAC06", "4SAC01", "4SAC02", "4SAC03", "4SAC04", "4SAC05", "4SAC06"));
$str_group2 = array_flip(array("5SAC01", "5SAC02", "6SAC08", "8CPA01-001", "8CPA01-002", "8SAC09", "8BTCA01-002", "8BTCA01-001"));
$str_group3 = array_flip(array("TATA-004", "999-08", "999-07", "999-14"));
$str_group4 = array_flip(array("SAC08", "TATA-003", "10SAC12"));

echo "Host DB =  " . $host . "\n\r";
echo "Today is " . date("Y/m/d") . "\n\r";
echo "Before is " . date("Y/m/d", strtotime("yesterday")) . "\n\r";

$query_daily_cond_ext = " AND (DOCTYPE.DT_DOCCODE in ('2','DS02','IS01','IS02','IV01','IV3','DDS5','CCS6','CCS7','IC5','IC6','IIS5','IIS6')) ";

$query_year = " AND DI_DATE BETWEEN '2020/05/28' AND '2020/05/31'";

$sql_sqlsvr = $select_query_daily . $select_query_daily_cond . $query_daily_cond_ext . $query_year . $select_query_daily_order;

// 1. Prepared Statements Outside Loop
$sql_check = "SELECT 1 FROM ims_product_sale_sac 
             WHERE DI_KEY = :DI_KEY AND DI_REF = :DI_REF 
             AND DI_DATE = :DI_DATE AND DT_DOCCODE = :DT_DOCCODE 
             AND TRD_SEQ = :TRD_SEQ LIMIT 1";
$stmt_check = $conn->prepare($sql_check);

$sql_update = " UPDATE ims_product_sale_sac SET TRD_Q_FREE=:TRD_Q_FREE, DI_ACTIVE=:DI_ACTIVE   
WHERE DI_KEY = :DI_KEY AND DI_REF = :DI_REF AND DI_DATE = :DI_DATE 
AND DT_DOCCODE = :DT_DOCCODE AND TRD_SEQ = :TRD_SEQ ";
$stmt_update = $conn->prepare($sql_update);

// 2. Pre-fetch existing keys
$sql_existing = "SELECT CONCAT(DI_KEY, '|', DI_REF, '|', DI_DATE, '|', DT_DOCCODE, '|', TRD_SEQ) FROM ims_product_sale_sac WHERE DI_DATE BETWEEN '2020/05/28' AND '2020/05/31'";
$stmt_existing = $conn->prepare($sql_existing);
$stmt_existing->execute();
$existing_keys = array_flip($stmt_existing->fetchAll(PDO::FETCH_COLUMN));

$stmt_sqlsvr = $conn_sqlsvr->prepare($sql_sqlsvr);
$stmt_sqlsvr->execute();

$conn->beginTransaction();

$count_update = 0;

try {
    while ($result_sqlsvr = $stmt_sqlsvr->fetch(PDO::FETCH_ASSOC)) {
        $key = $result_sqlsvr["DI_KEY"] . '|' . $result_sqlsvr["DI_REF"] . '|' . $result_sqlsvr["DI_DATE"] . '|' . $result_sqlsvr["DT_DOCCODE"] . '|' . $result_sqlsvr["TRD_SEQ"];

        $exists = isset($existing_keys[$key]);
        if (!$exists) {
            $stmt_check->execute([
                ':DI_KEY' => $result_sqlsvr["DI_KEY"],
                ':DI_REF' => $result_sqlsvr["DI_REF"],
                ':DI_DATE' => $result_sqlsvr["DI_DATE"],
                ':DT_DOCCODE' => $result_sqlsvr["DT_DOCCODE"],
                ':TRD_SEQ' => $result_sqlsvr["TRD_SEQ"]
            ]);
            $exists = (bool)$stmt_check->fetchColumn();
        }

        if ($exists) {
            $stmt_update->execute([
                ':TRD_Q_FREE' => $result_sqlsvr["TRD_Q_FREE"],
                ':DI_ACTIVE'   => $result_sqlsvr["DI_ACTIVE"],
                ':DI_KEY'      => $result_sqlsvr["DI_KEY"],
                ':DI_REF'      => $result_sqlsvr["DI_REF"],
                ':DI_DATE'     => $result_sqlsvr["DI_DATE"],
                ':DT_DOCCODE'  => $result_sqlsvr["DT_DOCCODE"],
                ':TRD_SEQ'     => $result_sqlsvr["TRD_SEQ"]
            ]);
            $count_update++;
        }
    }

    $conn->commit();
    echo "Process Completed successfully.\n";
    echo "Updated: " . $count_update . " rows.\n";

} catch (Exception $e) {
    $conn->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}

$conn_sqlsvr = null;
$conn = null;

echo "\nExecution time: " . (microtime(true) - $start_time) . " seconds\n";


