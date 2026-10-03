<?php
$start_time = microtime(true);

ini_set('display_errors', 1);
error_reporting(~0);

include ("../config/connect_sqlserver.php");
include ("../config/connect_db.php");
include('../cond_file/query_reserve_sac.php');

$query_year = " AND DI_DATE BETWEEN '" . date("Y/m/d", strtotime("yesterday")) . "' AND '" . date("Y/m/d") . "'";
$sql_sqlsvr = $sql_reserve . $query_year;

$stmt_sqlsvr = $conn_sqlsvr->prepare($sql_sqlsvr);
$stmt_sqlsvr->execute();
$rows_sqlsvr = $stmt_sqlsvr->fetchAll(PDO::FETCH_ASSOC);

$updated = 0;
$inserted = 0;

if (!empty($rows_sqlsvr)) {
    // Collect unique DI_KEYs from SQL Server results for targeted preloading
    $di_keys = array_unique(array_column($rows_sqlsvr, 'DI_KEY'));

    // Pre-fetch existing record keys and field values into memory (only for target DI_KEYs)
    $existing_records = array();
    $chunks = array_chunk($di_keys, 500);
    foreach ($chunks as $chunk) {
        $placeholders = implode(',', array_fill(0, count($chunk), '?'));
        $sql_preload = "SELECT DI_KEY, TRD_U_PRC, SKU_CODE, TRD_SEQ, AR_NAME, WL_CODE, DI_DATE, SKU_NAME, TRD_QTY, DI_REF, SLMN_NAME FROM ims_reserve_product_sac WHERE DI_KEY IN ($placeholders)";
        $stmt_preload = $conn->prepare($sql_preload);
        $stmt_preload->execute($chunk);
        while ($row = $stmt_preload->fetch(PDO::FETCH_ASSOC)) {
            $key = $row["DI_KEY"] . "|" . $row["TRD_U_PRC"] . "|" . $row["SKU_CODE"] . "|" . $row["TRD_SEQ"] . "|" . $row["AR_NAME"] . "|" . $row["WL_CODE"];
            $existing_records[$key] = [
                'DI_DATE'   => (string)$row["DI_DATE"],
                'SKU_NAME'  => (string)$row["SKU_NAME"],
                'TRD_QTY'   => (string)$row["TRD_QTY"],
                'DI_REF'    => (string)$row["DI_REF"],
                'SLMN_NAME' => (string)$row["SLMN_NAME"]
            ];
        }
    }

    $sql_update = "UPDATE ims_reserve_product_sac SET DI_DATE=:DI_DATE, SKU_NAME=:SKU_NAME, TRD_QTY=:TRD_QTY, DI_REF=:DI_REF, SLMN_NAME=:SLMN_NAME WHERE DI_KEY = :DI_KEY AND TRD_U_PRC = :TRD_U_PRC AND SKU_CODE = :SKU_CODE AND TRD_SEQ = :TRD_SEQ AND AR_NAME = :AR_NAME AND WL_CODE = :WL_CODE";
    $stmt_update = $conn->prepare($sql_update);

    $sql_insert = "INSERT INTO ims_reserve_product_sac(DI_KEY,TRD_SEQ,DI_DATE,SKU_CODE,SKU_NAME,TRD_QTY,TRD_U_PRC,WL_CODE,DI_REF,SLMN_NAME,AR_NAME) 
            VALUES (:DI_KEY,:TRD_SEQ,:DI_DATE,:SKU_CODE,:SKU_NAME,:TRD_QTY,:TRD_U_PRC,:WL_CODE,:DI_REF,:SLMN_NAME,:AR_NAME)";
    $stmt_insert = $conn->prepare($sql_insert);

    try {
        $conn->beginTransaction();

        foreach ($rows_sqlsvr as $result_sqlsvr) {
            $key = $result_sqlsvr["DI_KEY"] . "|" . $result_sqlsvr["TRD_U_PRC"] . "|" . $result_sqlsvr["SKU_CODE"] . "|" . $result_sqlsvr["TRD_SEQ"] . "|" . $result_sqlsvr["AR_NAME"] . "|" . $result_sqlsvr["WL_CODE"];
            $exists = isset($existing_records[$key]);

            $DI_DATE = substr($result_sqlsvr["DI_DATE"], 0, 10);
            $DI_DATE = str_replace('/', '-', $DI_DATE);

            if ($exists) {
                $cur = $existing_records[$key];
                // Skip UPDATE query execution if all field values are unchanged
                if ($cur['DI_DATE'] === (string)$DI_DATE &&
                    $cur['SKU_NAME'] === (string)$result_sqlsvr["SKU_NAME"] &&
                    $cur['TRD_QTY'] == $result_sqlsvr["TRD_QTY"] &&
                    $cur['DI_REF'] === (string)$result_sqlsvr["DI_REF"] &&
                    $cur['SLMN_NAME'] === (string)$result_sqlsvr["SLMN_NAME"]) {
                    $updated++;
                    continue;
                }

                $stmt_update->execute([
                    ':DI_DATE' => $DI_DATE,
                    ':SKU_NAME' => $result_sqlsvr["SKU_NAME"],
                    ':TRD_QTY' => $result_sqlsvr["TRD_QTY"],
                    ':DI_REF' => $result_sqlsvr["DI_REF"],
                    ':SLMN_NAME' => $result_sqlsvr["SLMN_NAME"],
                    ':DI_KEY' => $result_sqlsvr["DI_KEY"],
                    ':TRD_U_PRC' => $result_sqlsvr["TRD_U_PRC"],
                    ':SKU_CODE' => $result_sqlsvr["SKU_CODE"],
                    ':TRD_SEQ' => $result_sqlsvr["TRD_SEQ"],
                    ':AR_NAME' => $result_sqlsvr["AR_NAME"],
                    ':WL_CODE' => $result_sqlsvr["WL_CODE"]
                ]);
                $updated++;
            } else {
                $stmt_insert->execute([
                    ':DI_KEY' => $result_sqlsvr["DI_KEY"],
                    ':TRD_SEQ' => $result_sqlsvr["TRD_SEQ"],
                    ':DI_DATE' => $DI_DATE,
                    ':SKU_CODE' => $result_sqlsvr["SKU_CODE"],
                    ':SKU_NAME' => $result_sqlsvr["SKU_NAME"],
                    ':TRD_QTY' => $result_sqlsvr["TRD_QTY"],
                    ':TRD_U_PRC' => $result_sqlsvr["TRD_U_PRC"],
                    ':WL_CODE' => $result_sqlsvr["WL_CODE"],
                    ':DI_REF' => $result_sqlsvr["DI_REF"],
                    ':SLMN_NAME' => $result_sqlsvr["SLMN_NAME"],
                    ':AR_NAME' => $result_sqlsvr["AR_NAME"]
                ]);

                $existing_records[$key] = [
                    'DI_DATE'   => (string)$DI_DATE,
                    'SKU_NAME'  => (string)$result_sqlsvr["SKU_NAME"],
                    'TRD_QTY'   => (string)$result_sqlsvr["TRD_QTY"],
                    'DI_REF'    => (string)$result_sqlsvr["DI_REF"],
                    'SLMN_NAME' => (string)$result_sqlsvr["SLMN_NAME"]
                ];
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
}

$conn_sqlsvr = null;

$execution_time = round(microtime(true) - $start_time, 4);
echo "Updated: " . $updated . " | Inserted: " . $inserted . " | Execution time: " . $execution_time . " seconds\n";
