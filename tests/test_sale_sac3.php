<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ยอดขายสินค้า</title>
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

<div class="container mt-5">
    <h1>ยอดขายสินค้า</h1>
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
            <th class="text-right">SUM</th>
        </tr>
        </thead>
        <tbody>
        <!-- ข้อมูลจะถูกเพิ่มที่นี่โดย AJAX -->
        </tbody>
    </table>
</div>

<script>
    $(document).ready(function() {
        // ฟังก์ชันเพื่อโหลดข้อมูลยอดขาย
        function loadSalesData() {
            $.ajax({
                url: 'fetch_sales_data.php', // เส้นทางไปยังไฟล์ PHP ที่ดึงข้อมูล
                type: 'GET',
                dataType: 'json',
                success: function(data) {
                    let tbody = '';
                    $.each(data, function(index, item) {
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

                    // เรียกใช้งาน DataTables หลังจากโหลดข้อมูล
                    $('#salesTable').DataTable();
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    console.error('Error loading sales data: ', textStatus, errorThrown);
                }
            });
        }

        // ฟังก์ชันเพื่อจัดรูปแบบตัวเลข
        function formatNumber(num) {
            return parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        // เรียกใช้ฟังก์ชันเมื่อโหลดหน้าเว็บ
        loadSalesData();
    });
</script>

</body>
</html>
