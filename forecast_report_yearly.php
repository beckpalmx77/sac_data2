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

// Fetch Sale List
$sql_sales = "SELECT DISTINCT sale_name FROM ims_tires_plan_forecast WHERE sale_name IS NOT NULL AND sale_name != '' ORDER BY sale_name";
$stmt_sales = $conn->query($sql_sales);
$sales_list = $stmt_sales->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานสรุปเป้าหมายและคาดการณ์รายปี</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Prompt', sans-serif; background-color: #f8f9fa; margin: 20px; }
        .card { border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .card-header { background-color: #e67e22; color: white; border-radius: 15px 15px 0 0 !important; }
        th { background-color: #fdf2e9 !important; font-size: 0.8rem; text-align: center; }
        td { font-size: 0.85rem; vertical-align: middle; }
        .val-target { color: #2980b9; font-weight: bold; }
        .val-forecast { color: #27ae60; }
        .total-col { background-color: #fcf3cf !important; font-weight: bold; }
        .btn-purple { background-color: #6f42c1; color: white; }
        .btn-purple:hover { background-color: #59359a; color: white; }

        @media (max-width: 768px) {
            body { margin: 10px; }
            .card-header { flex-direction: column; text-align: center; }
            .card-header div { margin-top: 10px; display: flex; flex-wrap: wrap; justify-content: center; gap: 5px; }
            .card-header .btn { margin-left: 0 !important; }
            th, td { font-size: 0.7rem; padding: 5px !important; }
            .badge { font-size: 0.65rem; }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-chart-line me-2"></i> สรุปเป้าหมายและคาดการณ์รายปี</h4>
                <div>
                    <a href="plan_tire_manual.html" target="_blank" class="btn btn-outline-danger btn-sm fw-bold me-2">
                        <i class="fas fa-info-circle me-1"></i> คู่มือการใช้งานระบบ
                    </a>
                    <a href="forecast_report_yearly_sale_sku.php" class="btn btn-purple btn-sm text-white">
                        <i class="fas fa-table me-1"></i> ดูแยกตามพนักงาน + SKU
                    </a>
                    <button type="button" class="btn btn-light btn-sm ms-2" onclick="window.location.href='forecast_plan_display_new.php'">
                        <i class="fas fa-arrow-left me-1"></i> กลับหน้าจัดการแผน
                    </button>
                    <button type="button" class="btn btn-danger btn-sm ms-2" onclick="window.close();">
                        <i class="fas fa-times me-1"></i> Close
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-2">
                        <label class="form-label fw-bold">เลือกปี (ค.ศ.):</label>
                        <select id="filterYear" class="form-select">
                            <?php
                            for($y = $current_year - 1; $y <= $current_year + 2; $y++) {
                                $selected = ($y == $current_year) ? 'selected' : '';
                                echo "<option value='$y' $selected>$y (พ.ศ. ".($y+543).")</option>";
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
                                echo "<option value='".htmlspecialchars($sale)."' $selected>".htmlspecialchars($sale)."</option>";
                            endforeach; 
                            ?>
                        </select>
                    </div>
                    <div class="col-md-2 align-self-end">
                        <button type="button" id="btnFilter" class="btn btn-primary w-100">
                            <i class="fas fa-search me-1"></i> แสดงรายงาน
                        </button>
                    </div>
                    <div class="col text-end align-self-end">
                        <div class="mb-2 text-muted small"><i class="fas fa-info-circle me-1"></i> รายงานนี้สรุปยอดรวมจากแผนงานรายเดือน</div>
                        <span class="badge bg-info p-2 mb-1"><i class="fas fa-bullseye me-1"></i> สีน้ำเงิน = เป้าหมาย (Target)</span>
                        <span class="badge bg-success p-2 mb-1 ms-2"><i class="fas fa-chart-line me-1"></i> สีเขียว = คาดการณ์ (Forecast)</span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="reportTable" class="table table-bordered table-hover w-100">
                        <thead>
                            <tr>
                                <th rowspan="2" class="align-middle" style="min-width: 150px;">รายชื่อพนักงานขาย</th>
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
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#filterSale').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });

            <?php if ($account_type === 'sale_user' || $account_type === 'take'): ?>
                $('#filterSale').prop('disabled', true);
            <?php endif; ?>

            var table = $('#reportTable').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": "forecast_report_yearly_fetch.php",
                    "type": "POST",
                    "data": function(d) {
                        d.year = $('#filterYear').val();
                        d.sale_name = $('#filterSale').val();
                    }
                },
                "columns": [
                    { 
                        "data": "sale_name",
                        "render": function(data, type, row) {
                            return '<a href="forecast_report_yearly_sku.php?sale_name=' + encodeURIComponent(data) + '" class="text-decoration-none fw-bold">' +
                                   '<i class="fas fa-search-plus me-1 text-primary"></i> ' + data + '</a>';
                        }
                    },
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
