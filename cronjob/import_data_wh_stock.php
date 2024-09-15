<?php

ini_set('display_errors', 1);
error_reporting(~0);

include("../config/connect_sqlserver.php");
include("../config/connect_db.php");

include("../cond_file/doc_stock_warehouse.php");

echo "Today is " . date("Y/m/d");
echo "\n\r" . date("Y/m/d", strtotime("yesterday"));

$select_query_daily_cond = " AND 	DOCINFO.DI_DATE  = '2024/08/20' AND DT_DOCCODE = 'DM04'";

$select_query_daily_cond = " AND DOCINFO.DI_DATE BETWEEN '" . date("Y/m/d", strtotime("yesterday")) . "' AND '" . date("Y/m/d") . "'";

$sql_sqlsvr = $select_query . $sql_cond . " AND DT_DOCCODE = 'DM04' " . $select_query_daily_cond . $sql_order;

/*
$myfile = fopen("qry_file1.txt", "w") or die("Unable to open file!");
fwrite($myfile, $sql_sqlsvr);
fclose($myfile);
*/

$doc_id_compare = "";

$stmt_sqlsvr = $conn_sqlsvr->prepare($sql_sqlsvr);
$stmt_sqlsvr->execute();

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

    $sql_find = "SELECT COUNT(*) FROM wh_stock_record WHERE doc_id = :doc_id AND line_no = :line_no AND product_id = :product_id";
    $query_find = $conn->prepare($sql_find);
    $query_find->bindParam(':doc_id', $doc_id, PDO::PARAM_STR);
    $query_find->bindParam(':line_no', $result_sqlsvr["TRD_SEQ"], PDO::PARAM_INT);
    $query_find->bindParam(':product_id', $result_sqlsvr["TRD_SH_CODE"], PDO::PARAM_STR);
    $query_find->execute();
    $nRows = $query_find->fetchColumn();

    if ($nRows > 0) {
        // กรณีที่ข้อมูลมีอยู่แล้ว ทำการ Update
        $doc_date = substr($result_sqlsvr["DI_DATE"], 8, 2) . "-" . substr($result_sqlsvr["DI_DATE"], 5, 2) . "-" . strval(intval(substr($result_sqlsvr["DI_DATE"], 0, 4)));
        $doc_id = $result_sqlsvr["DI_REF"] . "-" . str_pad($result_sqlsvr["TRD_SEQ"], 3, '0', STR_PAD_LEFT);
        $sql_update = "UPDATE wh_stock_record SET 
                    doc_date = :doc_date,
                    qty = :qty,
                    wh_org = :wh_org,
                    wh_to = :wh_to,
                    doc_user_id = :doc_user_id,update_by= :update_by,remark= :remark
                   WHERE doc_id = :doc_id AND line_no = :line_no AND product_id = :product_id";
        $query_update = $conn->prepare($sql_update);
        $query_update->bindParam(':doc_date', $doc_date, PDO::PARAM_STR);
        $query_update->bindParam(':qty', $result_sqlsvr["TRD_SH_QTY"], PDO::PARAM_STR);
        $query_update->bindParam(':wh_org', $result_sqlsvr["WL_CODE"], PDO::PARAM_STR);
        $query_update->bindParam(':wh_to', $result_sqlsvr["WL_CODE_TO"], PDO::PARAM_STR);
        $query_update->bindParam(':doc_user_id', $result_sqlsvr["DT_DOCCODE"], PDO::PARAM_STR);
        $query_update->bindParam(':update_by', $result_sqlsvr["DI_CRE_BY"], PDO::PARAM_STR);
        $query_update->bindParam(':remark', $result_sqlsvr["DI_REMARK"], PDO::PARAM_STR);
        $query_update->bindParam(':doc_id', $doc_id, PDO::PARAM_STR);
        $query_update->bindParam(':line_no', $result_sqlsvr["TRD_SEQ"], PDO::PARAM_INT);
        $query_update->bindParam(':product_id', $result_sqlsvr["TRD_SH_CODE"], PDO::PARAM_STR);
        if ($query_update->execute()) {
            echo "Update SAVE OK " . $result_sqlsvr["DI_REF"] . " | " . $result_sqlsvr["TRD_SEQ"] . " | " . $result_sqlsvr["TRD_SH_CODE"] . "\n\r";
        } else {
            echo "Update Error";
        }
    } else {

        if ($doc_id_compare !== $result_sqlsvr["DI_REF"]) {
            $str = rand();
            $seq_record = md5($str);
            $doc_id_compare = $result_sqlsvr["DI_REF"];
        }

        // กรณีที่ข้อมูลยังไม่มีอยู่ในฐานข้อมูล ทำการ Insert
        $doc_date = substr($result_sqlsvr["DI_DATE"], 8, 2) . "-" . substr($result_sqlsvr["DI_DATE"], 5, 2) . "-" . strval(intval(substr($result_sqlsvr["DI_DATE"], 0, 4)));
        $doc_id = $result_sqlsvr["DI_REF"] . "-" . str_pad($result_sqlsvr["TRD_SEQ"], 3, '0', STR_PAD_LEFT);
        $sql_insert = "INSERT INTO wh_stock_record (doc_id, doc_date, line_no, product_id, qty, wh_org, wh_to, doc_user_id,seq_record,create_by,create_date,remark) 
            VALUES (:doc_id, :doc_date, :line_no, :product_id, :qty, :wh_org, :wh_to, :doc_user_id,:seq_record,:create_by,:create_date,:remark)";
        $query_insert = $conn->prepare($sql_insert);

        $query_insert->bindParam(':doc_id', $doc_id, PDO::PARAM_STR);
        $query_insert->bindParam(':doc_date', $doc_date, PDO::PARAM_STR);
        $query_insert->bindParam(':line_no', $result_sqlsvr["TRD_SEQ"], PDO::PARAM_INT);
        $query_insert->bindParam(':product_id', $result_sqlsvr["TRD_SH_CODE"], PDO::PARAM_STR);
        $query_insert->bindParam(':qty', $result_sqlsvr["TRD_SH_QTY"], PDO::PARAM_STR);
        $query_insert->bindParam(':wh_org', $result_sqlsvr["WL_CODE"], PDO::PARAM_STR);
        $query_insert->bindParam(':wh_to', $result_sqlsvr["WL_CODE_TO"], PDO::PARAM_STR);
        $query_insert->bindParam(':doc_user_id', $result_sqlsvr["DT_DOCCODE"], PDO::PARAM_STR);
        $query_insert->bindParam(':seq_record', $seq_record, PDO::PARAM_STR);
        $query_insert->bindParam(':create_by', $result_sqlsvr["DI_CRE_BY"], PDO::PARAM_STR);
        $query_insert->bindParam(':create_date', $result_sqlsvr["DI_CRE_DATE"], PDO::PARAM_STR);
        $query_insert->bindParam(':remark', $result_sqlsvr["DI_REMARK"], PDO::PARAM_STR);
        $query_insert->execute();

        $lastInsertId = $conn->lastInsertId();

        if ($lastInsertId) {
            echo "Insert Save OK " . $result_sqlsvr["DI_REF"] . " | " . $result_sqlsvr["TRD_SEQ"] . " | " . $result_sqlsvr["TRD_SH_CODE"] . "\n\r";
        } else {
            echo "Error";
        }
    }
}

$conn_sqlsvr = null;

