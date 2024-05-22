<?php
date_default_timezone_set('Asia/Bangkok');

$myCheckValue = $_POST["myCheckValue"];
$branch = $_POST["branch"];
$month = $_POST["month"];
$year = $_POST["year"];


//$my_file = fopen("Sale_D-CP.txt", "w") or die("Unable to open file!");
//fwrite($my_file, $branch . "-" .$month . "-" .$year . " myCheck  = " . $myCheck);
//fclose($my_file);


$filename = $branch . "-" . "Total_Data_Sale_CP" . "-" . date('m/d/Y H:i:s', time()) . ".csv";

@header('Content-type: text/csv; charset=UTF-8');
@header('Content-Encoding: UTF-8');
@header("Content-Disposition: attachment; filename=" . $filename);

$select_query_daily = "  SELECT DI_REF,DT_DOCCODE,AR_NAME ,SUM(TRD_G_KEYIN) AS SUM_TOTAL,BRANCH,DI_MONTH_NAME,DI_YEAR FROM ims_product_sale_cockpit ";

if ($myCheckValue === 'Y') {
    $select_where_daily = " WHERE DI_YEAR = " . $year;
} else {
    $select_where_daily = " WHERE DI_MONTH = " . $month . " AND DI_YEAR = " . $year;
}

$select_group_order = " GROUP BY DI_REF 
ORDER BY AR_NAME,BRANCH ";


include('../config/connect_db.php');


switch ($branch) {
    case "CP-340":
        $query_daily_cond_ext = " AND (DT_DOCCODE IN ('30')) ";
        break;
    case "CP-BY":
        $query_daily_cond_ext = " AND (DT_DOCCODE IN ('S.5')) ";
        break;
    case "CP-RP":
        $query_daily_cond_ext = " AND (DT_DOCCODE IN ('S.1')) ";
        break;
    case "CP-BB":
        $query_daily_cond_ext = " AND (DT_DOCCODE IN ('S.3')) ";
        break;
    case "ALL":
        $query_daily_cond_ext = " AND (DT_DOCCODE IN ('30','S.5','S.1','S.3')) ";
        break;
}


$String_Sql = $select_query_daily . $select_where_daily . $query_daily_cond_ext . $select_group_order;

//$my_file = fopen("D-sac_str_return.txt", "w") or die("Unable to open file!");
//fwrite($my_file,$String_Sql);
//fclose($my_file);

$data = "เลขที่เอกสาร,ชื่อลูกค้า,มูลค่ารวม,สาขา,เดือน,ปี\n";

$query = $conn->prepare($String_Sql);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

if ($query->rowCount() >= 1) {
    foreach ($results as $result) {

        $data .= " " . $result->DI_REF . ",";
        $data .= " " . $result->AR_NAME . ",";
        $data .= " " . $result->SUM_TOTAL . ",";
        $data .= " " . $result->BRANCH . ",";
        $data .= " " . $result->DI_MONTH_NAME . ",";
        $data .= " " . $result->DI_YEAR . "\n";

        //$data .= str_replace(",", "^", $row['WL_CODE']) . "\n";
    }

}

$data = iconv("utf-8", "tis-620", $data);
echo $data;

exit();