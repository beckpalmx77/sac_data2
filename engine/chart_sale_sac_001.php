<?php
header('Content-Type: application/json');

include("../config/connect_db.php");

/*
$month_start = "1";
$month_to = "6";
$year = "2024";
$sku_cat = "ยางเล็ก";
*/

$month_start = isset($_GET['month_start']) ? $_GET['month_start'] : '';
$month_to = isset($_GET['month_to']) ? $_GET['month_to'] : '';
$year = isset($_GET['year']) ? $_GET['year'] : '';
$sku_cat = isset($_GET['skuCat']) ? $_GET['skuCat'] : '';
$sale_name = isset($_GET['sale_name']) ? $_GET['sale_name'] : '';


$sql_get = "SELECT DI_MONTH,DI_MONTH_NAME,DI_YEAR,SKU_CAT
,SUM(CAST(TRD_QTY AS DECIMAL(10,2))) as SUM_TRD_QTY
,sum(CAST(TRD_TOTAL_PRICE AS DECIMAL(10,2))) as  SUM_TRD_TOTAL_PRICE
FROM ims_data_sale_sac_all WHERE 1 ";

if (!empty($month_start) && !empty($month_to)) {
    $sql_get .= " AND DI_MONTH BETWEEN '" . $month_start . "' AND '". $month_to ."' ";
}

if (!empty($year)) {
    $sql_get .= " AND DI_YEAR = '" . $year . "' ";
}

if (!empty($sku_cat)) {
    $sql_get .= " AND SKU_CAT = '" . $sku_cat . "' ";
}

if (!empty($sale_name)) {
    $sql_get .= " AND SALE_NAME = '" . $sale_name . "' ";
}

$sql_get .= " GROUP BY DI_MONTH,DI_MONTH_NAME,DI_YEAR,SKU_CAT
ORDER BY CAST(DI_YEAR AS unsigned), CAST(DI_MONTH AS unsigned)";

$stmt = $conn->prepare($sql_get);

/*
$stmt->bindParam(':sku_cat', $sku_cat);
$stmt->bindParam(':year', $year);
*/

$stmt->execute();
$data = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($data);
