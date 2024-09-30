<?php
header('Content-Type: application/json');

include("../config/connect_db.php");

$month_start = '1' ;
$month_to = '9';
$year = '2024';
$sku_cat = 'ยางเล็ก';
$sale_name = 'เมธี(เม) ผู้ใช้';
$brand = 'LLIT';

$month_start = isset($_GET['month_start']) ? $_GET['month_start'] : '';
$month_to = isset($_GET['month_to']) ? $_GET['month_to'] : '';
$year = isset($_GET['year']) ? $_GET['year'] : '';
$sku_cat = isset($_GET['skuCat']) ? $_GET['skuCat'] : '';
$brand = isset($_GET['brand']) ? $_GET['brand'] : '';
$sale_name = isset($_GET['sale_name']) ? $_GET['sale_name'] : '';

$field = "";
$group = "";
$sql_where1 = "";
$sql_where2 = "";
$sql_where3 = "";
$sql_where4 = "";
$sql_where5 = "";

if (!empty($month_start) && !empty($month_to)) {
    $sql_where1 .= " AND DI_MONTH BETWEEN '" . $month_start . "' AND '". $month_to ."' ";
}

if (!empty($year)) {
    $sql_where2 = " AND DI_YEAR = '" . $year . "' ";
}

if (!empty($sku_cat)) {
    $sql_where3 = " AND SKU_CAT = '" . $sku_cat . "' ";
}

if (!empty($sale_name)) {
    $sql_where4 = " AND SALE_NAME = '" . $sale_name . "' ";
}

if (!empty($brand)) {
    $sql_where5 = " AND BRAND = '" . $brand . "' ";
    $field = ",BRAND";
    $group = ",BRAND";
}

$sql_get = "SELECT ROW_NUMBER() OVER(ORDER BY DI_MONTH, DI_YEAR) AS RowNumber, DI_MONTH,DI_MONTH_NAME,DI_YEAR,SKU_CAT" . $field
    . ",SUM(CAST(TRD_QTY AS DECIMAL(10,2))) as SUM_TRD_QTY
,sum(CAST(TRD_TOTAL_PRICE AS DECIMAL(10,2))) as  SUM_TRD_TOTAL_PRICE
FROM ims_data_sale_sac_all WHERE 1 " . $sql_where1 . $sql_where2 . $sql_where3 . $sql_where4 . $sql_where5 ;
$sql_get .= " GROUP BY DI_MONTH,DI_MONTH_NAME,DI_YEAR,SKU_CAT" . $group
    . " ORDER BY CAST(DI_YEAR AS unsigned), CAST(DI_MONTH AS unsigned)";

/*
$txt = $sql_get;
$my_file = fopen("sale_param.txt", "w") or die("Unable to open file!");
fwrite($my_file, $txt);
fclose($my_file);
*/

$stmt = $conn->prepare($sql_get);

$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($data);

