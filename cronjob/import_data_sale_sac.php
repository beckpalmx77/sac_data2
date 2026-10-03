<?php
$start_time = microtime(true);

ini_set('display_errors', 1);
error_reporting(~0);

include("../config/connect_sqlserver.php");
include("../config/connect_db.php");

include('../cond_file/doc_info_sale_daily_cp.php');
include('../util/month_util.php');


$DT_DOCCODE_MINUS = "IS";

$str_doc1 = array_flip(array("DS02", "IS01", "IS02", "IV01"));
$str_doc2 = array_flip(array("2"));
$str_doc3 = array_flip(array("CCS6", "CCS7", "DDS5", "IC5", "IC6", "IIS5", "IIS6", "IV3"));

$str_group1 = array_flip(array("1SAC01", "1SAC02", "1SAC03", "1SAC04", "1SAC05", "1SAC06", "1SAC07", "1SAC08", "1SAC09", "1SAC10", "1SAC11", "1SAC12", "1SAC13", "1SAC14", "2SAC01", "2SAC02", "2SAC03", "2SAC04", "2SAC05", "2SAC06", "2SAC07", "2SAC08", "2SAC09", "2SAC10", "2SAC11", "2SAC12", "2SAC13", "2SAC14", "2SAC15", "3SAC01", "3SAC02", "3SAC03", "3SAC04", "3SAC05", "3SAC06", "4SAC01", "4SAC02", "4SAC03", "4SAC04", "4SAC05", "4SAC06"));
$str_group2 = array_flip(array("5SAC01", "5SAC02", "6SAC08", "8CPA01-001", "8CPA01-002", "8SAC09", "8BTCA01-002", "8BTCA01-001"));
$str_group3 = array_flip(array("TATA-004", "999-08", "999-07", "999-14"));
$str_group4 = array_flip(array("SAC08", "TATA-003", "10SAC12"));

$date_yesterday = date("Y/m/d", strtotime("yesterday"));
$date_today = date("Y/m/d");

echo "Today is " . $date_today . "\n\r" ;
echo "Yesterday is " . $date_yesterday . "\n\r" ;
echo "Host - " . $host . "\n\r";


$query_daily_cond_ext = " AND (DOCTYPE.DT_DOCCODE in ('2','DS02','IS01','IS02','IV01','IV3','DDS5','CCS6','CCS7','IC5','IC6','IIS5','IIS6')) ";

$query_year = " AND DI_DATE BETWEEN '" . $date_yesterday . "' AND '" . $date_today . "'";

$sql_sqlsvr = $select_query_daily . $select_query_daily_cond . $query_daily_cond_ext . $query_year . $select_query_daily_order;

echo "\n\rMSSQL COMMAND = " . $sql_sqlsvr . "\n\r";

// 1. Prepared Statement for DB Fallback Check (Prepared ONCE outside loop)
$sql_check = "SELECT 1 FROM ims_product_sale_sac 
              WHERE DI_KEY = :DI_KEY AND DI_REF = :DI_REF 
              AND DT_DOCCODE = :DT_DOCCODE AND TRD_SEQ = :TRD_SEQ LIMIT 1";
$stmt_check = $conn->prepare($sql_check);

// 2. Pre-fetch existing record keys into memory to eliminate N+1 SELECT queries
$existing_keys = array();
$sql_preload = "SELECT DI_KEY, DI_REF, DI_DATE, DT_DOCCODE, TRD_SEQ FROM ims_product_sale_sac WHERE DI_DATE LIKE '%" . date("Y") . "%' OR DI_DATE LIKE '%" . date("Y", strtotime("yesterday")) . "%'";
$stmt_preload = $conn->query($sql_preload);
if ($stmt_preload) {
    while ($row = $stmt_preload->fetch(PDO::FETCH_ASSOC)) {
        $key = $row["DI_KEY"] . "|" . $row["DI_REF"] . "|" . trim($row["DI_DATE"]) . "|" . $row["DT_DOCCODE"] . "|" . $row["TRD_SEQ"];
        $existing_keys[$key] = true;
    }
}

// 3. Prepare UPDATE and INSERT statements ONCE outside the loop
$sql_update = " UPDATE ims_product_sale_sac SET AR_CODE=:AR_CODE,AR_NAME=:AR_NAME,SLMN_SLT=:SLMN_SLT,SLMN_CODE=:SLMN_CODE,SLMN_NAME=:SLMN_NAME
,SKU_CODE=:SKU_CODE,SKU_NAME=:SKU_NAME,SKU_CAT=:SKU_CAT,ICCAT_CODE=:ICCAT_CODE,ICCAT_NAME=:ICCAT_NAME,TRD_QTY=:TRD_QTY,TRD_U_PRC=:TRD_U_PRC
,TRD_DSC_KEYINV=:TRD_DSC_KEYINV,TRD_B_SELL=:TRD_B_SELL
,TRD_B_VAT=:TRD_B_VAT,TRD_G_KEYIN=:TRD_G_KEYIN,WL_CODE=:WL_CODE,BRANCH=:BRANCH,BRN_CODE=:BRN_CODE
,BRN_NAME=:BRN_NAME,DI_TIME_CHK=:DI_TIME_CHK,PGROUP=:PGROUP,TRD_Q_FREE=:TRD_Q_FREE,DI_ACTIVE=:DI_ACTIVE  
        WHERE DI_KEY = :DI_KEY         
        AND DI_REF  = :DI_REF
        AND DI_DATE = :DI_DATE
        AND DT_DOCCODE = :DT_DOCCODE
        AND TRD_SEQ = :TRD_SEQ ";
$stmt_update = $conn->prepare($sql_update);

$sql_insert = " INSERT INTO ims_product_sale_sac (DI_KEY,DI_REF,DI_DATE,DI_MONTH,DI_MONTH_NAME,DI_YEAR
        ,AR_CODE,AR_NAME,SLMN_SLT,SLMN_CODE,SLMN_NAME,SKU_CODE,SKU_NAME,SKU_CAT,ICCAT_CODE,ICCAT_NAME,TRD_QTY,TRD_U_PRC
        ,TRD_DSC_KEYINV,TRD_B_SELL,TRD_B_VAT,TRD_G_KEYIN,WL_CODE,BRANCH,DT_DOCCODE,TRD_SEQ,BRN_CODE,BRN_NAME,DI_TIME_CHK,PGROUP,TRD_Q_FREE,DI_ACTIVE)
        VALUES (:DI_KEY,:DI_REF,:DI_DATE,:DI_MONTH,:DI_MONTH_NAME,:DI_YEAR,:AR_CODE,:AR_NAME,:SLMN_SLT,:SLMN_CODE,:SLMN_NAME,:SKU_CODE,:SKU_NAME,:SKU_CAT
        ,:ICCAT_CODE,:ICCAT_NAME,:TRD_QTY,:TRD_U_PRC,:TRD_DSC_KEYINV,:TRD_B_SELL,:TRD_B_VAT,:TRD_G_KEYIN
        ,:WL_CODE,:BRANCH,:DT_DOCCODE,:TRD_SEQ,:BRN_CODE,:BRN_NAME,:DI_TIME_CHK,:PGROUP,:TRD_Q_FREE,:DI_ACTIVE) ";
$stmt_insert = $conn->prepare($sql_insert);

$stmt_sqlsvr = $conn_sqlsvr->prepare($sql_sqlsvr);
$stmt_sqlsvr->execute();

$inserted_count = 0;
$updated_count = 0;

try {
    $conn->beginTransaction();

    while ($result_sqlsvr = $stmt_sqlsvr->fetch(PDO::FETCH_ASSOC)) {

        $ICCAT_CODE = $result_sqlsvr["ICCAT_CODE"];
        $DT_DOCCODE = $result_sqlsvr["DT_DOCCODE"];

        $branch = "";

        if (isset($str_doc1[$DT_DOCCODE])) {
            $branch = "SAC";
        }

        if (isset($str_doc2[$DT_DOCCODE])) {
            $branch = "RQ";
        }

        if (isset($str_doc3[$DT_DOCCODE])) {
            $branch = "BTC";
        }

        if (($result_sqlsvr['DT_PROPERTIES'] == 308) || ($result_sqlsvr['DT_PROPERTIES'] == 337)) {
            $TRD_QTY = (double)$result_sqlsvr["TRD_QTY"] > 0 ? "-" . $result_sqlsvr["TRD_QTY"] : $result_sqlsvr["TRD_QTY"];
            $TRD_U_PRC = (double)$result_sqlsvr["TRD_U_PRC"] > 0 ? "-" . $result_sqlsvr["TRD_U_PRC"] : $result_sqlsvr["TRD_U_PRC"];
            $TRD_DSC_KEYINV = (double)$result_sqlsvr["TRD_DSC_KEYINV"] > 0 ? "-" . $result_sqlsvr["TRD_DSC_KEYINV"] : $result_sqlsvr["TRD_DSC_KEYINV"];
            $TRD_B_SELL = (double)$result_sqlsvr["TRD_B_SELL"] > 0 ? "-" . $result_sqlsvr["TRD_B_SELL"] : $result_sqlsvr["TRD_B_SELL"];
            $TRD_B_VAT = (double)$result_sqlsvr["TRD_B_VAT"] > 0 ? "-" . $result_sqlsvr["TRD_B_VAT"] : $result_sqlsvr["TRD_B_VAT"];
            $TRD_G_KEYIN = (double)$result_sqlsvr["TRD_G_KEYIN"] > 0 ? "-" . $result_sqlsvr["TRD_G_KEYIN"] : $result_sqlsvr["TRD_G_KEYIN"];

        } else {
            $TRD_QTY = $result_sqlsvr["TRD_QTY"];
            $TRD_U_PRC = $result_sqlsvr["TRD_U_PRC"];
            $TRD_DSC_KEYINV = $result_sqlsvr["TRD_DSC_KEYINV"];
            $TRD_B_SELL = $result_sqlsvr["TRD_B_SELL"];
            $TRD_B_VAT = $result_sqlsvr["TRD_B_VAT"];
            $TRD_G_KEYIN = $result_sqlsvr["TRD_G_KEYIN"];
        }

        $p_group = "";

        if (isset($str_group1[$ICCAT_CODE])) {
            $p_group = "P1";
        }

        if (isset($str_group2[$ICCAT_CODE])) {
            $p_group = "P2";
        }

        if (isset($str_group3[$ICCAT_CODE])) {
            $p_group = "P3";
        }

        if (isset($str_group4[$ICCAT_CODE])) {
            $p_group = "P4";
        }

        $rec_key = $result_sqlsvr["DI_KEY"] . "|" . $result_sqlsvr["DI_REF"] . "|" . trim($result_sqlsvr["DI_DATE"]) . "|" . $result_sqlsvr["DT_DOCCODE"] . "|" . $result_sqlsvr["TRD_SEQ"];

        // Check memory array first
        $exists = isset($existing_keys[$rec_key]);

        if (!$exists) {
            // Fallback check directly in DB if memory lookup missed
            $stmt_check->execute([
                ':DI_KEY' => $result_sqlsvr["DI_KEY"],
                ':DI_REF' => $result_sqlsvr["DI_REF"],
                ':DT_DOCCODE' => $result_sqlsvr["DT_DOCCODE"],
                ':TRD_SEQ' => $result_sqlsvr["TRD_SEQ"]
            ]);
            $exists = (bool)$stmt_check->fetchColumn();
        }

        if ($exists) {
            $stmt_update->execute([
                ':AR_CODE' => $result_sqlsvr["AR_CODE"],
                ':AR_NAME' => $result_sqlsvr["AR_NAME"],
                ':SLMN_SLT' => $result_sqlsvr["SLMN_SLT"],
                ':SLMN_CODE' => $result_sqlsvr["SLMN_CODE"],
                ':SLMN_NAME' => $result_sqlsvr["SLMN_NAME"],
                ':SKU_CODE' => $result_sqlsvr["SKU_CODE"],
                ':SKU_NAME' => $result_sqlsvr["SKU_NAME"],
                ':SKU_CAT' => $result_sqlsvr["ICCAT_CODE"],
                ':ICCAT_CODE' => $result_sqlsvr["ICCAT_CODE"],
                ':ICCAT_NAME' => $result_sqlsvr["ICCAT_NAME"],
                ':TRD_QTY' => $TRD_QTY,
                ':TRD_U_PRC' => $TRD_U_PRC,
                ':TRD_DSC_KEYINV' => $TRD_DSC_KEYINV,
                ':TRD_B_SELL' => $TRD_B_SELL,
                ':TRD_B_VAT' => $TRD_B_VAT,
                ':TRD_G_KEYIN' => $TRD_G_KEYIN,
                ':WL_CODE' => $result_sqlsvr["WL_CODE"],
                ':BRANCH' => $branch,
                ':BRN_CODE' => $result_sqlsvr["BRN_CODE"],
                ':BRN_NAME' => $result_sqlsvr["BRN_NAME"],
                ':DI_TIME_CHK' => $result_sqlsvr["DI_TIME_CHK"],
                ':PGROUP' => $p_group,
                ':TRD_Q_FREE' => $result_sqlsvr["TRD_Q_FREE"],
                ':DI_ACTIVE' => $result_sqlsvr["DI_ACTIVE"],
                ':DI_KEY' => $result_sqlsvr["DI_KEY"],
                ':DI_REF' => $result_sqlsvr["DI_REF"],
                ':DI_DATE' => $result_sqlsvr["DI_DATE"],
                ':DT_DOCCODE' => $result_sqlsvr["DT_DOCCODE"],
                ':TRD_SEQ' => $result_sqlsvr["TRD_SEQ"]
            ]);
            $updated_count++;
        } else {
            $month_name = isset($month_arr[$result_sqlsvr["DI_MONTH"]]) ? $month_arr[$result_sqlsvr["DI_MONTH"]] : "";
            $stmt_insert->execute([
                ':DI_KEY' => $result_sqlsvr["DI_KEY"],
                ':DI_REF' => $result_sqlsvr["DI_REF"],
                ':DI_DATE' => $result_sqlsvr["DI_DATE"],
                ':DI_MONTH' => $result_sqlsvr["DI_MONTH"],
                ':DI_MONTH_NAME' => $month_name,
                ':DI_YEAR' => $result_sqlsvr["DI_YEAR"],
                ':AR_CODE' => $result_sqlsvr["AR_CODE"],
                ':AR_NAME' => $result_sqlsvr["AR_NAME"],
                ':SLMN_SLT' => $result_sqlsvr["SLMN_SLT"],
                ':SLMN_CODE' => $result_sqlsvr["SLMN_CODE"],
                ':SLMN_NAME' => $result_sqlsvr["SLMN_NAME"],
                ':SKU_CODE' => $result_sqlsvr["SKU_CODE"],
                ':SKU_NAME' => $result_sqlsvr["SKU_NAME"],
                ':SKU_CAT' => $result_sqlsvr["ICCAT_CODE"],
                ':ICCAT_CODE' => $result_sqlsvr["ICCAT_CODE"],
                ':ICCAT_NAME' => $result_sqlsvr["ICCAT_NAME"],
                ':TRD_QTY' => $TRD_QTY,
                ':TRD_U_PRC' => $TRD_U_PRC,
                ':TRD_DSC_KEYINV' => $TRD_DSC_KEYINV,
                ':TRD_B_SELL' => $TRD_B_SELL,
                ':TRD_B_VAT' => $TRD_B_VAT,
                ':TRD_G_KEYIN' => $TRD_G_KEYIN,
                ':WL_CODE' => $result_sqlsvr["WL_CODE"],
                ':BRANCH' => $branch,
                ':DT_DOCCODE' => $DT_DOCCODE,
                ':TRD_SEQ' => $result_sqlsvr["TRD_SEQ"],
                ':BRN_CODE' => $result_sqlsvr["BRN_CODE"],
                ':BRN_NAME' => $result_sqlsvr["BRN_NAME"],
                ':DI_TIME_CHK' => $result_sqlsvr["DI_TIME_CHK"],
                ':PGROUP' => $p_group,
                ':TRD_Q_FREE' => $result_sqlsvr["TRD_Q_FREE"],
                ':DI_ACTIVE' => $result_sqlsvr["DI_ACTIVE"]
            ]);
            $existing_keys[$rec_key] = true;
            $inserted_count++;
        }

    }

    $conn->commit();
} catch (Exception $e) {
    if ($conn->inTransaction()) {
        $conn->rollBack();
    }
    echo "Error during import: " . $e->getMessage() . "\n\r";
}

$conn_sqlsvr = null;

$execution_time = round(microtime(true) - $start_time, 4);
echo "Import summary for ims_product_sale_sac:\n\r";
echo "Inserted: " . $inserted_count . " records\n\r";
echo "Updated: " . $updated_count . " records\n\r";
echo "Execution time: " . $execution_time . " seconds\n\r";


