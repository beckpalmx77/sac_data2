<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Transactions</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.1/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.2.3/css/buttons.dataTables.min.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-5">
    <!-- ฟอร์มสำหรับเลือกช่วงวันที่ และเลือกบัญชีธนาคาร -->
    <form id="filterForm" method="POST">
        <div class="form-row">
            <div class="form-group col-md-4">
                <label for="start_date">Start Date:</label>
                <input type="date" id="start_date" name="start_date" class="form-control">
            </div>
            <div class="form-group col-md-4">
                <label for="end_date">End Date:</label>
                <input type="date" id="end_date" name="end_date" class="form-control">
            </div>
            <div class="form-group col-md-4">
                <label for="bnkac_key">Bank Account:</label>
                <select id="bnkac_key" name="bnkac_key" class="form-control">
                    <option value="106">Account 106</option>
                    <option value="107">Account 107</option>
                    <!-- เพิ่มบัญชีธนาคารอื่น ๆ ที่นี่ -->
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary">Filter</button>
    </form>

    <!-- ตารางข้อมูล -->
    <table id="transactionTable" class="table table-striped table-bordered">
        <thead>
        <tr>
            <th>BSTM_RECNL_DD</th>
            <th>BNKAC_NAME</th>
            <th>BSTM_CREDIT</th>
            <th>BSTM_DEBIT</th>
            <th>ยอดคงเหลือ</th>
            <th>BSTM_REMARK</th>
            <th>DI_DATE</th>
            <th>DI_REF</th>
            <th>CQBK_CHEQUE_DD</th>
            <th>BSTM_CHEQUE_NO</th>
        </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<!-- โหลด JavaScript libraries -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.1/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.3/jszip.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.print.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.2.3/js/buttons.flash.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.bundle.min.js"></script>

<!-- เขียน JavaScript เพื่อใช้ DataTables และดึงข้อมูล -->
<!--script>
    $(document).ready(function() {
        // Initial DataTables setup
        let table = $('#transactionTable').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": "model/fetch_bank_statement_transactions.php",
                "type": "POST",
                "data": function(d) {
                    d.start_date = $('#start_date').val();
                    d.end_date = $('#end_date').val();
                    d.bnkac_key = $('#bnkac_key').val();
                }
            },
            dom: 'Bfrtip', // Add buttons for exporting
            buttons: [
                'excel'
            ]
        });

        // เมื่อกด submit ฟอร์มให้โหลดข้อมูลใหม่
        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            table.ajax.reload();
        });
    });
</script-->

<!--script>
    $.ajax({
        "url": "model/fetch_bank_statement_transactions.php",
        "type": "POST",
        "data": function (d) {
            d.start_date = $('#start_date').val();
            d.end_date = $('#end_date').val();
            d.bnkac_key = $('#bnkac_key').val();
        },
        success: function (response) {
            console.log(response);  // ตรวจสอบ response ที่ได้จาก PHP
        },
        error: function (xhr, status, error) {
            console.log("Error: " + error);
        }
    });

</script-->

<script>
    $(document).ready(function () {
        function loadTransactionData() {
            // แสดง spinner ก่อนเริ่มการโหลด (ถ้ามี spinner)
            $('#spinner').show();

            let start_date = $('#start_date').val();
            let end_date = $('#end_date').val();
            let bnkac_key = $('#bnkac_key').val();

            // เปลี่ยน title สำหรับการ Export Excel (ถ้ามี)
            let title = 'Bank Transactions - ' + start_date + ' to ' + end_date;

            $.ajax({
                url: 'model/fetch_bank_statement_transactions.php',
                type: 'POST',
                data: {
                    start_date: start_date,
                    end_date: end_date,
                    bnkac_key: bnkac_key
                },
                dataType: 'json',
                success: function (data) {
                    let tbody = '';
                    $.each(data.data, function (index, item) {
                        tbody += '<tr>';
                        tbody += '<td>' + item.BSTM_RECNL_DD + '</td>';
                        tbody += '<td>' + item.BNKAC_NAME + '</td>';
                        tbody += '<td>' + formatNumber(item.BSTM_CREDIT) + '</td>';
                        tbody += '<td>' + formatNumber(item.BSTM_DEBIT) + '</td>';
                        tbody += '<td>' + formatNumber(item["ยอดคงเหลือ"]) + '</td>';
                        tbody += '<td>' + item.BSTM_REMARK + '</td>';
                        tbody += '<td>' + item.DI_DATE + '</td>';
                        tbody += '<td>' + item.DI_REF + '</td>';
                        tbody += '<td>' + item.CQBK_CHEQUE_DD + '</td>';
                        tbody += '<td>' + item.BSTM_CHEQUE_NO + '</td>';
                        tbody += '</tr>';
                    });
                    $('#transactionTable tbody').html(tbody);

                    // ทำลาย DataTables ก่อนถ้ามีการใช้งานอยู่แล้ว
                    if ($.fn.DataTable.isDataTable('#transactionTable')) {
                        $('#transactionTable').DataTable().destroy();
                    }

                    // เรียกใช้งาน DataTables
                    $('#transactionTable').DataTable({
                        "lengthMenu": [[15, 30, 50, 100], [15, 30, 50, 100]],
                        "pageLength": 15,
                        dom: 'Bfrtip',
                        buttons: [
                            {
                                extend: 'excelHtml5',
                                title: title,
                                text: 'Export Excel'
                            }
                        ]
                    });

                    // ซ่อน spinner เมื่อโหลดข้อมูลเสร็จ (ถ้ามี)
                    $('#spinner').hide();
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error('Error loading transaction data: ', textStatus, errorThrown);
                    // ซ่อน spinner ในกรณีเกิดข้อผิดพลาด
                    $('#spinner').hide();
                }
            });
        }

        // เมื่อกด submit ฟอร์มให้โหลดข้อมูลใหม่
        $('#filterForm').on('submit', function (e) {
            e.preventDefault();
            loadTransactionData(); // โหลดข้อมูลใหม่ตามที่กรอกในฟอร์ม
        });

        function formatNumber(num) {
            return parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        // โหลดข้อมูลครั้งแรกเมื่อหน้าเว็บถูกโหลด
        loadTransactionData();
    });
</script>


</body>
</html>
