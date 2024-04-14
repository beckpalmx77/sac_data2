<?php

ini_set('display_errors', 1);
error_reporting(~0);

include("../config/connect_sqlserver.php");
include("../config/connect_db.php");

include('../cond_file/doc_info_sale_daily_sac_all.php');
include('../util/month_util.php');


$DT_DOCCODE_MINUS = "IS";

$str_doc1 = array("DS02", "IS01", "IS02", "IV01");
$str_doc2 = array("2");
$str_doc3 = array("CCS6", "CCS7", "DDS5", "IC5", "IC6", "IIS5", "IIS6", "IV3");

$str_group1 = array("1SAC01", "1SAC02", "1SAC03", "1SAC04", "1SAC05", "1SAC06", "1SAC07", "1SAC08", "1SAC09", "1SAC10", "1SAC11", "1SAC12", "1SAC13", "1SAC14", "2SAC01", "2SAC02", "2SAC03", "2SAC04", "2SAC05", "2SAC06", "2SAC07", "2SAC08", "2SAC09", "2SAC10", "2SAC11", "2SAC12", "2SAC13", "2SAC14", "2SAC15", "3SAC01", "3SAC02", "3SAC03", "3SAC04", "3SAC05", "3SAC06", "4SAC01", "4SAC02", "4SAC03", "4SAC04", "4SAC05", "4SAC06");
$str_group2 = array("5SAC01", "5SAC02", "6SAC08", "8CPA01-001", "8CPA01-002", "8SAC09", "8BTCA01-002", "8BTCA01-001");
$str_group3 = array("TATA-004", "999-08", "999-07", "999-14");
$str_group4 = array("SAC08", "TATA-003", "10SAC12");

echo "Today is " . date("Y/m/d") . "\n\r";
echo "Yesterday is " . date("Y/m/d", strtotime("yesterday")) . "\n\r";


$query_daily_cond_ext = " AND (DOCTYPE.DT_DOCCODE in ('2','DS02','IS01','IS02','IV01','IV3','DDS5','CCS6','CCS7','IC5','IC6','IIS5','IIS6')) ";

//$query_year = " AND DI_DATE BETWEEN '" . date("Y/m/d", strtotime("yesterday")) . "' AND '" . date("Y/m/d") . "'";
//$query_year = " AND DI_DATE BETWEEN '2000/01/01' AND '2023/12/31'";
//$query_year = " AND DI_DATE BETWEEN '2022/05/15' AND '" . date("Y/m/d") . "'";
//$query_year = " AND DI_DATE BETWEEN '2022/08/21' AND '" . date("Y/m/d") . "'";

// $query_year = " AND DI_DATE BETWEEN '1900/01/01' AND '" . date("Y/m/d") . "'";

// $query_year = " AND DI_DATE BETWEEN '1900/01/01' AND '2023/12/31' ";

$query_year = " AND DI_DATE BETWEEN '2022/11/01' AND '2023/12/31'";

echo "Host = " . $host . "\n\r";

$sql_sqlsvr = $select_query_daily . $select_query_daily_cond . $query_daily_cond_ext . $query_year . $select_query_daily_order;

/*
$myfile = fopen("qry_file_mssql_server.txt", "w") or die("Unable to open file!");
fwrite($myfile, $sql_sqlsvr);
fclose($myfile);
*/


/*
 select * from ims_product_sale_sac
    order by
        STR_TO_DATE(DI_DATE, '%m/%d/%Y') desc
 */

$insert_data = "";
$update_data = "";

$res = "";

$stmt_sqlsvr = $conn_sqlsvr->prepare($sql_sqlsvr);
$stmt_sqlsvr->execute();

$return_arr = array();

while ($result_sqlsvr = $stmt_sqlsvr->fetch(PDO::FETCH_ASSOC)) {

/*
    $ICCAT_CODE = "";
    $DT_DOCCODE = $result_sqlsvr["DT_DOCCODE"];
    $ICCAT_CODE = $result_sqlsvr["ICCAT_CODE"];
*/
    
/*
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
*/

    // echo "[ " . $DT_DOCCODE . "\n\r";
    // echo "[ " . $TRD_QTY . " | " . $TRD_U_PRC . " | " . $TRD_DSC_KEYINV . " | " . $TRD_B_SELL . " | " . $TRD_B_VAT . " | " . $TRD_G_KEYIN . " ]" . "\n\r";

    $res = "Rec = " . $result_sqlsvr["DI_KEY"] . " | " .  $result_sqlsvr["TRD_SEQ"] . " : " . $result_sqlsvr["DI_REF"] . "  *** " . $result_sqlsvr["DT_DOCCODE"] . " *** " . "\n\r";

    echo $res;

    //$myfile = fopen("sql_get_DATA.txt", "w") or die("Unable to open file!");
    //fwrite($myfile, "[" . $res) ;
    //fclose($myfile);


    $sql_find = "SELECT * FROM ims_product_sale_sac "
        . " WHERE DI_KEY = '" . $result_sqlsvr["DI_KEY"]
        . "' AND DI_REF = '" . $result_sqlsvr["DI_REF"]
        . "' AND DI_DATE = '" . $result_sqlsvr["DI_DATE"]
        . "' AND DT_DOCCODE = '" . $result_sqlsvr["DT_DOCCODE"]
        . "' AND TRD_SEQ = '" . $result_sqlsvr["TRD_SEQ"] . "'";

    //echo $sql_find . "\n\r";

    $nRows = $conn->query($sql_find)->fetchColumn();
    if ($nRows > 0) {

        $sql_update = " UPDATE ims_product_sale_sac  SET TRD_Q_FREE=:TRD_Q_FREE,DI_ACTIVE=:DI_ACTIVE   
        WHERE DI_KEY = :DI_KEY         
        AND DI_REF  = :DI_REF
        AND DI_DATE = :DI_DATE
        AND DT_DOCCODE = :DT_DOCCODE
        AND TRD_SEQ = :TRD_SEQ ";

        $query = $conn->prepare($sql_update);
        $query->bindParam(':TRD_Q_FREE', $result_sqlsvr["TRD_Q_FREE"], PDO::PARAM_STR);
        $query->bindParam(':DI_ACTIVE', $result_sqlsvr["DI_ACTIVE"], PDO::PARAM_STR);

        $query->bindParam(':DI_KEY', $result_sqlsvr["DI_KEY"], PDO::PARAM_STR);
        $query->bindParam(':DI_REF', $result_sqlsvr["DI_REF"], PDO::PARAM_STR);
        $query->bindParam(':DI_DATE', $result_sqlsvr["DI_DATE"], PDO::PARAM_STR);
        $query->bindParam(':DT_DOCCODE', $result_sqlsvr["DT_DOCCODE"], PDO::PARAM_STR);
        $query->bindParam(':TRD_SEQ', $result_sqlsvr["TRD_SEQ"], PDO::PARAM_STR);

        $query->execute();

        $update_data = "Update = " . $result_sqlsvr["DI_KEY"] . " | " . $result_sqlsvr["DI_DATE"] . ":" . $result_sqlsvr["DI_REF"] . " |- " . $result_sqlsvr["ICCAT_CODE"] . "\n\r";

        echo  $update_data;

        //$myfile = fopen("update_chk.txt", "w") or die("Unable to open file!");
        //fwrite($myfile, $update_data);
        //fclose($myfile);

    }

}

$conn_sqlsvr = null;

