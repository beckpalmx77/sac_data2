<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bank Transactions</title>
    <!-- Bootstrap 5 CSS -->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/css/bootstrap.min.css">
</head>
<body>

<div class="container mt-5">
    <h2 class="mb-4">รายการธุรกรรมธนาคาร</h2>

    <!-- ฟอร์มสำหรับเลือกวันที่และธนาคาร -->
    <form action="export_process/export_data_bank_statement.php" method="post" class="mb-4">
        <div class="row">
            <div class="col-md-4 mb-3">
                <label for="start_date" class="form-label">เลือกวันที่เริ่มต้น:</label>
                <input type="date" class="form-control" id="start_date" name="start_date" required>
            </div>
            <div class="col-md-4 mb-3">
                <label for="end_date" class="form-label">เลือกวันที่สิ้นสุด:</label>
                <input type="date" class="form-control" id="end_date" name="end_date" required>
            </div>
            <div class="col-md-4 mb-3">
                <label for="bank" class="form-label">เลือกธนาคาร:</label>
                <select class="form-select" id="bank" name="bank" required>
                    <option value="106">ธนาคาร A</option>
                    <option value="107">ธนาคาร B</option>
                    <option value="108">ธนาคาร C</option>
                </select>
            </div>
        </div>
        <button type="submit" class="btn btn-primary" name="filter">แสดงข้อมูล</button>
        <button type="submit" class="btn btn-success" name="export_csv">Export to CSV</button>
    </form>

    <!-- ตารางแสดงข้อมูล -->
    <table class="table table-striped table-bordered">
        <thead class="table-dark">
        <tr>
            <th>วันที่</th>
            <th>ธนาคาร</th>
            <th>เครดิต</th>
            <th>เดบิต</th>
            <th>ยอดคงเหลือ</th>
            <th>หมายเหตุ</th>
            <th>วันที่เอกสาร</th>
            <th>เลขที่เอกสาร</th>
            <th>วันที่เช๊ค</th>
            <th>หมายเลขเช๊ค</th>
        </tr>
        </thead>
        <tbody>
        <?php
        include("model/fetch_bank_statement_transactions.php");
        ?>
        </tbody>
    </table>
</div>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>