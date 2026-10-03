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
    <title>ข้อมูล Tires Plan Forecast (Interactive)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Prompt', sans-serif; background-color: #f8f9fa; margin: 20px; }
        .card { border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .card-header { background-color: #198754; color: white; border-radius: 15px 15px 0 0 !important; }
        th, td { white-space: nowrap; font-size: 0.85rem; vertical-align: middle; }
        th { background-color: #f8f9fa !important; }
        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .form-control-sm { font-size: 0.8rem; }
        .compare-1, .compare-pct { background-color: #e9ecef !important; }

        /* Mobile Adjustments */
        @media (max-width: 768px) {
            body { margin: 10px; }
            .card-header { flex-direction: column; text-align: center; }
            .card-header div { margin-top: 10px; display: flex; flex-wrap: wrap; justify-content: center; gap: 5px; }
            .card-header .btn { margin-left: 0 !important; margin-bottom: 5px; }
            .btn-group { margin-left: 0 !important; }
            th, td { font-size: 0.75rem; padding: 8px 5px !important; }
            .form-control-sm { font-size: 0.75rem; padding: 2px 5px; }
        }
        /* ยกเลิกเส้นขีดกลางบนหัวข้อของ SweetAlert2 h2 */
        .swal2-popup h2::before {
            display: none !important;
            content: none !important;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0"><i class="fas fa-edit me-2"></i> Tires Plan Forecast (Interactive)</h4>
                <div>
                    <a href="forecast_import_plan.php" class="btn btn-primary btn-sm">
                        <i class="fas fa-file-import me-1"></i> นำเข้าข้อมูล
                    </a>
                    <a href="import_plan_howto.html" target="_blank" class="btn btn-outline-dark btn-sm ms-2">
                        <i class="fas fa-book me-1"></i> คู่มือการนำเข้า
                    </a>
                    <a href="plan_tire_manual.html" target="_blank" class="btn btn-outline-danger btn-sm ms-2 fw-bold">
                        <i class="fas fa-info-circle me-1"></i> คู่มือการใช้งานระบบ
                    </a>
                    <button type="button" id="btnSyncData" class="btn btn-info btn-sm fw-bold text-white ms-2">
                        <i class="fas fa-sync-alt me-1"></i> ปรับปรุงข้อมูลยอดขาย
                    </button>
                    <div class="btn-group ms-2">
                        <button type="button" class="btn btn-warning btn-sm fw-bold dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-chart-pie me-1"></i> รายงานสรุปรายปี
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><a class="dropdown-item" href="forecast_report_yearly.php" target="_blank">
                                <i class="fas fa-user-tie me-2 text-primary"></i> สรุปรายปี (แยกตามพนักงาน)
                            </a></li>
                            <li><a class="dropdown-item" href="forecast_report_yearly_sku.php" target="_blank">
                                <i class="fas fa-boxes me-2 text-success"></i> สรุปรายปี (แยกตาม SKU)
                            </a></li>
                            <li><a class="dropdown-item" href="forecast_report_yearly_sale_sku.php" target="_blank">
                                <i class="fas fa-table me-2 text-purple" style="color: #6f42c1;"></i> สรุปรายปี (พนักงาน + SKU)
                            </a></li>
                            <li><a class="dropdown-item" href="forecast_report_yearly_sale_ar_sku.php" target="_blank">
                                <i class="fas fa-file-invoice me-2 text-success" style="color: #20c997;"></i> สรุปรายปี (พนักงาน + ลูกค้า + SKU)
                            </a></li>
                            <li><a class="dropdown-item" href="forecast_report_yearly_comparison.php" target="_blank">
                                <i class="fas fa-balance-scale me-2 text-info"></i> เปรียบเทียบ ยอดขาย/เป้า/คาดการณ์
                            </a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-muted small" href="report_plan_method.html" target="_blank">
                                <i class="fas fa-info-circle me-2"></i> รายละเอียดรูปแบบรายงาน
                            </a></li>
                        </ul>
                    </div>
                    <button type="button" id="btnSaveAll" class="btn btn-warning btn-sm fw-bold ms-2">
                        <i class="fas fa-save me-1"></i> บันทึกการแก้ไขทั้งหมด
                    </button>
                    <button type="button" class="btn btn-danger btn-sm ms-2" onclick="window.close();">
                        <i class="fas fa-times me-1"></i> Close
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-2">
                        <label class="form-label fw-bold">เลือกเดือน:</label>
                        <select id="filterMonth" class="form-select">
                            <option value="">-- เลือกเดือน --</option>
                            <?php foreach ($thai_months as $num => $name): ?>
                                <option value="<?= $num ?>"><?= $name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">เลือกปี:</label>
                        <select id="filterYear" class="form-select">
                            <option value="">-- เลือกปี --</option>
                            <?php
                            $current_year = date('Y');
                            for($y = $current_year - 1; $y <= $current_year + 2; $y++) {
                                $selected = ($y == $current_year) ? 'selected' : '';
                                echo "<option value='$y' $selected>$y</option>";
                            }
                            ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">ชื่อพนักงานขาย:</label>
                        <select id="filterSale" class="form-select">
                            <option value="">-- ทั้งหมด --</option>
                            <?php foreach ($sales_list as $sale): 
                                $is_me = ($account_type !== 'admin') && $sale === $first_name;
                                $selected = $is_me ? 'selected' : '';
                                $disabled = ($account_type !== 'admin' && !$is_me) ? 'disabled' : '';
                                echo "<option value='".htmlspecialchars($sale)."' $selected $disabled>".htmlspecialchars($sale)."</option>";
                            endforeach; ?>
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
                        <div class="btn-group w-100">
                            <button type="button" id="btnFilter" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> กรองข้อมูล
                            </button>
                            <button type="button" id="btnShowSummary" class="btn btn-warning fw-bold">
                                <i class="fas fa-list-alt me-1"></i> สรุปยี่ห้อ
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Brand Summary Modal -->
                <div class="modal fade" id="summaryModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header bg-warning text-dark">
                                <h5 class="modal-title fw-bold"><i class="fas fa-chart-bar me-2"></i> สรุปยอดแผนวางแผนการขายแยกตามยี่ห้อ (DEMO)</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div id="summaryContent" class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>ยี่ห้อ</th>
                                                <th class="text-end">เฉลี่ย 12ด.</th>
                                                <th class="text-end">ยอดขาย M-3</th>
                                                <th class="text-end">ยอดขาย M-2</th>
                                                <th class="text-end">ยอดขาย M-1</th>
                                                <th class="text-end">เฉลี่ย 3ด.</th>
                                                <th class="text-end">คาดการณ์</th>
                                                <th class="text-end">ผลต่าง</th>
                                                <th class="text-end">% ต่าง</th>
                                            </tr>
                                        </thead>
                                        <tbody id="summaryTableBody">
                                            <!-- Data will be loaded here -->
                                        </tbody>
                                        <tfoot id="summaryTableFoot" class="table-secondary fw-bold">
                                            <!-- Totals will be loaded here -->
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
                            </div>
                        </div>
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
                                <th>เป้าหมาย</th>
                                <th>ยอดขาย M-1</th>
                                <th>ยอดขาย M-2</th>
                                <th>ยอดขาย M-3</th>
                                <th>เฉลี่ย 3ด.</th>
                                <th>เป้า2</th>
                                <th>คาดการณ์</th>
                                <th>เทียบคาดการณ์</th>
                                <th>เทียบยอดขาย%</th>
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

    <footer class="text-center pb-4 text-muted small">
        &copy; <?= date('Y') ?> BY SAC System Admin
    </footer>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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
                    "url": "forecast_plan_fetch_new.php",
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
                "order": [[28, "desc"]],
                "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "ทั้งหมด"]],
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
                },
                "drawCallback": function(settings) {
                    // Re-bind calculation events after table draw
                    $('.forecast-qty').on('input', function() {
                        calculateRow($(this).closest('tr'));
                    });
                }
            });

            function calculateRow($row) {
                var forecast = parseFloat($row.find('.forecast-qty').val().replace(/,/g, '')) || 0;
                var avg3m = parseFloat($row.find('.avg-3m').data('val')) || 0;
                
                var diff = forecast - avg3m;
                var pct = (avg3m !== 0) ? (diff / avg3m) * 100 : 0;
                
                $row.find('.compare-1').val(diff.toLocaleString('en-US', {minimumFractionDigits: 0}));
                $row.find('.compare-pct').val(pct.toFixed(0) + '%');
            }

            $('#btnFilter').click(function() {
                updateTableHeaders();
                table.draw();
            });

            $('#btnShowSummary').click(function() {
                var month = $('#filterMonth').val();
                var year = $('#filterYear').val();
                var sale_name = $('#filterSale').val();
                var ar_name = $('#filterCustomer').val();

                if (!month || !year) {
                    Swal.fire('แจ้งเตือน', 'กรุณาเลือกเดือนและปีเพื่อดูสรุป', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'กำลังประมวลผล',
                    text: 'กรุณารอสักครู่...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: 'forecast_fetch_brand_summary_demo.php',
                    type: 'POST',
                    data: {
                        month: month,
                        year: year,
                        sale_name: sale_name,
                        ar_name: ar_name
                    },
                    dataType: 'json',
                    success: function(response) {
                        Swal.close();
                        if (response.success) {
                            var html = '';
                            var total = { avg12: 0, m1: 0, m2: 0, m3: 0, avg3: 0, forecast: 0, diff: 0 };

                            response.data.forEach(function(item) {
                                var avg3 = parseFloat(item.total_avg_3m) || 0;
                                var forecast = parseFloat(item.total_forecast) || 0;
                                var diff = forecast - avg3;
                                var pct = (avg3 !== 0) ? (diff / avg3) * 100 : 0;

                                total.avg12 += parseFloat(item.total_avg_12m) || 0;
                                total.m1 += parseFloat(item.total_m1) || 0;
                                total.m2 += parseFloat(item.total_m2) || 0;
                                total.m3 += parseFloat(item.total_m3) || 0;
                                total.avg3 += avg3;
                                total.forecast += forecast;
                                total.diff += diff;

                                html += '<tr>' +
                                    '<td>' + (item.brand_code || 'ไม่ระบุ') + '</td>' +
                                    '<td class="text-end">' + numberFormat(item.total_avg_12m) + '</td>' +
                                    '<td class="text-end">' + numberFormat(item.total_m3) + '</td>' +
                                    '<td class="text-end">' + numberFormat(item.total_m2) + '</td>' +
                                    '<td class="text-end">' + numberFormat(item.total_m1) + '</td>' +
                                    '<td class="text-end">' + numberFormat(avg3) + '</td>' +
                                    '<td class="text-end">' + numberFormat(forecast) + '</td>' +
                                    '<td class="text-end">' + numberFormat(diff) + '</td>' +
                                    '<td class="text-end">' + pct.toFixed(0) + '%</td>' +
                                    '</tr>';
                            });

                            $('#summaryTableBody').html(html);

                            // Footer Totals
                            var totalPct = (total.avg3 !== 0) ? (total.diff / total.avg3) * 100 : 0;
                            var footHtml = '<tr>' +
                                '<td>รวมทั้งหมด</td>' +
                                '<td class="text-end">' + numberFormat(total.avg12) + '</td>' +
                                '<td class="text-end">' + numberFormat(total.m3) + '</td>' +
                                '<td class="text-end">' + numberFormat(total.m2) + '</td>' +
                                '<td class="text-end">' + numberFormat(total.m1) + '</td>' +
                                '<td class="text-end">' + numberFormat(total.avg3) + '</td>' +
                                '<td class="text-end">' + numberFormat(total.forecast) + '</td>' +
                                '<td class="text-end">' + numberFormat(total.diff) + '</td>' +
                                '<td class="text-end">' + totalPct.toFixed(0) + '%</td>' +
                                '</tr>';
                            $('#summaryTableFoot').html(footHtml);

                            $('#summaryModal').modal('show');
                        } else {
                            Swal.fire('ผิดพลาด', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('ผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
                    }
                });
            });

            function numberFormat(num) {
                return parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
            }

            function updateTableHeaders() {
                var month = parseInt($('#filterMonth').val());
                var year = parseInt($('#filterYear').val());
                
                if (!month || !year) return;

                var thai_months = [
                    '', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
                    'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'
                ];

                // Previous Year (BE)
                var prevYearBE = (year - 1) + 543;
                $(table.column(12).header()).text('เฉลี่ย 12ด. (' + prevYearBE + ')');

                // M-1, M-2, M-3
                var m1 = month - 1; if (m1 <= 0) m1 += 12;
                var m2 = month - 2; if (m2 <= 0) m2 += 12;
                var m3 = month - 3; if (m3 <= 0) m3 += 12;

                $(table.column(16).header()).text('ยอดขายเดือน ' + thai_months[m3]);
                $(table.column(17).header()).text('ยอดขายเดือน ' + thai_months[m2]);
                $(table.column(18).header()).text('ยอดขายเดือน ' + thai_months[m1]);
            }

            // Initial headers update if values exist
            updateTableHeaders();

            $('#btnSyncData').click(function() {
                var month = $('#filterMonth').val();
                var year = $('#filterYear').val();

                if (!month || !year) {
                    Swal.fire('แจ้งเตือน', 'กรุณาเลือกเดือนและปีที่ต้องการปรับปรุงข้อมูล', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'กำลังปรับปรุงข้อมูล',
                    text: 'ระบบกำลังดึงข้อมูลยอดขายจริงและคำนวณใหม่ กรุณารอสักครู่...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: 'forecast_update_sales_data.php',
                    type: 'POST',
                    data: { month: month, year: year },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('สำเร็จ', response.message, 'success');
                            table.draw();
                        } else {
                            Swal.fire('ผิดพลาด', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'error');
                    }
                });
            });

            $('#btnSaveAll').click(function() {
                var updates = [];
                table.rows().nodes().each(function(node) {
                    var $row = $(node);
                    var id = $row.find('.target-qty').data('id');
                    if (id) {
                        updates.push({
                            id: id,
                            target_qty: parseFloat($row.find('.target-qty').val().replace(/,/g, '')) || 0,
                            forecast_qty: parseFloat($row.find('.forecast-qty').val().replace(/,/g, '')) || 0,
                            compare_1: parseFloat($row.find('.compare-1').val().replace(/,/g, '')) || 0,
                            compare_pct: parseFloat($row.find('.compare-pct').val().replace(/%/g, '')) || 0,
                            remark: $row.find('.remark').val()
                        });
                    }
                });

                if (updates.length === 0) {
                    Swal.fire('แจ้งเตือน', 'ไม่มีข้อมูลให้บันทึกในหน้านี้', 'info');
                    return;
                }

                Swal.fire({
                    title: 'ยืนยันการบันทึก',
                    text: 'คุณต้องการบันทึกการแก้ไขทั้งหมดในหน้านี้หรือไม่?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'บันทึก',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'forecast_update_plan_new.php',
                            type: 'POST',
                            data: { updates: JSON.stringify(updates) },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('สำเร็จ', 'บันทึกข้อมูลเรียบร้อยแล้ว', 'success');
                                    table.draw(false);
                                } else {
                                    Swal.fire('ผิดพลาด', response.message, 'error');
                                }
                            },
                            error: function() {
                                Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'error');
                            }
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>

</html>
