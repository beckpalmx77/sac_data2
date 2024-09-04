<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบจัดการคำถาม-คำตอบลูกค้า</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css" rel="stylesheet">

    <!-- เรียกใช้ Select2 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/css/select2.min.css" rel="stylesheet" />
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.1.0-beta.1/js/select2.min.js"></script>
</head>
<body>
<div class="container mt-4">
    <h2>จัดการคำถาม-คำตอบลูกค้า</h2>

    <!-- ฟอร์มสำหรับเพิ่มข้อมูลเอกสาร -->
    <form id="documentForm" class="mb-4">
        <div class="row">
            <!-- Dropdown สำหรับเลือกชื่อลูกค้าด้วย Select2 -->
            <div class="col-md-4">
                <select class="form-control select2" id="customer_id" name="customer_id" required>
                    <option value="">เลือกชื่อลูกค้า</option>
                </select>
            </div>
            <div class="col-md-4">
                <input type="text" class="form-control" id="document_number" name="document_number" placeholder="เลขที่เอกสาร" required>
            </div>
            <div class="col-md-4">
                <input type="date" class="form-control" id="document_date" name="document_date" required>
            </div>
        </div>
        <button type="submit" class="btn btn-success mt-2">บันทึกข้อมูล</button>
    </form>

    <!-- ตารางแสดงข้อมูลเอกสาร -->
    <table id="documentsTable" class="display">
        <thead>
        <tr>
            <th>ชื่อลูกค้า</th>
            <th>เลขที่เอกสาร</th>
            <th>วันที่</th>
            <th>จัดการ</th>
        </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<script>
    $(document).ready(function() {
        // เรียกใช้ Select2 สำหรับ dropdown ของลูกค้า
        $('.select2').select2({
            placeholder: 'เลือกชื่อลูกค้า',
            allowClear: true
        });

        // โหลดข้อมูลลูกค้าเมื่อหน้าเพจเริ่มทำงาน
        loadCustomerOptions();

        // โหลดข้อมูลเอกสารเมื่อหน้าเพจเริ่มทำงาน
        loadDocuments();

        // ฟังก์ชันการโหลดข้อมูลลูกค้าสำหรับ dropdown
        function loadCustomerOptions() {
            $.ajax({
                url: 'model/fetch_customers.php',
                type: 'GET',
                success: function(response) {
                    let customers = JSON.parse(response);
                    $.each(customers, function(key, customer) {
                        $('#customer_id').append('<option value="' + customer.customer_id + '">' + customer.customer_name + '</option>');
                    });
                },
                error: function() {
                    alert('เกิดข้อผิดพลาดในการดึงข้อมูลลูกค้า');
                }
            });
        }

        // ฟังก์ชันการโหลดข้อมูลจากฐานข้อมูล
        function loadDocuments() {
            $('#documentsTable').DataTable({
                "ajax": {
                    "url": "fetch_documents.php",
                    "type": "POST"
                },
                "columns": [
                    { "data": "customer_name" },
                    { "data": "document_number" },
                    { "data": "document_date" },
                    { "data": "actions" }
                ]
            });
        }

        // ส่งข้อมูลฟอร์มด้วย AJAX
        $('#documentForm').submit(function(event) {
            event.preventDefault();
            $.ajax({
                url: 'save_document.php',
                type: 'POST',
                data: $(this).serialize(),
                success: function(response) {
                    alert(response);
                    $('#documentForm')[0].reset();
                    $('#documentsTable').DataTable().ajax.reload();
                },
                error: function() {
                    alert('เกิดข้อผิดพลาดในการบันทึกข้อมูล');
                }
            });
        });
    });
</script>
</body>
</html>
