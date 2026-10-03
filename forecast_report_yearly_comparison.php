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
if ($account_type === 'sale_user' || $account_type === 'take') {
    $where_cust .= " AND sale_name = :first_name";
    $params_cust[':first_name'] = $first_name;
}

// Fetch Lists
$sales_list = $conn->prepare("SELECT DISTINCT sale_name FROM ims_tires_plan_forecast $where_cust AND sale_name != '' ORDER BY sale_name");
$sales_list->execute($params_cust);
$sales_data = $sales_list->fetchAll(PDO::FETCH_COLUMN);

$cust_list = $conn->prepare("SELECT DISTINCT ar_name FROM ims_tires_plan_forecast $where_cust AND ar_name != '' ORDER BY ar_name");
$cust_list->execute($params_cust);
$customers_data = $cust_list->fetchAll(PDO::FETCH_COLUMN);

$sku_list = $conn->prepare("SELECT DISTINCT sku_code FROM ims_tires_plan_forecast $where_cust AND sku_code != '' ORDER BY sku_code");
$sku_list->execute($params_cust);
$skus_data = $sku_list->fetchAll(PDO::FETCH_COLUMN);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>รายงานเปรียบเทียบยอดขาย - เป้าหมาย - คาดการณ์ รายปี</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Prompt', sans-serif; background-color: #f0f2f5; margin: 20px; }
        .card { border-radius: 15px; border: none; box-shadow: 0 0 20px rgba(0,0,0,0.08); }
        .card-header { background: linear-gradient(45deg, #1a237e, #283593); color: white; border-radius: 15px 15px 0 0 !important; }
        th { font-size: 0.75rem; text-align: center; vertical-align: middle !important; background-color: #f8f9fa !important; }
        td { font-size: 0.75rem; vertical-align: middle; }
        .val-actual { color: #d32f2f; font-weight: bold; } /* Red for Actual */
        .val-target { color: #1976d2; } /* Blue for Target */
        .val-forecast { color: #388e3c; } /* Green for Forecast */
        .total-row-label { font-size: 0.65rem; color: #7f8c8d; display: inline-block; width: 45px; }
        .total-cell { background-color: #fff9db !important; }
        
        @media (max-width: 768px) {
            th, td { font-size: 0.6rem; padding: 4px !important; }
            .card-header { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-balance-scale me-2"></i> เปรียบเทียบยอดขาย - เป้าหมาย - คาดการณ์ รายปี</h4>
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
                <div class="row g-2 mb-4">
                    <div class="col-md-1">
                        <label class="form-label fw-bold">ปี:</label>
                        <select id="filterYear" class="form-select form-select-sm">
                            <?php for($y = $current_year - 1; $y <= $current_year + 1; $y++): ?>
                                <option value="<?= $y ?>" <?= $y == $current_year ? 'selected' : '' ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">พนักงาน:</label>
                        <select id="filterSale" class="form-select form-select-sm">
                            <option value="">-- ทั้งหมด --</option>
                            <?php foreach($sales_data as $s): 
                                $is_me = ($account_type !== 'admin') && $s === $first_name;
                                $selected = $is_me ? 'selected' : '';
                                $disabled = ($account_type !== 'admin' && !$is_me) ? 'disabled' : '';
                                echo "<option value='".htmlspecialchars($s)."' $selected $disabled>".htmlspecialchars($s)."</option>";
                            endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">ลูกค้า:</label>
                        <select id="filterCustomer" class="form-select form-select-sm">
                            <option value="">-- ทั้งหมด --</option>
                            <?php foreach($customers_data as $c): ?>
                                <option value="<?= htmlspecialchars($c) ?>"><?= htmlspecialchars($c) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">สินค้า (SKU):</label>
                        <select id="filterSku" class="form-select form-select-sm" multiple="multiple">
                            <?php foreach($skus_data as $sku): ?>
                                <option value="<?= htmlspecialchars($sku) ?>"><?= htmlspecialchars($sku) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 align-self-end">
                        <button type="button" id="btnFilter" class="btn btn-primary btn-sm w-100"><i class="fas fa-search"></i> แสดงข้อมูล</button>
                    </div>
                </div>

                <div class="mb-3">
                    <span class="badge bg-danger">A = ยอดขายจริง (Actual)</span>
                    <span class="badge bg-primary ms-2">T = เป้าหมาย (Target)</span>
                    <span class="badge bg-success ms-2">F = คาดการณ์ (Forecast)</span>
                </div>

                <div class="table-responsive">
                    <table id="compTable" class="table table-bordered table-hover w-100">
                        <thead>
                            <tr>
                                <th rowspan="2" class="align-middle">พนักงาน / ลูกค้า / SKU</th>
                                <th>ม.ค.</th><th>ก.พ.</th><th>มี.ค.</th><th>เม.ย.</th><th>พ.ค.</th><th>มิ.ย.</th>
                                <th>ก.ค.</th><th>ส.ค.</th><th>ก.ย.</th><th>ต.ค.</th><th>พ.ย.</th><th>ธ.ค.</th>
                                <th class="total-cell">รวมปี (A)</th>
                                <th class="total-cell">รวมปี (T)</th>
                                <th class="total-cell">รวมปี (F)</th>
                            </tr>
                            <tr>
                                <th>A/T/F</th><th>A/T/F</th><th>A/T/F</th><th>A/T/F</th><th>A/T/F</th><th>A/T/F</th>
                                <th>A/T/F</th><th>A/T/F</th><th>A/T/F</th><th>A/T/F</th><th>A/T/F</th><th>A/T/F</th>
                                <th class="total-cell">Actual</th>
                                <th class="total-cell">Target</th>
                                <th class="total-cell">Forecast</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <footer class="text-center mt-4 pb-4 text-muted small">
        &copy; <?= date('Y') ?> BY SAC System Admin
    </footer>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#filterSale, #filterCustomer, #filterSku').select2({ theme: 'bootstrap-5', width: '100%', allowClear: true });
            
            <?php if ($account_type !== 'admin'): ?>
                $('#filterSale').prop('disabled', true);
            <?php endif; ?>

            var table = $('#compTable').DataTable({
                "processing": true, "serverSide": true,
                "ajax": {
                    "url": "forecast_report_yearly_comparison_fetch.php", "type": "POST",
                    "data": function(d) {
                        d.year = $('#filterYear').val();
                        d.sale_name = $('#filterSale').val();
                        d.ar_name = $('#filterCustomer').val();
                        d.sku_code = $('#filterSku').val();
                    }
                },
                "columns": [
                    { "data": null, "render": function(data) {
                        return '<b>' + data.sale_name + '</b><br>' +
                               '<span class="text-primary small">' + data.ar_name + '</span><br>' +
                               '<span class="text-muted small">' + data.sku_code + ' (' + data.brand_code + ')</span>';
                    }},
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
                    { "data": "total_sales", "className": "text-end total-cell fw-bold text-danger" },
                    { "data": "total_target", "className": "text-end total-cell fw-bold text-primary" },
                    { "data": "total_forecast", "className": "text-end total-cell fw-bold text-success" }
                ],
                "pageLength": 10, "ordering": false,
                "language": { "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/th.json" }
            });

            function renderCell(data) {
                return '<div class="text-end">' +
                       '<span class="val-actual">' + data.actual + '</span><br>' +
                       '<span class="val-target">' + data.target + '</span><br>' +
                       '<span class="val-forecast">' + data.forecast + '</span>' +
                       '</div>';
            }

            $('#btnFilter').click(function() { table.draw(); });
        });
    </script>
</body>
</html>
