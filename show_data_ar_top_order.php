<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {

include 'config/connect_db.php';

$year = $_POST["year"] ?? '';
$month = $_POST["month"] ?? '';
$sale_name = $_POST["SALE_NAME"] ?? '';

/*
$txt = $sale_name . "  | " .$month ;
$myfile = fopen("sale_value.txt", "w") or die("Unable to open file!");
fwrite($myfile, $sql_get);
fclose($myfile);
*/

$sql_curr_month = "SELECT * FROM ims_month WHERE month = :month";

$stmt_curr_month = $conn->prepare($sql_curr_month);
$stmt_curr_month->bindParam(':month', $month, PDO::PARAM_STR);
$stmt_curr_month->execute();
$MonthCurr = $stmt_curr_month->fetchAll();
foreach ($MonthCurr as $row_curr) {
    $month_name = $row_curr["month_name"];
}

?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ยอดขายสินค้า</title>
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
    <!--link href="img/logo/logo.png" rel="icon"-->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <style>
        /* จัดแนวให้ชิดขวาสำหรับเซลล์ที่มีตัวเลข */
        .text-right {
            text-align: right;
        }
    </style>
</head>
<body>
<input type="hidden" name="month" id="month" value="<?php echo $month; ?>">
<input type="hidden" name="month_name" id="month_name" value="<?php echo $month_name; ?>">
<input type="hidden" name="year" id="year" value="<?php echo $year; ?>">
<input type="hidden" name="sale" id="sale" value="<?php echo $sale_name; ?>">

<div class="container mt-5">
    <h3>ยอดซื้อสินค้าลูกค้า Top Order เดือน <?php echo $month_name . " ปี "  . $year . " Sale  " . $sale_name; ?></h3>
    <table class="table table-striped" id="salesTable">
        <thead>
        <tr>
            <th>ลำดับ</th>
            <th>AR_NAME</th>
            <th class="text-right">LTB</th>
            <th class="text-right">LTR</th>
            <th class="text-right">TBB</th>
            <th class="text-right">TBR</th>
            <th class="text-right">ยางเล็ก</th>
            <th class="text-right">ยอดรวมทั้งหมด</th>
        </tr>
        </thead>
        <tbody>
        <!-- ข้อมูลจะถูกเพิ่มที่นี่โดย AJAX -->
        </tbody>
    </table>
</div>

<script>
    $(document).ready(function () {
        // ฟังก์ชันเพื่อโหลดข้อมูลยอดขาย
        function loadSalesData() {
            let year = $('#year').val();
            let month = $('#month').val();
            let sale_name = $('#sale').val();

            $.ajax({
                url: 'model/fetch_sales_data.php', // เส้นทางไปยังไฟล์ PHP ที่ดึงข้อมูล
                type: 'POST',
                data: {
                    year: year,
                    month: month,
                    SALE_NAME: sale_name
                },
                dataType: 'json',
                success: function (data) {
                    let tbody = '';
                    $.each(data, function (index, item) {
                        tbody += '<tr>';
                        tbody += '<td>' + (index + 1) + '</td>'; // เพิ่มลำดับที่
                        tbody += '<td>' + item.AR_NAME + '</td>';
                        tbody += '<td class="text-right">' + formatNumber(item.LTB) + '</td>'; // จัดรูปแบบตัวเลข
                        tbody += '<td class="text-right">' + formatNumber(item.LTR) + '</td>'; // จัดรูปแบบตัวเลข
                        tbody += '<td class="text-right">' + formatNumber(item.TBB) + '</td>'; // จัดรูปแบบตัวเลข
                        tbody += '<td class="text-right">' + formatNumber(item.TBR) + '</td>'; // จัดรูปแบบตัวเลข
                        tbody += '<td class="text-right">' + formatNumber(item.ยางเล็ก) + '</td>'; // จัดรูปแบบตัวเลข
                        tbody += '<td class="text-right">' + formatNumber(item.SUM) + '</td>'; // จัดรูปแบบตัวเลข
                        tbody += '</tr>';
                    });
                    $('#salesTable tbody').html(tbody);

                    // ทำลาย DataTables ก่อนถ้ามีการใช้งานอยู่แล้ว
                    if ($.fn.DataTable.isDataTable('#salesTable')) {
                        $('#salesTable').DataTable().destroy();
                    }

                    // เรียกใช้งาน DataTables พร้อมตัวเลือก lengthMenu และการตั้งค่าต่างๆ
                    $('#salesTable').DataTable({
                        "lengthMenu": [ [10, 20, 30, 50, 100], [10, 20, 30, 50, 100] ],
                        "pageLength": 10, // ตั้งค่าเริ่มต้นให้แสดง 10 แถว
                        "destroy": true
                    });
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error('Error loading sales data: ', textStatus, errorThrown);
                }
            });
        }

        // ฟังก์ชันเพื่อจัดรูปแบบตัวเลข
        function formatNumber(num) {
            return parseFloat(num).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        // เรียกใช้ฟังก์ชันเมื่อโหลดหน้าเว็บ
        loadSalesData();
    });
</script>

</body>
</html>

<?php } ?>