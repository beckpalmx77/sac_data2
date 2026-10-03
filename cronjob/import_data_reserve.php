<?php

ini_set('display_errors', 1);
error_reporting(~0);

include ("../config/connect_sqlserver.php");
include ("../config/connect_db.php");

$doc_id_prefix = 'BKSV%';
$year = date("Y");
$month = date("m");

echo "Year = " . $year ; echo "\n\r"; echo "Month = " . $month ; echo "\n\r";

$start_date = sprintf('%04d-%02d-01 00:00:00', $year, $month);
$end_date = date('Y-m-d H:i:s', strtotime("+1 month", strtotime("$year-$month-01")));

$sql_sqlsvr = "SELECT 
    DOCINFO.DI_KEY, 
    DOCINFO.DI_REF, 
    DOCINFO.DI_DATE, 
    ARFILE.AR_CODE, 
    ARFILE.AR_NAME
FROM DOCINFO WITH (NOLOCK)
JOIN DOCTYPE WITH (NOLOCK) ON DOCINFO.DI_DT = DOCTYPE.DT_KEY
JOIN AROE WITH (NOLOCK) ON DOCINFO.DI_KEY = AROE.AROE_DI
JOIN ARFILE WITH (NOLOCK) ON AROE.AROE_AR = ARFILE.AR_KEY
WHERE DOCTYPE.DT_PROPERTIES = 207
  AND DOCINFO.DI_REF LIKE :doc_id_prefix
  AND DOCINFO.DI_DATE >= :start_date AND DOCINFO.DI_DATE < :end_date
ORDER BY DOCINFO.DI_KEY";

$stmt_sqlsvr = $conn_sqlsvr->prepare($sql_sqlsvr);
$stmt_sqlsvr->execute([
    ':doc_id_prefix' => $doc_id_prefix,
    ':start_date'    => $start_date,
    ':end_date'      => $end_date
]);

// Pre-fetch existing keys into memory for O(1) hash lookup
$existing_keys = [];
$stmt_existing = $conn->query("SELECT DI_KEY FROM ims_price_approve_header");
while ($key = $stmt_existing->fetchColumn()) {
    $existing_keys[$key] = true;
}

$stmt_insert = $conn->prepare("INSERT INTO ims_price_approve_header(DI_KEY,doc_no,doc_date,customer_id,customer_name) VALUES (:DI_KEY,:doc_no,:doc_date,:customer_id,:customer_name)");

$conn->beginTransaction();
$count_insert = 0;
$count_dup = 0;

try {
    while ($result_sqlsvr = $stmt_sqlsvr->fetch(PDO::FETCH_ASSOC)) {
        $di_key = $result_sqlsvr["DI_KEY"];
        if (isset($existing_keys[$di_key])) {
            $count_dup++;
        } else {
            $doc_date = substr($result_sqlsvr["DI_DATE"], 8, 2) . "/" . substr($result_sqlsvr["DI_DATE"], 5, 2) . "/" . strval(intval(substr($result_sqlsvr["DI_DATE"], 0, 4)) + 543);

            $stmt_insert->execute([
                ':DI_KEY' => $di_key,
                ':doc_no' => $result_sqlsvr["DI_REF"],
                ':doc_date' => $doc_date,
                ':customer_id' => $result_sqlsvr["AR_CODE"],
                ':customer_name' => $result_sqlsvr["AR_NAME"]
            ]);
            $existing_keys[$di_key] = true;
            $count_insert++;
        }
    }
    $conn->commit();
    echo "reserve import finished. Insert: $count_insert, Duplicate: $count_dup\n";
} catch (Exception $e) {
    $conn->rollBack();
    echo "Error in reserve import: " . $e->getMessage() . "\n";
}

$conn_sqlsvr = null;



