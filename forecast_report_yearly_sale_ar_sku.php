<?php
session_start();
date_default_timezone_set("Asia/Bangkok");
include('config/connect_db.php');

if (!isset($_SESSION['alogin']) || $_SESSION['alogin'] == '') {
    header('Location: login.php');
    exit;
}

$current_year = date('Y');

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

// Fetch Sale List
$stmt_sales = $conn->prepare("SELECT DISTINCT sale_name FROM ims_tires_plan_forecast $where_cust AND sale_name IS NOT NULL AND sale_name != '' ORDER BY sale_name");
$stmt_sales->execute($params_cust);
$sales_list = $stmt_sales->fetchAll(PDO::FETCH_COLUMN);

// Fetch Customer List (DISTINCT ar_name)
$stmt_customers = $conn->prepare("SELECT DISTINCT ar_name FROM ims_tires_plan_forecast $where_cust AND ar_name IS NOT NULL AND ar_name != '' ORDER BY ar_name");
$stmt_customers->execute($params_cust);
$customers_list = $stmt_customers->fetchAll(PDO::FETCH_COLUMN);

// Fetch SKU List (DISTINCT sku_code)
$stmt_sku = $conn->prepare("SELECT DISTINCT sku_code FROM ims_tires_plan_forecast $where_cust AND sku_code IS NOT NULL AND sku_code != '' ORDER BY sku_code");
$stmt_sku->execute($params_cust);
$sku_list = $stmt_sku->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานสรุปรายปี พนักงาน + ลูกค้า + SKU</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Prompt', sans-serif; background-color: #f8f9fa; margin: 20px; }
        .card { border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .card-header { background-color: #20c997; color: white; border-radius: 15px 15px 0 0 !important; }
        th { background-color: #e6fffa !important; font-size: 0.75rem; text-align: center; }
        td { font-size: 0.8rem; vertical-align: middle; }
        .val-target { color: #007bff; font-weight: bold; }
        .val-forecast { color: #198754; }
        .total-col { background-color: #fff9db !important; font-weight: bold; }

        @media (max-width: 768px) {
            body { margin: 10px; }
            .card-header { flex-direction: column; text-align: center; }
            .card-header div { margin-top: 10px; }
            th, td { font-size: 0.65rem; padding: 4px !important; }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-file-invoice me-2"></i> สรุปเป้าหมายและคาดการณ์รายปี (พนักงาน + ลูกค้า + SKU)</h4>
                <div>
                    <a href="plan_tire_manual.html" target="_blank" class="btn btn-outline-danger btn-sm fw-bold me-2">
                        <i class="fas fa-info-circle me-1"></i> คู่มือการใช้งานระบบ
                    </a>
                    <button type="button" class="btn btn-light btn-sm" onclick="window.location.href='forecast_report_yearly.php'">
                        <i class="fas fa-arrow-left me-1"></i> กลับหน้ารวม
                    </button>
                    <button type="button" class="btn btn-danger btn-sm ms-2" onclick="window.close();">
                        <i class="fas fa-times me-1"></i> Close
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-1">
                        <label class="form-label fw-bold">เลือกปี:</label>
                        <select id="filterYear" class="form-select">
                            <?php
                            for($y = $current_year - 1; $y <= $current_year + 2; $y++) {
                                $selected = ($y == $current_year) ? 'selected' : '';
                                echo "<option value='$y' $selected>$y</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">พนักงานขาย:</label>
                        <select id="filterSale" class="form-select">
                            <option value="">-- ทั้งหมด --</option>
                            <?php 
                            foreach ($sales_list as $sale): 
                                $is_me = ($account_type === 'sale_user' || $account_type === 'take') && $sale === $first_name;
                                $selected = $is_me ? 'selected' : '';
                                $disabled = ($account_type === 'sale_user' || $account_type === 'take') && !$is_me ? 'disabled' : '';
                                echo "<option value='".htmlspecialchars($sale)."' $selected $disabled>".htmlspecialchars($sale)."</option>";
                            endforeach; 
                            ?>
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
                    <div class="col-md-3">
                        <label class="form-label fw-bold">รหัสสินค้า (SKU):</label>
                        <select id="filterSku" class="form-select" multiple="multiple">
                            <?php foreach ($sku_list as $sku): ?>
                                <option value="<?= htmlspecialchars($sku) ?>"><?= htmlspecialchars($sku) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 align-self-end">
                        <button type="button" id="btnFilter" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i> แสดงรายงาน
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="saleArSkuReportTable" class="table table-bordered table-hover w-100">
                        <thead>
                            <tr>
                                <th rowspan="2" class="align-middle">พนักงานขาย</th>
                                <th rowspan="2" class="align-middle">ชื่อลูกค้า</th>
                                <th rowspan="2" class="align-middle">ยี่ห้อ</th>
                                <th rowspan="2" class="align-middle">รหัสสินค้า</th>
                                <th>ม.ค.</th><th>ก.พ.</th><th>มี.ค.</th><th>เม.ย.</th><th>พ.ค.</th><th>มิ.ย.</th>
                                <th>ก.ค.</th><th>ส.ค.</th><th>ก.ย.</th><th>ต.ค.</th><th>พ.ย.</th><th>ธ.ค.</th>
                                <th colspan="3" class="total-col">สรุปภาพรวมรายปี</th>
                            </tr>
                            <tr>
                                <th>เป้า/คาด</th><th>เป้า/คาด</th><th>เป้า/คาด</th><th>เป้า/คาด</th><th>เป้า/คาด</th><th>เป้า/คาด</th>
                                <th>เป้า/คาด</th><th>เป้า/คาด</th><th>เป้า/คาด</th><th>เป้า/คาด</th><th>เป้า/คาด</th><th>เป้า/คาด</th>
                                <th class="total-col">เป้ารวม</th>
                                <th class="total-col">คาดการณ์รวม</th>
                                <th class="total-col">% Ach.</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-center pb-4 text-muted small">
        &copy; <?= date('Y') ?> BY SAC System Admin
    </footer>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#filterSale, #filterCustomer, #filterSku').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: '-- ทั้งหมด --',
                allowClear: true
            });

            <?php if ($account_type === 'sale_user' || $account_type === 'take'): ?>
                $('#filterSale').prop('disabled', true);
            <?php endif; ?>

            var table = $('#saleArSkuReportTable').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "forecast_report_yearly_sale_ar_sku_fetch.php",
                    "type": "POST",
                    "data": function(d) {
                        d.year = $('#filterYear').val();
                        d.sale_name = $('#filterSale').val();
                        d.ar_name = $('#filterCustomer').val();
                        d.sku_code = $('#filterSku').val();
                    }
                },
                "columns": [
                    { "data": "sale_name" },
                    { "data": "ar_name", "className": "fw-bold" },
                    { "data": "brand_code" },
                    { "data": "sku_code" },
                    { "data": "m1", "render": renderCell },
                    { "data": "m2", "render": renderCell },
                    { "data": "m3", "render": renderCell },
                    { "data": "m4", "render": renderCell },
                    { "data": "m5", "render": renderCell },
                    { "data": "m6", "render": renderCell },
                    { "data": "m7", "render": renderCell },
                    { "data": "m8", "render": renderCell },
                    { "data": "m9", "render": renderCell },
                    { "data": "m10", "render": renderCell },
                    { "data": "m11", "render": renderCell },
                    { "data": "m12", "render": renderCell },
                    { "data": "total_target", "className": "text-end total-col" },
                    { "data": "total_forecast", "className": "text-end total-col" },
                    { "data": "achievement", "className": "text-center fw-bold total-col" }
                ],
                "pageLength": 25,
                "ordering": false,
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/th.json"
                }
            });

            function renderCell(data) {
                return '<div class="text-end">' +
                       '<span class="val-target">' + data.target + '</span><br>' +
                       '<span class="val-forecast">' + data.forecast + '</span>' +
                       '</div>';
            }

            $('#btnFilter').click(function() {
                table.draw();
            });
        });
    </script>
</body>
</html>
