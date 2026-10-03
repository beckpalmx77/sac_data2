<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta date="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
    <script src="js/jquery-3.6.0.js"></script>
    <!--script src="js/chartjs-2.9.0.js"></script-->
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="fontawesome/css/font-awesome.css">
</head>
<?php
// เชื่อมต่อฐานข้อมูล
include 'config/connect_db.php';

// ปีที่ต้องการแสดงผล
$selected_year = 2024;
$selected_sale_name = 'จิรกร (เตี้ยม)';
$selected_sku_cat = 'ยางเล็ก';
$selected_brand = 'LLIT';

// คำสั่ง SQL เพื่อดึงข้อมูลเฉพาะปีที่เลือก
$sql = "
    SELECT DI_YEAR, DI_MONTH, SKU_CAT, BRAND, 
           SUM(CAST(TRD_QTY AS DECIMAL(10,2))) as TRD_QTY,
           SUM(CAST(TRD_AMOUNT_PRICE AS DECIMAL(10,2))) as TRD_AMOUNT_PRICE
    FROM ims_data_sale_sac_all
    WHERE SALE_NAME = :selected_sale_name 
      AND SKU_CAT = :selected_sku_cat
      AND BRAND = :selected_brand
      AND DI_YEAR = :selected_year
    GROUP BY DI_YEAR, DI_MONTH, SKU_CAT, BRAND
    ORDER BY DI_MONTH";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':selected_sale_name', $selected_sale_name);
$stmt->bindParam(':selected_sku_cat', $selected_sku_cat);
$stmt->bindParam(':selected_brand', $selected_brand);
$stmt->bindParam(':selected_year', $selected_year);
$stmt->execute();

// จัดรูปแบบข้อมูล
$sales_data = [];
$brands = [];
$months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $month = (int)$row['DI_MONTH'];  // Convert month to integer (1-12)
    $brand = $row['BRAND'];

    // เก็บข้อมูลในรูปแบบที่สามารถเข้าถึงได้ง่าย
    $sales_data[$brand][$month] = [
        'TRD_QTY' => $row['TRD_QTY'],
        'TRD_AMOUNT_PRICE' => $row['TRD_AMOUNT_PRICE']
    ];

    // เก็บรายชื่อแบรนด์
    if (!in_array($brand, $brands)) {
        $brands[] = $brand;
    }
}

// เริ่มแสดงข้อมูลในแนวนอน (สำหรับปีที่เลือก)
echo "<table border='1'>";

// แสดงหัวตาราง (12 เดือน)
echo "<tr><th>Year</th>";
echo "<th>Brand</th>";
foreach ($months as $month_name) {
    echo "<th>" . $month_name . " (Qty)</th>";
    echo "<th>" . $month_name . " (Price)</th>";
}
echo "</tr>";

// แสดงข้อมูล Brand ในแนวตั้ง
foreach ($brands as $brand) {
    echo "<tr><td>" . $selected_year . "</td>";
    echo "<td>" . $brand . "</td>";


    // แสดงข้อมูลสำหรับ 12 เดือน
    for ($month = 1; $month <= 12; $month++) {
        if (isset($sales_data[$brand][$month])) {
            echo "<td>" . $sales_data[$brand][$month]['TRD_QTY'] . "</td>";
            echo "<td>" . $sales_data[$brand][$month]['TRD_AMOUNT_PRICE'] . "</td>";
        } else {
            echo "<td>-</td><td>-</td>";  // หากไม่มีข้อมูลสำหรับเดือนนั้น
        }
    }

    echo "</tr>";
}

echo "</table>";

?>


</html>

