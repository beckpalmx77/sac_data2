<?php
session_start();
date_default_timezone_set("Asia/Bangkok");
include('config/connect_db.php');

if (!isset($_SESSION['alogin']) || $_SESSION['alogin'] == '') {
    header('Location: login.php');
    exit;
}

$thai_months = [
    1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม',
    4 => 'เมษายน', 5 => 'พฤษภาคม', 6 => 'มิถุนายน',
    7 => 'กรกฎาคม', 8 => 'สิงหาคม', 9 => 'กันยายน',
    10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
];

$account_type = $_SESSION['account_type'] ?? '';
$first_name = $_SESSION['first_name'] ?? '';

$where_cust = "WHERE 1=1";
$params_cust = [];

if ($account_type === 'sale_user') {
    $where_cust .= " AND sale_name = :first_name";
    $params_cust[':first_name'] = $first_name;
} elseif ($account_type === 'take') {
    $where_cust .= " AND take_name = :first_name";
    $params_cust[':first_name'] = $first_name;
}

$stmt_sales = $conn->prepare("SELECT DISTINCT sale_name FROM ims_tires_plan_forecast $where_cust AND sale_name IS NOT NULL AND sale_name != '' ORDER BY sale_name");
$stmt_sales->execute($params_cust);
$sales_list = $stmt_sales->fetchAll(PDO::FETCH_COLUMN);

$stmt_customers = $conn->prepare("SELECT DISTINCT ar_name FROM ims_tires_plan_forecast $where_cust AND ar_name IS NOT NULL AND ar_name != '' ORDER BY ar_name");
$stmt_customers->execute($params_cust);
$customers_list = $stmt_customers->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข้อมูล Tires Plan Forecast</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Prompt', sans-serif; background-color: #f8f9fa; margin: 20px; }
        .card { border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .card-header { background-color: #6610f2; color: white; border-radius: 15px 15px 0 0 !important; }
        th, td { white-space: nowrap; font-size: 0.85rem; vertical-align: middle; }
        th { background-color: #f8f9fa !important; }
        .table-responsive { overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-list-alt me-2"></i> ข้อมูล Tires Plan Forecast</h4>
                <div>
                    <a href="forecast_import_plan.php" class="btn btn-success btn-sm"><i class="fas fa-file-import me-1"></i> นำเข้าข้อมูล</a>
                    <button type="button" class="btn btn-danger btn-sm ms-2" onclick="window.close();"><i class="fas fa-times me-1"></i> Close</button>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-2">
                        <label class="form-label fw-bold">เลือกเดือน:</label>
                        <select id="filterMonth" class="form-select">
                            <option value="">-- ทั้งหมด --</option>
                            <?php foreach ($thai_months as $num => $name): ?>
                                <option value="<?= $num ?>"><?= $name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">เลือกปี:</label>
                        <select id="filterYear" class="form-select">
                            <option value="">-- ทั้งหมด --</option>
                            <?php
                            $current_year = date('Y');
                            for($y = $current_year - 1; $y <= $current_year + 2; $y++) {
                                echo "<option value='$y' " . ($y == $current_year ? 'selected' : '') . ">$y</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">ชื่อพนักงานขาย:</label>
                        <select id="filterSale" class="form-select">
                            <option value="">-- ทั้งหมด --</option>
                            <?php foreach ($sales_list as $sale): ?>
                                <option value="<?= htmlspecialchars($sale) ?>"><?= htmlspecialchars($sale) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">ชื่อลูกค้า:</label>
                        <select id="filterCustomer" class="form-select">
                            <option value="">-- ทั้งหมด --</option>
                            <?php foreach ($customers_list as $cust): ?>
                                <option value="<?= htmlspecialchars($cust) ?>"><?= htmlspecialchars($cust) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 align-self-end">
                        <button type="button" id="btnFilter" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i> กรองข้อมูล
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="planTable" class="table table-bordered table-striped table-hover w-100">
                        <thead>
                            <tr>
                                <th>เดือน</th>
                                <th>ปี</th>
                                <th>รหัสร้านค้า</th>
                                <th>ชื่อร้านค้า</th>
                                <th>พนักงานขาย</th>
                                <th>คนเทค</th>
                                <th>ทีม</th>
                                <th>ยี่ห้อ</th>
                                <th>รหัสสินค้า</th>
                                <th>อำเภอ</th>
                                <th>จังหวัด</th>
                                <th>เขต</th>
                                <th>เฉลี่ย 12ด.</th>
                                <th>MAX</th>
                                <th>MIN</th>
                                <th>เป้า1</th>
                                <th>ยอดขาย1</th>
                                <th>ยอดขาย2</th>
                                <th>ยอดขาย3</th>
                                <th>เฉลี่ย 3ด.</th>
                                <th>เป้า2</th>
                                <th>คาดการณ์1</th>
                                <th>เทียบคาดการณ์1</th>
                                <th>เทียบยอดขาย%1</th>
                                <th>หมายเหตุ</th>
                                <th>คาดการณ์2</th>
                                <th>เทียบคาดการณ์2</th>
                                <th>เทียบยอดขาย%2</th>
                                <th>วันที่นำเข้า</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('#filterSale, #filterCustomer').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: '-- ทั้งหมด --',
                allowClear: true
            });

            var table = $('#planTable').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "forecast_plan_fetch.php",
                    "type": "POST",
                    "data": function(d) {
                        d.month = $('#filterMonth').val();
                        d.year = $('#filterYear').val();
                        d.sale_name = $('#filterSale').val();
                        d.ar_name = $('#filterCustomer').val();
                    }
                },
                "columns": [
                    { "data": "forcast_month_name" },
                    { "data": "forcast_year" },
                    { "data": "ar_code" },
                    { "data": "ar_name" },
                    { "data": "sale_name" },
                    { "data": "take_name" },
                    { "data": "team_name" },
                    { "data": "brand_code" },
                    { "data": "sku_code" },
                    { "data": "amphur" },
                    { "data": "province" },
                    { "data": "zone" },
                    { "data": "avg_per_month_year", "className": "text-end" },
                    { "data": "max_qty_year", "className": "text-end" },
                    { "data": "min_qty_year", "className": "text-end" },
                    { "data": "target_qty_month", "className": "text-end" },
                    { "data": "sales_month_1", "className": "text-end" },
                    { "data": "sales_month_2", "className": "text-end" },
                    { "data": "sales_month_3", "className": "text-end" },
                    { "data": "avg_3_month", "className": "text-end" },
                    { "data": "target_qty_month_2", "className": "text-end" },
                    { "data": "forecast_qty_month_1", "className": "text-end" },
                    { "data": "compare_forecast_month_1", "className": "text-end" },
                    { "data": "compare_sales_percent", "className": "text-end" },
                    { "data": "remark" },
                    { "data": "forecast_qty_month_2", "className": "text-end" },
                    { "data": "compare_forecast_month_2", "className": "text-end" },
                    { "data": "compare_sales_percent_2", "className": "text-end" },
                    { "data": "import_date" }
                ],
                "order": [[26, "desc"]],
                "pageLength": 10,
                "language": {
                    "lengthMenu": "แสดง _MENU_ รายการ",
                    "zeroRecords": "ไม่พบข้อมูล",
                    "info": "แสดงหน้า _PAGE_ จาก _PAGES_",
                    "infoEmpty": "ไม่มีข้อมูล",
                    "infoFiltered": "(กรองจากทั้งหมด _MAX_ รายการ)",
                    "search": "ค้นหา:",
                    "paginate": {
                        "first": "หน้าแรก",
                        "last": "หน้าสุดท้าย",
                        "next": "ถัดไป",
                        "previous": "ก่อนหน้า"
                    }
                }
            });

            $('#btnFilter').click(function() {
                table.draw();
            });
        });
    </script>
</body>
</html>
