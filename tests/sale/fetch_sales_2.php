<?php

include '../../config/connect_db.php';

$customer_id = $_GET['customer_id'] ?? '';
$year = $_GET['year'] ?? '';
$month = $_GET['month'] ?? '';

$sql = "SELECT SKU_CODE,SKU_NAME, DI_YEAR, SUM(TRD_QTY) AS total_qty, AVG(TRD_PRC) AS avg_price 
        FROM ims_data_sale_sac_all 
        WHERE (SKU_CODE LIKE 'AT%' OR SKU_CODE LIKE 'LE%' OR SKU_CODE LIKE 'LL%') AND AR_CODE = :customer_id ";

$params = [':customer_id' => $customer_id];

if ($year) {
    $sql .= " AND DI_YEAR = :year";
    $params[':year'] = $year;
}

/*
if ($month) {
    $sql .= " AND DI_MONTH = :month";
    $params[':month'] = $month;
}
*/

$sql .= " GROUP BY SKU_CODE,SKU_NAME,DI_YEAR ORDER BY SKU_CODE ";
/*
$myfile = fopen("a-param.txt", "w") or die("Unable to open file!");
fwrite($myfile,  $sql . "\n\r" . $customer_id . " - " . $year . " - " . $month);
fclose($myfile);
*/
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($sales)) {
    echo "<p class='mt-4 text-danger'>ไม่พบข้อมูล</p>";
} else {
    echo "<h3 class='mt-4'>ผลลัพธ์</h3>
          <table class='table table-bordered'>
            <thead class='table-dark'>
            <tr>
                <th>รหัสสินค้า (SKU_CODE)</th>
                <th>ชื่อสินค้า (SKU_NAME)</th>                
                <th>ปี</th>
                <th>จำนวนการซื้อที่ผ่านมา</th>
                <th>ราคาเฉลี่ย</th>
                <th>จำนวนที่ต้องการ</th>
            </tr>
            </thead>
            <tbody>";
    foreach ($sales as $sale) {
        echo "<tr>
                <td>{$sale['SKU_CODE']}</td>
                <td>{$sale['SKU_NAME']}</td>                               
                <td>{$sale['DI_YEAR']}</td>
                <td>{$sale['total_qty']}</td>
                <td>" . number_format($sale['avg_price'], 2) . "</td>
                <td><input type='number' class='form-control' name='required_qty[{$sale['SKU_CODE']}]' min='1' value='0'></td>
              </tr>";
    }
    echo "</tbody></table>";
}

