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
fwrite($myfile, "year = " . $year . "| month = " . $month);
fclose($myfile);
*/

$sql = "SELECT 
  AR_CODE, 
  AR_NAME, 
  shop_type,
  SUM(COALESCE(sum_trd_qty, 0)) AS qty_all, 
  SUM(COALESCE(sum_trd_u_point, 0)) AS u_point, 
  SUM(COALESCE(sum_trd_u_point_total, 0)) AS u_point_all, 
  SUM(COALESCE(sum_trd_s_point, 0)) AS s_point, 
  SUM(COALESCE(sum_trd_s_point_total, 0)) AS s_point_all,  
  SUM(COALESCE(total_points, 0)) AS total_points
FROM v_sac_tires_summary_point_1
WHERE 1 
  AND DI_MONTH LIKE '%" . $month . "%'" . " AND DI_YEAR LIKE '%" . $year . "%' AND status LIKE '%' GROUP BY AR_CODE;";

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


