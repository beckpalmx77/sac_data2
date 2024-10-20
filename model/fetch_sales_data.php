<?php
include '../config/connect_db.php';

$year = $_POST["year"] ?? '';
$month = $_POST["month"] ?? '';
$sale = $_POST["SALE_NAME"] ?? '';

$year = $_POST["year"] ?? '';
$year = ($year === '' || $year === '-') ? "" : $year;

$month = $_POST["month"] ?? '';
$month = ($month === '' || $month === '-') ? "" : $month;

$sale = $_POST["SALE_NAME"] ?? '';
$sale = ($sale === '' || $sale === '-') ? "" : $sale;

/*
$myfile = fopen("param.txt", "w") or die("Unable to open file!");
fwrite($myfile, "year = " . $year . "| month = " . $month . " | sale = " . $sale);
fclose($myfile);
*/

$sql = "SELECT AR_NAME,
                   SUM(CASE WHEN SKU_CAT = 'LTB' THEN CAST(TRD_AMOUNT_PRICE AS DECIMAL(10, 2)) ELSE 0 END) AS LTB,
                   SUM(CASE WHEN SKU_CAT = 'LTR' THEN CAST(TRD_AMOUNT_PRICE AS DECIMAL(10, 2)) ELSE 0 END) AS LTR,
                   SUM(CASE WHEN SKU_CAT = 'TBB' THEN CAST(TRD_AMOUNT_PRICE AS DECIMAL(10, 2)) ELSE 0 END) AS TBB,
                   SUM(CASE WHEN SKU_CAT = 'TBR' THEN CAST(TRD_AMOUNT_PRICE AS DECIMAL(10, 2)) ELSE 0 END) AS TBR,
                   SUM(CASE WHEN SKU_CAT = 'ยางเล็ก' THEN CAST(TRD_AMOUNT_PRICE AS DECIMAL(10, 2)) ELSE 0 END) AS SMT,
                   SUM(CAST(TRD_AMOUNT_PRICE AS DECIMAL(10, 2))) AS SUM
            FROM ims_data_sale_sac_all
            WHERE DI_MONTH LIKE '%" . $month . "%'
                  AND DI_YEAR LIKE '%" . $year . "%%'
                  AND SALE_NAME LIKE '%" . $sale . "%' 
                  AND SKU_CAT IN ('LTB', 'LTR', 'TBB', 'TBR', 'ยางเล็ก')
                  GROUP BY AR_NAME
                  ORDER BY SUM DESC ";

/*
$myfile = fopen("param1.txt", "w") or die("Unable to open file!");
fwrite($myfile, "year = " . $year . "| month = " . $month . " | sale = " . $sale . " | " . $sql);
fclose($myfile);
*/


/*

SELECT AR_NAME,
SUM(CASE WHEN SKU_CAT = 'LTB' THEN CAST(TRD_AMOUNT_PRICE AS DECIMAL(10, 2)) ELSE 0 END) AS LTB,
SUM(CASE WHEN SKU_CAT = 'LTR' THEN CAST(TRD_AMOUNT_PRICE AS DECIMAL(10, 2)) ELSE 0 END) AS LTR,
SUM(CASE WHEN SKU_CAT = 'TBB' THEN CAST(TRD_AMOUNT_PRICE AS DECIMAL(10, 2)) ELSE 0 END) AS TBB,
SUM(CASE WHEN SKU_CAT = 'TBR' THEN CAST(TRD_AMOUNT_PRICE AS DECIMAL(10, 2)) ELSE 0 END) AS TBR,
SUM(CASE WHEN SKU_CAT = 'ยางเล็ก' THEN CAST(TRD_AMOUNT_PRICE AS DECIMAL(10, 2)) ELSE 0 END) AS ยางเล็ก,
SUM(CAST(TRD_AMOUNT_PRICE AS DECIMAL(10, 2))) AS SUM
FROM ims_data_sale_sac_all
WHERE DI_YEAR = '2024' AND SKU_CAT IN ('LTB', 'LTR', 'TBB', 'TBR', 'ยางเล็ก')
GROUP BY AR_NAME
ORDER BY SUM DESC

*/

// ดำเนินการคำสั่ง SQL
$stmt = $conn->query($sql);
$results = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ส่งผลลัพธ์กลับเป็น JSON
echo json_encode($results);


