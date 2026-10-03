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

// Optimized SQL Server query - selecting only required columns and joining only necessary tables
$sql_sqlsvr = "SELECT 
    DOCINFO.DI_KEY, 
    DOCINFO.DI_REF, 
    DOCINFO.DI_DATE, 
    ARFILE.AR_CODE, 
    ARFILE.AR_NAME, 
    TRANSTKD.TRD_SEQ, 
    TRANSTKD.TRD_SH_CODE, 
    TRANSTKD.TRD_SH_NAME, 
    TRANSTKD.TRD_K_U_PRC, 
    TRANSTKD.TRD_U_PRC
FROM DOCINFO WITH (NOLOCK)
JOIN DOCTYPE WITH (NOLOCK) ON DOCINFO.DI_DT = DOCTYPE.DT_KEY
JOIN AROE WITH (NOLOCK) ON DOCINFO.DI_KEY = AROE.AROE_DI
JOIN ARFILE WITH (NOLOCK) ON AROE.AROE_AR = ARFILE.AR_KEY
JOIN TRANSTKH WITH (NOLOCK) ON DOCINFO.DI_KEY = TRANSTKH.TRH_DI
JOIN TRANSTKD WITH (NOLOCK) ON TRANSTKH.TRH_KEY = TRANSTKD.TRD_TRH
WHERE DOCTYPE.DT_PROPERTIES = 207
  AND DOCINFO.DI_REF LIKE :doc_id_prefix
  AND DOCINFO.DI_DATE >= :start_date AND DOCINFO.DI_DATE < :end_date
ORDER BY DOCINFO.DI_KEY, TRANSTKD.TRD_SEQ";

$stmt_sqlsvr = $conn_sqlsvr->prepare($sql_sqlsvr);
$stmt_sqlsvr->execute([
    ':doc_id_prefix' => $doc_id_prefix,
    ':start_date'    => $start_date,
    ':end_date'      => $end_date
]);

// Pre-fetch existing composite keys (DI_KEY + line_no) into memory for fast O(1) lookup
$existing_keys = [];
$stmt_existing = $conn->query("SELECT DI_KEY, line_no FROM ims_price_approve_detail");
while ($row = $stmt_existing->fetch(PDO::FETCH_ASSOC)) {
    $existing_keys[$row['DI_KEY'] . '_' . $row['line_no']] = true;
}

$stmt_insert = $conn->prepare("INSERT INTO ims_price_approve_detail(DI_KEY,doc_no,line_no,doc_date,customer_id,customer_name,product_id,product_name,price_normal,price_special,remark) VALUES (:DI_KEY,:doc_no,:line_no,:doc_date,:customer_id,:customer_name,:product_id,:product_name,:price_normal,:price_special,:remark)");

$conn->beginTransaction();
$count_insert = 0;
$count_dup = 0;

try {
    while ($result_sqlsvr = $stmt_sqlsvr->fetch(PDO::FETCH_ASSOC)) {
        $di_key = $result_sqlsvr["DI_KEY"];
        $line_no = $result_sqlsvr["TRD_SEQ"];
        $key = $di_key . '_' . $line_no;

        if (isset($existing_keys[$key])) {
            $count_dup++;
        } else {
            $doc_date = substr($result_sqlsvr["DI_DATE"], 8, 2) . "/" . substr($result_sqlsvr["DI_DATE"], 5, 2) . "/" . strval(intval(substr($result_sqlsvr["DI_DATE"], 0, 4)) + 543);
            $remark = "Price/Unit ^^ TRD_K_U_PRC = " . $result_sqlsvr["TRD_K_U_PRC"] . " | TRD_U_PRC = " . $result_sqlsvr["TRD_U_PRC"];

            $stmt_insert->execute([
                ':DI_KEY' => $di_key,
                ':doc_no' => $result_sqlsvr["DI_REF"],
                ':line_no' => $line_no,
                ':doc_date' => $doc_date,
                ':customer_id' => $result_sqlsvr["AR_CODE"],
                ':customer_name' => $result_sqlsvr["AR_NAME"],
                ':product_id' => $result_sqlsvr["TRD_SH_CODE"],
                ':product_name' => $result_sqlsvr["TRD_SH_NAME"],
                ':price_normal' => $result_sqlsvr["TRD_K_U_PRC"],
                ':price_special' => $result_sqlsvr["TRD_K_U_PRC"],
                ':remark' => $remark
            ]);
            $existing_keys[$key] = true;
            $count_insert++;
        }
    }
    $conn->commit();
    echo "reserve_detail import finished. Insert: $count_insert, Duplicate: $count_dup\n";
} catch (Exception $e) {
    $conn->rollBack();
    echo "Error in reserve_detail import: " . $e->getMessage() . "\n";
}

$conn_sqlsvr = null;



