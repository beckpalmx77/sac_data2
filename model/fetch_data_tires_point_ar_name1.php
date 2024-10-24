<?php
include '../config/connect_db.php';

$year = $_POST["year"] ?? '';
$month = $_POST["month"] ?? '';

$year = $_POST["year"] ?? '';
$year = ($year === '' || $year === '-') ? "" : $year;

$month = $_POST["month"] ?? '';
$month = ($month === '' || $month === '-') ? "" : $month;
/*
$shop_type = $_POST["shop_type"] ?? '';
$shop_type = ($shop_type === '' || $shop_type === '-') ? "" : $shop_type;
*/
/*
$myfile = fopen("param.txt", "w") or die("Unable to open file!");
fwrite($myfile, "year = " . $year . "| month = " . $month . " | sale = " . $sale);
fclose($myfile);
*/

$sql = "SELECT a.AR_CODE,a.AR_NAME,a.SKU_CODE,a.SKU_NAME,p.TIRES_EDGE,CASE WHEN s.status = 'Y' THEN 'SHOP' ELSE 'ร้านทั่วไป' END AS SHOP_TYPE
,a.TRD_QTY,a.TRD_U_POINT,a.TRD_R_POINT ,a.TRD_S_POINT,a.TRD_T_POINT,a.TRD_R_POINT + a.TRD_T_POINT AS TRD_TOTAL_POINT_ALL
FROM `ims_data_sale_sac_all`a
LEFT JOIN ims_ar_shop s ON s.ar_code = a.AR_CODE
LEFT JOIN ims_sac_tires_point p ON p.sku_code = a.SKU_CODE
WHERE (a.SKU_CODE LIKE 'LL%' OR a.SKU_CODE LIKE 'LE%' OR a.SKU_CODE LIKE 'AT%') AND a.SKU_CODE NOT LIKE 'CL%' 
AND COALESCE(a.TRD_U_POINT,0) > 0 
AND DI_MONTH LIKE '%" . $month . "%'" . " AND DI_YEAR LIKE '%" . $year . "%'
ORDER BY AR_CODE,SKU_CODE ";

//AND a.SKU_CODE NOT LIKE 'CL%'

/*
$myfile = fopen("param1.txt", "w") or die("Unable to open file!");
fwrite($myfile, "year = " . $year . "| month = " . $month . " | " . $sql);
fclose($myfile);
*/

// ดำเนินการคำสั่ง SQL
$stmt = $conn->query($sql);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ส่งผลลัพธ์กลับเป็น JSON
echo json_encode($results);


