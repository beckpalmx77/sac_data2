<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_reporting(0);

include '../config/connect_db.php';

isset( $_POST['doc_date_start'] ) ? $doc_date_start = $_POST['doc_date_start'] : $doc_date_start = "";
isset( $_POST['doc_date_to'] ) ? $doc_date_to = $_POST['doc_date_to'] : $doc_date_to = "";

// แปลงจากรูปแบบ DD-MM-YYYY เป็น YYYY-MM-DD
//$doc_date_start = DateTime::createFromFormat('d-m-Y', $doc_date_start)->format('Y-m-d');
//$doc_date_to = DateTime::createFromFormat('d-m-Y', $doc_date_to)->format('Y-m-d');

$doc_date_start = substr($doc_date_start,6,4) . "-" . substr($doc_date_start,3,2) . "-" . substr($doc_date_start,0,2);
$doc_date_to = substr($doc_date_to,6,4) . "-" . substr($doc_date_to,3,2) . "-" . substr($doc_date_to,0,2);

$account_type = $_SESSION['account_type'] ?? '';
$sale_name = $_SESSION['first_name'] ?? '';
$where_name = "";

if ($account_type === 'sale_user' && !empty($sale_name)) {
    $sale_name_clean = str_replace(' ', '', $sale_name);
    $where_name = " AND (a.SALE_NAME LIKE '%" . $sale_name . "%' OR REPLACE(a.SALE_NAME, ' ', '') LIKE '%" . $sale_name_clean . "%')";
}

$search_Query = "";

if (!empty($doc_date_start) && !empty($doc_date_to)) {
    $search_Query .= " AND STR_TO_DATE(a.DI_DATE, '%d-%m-%Y') BETWEEN '" . $doc_date_start . "' AND '". $doc_date_to ."' ";
}

$sql = "SELECT 
  a.DI_DATE,
  a.DI_MONTH,
  a.DI_YEAR,
  a.DI_REF,
  a.AR_CODE,
  a.AR_NAME, 
  a.SKU_CODE,
  a.SKU_NAME,
  a.SHOP_TYPE,
  a.TRD_QTY,
  p.TRD_U_POINT,
  a.TRD_QTY * p.TRD_U_POINT AS TRD_U_POINT_TOTAL,
  CASE WHEN a.SHOP_TYPE_STATUS = 'Y' THEN p.TRD_S_POINT ELSE 0 END AS TRD_S_POINT,
  CASE WHEN a.SHOP_TYPE_STATUS = 'Y' THEN a.TRD_QTY * p.TRD_S_POINT ELSE 0 END AS TRD_S_POINT_TOTAL,
  a.TRD_QTY * p.TRD_U_POINT + 
  CASE WHEN a.SHOP_TYPE_STATUS = 'Y' THEN a.TRD_QTY * p.TRD_S_POINT ELSE 0 END AS TOTAL_POINTS,
  CASE WHEN a.SHOP_TYPE_STATUS = 'Y' THEN 'Y' ELSE 'N' END AS status,
  COALESCE(p.TIRES_EDGE, '-') AS TIRES_EDGE
FROM ims_data_sale_sac_all a
LEFT JOIN ims_sac_tires_point p ON p.SKU_CODE = a.SKU_CODE
WHERE a.SKU_CODE NOT LIKE 'CL%' AND p.TIRES_EDGE LIKE 'R%'
  AND (a.SKU_CODE LIKE 'FT%' OR a.SKU_CODE LIKE 'PR%') 
  $search_Query
  $where_name
ORDER BY STR_TO_DATE(a.DI_DATE, '%d-%m-%Y') ASC, 
  CAST(a.DI_YEAR AS UNSIGNED) ASC, 
  CAST(a.DI_MONTH AS UNSIGNED) ASC, 
  CAST(a.DI_DAY AS UNSIGNED) ASC, 
  a.AR_CODE ASC, 
  a.SKU_CODE ASC; ";

/*
$myfile = fopen("a-param1.txt", "w") or die("Unable to open file!");
fwrite($myfile, $sql);
fclose($myfile);
*/


// ดำเนินการคำสั่ง SQL
$stmt = $conn->query($sql);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ส่งผลลัพธ์กลับเป็น JSON
echo json_encode($results);