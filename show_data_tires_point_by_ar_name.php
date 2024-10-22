<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {

include 'config/connect_db.php';

$year = $_POST["year"] ?? '';
$month = $_POST["month"] ?? '';

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
    <title>สรุปคะแนนลูกค้า</title>
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

    <style>
        /* กำหนดให้คอลัมน์ที่ 1 และ 2 อยู่คงที่ */
        .table-responsive {
            position: relative;
        }

        th:nth-child(1), td:nth-child(1) {
            position: sticky;
            left: 0;
            background-color: white; /* กำหนดสีพื้นหลังเพื่อไม่ให้ทับกับแถวอื่น */
            z-index: 999; /* กำหนดความสำคัญให้สูง */
        }
/*  ปรับให้เลื่อนไปทางขวาตามความกว้างของคอลัมน์แรก
        th:nth-child(2), td:nth-child(2) {
            position: sticky;
            left: 250px;
            background-color: white;
            z-index: 998;
        }
*/
        /* สำหรับการจัดแนวขวา */
        .text-right {
            text-align: right;
        }

        /* ป้องกันไม่ให้ข้อมูลตกบรรทัด */
        th, td {
            white-space: nowrap;
        }

        /* ปรับขนาดฟอนต์ */
        th, td {
            font-size: 14px;
            padding: 4px 8px;
        }
    </style>

</head>
<body>
<input type="hidden" name="month" id="month" value="<?php echo $month; ?>">
<input type="hidden" name="month_name" id="month_name" value="<?php echo $month_name; ?>">
<input type="hidden" name="year" id="year" value="<?php echo $year; ?>">

<div id="spinner" class="d-flex justify-content-center" style="display: none;">
    <div class="spinner-border" role="status">
        <span class="visually-hidden">Loading...</span>
    </div>
</div>

<div class="container mt-5">
    <h3>สรุปคะแนนลูกค้า ตาม Size ยาง เดือน <?php echo $month_name . " ปี "  . $year  ?></h3>
    <div class="table-responsive">
        <table class="table table-striped" id="salesTable">
            <thead>
            <tr>
                <th>ชื่อลูกค้า</th>
                <th>รายละเอียดสินค้า</th>
                <th>ขอบ</th>
                <th class="text-right">จำนวน</th>
                <th class="text-right">คะแนนร้านทั่วไป</th>
                <th class="text-right">คะแนนรวมร้านทั่วไป</th>
                <th class="text-right">คะแนน Shop</th>
                <th class="text-right">คะแนนรวม Shop</th>
                <th class="text-right">คะแนนรวมทั้งหมด</th>
            </tr>
            </thead>
            <tbody>
            <!-- ข้อมูลจะถูกเพิ่มที่นี่โดย AJAX -->
            </tbody>
        </table>
    </div>
</div>

<script>
    $(document).ready(function () {
        function loadSalesData() {
            let year = $('#year').val();
            let month = $('#month').val();
            let title = 'สรุปคะแนนลูกค้า-เดือน-' + month + "-" + year;

            $('#spinner').show();

            $.ajax({
                url: 'model/fetch_data_tires_point_ar_name.php',
                type: 'POST',
                data: {
                    year: year,
                    month: month,
                },
                dataType: 'json',
                success: function (data) {
                    let tbody = '';
                    $.each(data, function (index, item) {
                        tbody += '<tr>';
                        tbody += '<td>' + item.AR_NAME + '</td>';
                        tbody += '<td>' + item.SKU_NAME + '</td>';
                        tbody += '<td>' + item.TIRES_EDGE + '</td>';
                        tbody += '<td class="text-right">' + formatNumber(item.SUM_TRD_QTY) + '</td>';
                        tbody += '<td class="text-right">' + formatNumber(item.SUM_TRD_U_POINT) + '</td>';
                        tbody += '<td class="text-right">' + formatNumber(item.SUM_TRD_U_POINT_TOTAL) + '</td>';
                        tbody += '<td class="text-right">' + formatNumber(item.TRD_S_POINT) + '</td>';
                        tbody += '<td class="text-right">' + formatNumber(item.SUM_TRD_S_POINT_TOTAL) + '</td>';
                        tbody += '<td class="text-right">' + formatNumber(item.TOTAL_POINTS) + '</td>';
                        tbody += '</tr>';
                    });
                    $('#salesTable tbody').html(tbody);

                    $('#spinner').hide();

                    // ทำลาย DataTables ก่อนถ้ามีการใช้งานอยู่แล้ว
                    if ($.fn.DataTable.isDataTable('#salesTable')) {
                        $('#salesTable').DataTable().destroy();
                    }

                    // เรียกใช้งาน DataTables พร้อมตัวเลือก buttons สำหรับการ export
                    $('#salesTable').DataTable({
                        "lengthMenu": [ [10, 20, 30, 50, 100], [10, 20, 30, 50, 100] ],
                        "pageLength": 10,
                        "destroy": true,
                        dom: 'Bfrtip', // เพิ่มส่วนของปุ่ม
                        buttons: [
                            {
                                extend: 'excelHtml5',
                                title: title,
                                text: 'Export Excel'
                            }
                        ]
                    });
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error('Error loading sales data: ', textStatus, errorThrown);
                    $('#spinner').hide();
                }
            });
        }

        function formatNumber(num) {
            return parseFloat(num).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
        }

        loadSalesData();
    });
</script>

</body>
</html>

<?php } ?>