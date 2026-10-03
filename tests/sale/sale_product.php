<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Master - Detail Sales</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
<div class="container mt-4">
    <h2>บันทึกการขายสินค้า</h2>

    <!-- Master Form -->
    <form id="salesForm">
        <div class="mb-3">
            <label for="docNo" class="form-label">หมายเลขเอกสาร</label>
            <input type="text" id="docNo" name="docNo" class="form-control" readonly>
        </div>
        <div class="mb-3">
            <label for="docDate" class="form-label">วันที่เอกสาร</label>
            <input type="date" id="docDate" name="docDate" class="form-control" required>
        </div>
        <div class="mb-3">
            <label for="customer" class="form-label">ชื่อลูกค้า</label>
            <input type="text" id="customer" name="customer" class="form-control" required>
        </div>

        <!-- Detail Table -->
        <table class="table table-bordered" id="detailTable">
            <thead>
            <tr>
                <th>รหัสสินค้า</th>
                <th>ราคา</th>
                <th>จำนวน</th>
                <th>ราคารวม</th>
                <th>จัดการ</th>
            </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
        <button type="button" class="btn btn-primary" id="addRow">เพิ่มรายการ</button>
        <button type="submit" class="btn btn-success">บันทึก</button>
    </form>
</div>

<script>
    $(document).ready(function () {
        $('#addRow').click(function () {
            let row = `<tr>
                    <td><input type="text" name="productCode[]" class="form-control" required></td>
                    <td><input type="number" name="price[]" class="form-control price" required></td>
                    <td><input type="number" name="quantity[]" class="form-control quantity" required></td>
                    <td><input type="text" name="total[]" class="form-control total" readonly></td>
                    <td><button type="button" class="btn btn-danger removeRow">ลบ</button></td>
                </tr>`;
            $('#detailTable tbody').append(row);
        });

        $(document).on('click', '.removeRow', function () {
            $(this).closest('tr').remove();
        });

        $(document).on('input', '.price, .quantity', function () {
            let tr = $(this).closest('tr');
            let price = parseFloat(tr.find('.price').val()) || 0;
            let quantity = parseInt(tr.find('.quantity').val()) || 0;
            let total = price * quantity;
            tr.find('.total').val(total.toFixed(2));
        });

        $('#salesForm').submit(function (e) {
            e.preventDefault();
            let formData = $(this).serialize();

            $.post('generate_doc_no.php', { customer: $('#customer').val() }, function(docNo) {
                $('#docNo').val(docNo);
                formData = $('#salesForm').serialize();

                $.post('save_sales.php', formData, function (response) {
                    alert(response);
                });
            });
        });
    });
</script>
</body>
</html>
