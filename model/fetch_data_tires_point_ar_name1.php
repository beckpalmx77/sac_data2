<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
error_reporting(0);

include '../config/connect_db.php';

$year = isset($_POST["year"]) && $_POST["year"] !== '' && $_POST["year"] !== '-' ? $_POST["year"] : "";
$month = isset($_POST["month"]) && $_POST["month"] !== '' && $_POST["month"] !== '-' ? $_POST["month"] : "";

$account_type = $_SESSION['account_type'];
$sale_name = $_SESSION['first_name'];
$where_name = "";

if ($account_type === 'sale_user' && !empty($sale_name)) {
    $sale_name_clean = str_replace(' ', '', $sale_name);
    $where_name = " AND (a.SALE_NAME LIKE '%" . $sale_name .  "%' OR REPLACE(a.SALE_NAME, ' ', '') LIKE '%" . $sale_name_clean . "%')";
}

$sql = "
SELECT 
    a.AR_CODE,
    a.AR_NAME,
    a.SKU_CODE,
    a.SKU_NAME,
    p.TIRES_EDGE,
    CASE 
        WHEN s.status = 'Y' THEN 'SHOP' 
        ELSE 'ร้านทั่วไป' 
    END AS SHOP_TYPE,
    a.TRD_QTY,
    p.TRD_U_POINT,
    a.TRD_QTY * p.TRD_U_POINT AS TRD_R_POINT,
    CASE 
        WHEN s.status = 'Y' THEN p.TRD_S_POINT 
        ELSE 0 
    END AS TRD_S_POINT,
    a.TRD_QTY * 
        CASE 
            WHEN s.status = 'Y' THEN p.TRD_S_POINT 
            ELSE 0 
        END AS TRD_T_POINT,
    (a.TRD_QTY * p.TRD_U_POINT) + 
        a.TRD_QTY * 
        CASE 
            WHEN s.status = 'Y' THEN p.TRD_S_POINT 
            ELSE 0 
        END AS TRD_TOTAL_POINT_ALL
FROM ims_data_sale_sac_all a
LEFT JOIN ims_ar_shop s ON s.ar_code = a.AR_CODE
LEFT JOIN ims_sac_tires_point p ON p.sku_code = a.SKU_CODE
WHERE 
    (a.SKU_CODE LIKE 'LL%' OR a.SKU_CODE LIKE 'LE%' OR a.SKU_CODE LIKE 'AT%') 
    AND a.SKU_CODE NOT LIKE 'CL%'
    AND p.TIRES_EDGE LIKE 'R%'    
    AND a.DI_MONTH LIKE '%" . $month . "%' 
    AND a.DI_YEAR LIKE '%" . $year . "%'" . $where_name . " ORDER BY a.AR_CODE, a.SKU_CODE, p.TIRES_EDGE; ";

//AND a.SKU_CODE NOT LIKE 'CL%'

/*
$myfile = fopen("a-param1.txt", "w") or die("Unable to open file!");
fwrite($myfile, "year = " . $year . "| month = " . $month . " | " . $sql);
fclose($myfile);
*/

// ดำเนินการคำสั่ง SQL
$stmt = $conn->query($sql);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ส่งผลลัพธ์กลับเป็น JSON
echo json_encode($results);


