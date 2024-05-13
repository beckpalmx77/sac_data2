<?php

ini_set('display_errors', 1);
error_reporting(~0);

include("../config/connect_sqlserver.php");
include("../config/connect_db_sac.php");

include('../cond_file/doc_info_receive_products.php');
include('../util/month_util.php');

echo "Today is " . date("Y/m/d");
echo "\n\r" . date("Y/m/d", strtotime("yesterday"));

$query_year = " AND DI_DATE BETWEEN '" . date("Y/m/d", strtotime("yesterday")) . "' AND '" . date("Y/m/d") . "'";
//$query_year = " AND DI_DATE BETWEEN '2023/01/01' AND '2024/05/31'";
//$query_year = " AND DI_DATE BETWEEN '2000/01/01' AND '" . date("Y/m/d") . "'";

$sql_sqlsvr = $str_query_select . $str_query_from . $str_query_where . $query_year . $str_query_order;

echo $sql_sqlsvr;
/*
$myfile = fopen("qry_file_mssql_server.txt", "w") or die("Unable to open file!");
fwrite($myfile, $sql_sqlsvr);
fclose($myfile);
*/

$insert_data = "";
$update_data = "";

$res = "";

$stmt_sqlsvr = $conn_sqlsvr->prepare($sql_sqlsvr);
$stmt_sqlsvr->execute();

$return_arr = array();

while ($result_sqlsvr = $stmt_sqlsvr->fetch(PDO::FETCH_ASSOC)) {

    $sql_find = "SELECT * FROM ims_product_receive_sac "
        . " WHERE DI_KEY = '" . $result_sqlsvr["DI_KEY"]
        . "' AND DI_REF = '" . $result_sqlsvr["DI_REF"]
        . "' AND DI_DATE = '" . $result_sqlsvr["DI_DATE"]
        . "' AND DT_DOCCODE = '" . $result_sqlsvr["DT_DOCCODE"]
        . "' AND TRD_SEQ = '" . $result_sqlsvr["TRD_SEQ"] . "'";
    /*
        $myfile = fopen("qry_file_mssql_server1.txt", "w") or die("Unable to open file!");
        fwrite($myfile, $sql_find);
        fclose($myfile);
    */

    $nRows = $conn_sac->query($sql_find)->fetchColumn();

    if ($nRows > 0) {

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

        $query = $conn_sac->prepare($sql_update);

        $query->bindParam(':DI_DAY', $result_sqlsvr["DI_DAY"], PDO::PARAM_STR);
        $query->bindParam(':DI_MONTH', $result_sqlsvr["DI_MONTH"], PDO::PARAM_STR);
        $query->bindParam(':DI_MONTH_NAME', $month_arr[$result_sqlsvr["DI_MONTH"]], PDO::PARAM_STR);
        $query->bindParam(':DI_YEAR', $result_sqlsvr["DI_YEAR"], PDO::PARAM_STR);

        $query->bindParam(':TRD_QTY', $result_sqlsvr["TRD_QTY"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_SH_QTY', $result_sqlsvr["TRD_SH_QTY"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_Q_FREE', $result_sqlsvr["TRD_Q_FREE"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_SH_UPRC', $result_sqlsvr["TRD_SH_UPRC"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_G_KEYIN', $result_sqlsvr["TRD_G_KEYIN"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_DSC_KEYIN', $result_sqlsvr["TRD_DSC_KEYIN"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_DSC_KEYINV', $result_sqlsvr["TRD_DSC_KEYINV"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_TDSC_KEYINV', $result_sqlsvr["TRD_TDSC_KEYINV"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_U_PRC', $result_sqlsvr["TRD_U_PRC"],  PDO::PARAM_STR);

        $query->bindParam(':TRD_G_SELL', $result_sqlsvr["TRD_G_SELL"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_G_VAT', $result_sqlsvr["TRD_G_VAT"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_G_AMT', $result_sqlsvr["TRD_G_AMT"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_B_SELL', $result_sqlsvr["TRD_B_SELL"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_B_VAT', $result_sqlsvr["TRD_B_VAT"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_B_AMT', $result_sqlsvr["TRD_B_AMT"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_VAT_TY', $result_sqlsvr["TRD_VAT_TY"],  PDO::PARAM_STR);

        $query->bindParam(':TRD_UTQNAME', $result_sqlsvr["TRD_UTQNAME"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_UTQQTY', $result_sqlsvr["TRD_UTQQTY"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_VAT_R', $result_sqlsvr["TRD_VAT_R"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_REFER_REF', $result_sqlsvr["TRD_REFER_REF"],  PDO::PARAM_STR);

        $query->bindParam(':VAT_RATE', $result_sqlsvr["VAT_RATE"],  PDO::PARAM_STR);
        $query->bindParam(':VAT_REF', $result_sqlsvr["VAT_REF"],  PDO::PARAM_STR);
        $query->bindParam(':VAT_DATE', $result_sqlsvr["VAT_DATE"],  PDO::PARAM_STR);
        $query->bindParam(':APD_G_SV', $result_sqlsvr["APD_G_SV"],  PDO::PARAM_STR);
        $query->bindParam(':APD_G_SNV', $result_sqlsvr["APD_G_SNV"],  PDO::PARAM_STR);
        $query->bindParam(':APD_G_VAT', $result_sqlsvr["APD_G_VAT"],  PDO::PARAM_STR);
        $query->bindParam(':APD_B_SV', $result_sqlsvr["APD_B_SV"],  PDO::PARAM_STR);
        $query->bindParam(':APD_B_SNV', $result_sqlsvr["APD_B_SNV"],  PDO::PARAM_STR);
        $query->bindParam(':APD_B_VAT', $result_sqlsvr["APD_B_VAT"],  PDO::PARAM_STR);
        $query->bindParam(':APD_B_AMT', $result_sqlsvr["APD_B_AMT"],  PDO::PARAM_STR);
        $query->bindParam(':APD_G_KEYIN', $result_sqlsvr["APD_G_KEYIN"],  PDO::PARAM_STR);
        $query->bindParam(':TRH_N_QTY', $result_sqlsvr["TRH_N_QTY"],  PDO::PARAM_STR);
        $query->bindParam(':TRH_N_ITEMS', $result_sqlsvr["TRH_N_ITEMS"],  PDO::PARAM_STR);
        $query->bindParam(':APD_TDSC_KEYIN', $result_sqlsvr["APD_TDSC_KEYIN"],  PDO::PARAM_STR);
        $query->bindParam(':APD_TDSC_KEYINV', $result_sqlsvr["APD_TDSC_KEYINV"],  PDO::PARAM_STR);

        $query->bindParam(':WH_CODE', $result_sqlsvr["WH_CODE"],  PDO::PARAM_STR);
        $query->bindParam(':WH_NAME', $result_sqlsvr["WH_NAME"],  PDO::PARAM_STR);
        $query->bindParam(':WL_CODE', $result_sqlsvr["WL_CODE"],  PDO::PARAM_STR);
        $query->bindParam(':WL_NAME', $result_sqlsvr["WL_NAME"],  PDO::PARAM_STR);

        $query->bindParam(':DI_ACTIVE', $result_sqlsvr["DI_ACTIVE"],  PDO::PARAM_STR);
        $query->bindParam(':DI_KEY', $result_sqlsvr["DI_KEY"],  PDO::PARAM_STR);
        $query->bindParam(':DI_REF', $result_sqlsvr["DI_REF"],  PDO::PARAM_STR);
        $query->bindParam(':DI_DATE', $result_sqlsvr["DI_DATE"],  PDO::PARAM_STR);
        $query->bindParam(':DT_DOCCODE', $result_sqlsvr["DT_DOCCODE"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_SEQ', $result_sqlsvr["TRD_SEQ"],  PDO::PARAM_STR);

        $query->execute();

        $update_data .= "Update OK = " . $result_sqlsvr["DI_DATE"] . ":" . $result_sqlsvr["DI_REF"] . " |- " . $result_sqlsvr["TRD_SH_CODE"]
            . " |- " . $result_sqlsvr["TRD_SH_UPRC"] . " |- " . $result_sqlsvr["TRD_QTY"]
            . " |- " . $result_sqlsvr["DI_ACTIVE"] . "\n\r";

        echo " UPDATE DATA " . $update_data . "\n\r";

        //$myfile = fopen("update_chk.txt", "w") or die("Unable to open file!");
        //fwrite($myfile, $update_data);
        //fclose($myfile);

    } else {

        $sql = " INSERT INTO ims_product_receive_sac (DI_REF,DI_DATE,DI_DAY,DI_MONTH,DI_MONTH_NAME,DI_YEAR,DI_CRE_BY,AP_CODE,AP_NAME,APCAT_CODE,APCAT_NAME,APCD_NAME,APD_DUE_DA,APD_CHQ_DA
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

        /*
        $myfile = fopen("insert_sql.txt", "w") or die("Unable to open file!");
        fwrite($myfile, $sql);
        fclose($myfile);
        */

        $query = $conn_sac->prepare($sql);

        $query->bindParam(':DI_REF', $result_sqlsvr["DI_REF"],  PDO::PARAM_STR);
        $query->bindParam(':DI_DATE', $result_sqlsvr["DI_DATE"],  PDO::PARAM_STR);
        $query->bindParam(':DI_DAY', $result_sqlsvr["DI_DAY"], PDO::PARAM_STR);
        $query->bindParam(':DI_MONTH', $result_sqlsvr["DI_MONTH"], PDO::PARAM_STR);
        $query->bindParam(':DI_MONTH_NAME', $month_arr[$result_sqlsvr["DI_MONTH"]], PDO::PARAM_STR);
        $query->bindParam(':DI_YEAR', $result_sqlsvr["DI_YEAR"], PDO::PARAM_STR);
        $query->bindParam(':DI_CRE_BY', $result_sqlsvr["DI_CRE_BY"],  PDO::PARAM_STR);
        $query->bindParam(':AP_CODE', $result_sqlsvr["AP_CODE"],  PDO::PARAM_STR);
        $query->bindParam(':AP_NAME', $result_sqlsvr["AP_NAME"],  PDO::PARAM_STR);
        $query->bindParam(':APCAT_CODE', $result_sqlsvr["APCAT_CODE"],  PDO::PARAM_STR);
        $query->bindParam(':APCAT_NAME', $result_sqlsvr["APCAT_NAME"],  PDO::PARAM_STR);
        $query->bindParam(':APCD_NAME', $result_sqlsvr["APCD_NAME"],  PDO::PARAM_STR);
        $query->bindParam(':APD_DUE_DA', $result_sqlsvr["APD_DUE_DA"],  PDO::PARAM_STR);
        $query->bindParam(':APD_CHQ_DA', $result_sqlsvr["APD_CHQ_DA"],  PDO::PARAM_STR);
        $query->bindParam(':TRH_SHIP_DATE', $result_sqlsvr["TRH_SHIP_DATE"],  PDO::PARAM_STR);
        $query->bindParam(':SB_NAME', $result_sqlsvr["SB_NAME"],  PDO::PARAM_STR);
        $query->bindParam(':DEPT_CODE', $result_sqlsvr["DEPT_CODE"],  PDO::PARAM_STR);
        $query->bindParam(':DEPT_THAIDESC', $result_sqlsvr["DEPT_THAIDESC"],  PDO::PARAM_STR);
        $query->bindParam(':DEPT_ENGDESC', $result_sqlsvr["DEPT_ENGDESC"],  PDO::PARAM_STR);
        $query->bindParam(':PRJ_CODE', $result_sqlsvr["PRJ_CODE"],  PDO::PARAM_STR);
        $query->bindParam(':PRJ_NAME', $result_sqlsvr["PRJ_NAME"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_SH_CODE', $result_sqlsvr["TRD_SH_CODE"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_SH_NAME', $result_sqlsvr["TRD_SH_NAME"],  PDO::PARAM_STR);
        $query->bindParam(':BRN_CODE', $result_sqlsvr["BRN_CODE"],  PDO::PARAM_STR);
        $query->bindParam(':BRN_NAME', $result_sqlsvr["BRN_NAME"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_LOT_NO', $result_sqlsvr["TRD_LOT_NO"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_SERIAL', $result_sqlsvr["TRD_SERIAL"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_QTY', $result_sqlsvr["TRD_QTY"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_SH_QTY', $result_sqlsvr["TRD_SH_QTY"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_Q_FREE', $result_sqlsvr["TRD_Q_FREE"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_SH_UPRC', $result_sqlsvr["TRD_SH_UPRC"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_G_KEYIN', $result_sqlsvr["TRD_G_KEYIN"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_DSC_KEYIN', $result_sqlsvr["TRD_DSC_KEYIN"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_DSC_KEYINV', $result_sqlsvr["TRD_DSC_KEYINV"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_TDSC_KEYINV', $result_sqlsvr["TRD_TDSC_KEYINV"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_U_PRC', $result_sqlsvr["TRD_U_PRC"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_G_SELL', $result_sqlsvr["TRD_G_SELL"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_G_VAT', $result_sqlsvr["TRD_G_VAT"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_G_AMT', $result_sqlsvr["TRD_G_AMT"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_B_SELL', $result_sqlsvr["TRD_B_SELL"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_B_VAT', $result_sqlsvr["TRD_B_VAT"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_B_AMT', $result_sqlsvr["TRD_B_AMT"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_VAT_TY', $result_sqlsvr["TRD_VAT_TY"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_UTQNAME', $result_sqlsvr["TRD_UTQNAME"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_UTQQTY', $result_sqlsvr["TRD_UTQQTY"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_VAT_R', $result_sqlsvr["TRD_VAT_R"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_REFER_REF', $result_sqlsvr["TRD_REFER_REF"],  PDO::PARAM_STR);
        $query->bindParam(':VAT_RATE', $result_sqlsvr["VAT_RATE"],  PDO::PARAM_STR);
        $query->bindParam(':VAT_REF', $result_sqlsvr["VAT_REF"],  PDO::PARAM_STR);
        $query->bindParam(':VAT_DATE', $result_sqlsvr["VAT_DATE"],  PDO::PARAM_STR);
        $query->bindParam(':APD_G_SV', $result_sqlsvr["APD_G_SV"],  PDO::PARAM_STR);
        $query->bindParam(':APD_G_SNV', $result_sqlsvr["APD_G_SNV"],  PDO::PARAM_STR);
        $query->bindParam(':APD_G_VAT', $result_sqlsvr["APD_G_VAT"],  PDO::PARAM_STR);
        $query->bindParam(':APD_B_SV', $result_sqlsvr["APD_B_SV"],  PDO::PARAM_STR);
        $query->bindParam(':APD_B_SNV', $result_sqlsvr["APD_B_SNV"],  PDO::PARAM_STR);
        $query->bindParam(':APD_B_VAT', $result_sqlsvr["APD_B_VAT"],  PDO::PARAM_STR);
        $query->bindParam(':APD_B_AMT', $result_sqlsvr["APD_B_AMT"],  PDO::PARAM_STR);
        $query->bindParam(':APD_G_KEYIN', $result_sqlsvr["APD_G_KEYIN"],  PDO::PARAM_STR);
        $query->bindParam(':TRH_N_QTY', $result_sqlsvr["TRH_N_QTY"],  PDO::PARAM_STR);
        $query->bindParam(':TRH_N_ITEMS', $result_sqlsvr["TRH_N_ITEMS"],  PDO::PARAM_STR);
        $query->bindParam(':APD_TDSC_KEYIN', $result_sqlsvr["APD_TDSC_KEYIN"],  PDO::PARAM_STR);
        $query->bindParam(':APD_TDSC_KEYINV', $result_sqlsvr["APD_TDSC_KEYINV"],  PDO::PARAM_STR);
        $query->bindParam(':WH_CODE', $result_sqlsvr["WH_CODE"],  PDO::PARAM_STR);
        $query->bindParam(':WH_NAME', $result_sqlsvr["WH_NAME"],  PDO::PARAM_STR);
        $query->bindParam(':WL_CODE', $result_sqlsvr["WL_CODE"],  PDO::PARAM_STR);
        $query->bindParam(':WL_NAME', $result_sqlsvr["WL_NAME"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_SH_REMARK', $result_sqlsvr["TRD_SH_REMARK"],  PDO::PARAM_STR);
        $query->bindParam(':APD_BIL_DA', $result_sqlsvr["APD_BIL_DA"],  PDO::PARAM_STR);
        $query->bindParam(':BR_CODE', $result_sqlsvr["BR_CODE"],  PDO::PARAM_STR);
        $query->bindParam(':DI_ACTIVE', $result_sqlsvr["DI_ACTIVE"],  PDO::PARAM_STR);
        $query->bindParam(':TRD_SEQ', $result_sqlsvr["TRD_SEQ"],  PDO::PARAM_STR);
        $query->bindParam(':DI_KEY', $result_sqlsvr["DI_KEY"],  PDO::PARAM_STR);
        $query->bindParam(':DT_DOCCODE', $result_sqlsvr["DT_DOCCODE"],  PDO::PARAM_STR);


        $query->execute();

        $lastInsertId = $conn_sac->lastInsertId();

        if ($lastInsertId) {
            $insert_data .= "Insert = " . $result_sqlsvr["DI_DATE"] . ":" . $result_sqlsvr["DI_REF"] . " |- " . $result_sqlsvr["TRD_SH_CODE"]
                . " |- " . $result_sqlsvr["TRD_SH_UPRC"] . " |- " . $result_sqlsvr["TRD_QTY"]
                . " |- " . $result_sqlsvr["DI_ACTIVE"] . "\n\r";
            echo "  INSERT OK = " . $insert_data . "\n\r";
        } else {
            echo " Error ";
        }

    }

}

$conn_sqlsvr = null;

