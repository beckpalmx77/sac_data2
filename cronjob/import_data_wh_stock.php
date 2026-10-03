<?php

$start_time = microtime(true);

ini_set('display_errors', 1);
error_reporting(~0);

include("../config/connect_sqlserver.php");
include("../config/connect_db.php");

include("../cond_file/doc_stock_warehouse.php");

$select_query_daily_cond = " AND DOCINFO.DI_DATE BETWEEN '" . date("Y/m/d", strtotime("yesterday")) . "' AND '" . date("Y/m/d") . "'";

$sql_sqlsvr = $select_query . $sql_cond . " AND DT_DOCCODE = 'DM04' " . $select_query_daily_cond . $sql_order;

$doc_id_compare = "";

$stmt_sqlsvr = $conn_sqlsvr->prepare($sql_sqlsvr);
$stmt_sqlsvr->execute();

// Pre-fetch existing product master keys into memory
$existing_products = array();
$stmt_pm = $conn->query("SELECT product_id FROM wh_product_master");
while ($prow = $stmt_pm->fetch(PDO::FETCH_ASSOC)) {
    if ($prow['product_id'] !== null) {
        $existing_products[$prow['product_id']] = true;
    }
}

// Pre-fetch existing stock record keys into memory
$existing_stock = array();
$stmt_sr = $conn->query("SELECT doc_id, line_no, product_id FROM wh_stock_record");
while ($srow = $stmt_sr->fetch(PDO::FETCH_ASSOC)) {
    $composite = $srow['doc_id'] . '|' . $srow['line_no'] . '|' . $srow['product_id'];
    $existing_stock[$composite] = true;
}

// Move PDO prepare statements OUTSIDE the loop
$sql_pm_insert = "INSERT INTO wh_product_master(product_id, product_name) VALUES (:product_id, :product_name)";
$query_pm_insert = $conn->prepare($sql_pm_insert);

$sql_stock_check = "SELECT COUNT(*) FROM wh_stock_record WHERE doc_id = :doc_id AND line_no = :line_no AND product_id = :product_id";
$stmt_check = $conn->prepare($sql_stock_check);

$sql_update = "UPDATE wh_stock_record SET 
            doc_date = :doc_date,
            qty = :qty,
            wh_org = :wh_org,
            wh_to = :wh_to,
            doc_user_id = :doc_user_id, update_by = :update_by, remark = :remark
           WHERE doc_id = :doc_id AND line_no = :line_no AND product_id = :product_id";
$query_update = $conn->prepare($sql_update);

$sql_insert = "INSERT INTO wh_stock_record (doc_id, doc_date, line_no, product_id, qty, wh_org, wh_to, doc_user_id, seq_record, create_by, create_date, remark) 
    VALUES (:doc_id, :doc_date, :line_no, :product_id, :qty, :wh_org, :wh_to, :doc_user_id, :seq_record, :create_by, :create_date, :remark)";
$query_insert = $conn->prepare($sql_insert);

$inserted_count = 0;
$updated_count = 0;

// Wrap transaction around the loop
$conn->beginTransaction();
try {
    while ($result_sqlsvr = $stmt_sqlsvr->fetch(PDO::FETCH_ASSOC)) {

        // Check product master
        $prod_id = $result_sqlsvr["TRD_SH_CODE"];
        if (!isset($existing_products[$prod_id])) {
            $query_pm_insert->execute([
                ':product_id' => $prod_id,
                ':product_name' => $result_sqlsvr["TRD_SH_NAME"]
            ]);
            $existing_products[$prod_id] = true;
        }

        $doc_id = $result_sqlsvr["DI_REF"] . "-" . str_pad($result_sqlsvr["TRD_SEQ"], 3, '0', STR_PAD_LEFT);
        $composite_stock = $doc_id . '|' . $result_sqlsvr["TRD_SEQ"] . '|' . $prod_id;

        $doc_date = substr($result_sqlsvr["DI_DATE"], 8, 2) . "-" . substr($result_sqlsvr["DI_DATE"], 5, 2) . "-" . strval(intval(substr($result_sqlsvr["DI_DATE"], 0, 4)));

        $exists = isset($existing_stock[$composite_stock]);
        if (!$exists) {
            $stmt_check->execute([
                ':doc_id' => $doc_id,
                ':line_no' => $result_sqlsvr["TRD_SEQ"],
                ':product_id' => $prod_id
            ]);
            $exists = (bool)$stmt_check->fetchColumn();
        }

        if ($exists) {
            $query_update->execute([
                ':doc_date' => $doc_date,
                ':qty' => $result_sqlsvr["TRD_SH_QTY"],
                ':wh_org' => $result_sqlsvr["WL_CODE"],
                ':wh_to' => $result_sqlsvr["WL_CODE_TO"],
                ':doc_user_id' => $result_sqlsvr["DT_DOCCODE"],
                ':update_by' => $result_sqlsvr["DI_CRE_BY"],
                ':remark' => $result_sqlsvr["DI_REMARK"],
                ':doc_id' => $doc_id,
                ':line_no' => $result_sqlsvr["TRD_SEQ"],
                ':product_id' => $prod_id
            ]);
            $updated_count++;
        } else {
            if ($doc_id_compare !== $result_sqlsvr["DI_REF"]) {
                $str = rand();
                $seq_record = md5($str);
                $doc_id_compare = $result_sqlsvr["DI_REF"];
            }

            $query_insert->execute([
                ':doc_id' => $doc_id,
                ':doc_date' => $doc_date,
                ':line_no' => $result_sqlsvr["TRD_SEQ"],
                ':product_id' => $prod_id,
                ':qty' => $result_sqlsvr["TRD_SH_QTY"],
                ':wh_org' => $result_sqlsvr["WL_CODE"],
                ':wh_to' => $result_sqlsvr["WL_CODE_TO"],
                ':doc_user_id' => $result_sqlsvr["DT_DOCCODE"],
                ':seq_record' => $seq_record,
                ':create_by' => $result_sqlsvr["DI_CRE_BY"],
                ':create_date' => $result_sqlsvr["DI_CRE_DATE"],
                ':remark' => $result_sqlsvr["DI_REMARK"]
            ]);
            $existing_stock[$composite_stock] = true;
            $inserted_count++;
        }
    }
    $conn->commit();
} catch (Exception $e) {
    $conn->rollBack();
    echo "Error during import: " . $e->getMessage() . "\n";
}

$conn_sqlsvr = null;

$end_time = microtime(true);
$execution_time = round($end_time - $start_time, 4);

echo "Import Completed | Updated: $updated_count | Inserted: $inserted_count | Execution Time: {$execution_time} seconds\n";

