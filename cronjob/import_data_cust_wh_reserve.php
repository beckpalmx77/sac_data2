<?php

ini_set('display_errors', 1);
error_reporting(~0);

include("../config/connect_sqlserver.php");
include("../config/connect_db.php");

include('../cond_file/doc_reserve_warehouse.php');
include('../util/month_util.php');

echo "Today is " . date("Y/m/d") . "\n\r";
echo "Yesterday is " . date("Y/m/d", strtotime("yesterday")) . "\n\r";
echo "Host - " . $host . "\n\r";

$query_year = " AND DI_DATE BETWEEN '" . date("Y/m/d", strtotime("yesterday")) . "' AND '" . date("Y/m/d") . "'";

$sql_sqlsvr = $sql_reserve . $query_year;

$insert_data = "";
$update_data = "";
$doc_id_compare = "";
$res = "";

$stmt_sqlsvr = $conn_sqlsvr->prepare($sql_sqlsvr);
$stmt_sqlsvr->execute();

$return_arr = array();

while ($result_sqlsvr = $stmt_sqlsvr->fetch(PDO::FETCH_ASSOC)) {

    // ใช้ COUNT(*) แทนการ SELECT * เพื่อปรับปรุงประสิทธิภาพ
    $sql_find_master = "SELECT COUNT(*) FROM wh_product_master WHERE product_id = :product_id";
    $query_find = $conn->prepare($sql_find_master);
    $query_find->bindParam(':product_id', $result_sqlsvr["TRD_SH_CODE"], PDO::PARAM_STR);
    $query_find->execute();
    $nRows = $query_find->fetchColumn();

    if ($nRows <= 0) {
        // ใช้ prepare statement สำหรับการ INSERT ด้วย
        $sql = "INSERT INTO wh_product_master(product_id, product_name) VALUES (:product_id, :product_name)";
        $query = $conn->prepare($sql);
        $query->bindParam(':product_id', $result_sqlsvr["TRD_SH_CODE"], PDO::PARAM_STR);
        $query->bindParam(':product_name', $result_sqlsvr["TRD_SH_NAME"], PDO::PARAM_STR);
        $query->execute();
        echo "Product Save OK = " . $result_sqlsvr["TRD_SH_CODE"] . " | " . $result_sqlsvr["TRD_SH_NAME"] . "\n\r";
    }

    $doc_id = $result_sqlsvr["DI_REF"] . "-" . str_pad($result_sqlsvr["TRD_SEQ"], 3, '0', STR_PAD_LEFT);

    $sql_find_master = "SELECT COUNT(*) FROM wh_stock_movement_out WHERE doc_id = :doc_id AND product_id = :product_id AND line_no = :line_no ";
    $query_find = $conn->prepare($sql_find_master);
    $query_find->bindParam(':doc_id', $doc_id, PDO::PARAM_STR);
    $query_find->bindParam(':product_id', $result_sqlsvr["TRD_SH_CODE"], PDO::PARAM_STR);
    $query_find->bindParam(':line_no', $result_sqlsvr["TRD_SEQ"], PDO::PARAM_STR);
    $query_find->execute();
    $nRows = $query_find->fetchColumn();

    if ($nRows <= 0) {

        //SAC.0001405=Ready Quick
        if ($result_sqlsvr['AR_CODE'] === "SAC.0001405") {
            $customer_name = preg_replace("/\s+/", " ", $result_sqlsvr['DI_REMARK']);
        } else {
            $customer_name = $result_sqlsvr['AR_NAME'];
        }

        $doc_date = substr($result_sqlsvr['DI_DATE'], 8, 2) . "-" . substr($result_sqlsvr['DI_DATE'], 5, 2) . "-" . substr($result_sqlsvr['DI_DATE'], 0, 4);
        $product_id = $result_sqlsvr["TRD_SH_CODE"];
        $wh_org = $result_sqlsvr["WL_CODE"];
        $qty = $result_sqlsvr["TRD_QTY"];
        $sale_take = $result_sqlsvr["SLMN_NAME"];
        $create_by = $result_sqlsvr["DI_CRE_BY"];
        $line_no = $result_sqlsvr["TRD_SEQ"];

        if ($doc_id_compare !== $result_sqlsvr["DI_REF"]) {
            $str = rand();
            $seq_record = md5($str);
            $doc_id_compare = $result_sqlsvr["DI_REF"];
        }

        $doc_user_id = "BKRS";

        /*
            echo $doc_id . " | " . $doc_date . " | " . $customer_name . " | "
                . $product_id . " | " . $wh_org . " | " . $qty . " | " . $sale_take
                . "\n\r";
        */

        $insert_data = " INSERT INTO wh_stock_movement_out (doc_id,doc_date,product_id,wh_org,qty,customer_name,sale_take,create_by,seq_record,doc_user_id,line_no)
             VALUES (:doc_id,:doc_date,:product_id,:wh_org,:qty,:customer_name,:sale_take,:create_by,:seq_record,:doc_user_id,:line_no) ";

        $query = $conn->prepare($insert_data);
        $query->bindParam(':doc_id', $doc_id, PDO::PARAM_STR);
        $query->bindParam(':doc_date', $doc_date, PDO::PARAM_STR);
        $query->bindParam(':product_id', $product_id, PDO::PARAM_STR);
        $query->bindParam(':wh_org', $wh_org, PDO::PARAM_STR);
        $query->bindParam(':qty', $qty, PDO::PARAM_STR);
        $query->bindParam(':customer_name', $customer_name, PDO::PARAM_STR);
        $query->bindParam(':sale_take', $sale_take, PDO::PARAM_STR);
        $query->bindParam(':create_by', $create_by, PDO::PARAM_STR);
        $query->bindParam(':seq_record', $seq_record, PDO::PARAM_STR);
        $query->bindParam(':doc_user_id', $doc_user_id, PDO::PARAM_STR);
        $query->bindParam(':line_no', $line_no, PDO::PARAM_STR);
        $query->execute();

        $lastInsertId = $conn->lastInsertId();

        if ($lastInsertId) {
            //$insert_data .= $doc_id . ":" . $doc_date . " | " . $product_id . " | " . $wh_org . " | " . $qty . " | " . $sale_take . "\n\r";
            echo " Save OK " . $product_id . " | " . $sale_take . "\n\r";
        } else {
            echo " Error ";
        }

    }

}

$conn_sqlsvr = null;

