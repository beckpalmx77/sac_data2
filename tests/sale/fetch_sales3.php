<?php
include '../../config/connect_db.php';

$customer_id = $_GET['customer_id'] ?? '';
$year = $_GET['year'] ?? '';
$month = $_GET['month'] ?? '';

// SQL query to fetch SKU_CODE, SKU_NAME, DI_YEAR, total_qty, avg_price
$sql = "SELECT SKU_CODE, SKU_NAME, DI_YEAR, SUM(TRD_QTY) AS total_qty, AVG(TRD_PRC) AS avg_price
        FROM ims_data_sale_sac_all
        WHERE (SKU_CODE LIKE 'AT%' OR SKU_CODE LIKE 'LE%' OR SKU_CODE LIKE 'LL%') AND AR_CODE = :customer_id ";

$params = [':customer_id' => $customer_id];

if ($year) {
    //$sql .= " AND DI_YEAR = :year";
    //$params[':year'] = $year;
}

$sql .= " GROUP BY SKU_CODE, SKU_NAME, DI_YEAR ORDER BY SKU_CODE";

// Prepare and execute the SQL statement
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize an array to store the sales data by year
$sales_by_year = [];

if (empty($sales)) {
    echo "<p class='mt-4 text-danger'>ไม่พบข้อมูล</p>";
} else {
    // Organize the sales data by SKU_CODE
    foreach ($sales as $sale) {
        $sales_by_year[$sale['SKU_CODE']]['SKU_NAME'] = $sale['SKU_NAME'];
        $sales_by_year[$sale['SKU_CODE']]['data'][$sale['DI_YEAR']] = [
            'total_qty' => $sale['total_qty'],
            'avg_price' => $sale['avg_price'],
        ];
    }

    // Get all unique years from the sales data
    $years = array_unique(array_column($sales, 'DI_YEAR'));
    sort($years);

    // Display the table
    echo "<h3 class='mt-4'>รายการยางซื้อที่ผ่านมา และ จำนวนที่ต้องการ</h3>
          <table class='table table-bordered'>
            <thead class='table-dark'>
            <tr>
                <th>รหัสสินค้า (SKU_CODE)</th>
                <th>ชื่อสินค้า (SKU_NAME)</th>";

    // Display years with quantity and average price columns (ปี1/จำนวน, ปี1/ราคา)
    foreach ($years as $year) {
        echo "<th>ปี{$year} จำนวน</th>";
        echo "<th>ปี{$year} ราคา</th>";
    }

    echo "<th>จำนวนที่ต้องการ</th>
            </tr>
            </thead>
            <tbody>" ;

    // Display the data for each SKU_CODE
    foreach ($sales_by_year as $sku_code => $data) {
        echo "<tr>
                <td>{$sku_code}</td>
                <td>{$data['SKU_NAME']}</td>";

        // Display the quantity and average price for each year
        foreach ($years as $year) {
            if (isset($data['data'][$year])) {
                $total_qty = $data['data'][$year]['total_qty'];
                $avg_price = number_format($data['data'][$year]['avg_price'], 2);
                echo "<td>{$total_qty}</td>";
                echo "<td>{$avg_price}</td>";
            } else {
                echo "<td>-</td><td>-</td>";
            }
        }

        echo "<td><input type='number' class='form-control' name='required_qty[{$sku_code}]' min='1' value='0'></td>
              </tr>";
    }

    echo "</tbody></table>";
}
?>

<!-- Add JavaScript to show/hide and scroll to top -->
<script>
    // Get the button
    var mybutton = document.getElementById("myBtn");

    // When the user scrolls down 20px from the top of the document, show the button
    window.onscroll = function() {scrollFunction()};

    function scrollFunction() {
        if (document.body.scrollTop > 20 || document.documentElement.scrollTop > 20) {
            mybutton.style.display = "block";
        } else {
            mybutton.style.display = "none";
        }
    }

    // When the user clicks on the button, scroll to the top of the document
    function topFunction() {
        document.body.scrollTop = 0; // For Safari
        document.documentElement.scrollTop = 0; // For Chrome, Firefox, IE and Opera
    }
</script>

<!-- Scroll to Top Button -->
<button id="myBtn" title="Go to top" onclick="topFunction()" style="display: none; position: fixed; bottom: 20px; right: 30px; z-index: 99; font-size: 18px; border: none; outline: none; background-color: #007bff; color: white; cursor: pointer; border-radius: 4px; padding: 10px 15px;">Top</button>
