<?php

$start_time = microtime(true);

ini_set('display_errors', 1);
error_reporting(~0);

include("../config/connect_sqlserver.php");
include("../config/connect_db.php");
include('../util/month_util.php');

// Optimized SQL Server Query: Only join necessary tables and select needed columns
$sql_query_data = " SELECT 
     DOCINFO.DI_REF,
     DOCINFO.DI_DATE,
     TRANPAYA.TPA_REFER_REF
FROM
     DOCINFO WITH (NOLOCK)
     JOIN DOCTYPE WITH (NOLOCK) ON DI_DT = DT_KEY
     JOIN TRANPAYH WITH (NOLOCK) ON DI_KEY = TPH_DI
     JOIN TRANPAYA WITH (NOLOCK) ON TPH_KEY = TPA_TPH
WHERE
     (DOCTYPE.DT_PROPERTIES = '404') AND
     (DOCINFO.DI_ACTIVE = 0) ";

$select_query_daily_cond = " AND DOCINFO.DI_DATE BETWEEN '" . date('Y/m/d', strtotime("-4 month")) . "' AND '" . date("Y/m/d") . "'";
$order_by = " ORDER BY DOCINFO.DI_DATE ASC, DOCINFO.DI_REF ASC ";

$sql_sqlsvr = $sql_query_data . $select_query_daily_cond . $order_by;

$stmt_sqlsvr = $conn_sqlsvr->prepare($sql_sqlsvr);
$stmt_sqlsvr->execute();
$rows_sqlsvr = $stmt_sqlsvr->fetchAll(PDO::FETCH_ASSOC);

$updated_count = 0;
$not_found_count = 0;
$payment_status = 'Y';

if (!empty($rows_sqlsvr)) {
    // Extract unique TPA_REFER_REF keys to preload target records
    $refer_refs = array_filter(array_unique(array_column($rows_sqlsvr, 'TPA_REFER_REF')));

    // Pre-fetch existing bill records for targeted keys only
    $existing_records = array();
    $chunks = array_chunk($refer_refs, 500);
    foreach ($chunks as $chunk) {
        $placeholders = implode(',', array_fill(0, count($chunk), '?'));
        $sql_preload = "SELECT DI_REF, PAYMENT_DOC_DI, PAYMENT_DOC_DATE, PAYMENT_STATUS FROM ims_document_bill WHERE DI_REF IN ($placeholders)";
        $stmt_preload = $conn->prepare($sql_preload);
        $stmt_preload->execute($chunk);
        while ($row = $stmt_preload->fetch(PDO::FETCH_ASSOC)) {
            $existing_records[$row["DI_REF"]] = [
                'PAYMENT_DOC_DI'   => (string)$row["PAYMENT_DOC_DI"],
                'PAYMENT_DOC_DATE' => (string)$row["PAYMENT_DOC_DATE"],
                'PAYMENT_STATUS'   => (string)$row["PAYMENT_STATUS"]
            ];
        }
    }

    $sql_update = " UPDATE ims_document_bill SET PAYMENT_DOC_DI=:PAYMENT_DOC_DI, PAYMENT_DOC_DATE=:PAYMENT_DOC_DATE, PAYMENT_STATUS=:PAYMENT_STATUS 
    WHERE DI_REF = :TPA_REFER_REF ";
    $stmt_update = $conn->prepare($sql_update);

    try {
        $conn->beginTransaction();

        foreach ($rows_sqlsvr as $result_sqlsvr) {
            $refer_ref = $result_sqlsvr["TPA_REFER_REF"];
            if (empty($refer_ref)) {
                $not_found_count++;
                continue;
            }

            $exists = isset($existing_records[$refer_ref]);

            if ($exists) {
                $cur = $existing_records[$refer_ref];
                $pay_di = (string)$result_sqlsvr["DI_REF"];
                $pay_date = (string)$result_sqlsvr["DI_DATE"];

                // Skip UPDATE query if values are already up-to-date
                if ($cur['PAYMENT_DOC_DI'] === $pay_di &&
                    $cur['PAYMENT_DOC_DATE'] === $pay_date &&
                    $cur['PAYMENT_STATUS'] === $payment_status) {
                    $updated_count++;
                    continue;
                }

                $stmt_update->execute([
                    ':PAYMENT_DOC_DI'   => $pay_di,
                    ':PAYMENT_DOC_DATE' => $pay_date,
                    ':PAYMENT_STATUS'   => $payment_status,
                    ':TPA_REFER_REF'    => $refer_ref
                ]);

                $existing_records[$refer_ref] = [
                    'PAYMENT_DOC_DI'   => $pay_di,
                    'PAYMENT_DOC_DATE' => $pay_date,
                    'PAYMENT_STATUS'   => $payment_status
                ];
                $updated_count++;
            } else {
                $not_found_count++;
            }
        }

        $conn->commit();
    } catch (Exception $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        echo "Error: " . $e->getMessage() . "\n";
    }
}

$conn_sqlsvr = null;

$execution_time = round(microtime(true) - $start_time, 4);
echo "Import Bill Payment SAC Summary:\n";
echo "Updated: $updated_count\n";
echo "Not Found / Skipped: $not_found_count\n";
echo "Execution Time: {$execution_time} seconds\n";