<?php
$start_time = microtime(true);

ini_set('display_errors', 1);
error_reporting(~0);

include("../config/connect_sqlserver.php");
include("../config/connect_db.php");

include('../cond_file/doc_info_sale_daily_cp.php');
include('../util/month_util.php');

$str_doc1 = array_flip(array("30", "CS4", "CS5", "DS4", "IS3", "IS4", "ISC3", "ISC4"));
$str_doc2 = array_flip(array("CS.8", "CS.9", "IC.3", "IC.4", "IS.3", "IS.4", "S.5", "S.6"));
$str_doc3 = array_flip(array("CS.6", "CS.7", "IC.1", "IC.2", "IS.1", "IS.2", "S.1", "S.2"));
$str_doc4 = array_flip(array("CS.2", "CS.3", "IC.5", "IC.6", "IS.5", "IS.6", "S.3", "S.4"));

$str_group1 = array_flip(array("6SAC08","2SAC01","2SAC09","2SAC11","2SAC02","2SAC06","2SAC05","2SAC04","2SAC03","2SAC12","2SAC07","2SAC08","2SAC10","2SAC13","2SAC14","2SAC15","2SAC16","2SAC17","2SAC18","2SAC19","2SAC20","2SAC21","3SAC03","1SAC10","1SAC04"));
$str_group2 = array_flip(array("5SAC02","8SAC11","5SAC01","TA01-001","8SAC09","TA01-003","8CPA01-002","8BTCA01-002","8CPA01-001","8BTCA01-001"));
$str_group3 = array_flip(array("9SA01","999-13","999-07","999-08","TATA-004"));
$str_group4 = array_flip(array("TATA-003","SAC08","10SAC12"));

$date_yesterday = date("Y/m/d", strtotime("yesterday"));
$date_today = date("Y/m/d");

$query_daily_cond_ext = " AND (DOCTYPE.DT_DOCCODE in ('30','CS4','CS5','DS4','IS3','IS4','ISC3','ISC4','CS.8','CS.9','IC.3','IC.4','IS.3','IS.4','S.5','S.6','CS.6','CS.7','IC.1','IC.2','IS.1','IS.2','S.1','S.2','CS.2','CS.3','IC.5','IC.6','IS.5','IS.6','S.3','S.4')) ";
$query_year = " AND DI_DATE BETWEEN '" . $date_yesterday . "' AND '" . $date_today . "'";

$sql_sqlsvr = $select_query_daily . $select_query_daily_cond . $query_daily_cond_ext . $query_year . $select_query_daily_order;

// Pre-fetch existing record keys into memory to eliminate N+1 SELECT queries
$existing_keys = array();
$sql_preload = "SELECT DI_KEY, DI_REF, DI_DATE, DT_DOCCODE, TRD_SEQ FROM ims_product_sale_cockpit WHERE DI_DATE BETWEEN '" . $date_yesterday . "' AND '" . $date_today . "'";
$stmt_preload = $conn->query($sql_preload);
if ($stmt_preload) {
    while ($row = $stmt_preload->fetch(PDO::FETCH_ASSOC)) {
        $key = $row["DI_KEY"] . "|" . $row["DI_REF"] . "|" . $row["DI_DATE"] . "|" . $row["DT_DOCCODE"] . "|" . $row["TRD_SEQ"];
        $existing_keys[$key] = true;
    }
}

// Prepare CHECK statement ONCE outside loop
$sql_check = "SELECT 1 FROM ims_product_sale_cockpit WHERE DI_KEY = :DI_KEY AND DI_REF = :DI_REF AND DI_DATE = :DI_DATE AND DT_DOCCODE = :DT_DOCCODE AND TRD_SEQ = :TRD_SEQ LIMIT 1";
$stmt_check = $conn->prepare($sql_check);

// Prepare UPDATE statement ONCE outside loop
$sql_update = " UPDATE ims_product_sale_cockpit SET AR_CODE=:AR_CODE,AR_NAME=:AR_NAME,SLMN_CODE=:SLMN_CODE,SLMN_NAME=:SLMN_NAME
,SKU_CODE=:SKU_CODE,SKU_NAME=:SKU_NAME,SKU_CAT=:SKU_CAT,ICCAT_CODE=:ICCAT_CODE,ICCAT_NAME=:ICCAT_NAME,TRD_QTY=:TRD_QTY,TRD_U_PRC=:TRD_U_PRC
,TRD_DSC_KEYINV=:TRD_DSC_KEYINV,TRD_B_SELL=:TRD_B_SELL
,TRD_B_VAT=:TRD_B_VAT,TRD_G_KEYIN=:TRD_G_KEYIN,WL_CODE=:WL_CODE,BRANCH=:BRANCH,BRN_CODE=:BRN_CODE
,BRN_NAME=:BRN_NAME,DI_TIME_CHK=:DI_TIME_CHK,PGROUP=:PGROUP  
        WHERE DI_KEY = :DI_KEY         
        AND DI_REF  = :DI_REF
        AND DI_DATE = :DI_DATE
        AND DT_DOCCODE = :DT_DOCCODE
        AND TRD_SEQ = :TRD_SEQ ";
$stmt_update = $conn->prepare($sql_update);

// Prepare INSERT statement ONCE outside loop
$sql_insert = " INSERT INTO ims_product_sale_cockpit (DI_KEY,DI_REF,DI_DATE,DI_MONTH,DI_MONTH_NAME,DI_YEAR
        ,AR_CODE,AR_NAME,SLMN_CODE,SLMN_NAME,SKU_CODE,SKU_NAME,SKU_CAT,ICCAT_CODE,ICCAT_NAME,TRD_QTY,TRD_U_PRC
        ,TRD_DSC_KEYINV,TRD_B_SELL,TRD_B_VAT,TRD_G_KEYIN,WL_CODE,BRANCH,DT_DOCCODE,TRD_SEQ,BRN_CODE,BRN_NAME,DI_TIME_CHK,PGROUP)
        VALUES (:DI_KEY,:DI_REF,:DI_DATE,:DI_MONTH,:DI_MONTH_NAME,:DI_YEAR,:AR_CODE,:AR_NAME,:SLMN_CODE,:SLMN_NAME,:SKU_CODE,:SKU_NAME,:SKU_CAT
        ,:ICCAT_CODE,:ICCAT_NAME,:TRD_QTY,:TRD_U_PRC,:TRD_DSC_KEYINV,:TRD_B_SELL,:TRD_B_VAT,:TRD_G_KEYIN
        ,:WL_CODE,:BRANCH,:DT_DOCCODE,:TRD_SEQ,:BRN_CODE,:BRN_NAME,:DI_TIME_CHK,:PGROUP) ";
$stmt_insert = $conn->prepare($sql_insert);

$stmt_sqlsvr = $conn_sqlsvr->prepare($sql_sqlsvr);
$stmt_sqlsvr->execute();

$inserted = 0;
$updated = 0;

try {
    $conn->beginTransaction();

    while ($result_sqlsvr = $stmt_sqlsvr->fetch(PDO::FETCH_ASSOC)) {
        $ICCAT_CODE = $result_sqlsvr["ICCAT_CODE"];
        $DT_DOCCODE = $result_sqlsvr["DT_DOCCODE"];

        $branch = "";
        if (isset($str_doc1[$DT_DOCCODE])) { $branch = "CP-340"; }
        if (isset($str_doc2[$DT_DOCCODE])) { $branch = "CP-BY"; }
        if (isset($str_doc3[$DT_DOCCODE])) { $branch = "CP-RP"; }
        if (isset($str_doc4[$DT_DOCCODE])) { $branch = "CP-BB"; }

        $p_group = "";
        if (isset($str_group1[$ICCAT_CODE])) { $p_group = "P1"; }
        if (isset($str_group2[$ICCAT_CODE])) { $p_group = "P2"; }
        if (isset($str_group3[$ICCAT_CODE])) { $p_group = "P3"; }
        if (isset($str_group4[$ICCAT_CODE])) { $p_group = "P4"; }

        $rec_key = $result_sqlsvr["DI_KEY"] . "|" . $result_sqlsvr["DI_REF"] . "|" . $result_sqlsvr["DI_DATE"] . "|" . $result_sqlsvr["DT_DOCCODE"] . "|" . $result_sqlsvr["TRD_SEQ"];

        $exists = isset($existing_keys[$rec_key]);
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
                ':AR_CODE' => $result_sqlsvr["AR_CODE"],
                ':AR_NAME' => $result_sqlsvr["AR_NAME"],
                ':SLMN_CODE' => $result_sqlsvr["SLMN_CODE"],
                ':SLMN_NAME' => $result_sqlsvr["SLMN_NAME"],
                ':SKU_CODE' => $result_sqlsvr["SKU_CODE"],
                ':SKU_NAME' => $result_sqlsvr["SKU_NAME"],
                ':SKU_CAT' => $result_sqlsvr["ICCAT_CODE"],
                ':ICCAT_CODE' => $result_sqlsvr["ICCAT_CODE"],
                ':ICCAT_NAME' => $result_sqlsvr["ICCAT_NAME"],
                ':TRD_QTY' => $result_sqlsvr["TRD_QTY"],
                ':TRD_U_PRC' => $result_sqlsvr["TRD_U_PRC"],
                ':TRD_DSC_KEYINV' => $result_sqlsvr["TRD_DSC_KEYINV"],
                ':TRD_B_SELL' => $result_sqlsvr["TRD_B_SELL"],
                ':TRD_B_VAT' => $result_sqlsvr["TRD_B_VAT"],
                ':TRD_G_KEYIN' => $result_sqlsvr["TRD_G_KEYIN"],
                ':WL_CODE' => $result_sqlsvr["WL_CODE"],
                ':BRANCH' => $branch,
                ':BRN_CODE' => $result_sqlsvr["BRN_CODE"],
                ':BRN_NAME' => $result_sqlsvr["BRN_NAME"],
                ':DI_TIME_CHK' => $result_sqlsvr["DI_TIME_CHK"],
                ':PGROUP' => $p_group,
                ':DI_KEY' => $result_sqlsvr["DI_KEY"],
                ':DI_REF' => $result_sqlsvr["DI_REF"],
                ':DI_DATE' => $result_sqlsvr["DI_DATE"],
                ':DT_DOCCODE' => $result_sqlsvr["DT_DOCCODE"],
                ':TRD_SEQ' => $result_sqlsvr["TRD_SEQ"]
            ]);
            $updated++;
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
                ':SLMN_CODE' => $result_sqlsvr["SLMN_CODE"],
                ':SLMN_NAME' => $result_sqlsvr["SLMN_NAME"],
                ':SKU_CODE' => $result_sqlsvr["SKU_CODE"],
                ':SKU_NAME' => $result_sqlsvr["SKU_NAME"],
                ':SKU_CAT' => $result_sqlsvr["ICCAT_CODE"],
                ':ICCAT_CODE' => $result_sqlsvr["ICCAT_CODE"],
                ':ICCAT_NAME' => $result_sqlsvr["ICCAT_NAME"],
                ':TRD_QTY' => $result_sqlsvr["TRD_QTY"],
                ':TRD_U_PRC' => $result_sqlsvr["TRD_U_PRC"],
                ':TRD_DSC_KEYINV' => $result_sqlsvr["TRD_DSC_KEYINV"],
                ':TRD_B_SELL' => $result_sqlsvr["TRD_B_SELL"],
                ':TRD_B_VAT' => $result_sqlsvr["TRD_B_VAT"],
                ':TRD_G_KEYIN' => $result_sqlsvr["TRD_G_KEYIN"],
                ':WL_CODE' => $result_sqlsvr["WL_CODE"],
                ':BRANCH' => $branch,
                ':DT_DOCCODE' => $DT_DOCCODE,
                ':TRD_SEQ' => $result_sqlsvr["TRD_SEQ"],
                ':BRN_CODE' => $result_sqlsvr["BRN_CODE"],
                ':BRN_NAME' => $result_sqlsvr["BRN_NAME"],
                ':DI_TIME_CHK' => $result_sqlsvr["DI_TIME_CHK"],
                ':PGROUP' => $p_group
            ]);
            $existing_keys[$rec_key] = true;
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

$conn_sqlsvr = null;

$execution_time = round(microtime(true) - $start_time, 4);
echo "Updated: " . $updated . " | Inserted: " . $inserted . " | Execution time: " . $execution_time . " seconds\n";


