<?php

$start_time = microtime(true);

ini_set('display_errors', 1);
error_reporting(~0);

include("../config/connect_sqlserver.php");
include("../config/connect_db_sac.php");

include('../cond_file/doc_info_receive_products.php');
include('../util/month_util.php');

$query_year = " AND DI_DATE BETWEEN '" . date("Y/m/d", strtotime("yesterday")) . "' AND '" . date("Y/m/d") . "'";

$sql_sqlsvr = $str_query_select . $str_query_from . $str_query_where . $query_year . $str_query_order;

$stmt_sqlsvr = $conn_sqlsvr->prepare($sql_sqlsvr);
$stmt_sqlsvr->execute();

// Pre-fetch existing record keys into PHP Memory lookup array to eliminate N+1 queries
$existing_keys = array();
$stmt_check_keys = $conn_sac->query("SELECT DI_KEY, DI_REF, DI_DATE, DT_DOCCODE, TRD_SEQ FROM ims_product_receive_sac");
while ($krow = $stmt_check_keys->fetch(PDO::FETCH_ASSOC)) {
    $composite = $krow['DI_KEY'] . '|' . $krow['DI_REF'] . '|' . $krow['DI_DATE'] . '|' . $krow['DT_DOCCODE'] . '|' . $krow['TRD_SEQ'];
    $existing_keys[$composite] = true;
}

// Move PDO prepare statements OUTSIDE the while loop
$sql_find = "SELECT COUNT(*) FROM ims_product_receive_sac WHERE DI_KEY = :DI_KEY AND DI_REF = :DI_REF AND DI_DATE = :DI_DATE AND DT_DOCCODE = :DT_DOCCODE AND TRD_SEQ = :TRD_SEQ";
$stmt_check = $conn_sac->prepare($sql_find);

$sql_update = " UPDATE ims_product_receive_sac  SET DI_DAY=:DI_DAY,DI_MONTH=:DI_MONTH,DI_MONTH_NAME=:DI_MONTH_NAME,DI_YEAR=:DI_YEAR
,TRD_QTY=:TRD_QTY,TRD_SH_QTY=:TRD_SH_QTY,TRD_Q_FREE=:TRD_Q_FREE,TRD_SH_UPRC=:TRD_SH_UPRC
,TRD_G_KEYIN=:TRD_G_KEYIN,TRD_DSC_KEYIN=:TRD_DSC_KEYIN,TRD_DSC_KEYINV=:TRD_DSC_KEYINV,TRD_TDSC_KEYINV=:TRD_TDSC_KEYINV,TRD_U_PRC=:TRD_U_PRC        
,TRD_G_SELL=:TRD_G_SELL,TRD_G_VAT=:TRD_G_VAT,TRD_G_AMT=:TRD_G_AMT,TRD_B_SELL=:TRD_B_SELL,TRD_B_VAT=:TRD_B_VAT,TRD_B_AMT=:TRD_B_AMT,TRD_VAT_TY=:TRD_VAT_TY        
,TRD_UTQNAME=:TRD_UTQNAME,TRD_UTQQTY=:TRD_UTQQTY,TRD_VAT_R=:TRD_VAT_R,TRD_REFER_REF=:TRD_REFER_REF
,VAT_RATE=:VAT_RATE,VAT_REF=:VAT_REF,VAT_DATE=:VAT_DATE        
,APD_G_SV=:APD_G_SV,APD_G_SNV=:APD_G_SNV,APD_G_VAT=:APD_G_VAT
,APD_B_SV=:APD_B_SV,APD_B_SNV=:APD_B_SNV,APD_B_VAT=:APD_B_VAT,APD_B_AMT=:APD_B_AMT        
,APD_G_KEYIN=:APD_G_KEYIN,TRH_N_QTY=:TRH_N_QTY,TRH_N_ITEMS=:TRH_N_ITEMS,APD_TDSC_KEYIN=:APD_TDSC_KEYIN,APD_TDSC_KEYINV=:APD_TDSC_KEYINV       
,WH_CODE=:WH_CODE,WH_NAME=:WH_NAME,WL_CODE=:WL_CODE,WL_NAME=:WL_NAME   
,DI_ACTIVE=:DI_ACTIVE 
WHERE DI_KEY = :DI_KEY         
AND DI_REF  = :DI_REF
AND DI_DATE = :DI_DATE
AND DT_DOCCODE = :DT_DOCCODE
AND TRD_SEQ = :TRD_SEQ ";

$query_update = $conn_sac->prepare($sql_update);

$sql_insert = " INSERT INTO ims_product_receive_sac (DI_REF,DI_DATE,DI_DAY,DI_MONTH,DI_MONTH_NAME,DI_YEAR,DI_CRE_BY,AP_CODE,AP_NAME,APCAT_CODE,APCAT_NAME,APCD_NAME,APD_DUE_DA,APD_CHQ_DA
,TRH_SHIP_DATE,SB_NAME,DEPT_CODE,DEPT_THAIDESC,DEPT_ENGDESC,PRJ_CODE,PRJ_NAME,TRD_SH_CODE,TRD_SH_NAME,BRN_CODE,BRN_NAME,TRD_LOT_NO,TRD_SERIAL,TRD_QTY
,TRD_SH_QTY,TRD_Q_FREE,TRD_SH_UPRC,TRD_G_KEYIN,TRD_DSC_KEYIN,TRD_DSC_KEYINV,TRD_TDSC_KEYINV,TRD_U_PRC,TRD_G_SELL,TRD_G_VAT,TRD_G_AMT,TRD_B_SELL,TRD_B_VAT
,TRD_B_AMT,TRD_VAT_TY,TRD_UTQNAME,TRD_UTQQTY,TRD_VAT_R,TRD_REFER_REF,VAT_RATE,VAT_REF,VAT_DATE,APD_G_SV,APD_G_SNV,APD_G_VAT,APD_B_SV,APD_B_SNV,APD_B_VAT
,APD_B_AMT,APD_G_KEYIN,TRH_N_QTY,TRH_N_ITEMS,APD_TDSC_KEYIN,APD_TDSC_KEYINV
,WH_CODE,WH_NAME,WL_CODE,WL_NAME,TRD_SH_REMARK,APD_BIL_DA,BR_CODE,DI_ACTIVE,TRD_SEQ,DI_KEY,DT_DOCCODE)
VALUES (:DI_REF,:DI_DATE,:DI_DAY,:DI_MONTH,:DI_MONTH_NAME,:DI_YEAR,:DI_CRE_BY,:AP_CODE,:AP_NAME,:APCAT_CODE,:APCAT_NAME,:APCD_NAME,:APD_DUE_DA,:APD_CHQ_DA,:TRH_SHIP_DATE
,:SB_NAME,:DEPT_CODE,:DEPT_THAIDESC,:DEPT_ENGDESC,:PRJ_CODE,:PRJ_NAME,:TRD_SH_CODE,:TRD_SH_NAME,:BRN_CODE,:BRN_NAME,:TRD_LOT_NO,:TRD_SERIAL
,:TRD_QTY,:TRD_SH_QTY,:TRD_Q_FREE,:TRD_SH_UPRC,:TRD_G_KEYIN,:TRD_DSC_KEYIN,:TRD_DSC_KEYINV,:TRD_TDSC_KEYINV,:TRD_U_PRC,:TRD_G_SELL,:TRD_G_VAT
,:TRD_G_AMT,:TRD_B_SELL,:TRD_B_VAT,:TRD_B_AMT,:TRD_VAT_TY,:TRD_UTQNAME,:TRD_UTQQTY,:TRD_VAT_R,:TRD_REFER_REF,:VAT_RATE,:VAT_REF,:VAT_DATE
,:APD_G_SV,:APD_G_SNV,:APD_G_VAT,:APD_B_SV,:APD_B_SNV,:APD_B_VAT,:APD_B_AMT,:APD_G_KEYIN,:TRH_N_QTY,:TRH_N_ITEMS,:APD_TDSC_KEYIN,:APD_TDSC_KEYINV
,:WH_CODE,:WH_NAME,:WL_CODE,:WL_NAME,:TRD_SH_REMARK,:APD_BIL_DA,:BR_CODE,:DI_ACTIVE,:TRD_SEQ,:DI_KEY,:DT_DOCCODE) ";

$query_insert = $conn_sac->prepare($sql_insert);

function format_date_dmy($date_str) {
    if (empty($date_str)) {
        return '';
    }
    $date_str = trim((string)$date_str);
    if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date_str)) {
        return $date_str;
    }
    if (preg_match('/^(\d{4})[-\/](\d{2})[-\/](\d{2})/', $date_str, $m)) {
        return $m[3] . '/' . $m[2] . '/' . $m[1];
    }
    $ts = strtotime($date_str);
    if ($ts !== false && $ts > 0) {
        return date('d/m/Y', $ts);
    }
    return $date_str;
}

$inserted_count = 0;
$updated_count = 0;

// Wrap transaction around the loop
$conn_sac->beginTransaction();
try {
    while ($result_sqlsvr = $stmt_sqlsvr->fetch(PDO::FETCH_ASSOC)) {

        $di_date = format_date_dmy($result_sqlsvr["DI_DATE"]);
        $apd_due_da = format_date_dmy($result_sqlsvr["APD_DUE_DA"]);
        $apd_chq_da = format_date_dmy($result_sqlsvr["APD_CHQ_DA"]);
        $trh_ship_date = format_date_dmy($result_sqlsvr["TRH_SHIP_DATE"]);
        $vat_date = format_date_dmy($result_sqlsvr["VAT_DATE"]);
        $apd_bil_da = format_date_dmy($result_sqlsvr["APD_BIL_DA"]);

        $composite_key = $result_sqlsvr["DI_KEY"] . '|' . $result_sqlsvr["DI_REF"] . '|' . $di_date . '|' . $result_sqlsvr["DT_DOCCODE"] . '|' . $result_sqlsvr["TRD_SEQ"];
        $month_name = isset($month_arr[$result_sqlsvr["DI_MONTH"]]) ? $month_arr[$result_sqlsvr["DI_MONTH"]] : '';

        $exists = isset($existing_keys[$composite_key]);
        if (!$exists) {
            $stmt_check->execute([
                ':DI_KEY' => $result_sqlsvr["DI_KEY"],
                ':DI_REF' => $result_sqlsvr["DI_REF"],
                ':DI_DATE' => $di_date,
                ':DT_DOCCODE' => $result_sqlsvr["DT_DOCCODE"],
                ':TRD_SEQ' => $result_sqlsvr["TRD_SEQ"]
            ]);
            $exists = (bool)$stmt_check->fetchColumn();
        }

        if ($exists) {
            $query_update->execute([
                ':DI_DAY' => $result_sqlsvr["DI_DAY"],
                ':DI_MONTH' => $result_sqlsvr["DI_MONTH"],
                ':DI_MONTH_NAME' => $month_name,
                ':DI_YEAR' => $result_sqlsvr["DI_YEAR"],
                ':TRD_QTY' => $result_sqlsvr["TRD_QTY"],
                ':TRD_SH_QTY' => $result_sqlsvr["TRD_SH_QTY"],
                ':TRD_Q_FREE' => $result_sqlsvr["TRD_Q_FREE"],
                ':TRD_SH_UPRC' => $result_sqlsvr["TRD_SH_UPRC"],
                ':TRD_G_KEYIN' => $result_sqlsvr["TRD_G_KEYIN"],
                ':TRD_DSC_KEYIN' => $result_sqlsvr["TRD_DSC_KEYIN"],
                ':TRD_DSC_KEYINV' => $result_sqlsvr["TRD_DSC_KEYINV"],
                ':TRD_TDSC_KEYINV' => $result_sqlsvr["TRD_TDSC_KEYINV"],
                ':TRD_U_PRC' => $result_sqlsvr["TRD_U_PRC"],
                ':TRD_G_SELL' => $result_sqlsvr["TRD_G_SELL"],
                ':TRD_G_VAT' => $result_sqlsvr["TRD_G_VAT"],
                ':TRD_G_AMT' => $result_sqlsvr["TRD_G_AMT"],
                ':TRD_B_SELL' => $result_sqlsvr["TRD_B_SELL"],
                ':TRD_B_VAT' => $result_sqlsvr["TRD_B_VAT"],
                ':TRD_B_AMT' => $result_sqlsvr["TRD_B_AMT"],
                ':TRD_VAT_TY' => $result_sqlsvr["TRD_VAT_TY"],
                ':TRD_UTQNAME' => $result_sqlsvr["TRD_UTQNAME"],
                ':TRD_UTQQTY' => $result_sqlsvr["TRD_UTQQTY"],
                ':TRD_VAT_R' => $result_sqlsvr["TRD_VAT_R"],
                ':TRD_REFER_REF' => $result_sqlsvr["TRD_REFER_REF"],
                ':VAT_RATE' => $result_sqlsvr["VAT_RATE"],
                ':VAT_REF' => $result_sqlsvr["VAT_REF"],
                ':VAT_DATE' => $vat_date,
                ':APD_G_SV' => $result_sqlsvr["APD_G_SV"],
                ':APD_G_SNV' => $result_sqlsvr["APD_G_SNV"],
                ':APD_G_VAT' => $result_sqlsvr["APD_G_VAT"],
                ':APD_B_SV' => $result_sqlsvr["APD_B_SV"],
                ':APD_B_SNV' => $result_sqlsvr["APD_B_SNV"],
                ':APD_B_VAT' => $result_sqlsvr["APD_B_VAT"],
                ':APD_B_AMT' => $result_sqlsvr["APD_B_AMT"],
                ':APD_G_KEYIN' => $result_sqlsvr["APD_G_KEYIN"],
                ':TRH_N_QTY' => $result_sqlsvr["TRH_N_QTY"],
                ':TRH_N_ITEMS' => $result_sqlsvr["TRH_N_ITEMS"],
                ':APD_TDSC_KEYIN' => $result_sqlsvr["APD_TDSC_KEYIN"],
                ':APD_TDSC_KEYINV' => $result_sqlsvr["APD_TDSC_KEYINV"],
                ':WH_CODE' => $result_sqlsvr["WH_CODE"],
                ':WH_NAME' => $result_sqlsvr["WH_NAME"],
                ':WL_CODE' => $result_sqlsvr["WL_CODE"],
                ':WL_NAME' => $result_sqlsvr["WL_NAME"],
                ':DI_ACTIVE' => $result_sqlsvr["DI_ACTIVE"],
                ':DI_KEY' => $result_sqlsvr["DI_KEY"],
                ':DI_REF' => $result_sqlsvr["DI_REF"],
                ':DI_DATE' => $di_date,
                ':DT_DOCCODE' => $result_sqlsvr["DT_DOCCODE"],
                ':TRD_SEQ' => $result_sqlsvr["TRD_SEQ"]
            ]);
            $updated_count++;
        } else {
            $query_insert->execute([
                ':DI_REF' => $result_sqlsvr["DI_REF"],
                ':DI_DATE' => $di_date,
                ':DI_DAY' => $result_sqlsvr["DI_DAY"],
                ':DI_MONTH' => $result_sqlsvr["DI_MONTH"],
                ':DI_MONTH_NAME' => $month_name,
                ':DI_YEAR' => $result_sqlsvr["DI_YEAR"],
                ':DI_CRE_BY' => $result_sqlsvr["DI_CRE_BY"],
                ':AP_CODE' => $result_sqlsvr["AP_CODE"],
                ':AP_NAME' => $result_sqlsvr["AP_NAME"],
                ':APCAT_CODE' => $result_sqlsvr["APCAT_CODE"],
                ':APCAT_NAME' => $result_sqlsvr["APCAT_NAME"],
                ':APCD_NAME' => $result_sqlsvr["APCD_NAME"],
                ':APD_DUE_DA' => $apd_due_da,
                ':APD_CHQ_DA' => $apd_chq_da,
                ':TRH_SHIP_DATE' => $trh_ship_date,
                ':SB_NAME' => $result_sqlsvr["SB_NAME"],
                ':DEPT_CODE' => $result_sqlsvr["DEPT_CODE"],
                ':DEPT_THAIDESC' => $result_sqlsvr["DEPT_THAIDESC"],
                ':DEPT_ENGDESC' => $result_sqlsvr["DEPT_ENGDESC"],
                ':PRJ_CODE' => $result_sqlsvr["PRJ_CODE"],
                ':PRJ_NAME' => $result_sqlsvr["PRJ_NAME"],
                ':TRD_SH_CODE' => $result_sqlsvr["TRD_SH_CODE"],
                ':TRD_SH_NAME' => $result_sqlsvr["TRD_SH_NAME"],
                ':BRN_CODE' => $result_sqlsvr["BRN_CODE"],
                ':BRN_NAME' => $result_sqlsvr["BRN_NAME"],
                ':TRD_LOT_NO' => $result_sqlsvr["TRD_LOT_NO"],
                ':TRD_SERIAL' => $result_sqlsvr["TRD_SERIAL"],
                ':TRD_QTY' => $result_sqlsvr["TRD_QTY"],
                ':TRD_SH_QTY' => $result_sqlsvr["TRD_SH_QTY"],
                ':TRD_Q_FREE' => $result_sqlsvr["TRD_Q_FREE"],
                ':TRD_SH_UPRC' => $result_sqlsvr["TRD_SH_UPRC"],
                ':TRD_G_KEYIN' => $result_sqlsvr["TRD_G_KEYIN"],
                ':TRD_DSC_KEYIN' => $result_sqlsvr["TRD_DSC_KEYIN"],
                ':TRD_DSC_KEYINV' => $result_sqlsvr["TRD_DSC_KEYINV"],
                ':TRD_TDSC_KEYINV' => $result_sqlsvr["TRD_TDSC_KEYINV"],
                ':TRD_U_PRC' => $result_sqlsvr["TRD_U_PRC"],
                ':TRD_G_SELL' => $result_sqlsvr["TRD_G_SELL"],
                ':TRD_G_VAT' => $result_sqlsvr["TRD_G_VAT"],
                ':TRD_G_AMT' => $result_sqlsvr["TRD_G_AMT"],
                ':TRD_B_SELL' => $result_sqlsvr["TRD_B_SELL"],
                ':TRD_B_VAT' => $result_sqlsvr["TRD_B_VAT"],
                ':TRD_B_AMT' => $result_sqlsvr["TRD_B_AMT"],
                ':TRD_VAT_TY' => $result_sqlsvr["TRD_VAT_TY"],
                ':TRD_UTQNAME' => $result_sqlsvr["TRD_UTQNAME"],
                ':TRD_UTQQTY' => $result_sqlsvr["TRD_UTQQTY"],
                ':TRD_VAT_R' => $result_sqlsvr["TRD_VAT_R"],
                ':TRD_REFER_REF' => $result_sqlsvr["TRD_REFER_REF"],
                ':VAT_RATE' => $result_sqlsvr["VAT_RATE"],
                ':VAT_REF' => $result_sqlsvr["VAT_REF"],
                ':VAT_DATE' => $vat_date,
                ':APD_G_SV' => $result_sqlsvr["APD_G_SV"],
                ':APD_G_SNV' => $result_sqlsvr["APD_G_SNV"],
                ':APD_G_VAT' => $result_sqlsvr["APD_G_VAT"],
                ':APD_B_SV' => $result_sqlsvr["APD_B_SV"],
                ':APD_B_SNV' => $result_sqlsvr["APD_B_SNV"],
                ':APD_B_VAT' => $result_sqlsvr["APD_B_VAT"],
                ':APD_B_AMT' => $result_sqlsvr["APD_B_AMT"],
                ':APD_G_KEYIN' => $result_sqlsvr["APD_G_KEYIN"],
                ':TRH_N_QTY' => $result_sqlsvr["TRH_N_QTY"],
                ':TRH_N_ITEMS' => $result_sqlsvr["TRH_N_ITEMS"],
                ':APD_TDSC_KEYIN' => $result_sqlsvr["APD_TDSC_KEYIN"],
                ':APD_TDSC_KEYINV' => $result_sqlsvr["APD_TDSC_KEYINV"],
                ':WH_CODE' => $result_sqlsvr["WH_CODE"],
                ':WH_NAME' => $result_sqlsvr["WH_NAME"],
                ':WL_CODE' => $result_sqlsvr["WL_CODE"],
                ':WL_NAME' => $result_sqlsvr["WL_NAME"],
                ':TRD_SH_REMARK' => $result_sqlsvr["TRD_SH_REMARK"],
                ':APD_BIL_DA' => $apd_bil_da,
                ':BR_CODE' => $result_sqlsvr["BR_CODE"],
                ':DI_ACTIVE' => $result_sqlsvr["DI_ACTIVE"],
                ':TRD_SEQ' => $result_sqlsvr["TRD_SEQ"],
                ':DI_KEY' => $result_sqlsvr["DI_KEY"],
                ':DT_DOCCODE' => $result_sqlsvr["DT_DOCCODE"]
            ]);
            $existing_keys[$composite_key] = true;
            $inserted_count++;
        }
    }
    $conn_sac->commit();
} catch (Exception $e) {
    $conn_sac->rollBack();
    echo "Error during import: " . $e->getMessage() . "\n";
}

$conn_sqlsvr = null;

$end_time = microtime(true);
$execution_time = round($end_time - $start_time, 4);

echo "Import Completed | Updated: $updated_count | Inserted: $inserted_count | Execution Time: {$execution_time} seconds\n";

