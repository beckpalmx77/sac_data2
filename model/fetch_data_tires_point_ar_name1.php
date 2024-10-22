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

$sql = "SELECT 
  a.AR_CODE,
  a.AR_NAME, 
  a.SKU_CODE,
  a.SKU_NAME,
  p.TIRES_EDGE, 
  SUM(a.TRD_QTY) AS SUM_TRD_QTY,
  SUM(a.TRD_U_POINT) AS SUM_TRD_U_POINT,
  SUM(a.TRD_QTY) * SUM(a.TRD_U_POINT) AS SUM_TRD_U_POINT_TOTAL,
  CASE WHEN s.status = 'Y' THEN p.TRD_S_POINT ELSE 0 END AS TRD_S_POINT,
  CASE WHEN s.status = 'Y' THEN SUM(a.TRD_QTY) * SUM(a.TRD_S_POINT) ELSE 0 END AS SUM_TRD_S_POINT_TOTAL,
  SUM(a.TRD_QTY) * SUM(a.TRD_U_POINT) + 
  CASE WHEN s.status = 'Y' THEN SUM(a.TRD_QTY) * SUM(a.TRD_S_POINT) ELSE 0 END AS TOTAL_POINTS,
  CASE WHEN s.status = 'Y' THEN 'Y' ELSE 'N' END AS status,
  CASE WHEN s.status = 'Y' THEN 'SHOP' ELSE 'ร้านทั่วไป' END AS shop_type
    FROM ims_data_sale_sac_all a
    LEFT JOIN ims_sac_tires_point p ON p.SKU_CODE = a.SKU_CODE
    LEFT JOIN ims_ar_shop s ON s.AR_CODE = a.AR_CODE
    WHERE 1  
        AND a.DI_MONTH LIKE '%" . $month . "%'
        AND a.DI_YEAR LIKE '%" . $year . "%'        
        AND a.TRD_U_POINT > 0
        AND a.SKU_CODE NOT LIKE 'CL%'  
    GROUP BY a.AR_CODE, a.SKU_CODE
    ORDER BY a.AR_CODE, a.SKU_CODE; ";

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


