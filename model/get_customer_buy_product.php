<?php
include '../config/connect_db.php';

$customer_id = $_GET['customer_id'] ?? '';
$year = $_GET['year'] ?? '';
$month = $_GET['month'] ?? '';

// SQL query to fetch SKU_CODE, SKU_NAME, BRAND, DI_YEAR, total_qty, avg_price
$sql = "SELECT SKU_CODE, SKU_NAME, BRAND, DI_YEAR, SUM(TRD_QTY) AS total_qty, AVG(TRD_PRC) AS avg_price
        FROM ims_data_sale_sac_all
        WHERE (SKU_CODE LIKE 'AT%' OR SKU_CODE LIKE 'LE%' OR SKU_CODE LIKE 'LL%') AND AR_CODE = :customer_id
        GROUP BY SKU_CODE, SKU_NAME, BRAND, DI_YEAR
        ORDER BY BRAND,SKU_CODE";

$params = [':customer_id' => $customer_id];

// Prepare and execute the SQL statement
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$sales = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Initialize an array to store the sales data by year
$sales_by_year = [];

if (empty($sales)) {
    echo "<p class='mt-4 text-danger'>ไม่พบข้อมูล</p>";
} else {
    foreach ($sales as $sale) {
        $sales_by_year[$sale['SKU_CODE']]['SKU_NAME'] = $sale['SKU_NAME'];
        $sales_by_year[$sale['SKU_CODE']]['BRAND'] = $sale['BRAND'];  // Add BRAND to the data
        $sales_by_year[$sale['SKU_CODE']]['data'][$sale['DI_YEAR']] = [
            'total_qty' => $sale['total_qty'],
            'avg_price' => $sale['avg_price'],
        ];
    }

    // Get all unique years from the sales data
    $years = array_unique(array_column($sales, 'DI_YEAR'));
    sort($years);

    echo "<h3 class='mt-4'>รายการยางซื้อที่ผ่านมา และ จำนวนที่ต้องการ</h3>";

    // **เพิ่ม ID ให้ตารางเพื่อใช้กับ DataTables**
    echo "<table id='salesTable' class='table table-bordered table-striped'>
            <thead class='table-dark'>
            <tr>
                <th>รหัสสินค้า (SKU_CODE)</th>
                <th>ชื่อสินค้า (SKU_NAME)</th>
                <th>ยี่ห้อ (BRAND)</th>";  // เพิ่มคอลัมน์ BRAND

    foreach ($years as $year) {
        echo "<th>ปี{$year} จำนวน</th>";
        echo "<th>ปี{$year} ราคา (เฉลี่ย)</th>";
    }

    //echo "<th>จำนวนที่ต้องการ</th>";
    echo "</tr></thead><tbody>";

    foreach ($sales_by_year as $sku_code => $data) {
        echo "<tr>
                <td>{$sku_code}</td>
                <td>{$data['SKU_NAME']}</td>
                <td>{$data['BRAND']}</td>";  // แสดงข้อมูล BRAND

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

        //echo "<td><input type='number' class='form-control' name='required_qty[{$sku_code}]' min='1' value='0'></td>";
        echo "</tr>";
    }

    echo "</tbody></table>";
}
?>

<!-- jQuery และ DataTables -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

<!-- CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/fixedcolumns/4.0.1/css/fixedColumns.dataTables.min.css">

<!-- JS -->
<script src="https://cdn.datatables.net/fixedcolumns/4.0.1/js/dataTables.fixedColumns.min.js"></script>

<script>
    $(document).ready(function () {
        setTimeout(function () {
            if ($('#salesTable tbody tr').length > 0) {
                $('#salesTable').DataTable({
                    "scrollX": true, // เปิดการเลื่อนแนวนอน
                    "paging": true,
                    "searching": true,
                    "ordering": true,
                    "pageLength": 5,
                    "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "ทั้งหมด"]],
                    "language": {
                        "lengthMenu": "แสดง _MENU_ รายการต่อหน้า",
                        "zeroRecords": "ไม่พบข้อมูล",
                        "info": "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
                        "infoEmpty": "ไม่มีข้อมูล",
                        "infoFiltered": "(ค้นหาจากทั้งหมด _MAX_ รายการ)",
                        "search": "ค้นหา:",
                        "paginate": {
                            "first": "หน้าแรก",
                            "last": "หน้าสุดท้าย",
                            "next": "ถัดไป",
                            "previous": "ก่อนหน้า"
                        }
                    },
                    "fixedColumns": { left: 3 } // Fix คอลัมน์แรก (SKU_CODE)
                });
            }
        }, 500);
    });

</script>

<!-- Scroll to Top Button -->
<button id="myBtn" title="Go to top" onclick="topFunction()"
        style="display: none; position: fixed; bottom: 20px; left: 30px; z-index: 99;
    font-size: 18px; border: none; outline: none; background-color: #007bff;
    color: white; cursor: pointer; border-radius: 4px; padding: 10px 15px;">
    Top
</button>

<script>
    var mybutton = document.getElementById("myBtn");

    window.onscroll = function () { scrollFunction(); };

    function scrollFunction() {
        if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
            mybutton.style.display = "block";
        } else {
            mybutton.style.display = "none";
        }
    }

    function topFunction() {
        document.body.scrollTop = 0;
        document.documentElement.scrollTop = 0;
    }
</script>

