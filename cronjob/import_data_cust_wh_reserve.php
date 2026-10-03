<?php

$start_time = microtime(true);

ini_set('display_errors', 1);
error_reporting(~0);

include("../config/connect_sqlserver.php");
include("../config/connect_db.php");

include('../cond_file/doc_reserve_warehouse.php');
include('../util/month_util.php');

$query_year = " AND DI_DATE BETWEEN '" . date("Y/m/d", strtotime("yesterday")) . "' AND '" . date("Y/m/d") . "'";

$sql_sqlsvr = $sql_reserve . $query_year;

// Pre-fetch existing record keys into PHP Memory lookup array
$existing_products = array_flip($conn->query("SELECT product_id FROM wh_product_master")->fetchAll(PDO::FETCH_COLUMN));
$existing_movements = array_flip($conn->query("SELECT CONCAT(doc_id, '|', product_id, '|', line_no) FROM wh_stock_movement_out")->fetchAll(PDO::FETCH_COLUMN));

// Prepared check statements ONCE outside loop
$sql_check_product = "SELECT 1 FROM wh_product_master WHERE product_id = :product_id LIMIT 1";
$stmt_check_product = $conn->prepare($sql_check_product);

$sql_check_movement = "SELECT 1 FROM wh_stock_movement_out WHERE doc_id = :doc_id AND product_id = :product_id AND line_no = :line_no LIMIT 1";
$stmt_check_movement = $conn->prepare($sql_check_movement);

// Prepared UPDATE statements ONCE outside loop
$sql_update_product = "UPDATE wh_product_master SET product_name = :product_name WHERE product_id = :product_id";
$stmt_update_product = $conn->prepare($sql_update_product);

$sql_update_movement = "UPDATE wh_stock_movement_out SET doc_date = :doc_date, wh_org = :wh_org, qty = :qty, customer_name = :customer_name, sale_take = :sale_take, create_by = :create_by, seq_record = :seq_record, doc_user_id = :doc_user_id WHERE doc_id = :doc_id AND product_id = :product_id AND line_no = :line_no";
$stmt_update_movement = $conn->prepare($sql_update_movement);

// Prepare INSERT statements ONCE outside the loop
$sql_insert_product = "INSERT INTO wh_product_master(product_id, product_name) VALUES (:product_id, :product_name)";
$stmt_insert_product = $conn->prepare($sql_insert_product);

$sql_insert_movement = "INSERT INTO wh_stock_movement_out (doc_id,doc_date,product_id,wh_org,qty,customer_name,sale_take,create_by,seq_record,doc_user_id,line_no)
     VALUES (:doc_id,:doc_date,:product_id,:wh_org,:qty,:customer_name,:sale_take,:create_by,:seq_record,:doc_user_id,:line_no)";
$stmt_insert_movement = $conn->prepare($sql_insert_movement);

$stmt_sqlsvr = $conn_sqlsvr->prepare($sql_sqlsvr);
$stmt_sqlsvr->execute();

$doc_id_compare = "";
$seq_record = "";
$products_inserted = 0;
$products_updated = 0;
$movements_inserted = 0;
$movements_updated = 0;

$conn->beginTransaction();
try {
    while ($result_sqlsvr = $stmt_sqlsvr->fetch(PDO::FETCH_ASSOC)) {

        $trd_sh_code = $result_sqlsvr["TRD_SH_CODE"];
        $product_exists = isset($existing_products[$trd_sh_code]);
        if (!$product_exists) {
            $stmt_check_product->execute([':product_id' => $trd_sh_code]);
            $product_exists = (bool)$stmt_check_product->fetchColumn();
        }

        if ($product_exists) {
            $stmt_update_product->execute([
                ':product_name' => $result_sqlsvr["TRD_SH_NAME"],
                ':product_id' => $trd_sh_code
            ]);
            $products_updated++;
        } else {
            $stmt_insert_product->execute([
                ':product_id' => $trd_sh_code,
                ':product_name' => $result_sqlsvr["TRD_SH_NAME"]
            ]);
            $existing_products[$trd_sh_code] = true;
            $products_inserted++;
        }

        $doc_id = $result_sqlsvr["DI_REF"] . "-" . str_pad($result_sqlsvr["TRD_SEQ"], 3, '0', STR_PAD_LEFT);
        $line_no = $result_sqlsvr["TRD_SEQ"];
        $mov_key = $doc_id . '|' . $trd_sh_code . '|' . $line_no;

        if ($result_sqlsvr['AR_CODE'] === "SAC.0001405") {
            $customer_name = preg_replace("/\s+/", " ", $result_sqlsvr['DI_REMARK']);
        } else {
            $customer_name = $result_sqlsvr['AR_NAME'];
        }

        $doc_date = substr($result_sqlsvr['DI_DATE'], 8, 2) . "-" . substr($result_sqlsvr['DI_DATE'], 5, 2) . "-" . substr($result_sqlsvr['DI_DATE'], 0, 4);
        $product_id = $trd_sh_code;
        $wh_org = $result_sqlsvr["WL_CODE"];
        $qty = $result_sqlsvr["TRD_QTY"];
        $sale_take = $result_sqlsvr["SLMN_NAME"];
        $create_by = $result_sqlsvr["DI_CRE_BY"];

        if ($doc_id_compare !== $result_sqlsvr["DI_REF"]) {
            $str = rand();
            $seq_record = md5($str);
            $doc_id_compare = $result_sqlsvr["DI_REF"];
        }

        $doc_user_id = "BKRS";

        $mov_exists = isset($existing_movements[$mov_key]);
        if (!$mov_exists) {
            $stmt_check_movement->execute([
                ':doc_id' => $doc_id,
                ':product_id' => $product_id,
                ':line_no' => $line_no
            ]);
            $mov_exists = (bool)$stmt_check_movement->fetchColumn();
        }

        if ($mov_exists) {
            $stmt_update_movement->execute([
                ':doc_date' => $doc_date,
                ':wh_org' => $wh_org,
                ':qty' => $qty,
                ':customer_name' => $customer_name,
                ':sale_take' => $sale_take,
                ':create_by' => $create_by,
                ':seq_record' => $seq_record,
                ':doc_user_id' => $doc_user_id,
                ':doc_id' => $doc_id,
                ':product_id' => $product_id,
                ':line_no' => $line_no
            ]);
            $movements_updated++;
        } else {
            $stmt_insert_movement->execute([
                ':doc_id' => $doc_id,
                ':doc_date' => $doc_date,
                ':product_id' => $product_id,
                ':wh_org' => $wh_org,
                ':qty' => $qty,
                ':customer_name' => $customer_name,
                ':sale_take' => $sale_take,
                ':create_by' => $create_by,
                ':seq_record' => $seq_record,
                ':doc_user_id' => $doc_user_id,
                ':line_no' => $line_no
            ]);

            $existing_movements[$mov_key] = true;
            $movements_inserted++;
        }
    }
    $conn->commit();
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "Error: " . $e->getMessage() . "\n";
}

$conn_sqlsvr = null;

$execution_time = round(microtime(true) - $start_time, 4);
echo "Import Cust WH Reserve Summary:\n";
echo "Products Inserted: $products_inserted | Products Updated: $products_updated\n";
echo "Movements Inserted: $movements_inserted | Movements Updated: $movements_updated\n";
echo "Execution Time: {$execution_time} seconds\n";


