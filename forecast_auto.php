<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    ?>

    <!DOCTYPE html>
    <html lang="th">
    <head>
        <!-- Chart.js CDN -->
        <script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
        <!-- SweetAlert2 CDN -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

        <style>
            .select2-container {
                width: 100% !important;
            }
            .select2-selection--single {
                height: 38px !important;
                padding: 0.375rem 0.75rem !important;
                font-size: 1rem !important;
                line-height: 1.5 !important;
            }
            .select2-selection__rendered {
                line-height: 25px !important;
            }
            .select2-selection__arrow {
                height: 38px !important;
            }
            .border-left-primary {
                border-left: .25rem solid #4e73df !important;
            }
            .border-left-success {
                border-left: .25rem solid #1cc88a !important;
            }
            .border-left-info {
                border-left: .25rem solid #36b9cc !important;
            }
            .border-left-warning {
                border-left: .25rem solid #f6c23e !important;
            }
            .border-left-danger {
                border-left: .25rem solid #e74a3b !important;
            }
            .table-forecast th {
                background-color: #343a40;
                color: #ffffff;
                text-align: center;
                vertical-align: middle;
                font-size: 0.85rem;
            }
            .table-forecast td {
                vertical-align: middle;
                font-size: 0.85rem;
            }
            .forecast-highlight {
                background-color: #e8f5e9 !important;
                font-weight: bold;
                color: #2e7d32;
            }
            .actual-highlight {
                background-color: #f3e5f5 !important;
                font-weight: bold;
                color: #6a1b9a;
            }
            .spinner-overlay {
                position: fixed;
                top: 0; left: 0; width: 100%; height: 100%;
                background: rgba(255, 255, 255, 0.8);
                z-index: 9999;
                display: flex;
                align-items: center;
                justify-content: center;
            }
        </style>
    </head>

    <body id="page-top">
    <div id="wrapper">
        <?php include('includes/Side-Bar.php'); ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('includes/Top-Bar.php'); ?>

                <div class="container-fluid" id="container-wrapper">
                    <!-- Page Title & Breadcrumb -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><i class="fas fa-magic text-primary mr-2"></i>ระบบพยากรณ์ยอดขายอัตโนมัติ (Auto Sales Forecasting)</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a></li>
                            <li class="breadcrumb-item">วางแผนการขาย</li>
                            <li class="breadcrumb-item active" aria-current="page">Auto Forecast</li>
                        </ol>
                    </div>

                    <!-- Filter Form Card -->
                    <div class="card mb-4 shadow border-0">
                        <div class="card-header bg-primary text-white py-3 d-flex flex-row align-items-center justify-content-between rounded-top">
                            <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-sliders-h mr-2"></i>ตั้งค่าและเงื่อนไขการคำนวณพยากรณ์ยอดขาย</h6>
                        </div>
                        <div class="card-body">
                            <form id="form_auto_forecast" method="post">
                                <div class="row">
                                    <!-- มุมมองการจัดกลุ่มข้อมูล -->
                                    <div class="col-md-4 mb-3">
                                        <label for="group_by" class="control-label font-weight-bold text-primary"><i class="fas fa-boxes mr-1"></i>มุมมองการจัดกลุ่มข้อมูล (Group By)</label>
                                        <select id="group_by" name="group_by" class="form-control select2">
                                            <option value="SKU_ONLY" selected>📦 สรุปตามรายสินค้า (SKU_CODE / SKU_NAME)</option>
                                            <option value="AR_ONLY">👥 สรุปตามรายลูกค้า (AR_NAME / AR_CODE)</option>
                                            <option value="AR_SKU">🔍 สรุปตามรายลูกค้า + รายสินค้า (AR & SKU)</option>
                                        </select>
                                    </div>

                                    <!-- เดือนพยากรณ์ -->
                                    <div class="col-md-4 mb-3">
                                        <label for="target_month" class="control-label font-weight-bold">เดือนเป้าหมาย (Forecast Month)</label>
                                        <select id="target_month" name="target_month" class="form-control select2">
                                            <?php
                                            $months_th = [
                                                1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
                                                5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
                                                9 => 'กันยายน', 10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
                                            ];
                                            $curr_m = intval(date('n'));
                                            foreach ($months_th as $m_num => $m_name) {
                                                $selected = ($m_num == $curr_m) ? 'selected' : '';
                                                echo "<option value='{$m_num}' {$selected}>{$m_num} - {$m_name}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>

                                    <!-- ปีพยากรณ์ -->
                                    <div class="col-md-4 mb-3">
                                        <label for="target_year" class="control-label font-weight-bold">ปีเป้าหมาย (Forecast Year)</label>
                                        <select id="target_year" name="target_year" class="form-control select2">
                                            <?php
                                            $curr_y = intval(date('Y'));
                                            for ($y = $curr_y - 1; $y <= $curr_y + 2; $y++) {
                                                $selected = ($y == $curr_y) ? 'selected' : '';
                                                echo "<option value='{$y}' {$selected}>{$y} (พ.ศ. " . ($y + 543) . ")</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- วิธีการพยากรณ์ (Algorithm) -->
                                    <div class="col-md-4 mb-3">
                                        <label for="algorithm" class="control-label font-weight-bold">วิธีการคำนวณ (Algorithm)</label>
                                        <select id="algorithm" name="algorithm" class="form-control select2">
                                            <option value="AUTO_BEST_FIT" selected>⭐ Auto Best-Fit (ระบบปรับสูตรอัตโนมัติ)</option>
                                            <option value="WMA_3M">📈 Weighted 3M (ถ่วงน้ำหนัก 3 เดือน 50:30:20)</option>
                                            <option value="AVG_3M">📊 Simple Avg 3M (เฉลี่ย 3 เดือน)</option>
                                            <option value="YOY_GROWTH">📅 YoY Growth (แนวโน้มเดือนเดียวกันปีที่แล้ว)</option>
                                            <option value="EXP_SMOOTHING">📉 Exponential Smoothing (ปรับเรียบเอ็กซ์โพเนนเชียล)</option>
                                        </select>
                                    </div>

                                    <!-- ปรับแต่ง% การเติบโต -->
                                    <div class="col-md-4 mb-3">
                                        <label for="growth_factor" class="control-label font-weight-bold">ปรับแต่งเป้าเติบโต (% Growth Adj.)</label>
                                        <div class="input-group">
                                            <input type="number" step="0.5" id="growth_factor" name="growth_factor" class="form-control" value="0.0" placeholder="0.0">
                                            <div class="input-group-append">
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- ค้นหาชื่อ AR_NAME / AR_CODE -->
                                    <div class="col-md-4 mb-3">
                                        <label for="AR_NAME" class="control-label font-weight-bold">กรองชื่อลูกค้า (AR_NAME / AR_CODE)</label>
                                        <select id="AR_NAME" name="AR_NAME" class="form-control select2">
                                            <option value="-">ทุกลูกค้า (All Customers)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="row">
                                    <!-- ค้นหาชื่อ SALE / TAKE -->
                                    <div class="col-md-4 mb-3">
                                        <label for="SALE_NAME" class="control-label font-weight-bold">กรองพนักงานขาย / ผู้เทค (Sale / Take)</label>
                                        <select id="SALE_NAME" name="SALE_NAME[]" multiple="multiple" class="form-control select2">
                                            <option value="-">ทั้งหมด (All Sales)</option>
                                        </select>
                                    </div>

                                    <!-- กรองยี่ห้อ -->
                                    <div class="col-md-4 mb-3">
                                        <label for="BRAND" class="control-label font-weight-bold">กรองยี่ห้อสินค้า (Brand)</label>
                                        <select id="BRAND" name="BRAND" class="form-control select2">
                                            <option value="-">ทุกยี่ห้อ (All Brands)</option>
                                        </select>
                                    </div>

                                    <!-- กรองประเภทสินค้า -->
                                    <div class="col-md-4 mb-3">
                                        <label for="SKU_CAT" class="control-label font-weight-bold">กรองประเภทสินค้า (Category)</label>
                                        <select id="SKU_CAT" name="SKU_CAT" class="form-control select2">
                                            <option value="-">ทุกประเภท (All Categories)</option>
                                        </select>
                                    </div>
                                </div>

                                <hr>

                                <!-- Buttons -->
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <button type="button" class="btn btn-primary btn-lg font-weight-bold mr-2" id="btnCalculate" onclick="runAutoForecast();">
                                            <i class="fas fa-calculator mr-1"></i> คำนวณ Forecast อัตโนมัติ
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary btn-lg" onclick="resetForm();">
                                            <i class="fas fa-redo mr-1"></i> รีเซ็ต
                                        </button>
                                    </div>
                                    <div>
                                        <button type="button" class="btn btn-success btn-lg font-weight-bold mr-2" id="btnSaveSystem" onclick="saveToSystem();" disabled>
                                            <i class="fas fa-save mr-1"></i> บันทึกเข้าระบบแผนการขาย
                                        </button>
                                        <button type="button" class="btn btn-info btn-lg font-weight-bold" id="btnExportCSV" onclick="exportForecastCSV();" disabled>
                                            <i class="fas fa-file-csv mr-1"></i> Export CSV
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Spinner overlay -->
                    <div id="spinner_loading" class="spinner-overlay" style="display: none;">
                        <div class="text-center">
                            <div class="spinner-border text-primary" style="width: 3rem; height: 3rem;" role="status"></div>
                            <h5 class="mt-3 text-dark font-weight-bold">กำลังประมวลผลคำนวณ Auto Forecast...</h5>
                        </div>
                    </div>

                    <!-- KPI Cards Section -->
                    <div class="row mb-4" id="kpi_section" style="display: none;">
                        <!-- Total Forecast Qty -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card h-100 py-2 border-left-primary shadow">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">ยอดพยากรณ์รวม (Auto Forecast Qty)</div>
                                            <div class="h3 mb-0 font-weight-bold text-gray-800" id="kpi_forecast_qty">0</div>
                                            <div class="text-xs mt-1" id="kpi_growth_badge">---</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-chart-line fa-2x text-primary"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actual Sales Target Month -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card h-100 py-2 border-left-danger shadow">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">ยอดขายจริงเดือนนี้ (Actual Target Sales)</div>
                                            <div class="h3 mb-0 font-weight-bold text-gray-800" id="kpi_actual_target_qty">0</div>
                                            <div class="text-xs mt-1" id="kpi_accuracy_badge">---</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-shopping-cart fa-2x text-danger"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Total Estimated Value -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card h-100 py-2 border-left-success shadow">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">ประเมินมูลค่ารวม (Est. Revenue)</div>
                                            <div class="h3 mb-0 font-weight-bold text-gray-800" id="kpi_forecast_val">0 ฿</div>
                                            <div class="text-xs text-muted mt-1">คำนวณตามราคาขายเฉลี่ย</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-coins fa-2x text-success"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Avg 3M Baseline -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card h-100 py-2 border-left-warning shadow">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">ฐานเฉลี่ย 3 เดือน (Avg 3M Baseline)</div>
                                            <div class="h3 mb-0 font-weight-bold text-gray-800" id="kpi_avg_3m">0</div>
                                            <div class="text-xs text-muted mt-1">ยอดขายเฉลี่ย 3 เดือนล่าสุด</div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-balance-scale fa-2x text-warning"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chart & Table Results Container -->
                    <div id="results_container" style="display: none;">
                        <!-- Comparison Chart Card -->
                        <div class="card shadow mb-4 border-0">
                            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary" id="chart_title"><i class="fas fa-chart-bar mr-2"></i>แผนภูมิเปรียบเทียบ ยอดขายจริงย้อนหลัง vs ยอดพยากรณ์อัตโนมัติ vs ยอดขายจริงเดือนนี้</h6>
                            </div>
                            <div class="card-body">
                                <div style="height: 320px;">
                                    <canvas id="chartForecast"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Forecast Results DataTable Card -->
                        <div class="card shadow mb-4 border-0">
                            <div class="card-header bg-dark text-white py-3 d-flex align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-table mr-2"></i>ตารางแสดงผลการพยากรณ์และเปรียบเทียบยอดขายจริง (Forecast & Actual Table)</h6>
                                <div>
                                    <span class="badge badge-info mr-2" id="badge_group_mode">Group By</span>
                                    <span class="badge badge-primary" id="badge_algo_name">Algorithm</span>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-striped table-forecast" id="dtForecast" width="100%">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>รหัสสินค้า (SKU_CODE)</th>
                                                <th>รายละเอียดสินค้า (SKU_NAME)</th>
                                                <th>ยี่ห้อ</th>
                                                <th>รหัส/ชื่อลูกค้า (Customer)</th>
                                                <th id="th_m3">ม.-3</th>
                                                <th id="th_m2">ม.-2</th>
                                                <th id="th_m1">ม.-1</th>
                                                <th>เฉลี่ย 3ด.</th>
                                                <th>ปีที่แล้ว</th>
                                                <th class="bg-success text-white" id="th_forecast">Auto Forecast Qty</th>
                                                <th class="actual-highlight" id="th_actual">ยอดขายจริง (Actual)</th>
                                                <th>ผลต่าง ยอดจริง vs เป้า</th>
                                                <th>% ความบรรลุเป้า</th>
                                                <th>มูลค่าประเมิน (บาท)</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            <?php
            include('includes/Modal-Logout.php');
            include('includes/Footer.php');
            ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="js/myadmin.min.js"></script>

    <script>
        let calculatedItems = [];
        let forecastChart = null;

        $(document).ready(function () {
            $('.select2').select2({ width: '100%' });

            // Load Customers dropdown
            $.ajax({
                url: 'model/get_ar_name.php',
                method: 'GET',
                dataType: 'json',
                success: function (data) {
                    let select = $('#AR_NAME');
                    select.empty();
                    select.append('<option value="-">ทุกลูกค้า (All Customers)</option>');
                    $.each(data, function (index, item) {
                        select.append($('<option>', {
                            value: item.AR_NAME,
                            text: item.AR_NAME
                        }));
                    });
                }
            });

            // Load Sale / Take names dropdown
            $.ajax({
                url: 'model/get_sale_take_name.php',
                method: 'GET',
                dataType: 'json',
                success: function (data) {
                    let select = $('#SALE_NAME');
                    select.empty();
                    select.append('<option value="-">ทั้งหมด (All Sales)</option>');
                    $.each(data, function (index, sale_take) {
                        select.append($('<option>', {
                            value: sale_take.NAME,
                            text: sale_take.NAME
                        }));
                    });
                }
            });

            // Load Brands
            $.ajax({
                url: 'model/get_sku_brand.php',
                method: 'GET',
                dataType: 'json',
                success: function (data) {
                    let select = $('#BRAND');
                    $.each(data, function (index, item) {
                        select.append($('<option>', {
                            value: item.BRAND,
                            text: item.BRAND
                        }));
                    });
                }
            });

            // Load Categories
            $.ajax({
                url: 'model/get_sku_cat.php',
                method: 'GET',
                dataType: 'json',
                success: function (data) {
                    let select = $('#SKU_CAT');
                    $.each(data, function (index, item) {
                        select.append($('<option>', {
                            value: item.SKU_CAT,
                            text: item.SKU_CAT
                        }));
                    });
                }
            });
        });

        function runAutoForecast() {
            $('#spinner_loading').show();
            let formData = $('#form_auto_forecast').serialize() + '&action=calculate';

            $.ajax({
                url: 'export_process/forecast_auto_process.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function (res) {
                    $('#spinner_loading').hide();

                    if (res.status === 'success') {
                        calculatedItems = res.items;

                        // Display Summary KPIs
                        $('#kpi_forecast_qty').text(numberWithCommas(res.summary.total_forecast_qty));
                        $('#kpi_actual_target_qty').text(numberWithCommas(res.summary.total_actual_target_qty));
                        $('#kpi_forecast_val').text(numberWithCommas(res.summary.total_forecast_value) + ' ฿');
                        $('#kpi_avg_3m').text(numberWithCommas(res.summary.total_avg_3m));

                        let gBadge = res.summary.growth_vs_avg3m >= 0 
                            ? `<span class="badge badge-success font-weight-bold"><i class="fas fa-arrow-up text-white"></i> +${res.summary.growth_vs_avg3m}% เทียบ Avg 3M</span>`
                            : `<span class="badge badge-danger font-weight-bold"><i class="fas fa-arrow-down text-white"></i> ${res.summary.growth_vs_avg3m}% เทียบ Avg 3M</span>`;
                        $('#kpi_growth_badge').html(gBadge);

                        let accBadge = res.summary.total_accuracy_percent >= 100 
                            ? `<span class="badge badge-success font-weight-bold"><i class="fas fa-check-circle text-white"></i> ${res.summary.total_accuracy_percent}% บรรลุเป้าหมาย</span>`
                            : `<span class="badge badge-danger font-weight-bold"><i class="fas fa-exclamation-circle text-white"></i> ${res.summary.total_accuracy_percent}% บรรลุเป้าหมาย</span>`;
                        $('#kpi_accuracy_badge').html(accBadge);

                        // Dynamic Month Name Headers
                        if (res.month_labels) {
                            $('#th_m3').text(res.month_labels.m3);
                            $('#th_m2').text(res.month_labels.m2);
                            $('#th_m1').text(res.month_labels.m1);
                            $('#th_forecast').text('พยากรณ์ ' + res.month_labels.target);
                            $('#th_actual').text('ขายจริง ' + res.month_labels.target);
                        }

                        if (res.group_by === 'SKU_ONLY') {
                            $('#badge_group_mode').text('สรุปตามรายสินค้า (SKU_CODE)');
                            $('#chart_title').html('<i class="fas fa-chart-bar mr-2"></i>แผนภูมิเปรียบเทียบ ยอดขายจริงย้อนหลัง vs ยอดพยากรณ์ vs ยอดขายจริงเดือนนี้ (Top 10 SKU)');
                        } else if (res.group_by === 'AR_ONLY') {
                            $('#badge_group_mode').text('สรุปตามรายลูกค้า (AR_NAME)');
                            $('#chart_title').html('<i class="fas fa-chart-bar mr-2"></i>แผนภูมิเปรียบเทียบ ยอดขายจริงย้อนหลัง vs ยอดพยากรณ์ vs ยอดขายจริงเดือนนี้ (Top 10 AR)');
                        } else {
                            $('#badge_group_mode').text('สรุปตามรายลูกค้า + สินค้า (AR & SKU)');
                            $('#chart_title').html('<i class="fas fa-chart-bar mr-2"></i>แผนภูมิเปรียบเทียบ ยอดขายจริงย้อนหลัง vs ยอดพยากรณ์ vs ยอดขายจริงเดือนนี้ (Top 10 รายการ)');
                        }

                        let algoText = $('#algorithm option:selected').text();
                        $('#badge_algo_name').text(algoText);

                        $('#kpi_section').fadeIn();
                        $('#results_container').fadeIn();

                        $('#btnSaveSystem').prop('disabled', false);
                        $('#btnExportCSV').prop('disabled', false);

                        // Render DataTable
                        renderTable(calculatedItems, res.group_by);

                        // Render Comparison Chart
                        renderChart(calculatedItems, res.group_by, res.month_labels);

                        Swal.fire({
                            icon: 'success',
                            title: 'คำนวณสำเร็จ!',
                            text: `ระบบทำการคำนวณ Auto Forecast เรียบร้อยแล้ว ทั้งหมด ${res.summary.total_items} รายการ`,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire('ข้อผิดพลาด', res.message || 'เกิดข้อผิดพลาดในการคำนวณ', 'error');
                    }
                },
                error: function (xhr, status, error) {
                    $('#spinner_loading').hide();
                    Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์: ' + error, 'error');
                }
            });
        }

        function renderTable(items, groupByMode) {
            let tbody = $('#dtForecast tbody');
            tbody.empty();

            if (items.length === 0) {
                tbody.append('<tr><td colspan="15" class="text-center text-muted py-4">ไม่พบข้อมูลตามเงื่อนไขที่เลือก</td></tr>');
                return;
            }

            let html = '';
            $.each(items, function (i, r) {
                let diffBadge = r.diff_actual_vs_forecast >= 0 
                    ? `<span class="text-success font-weight-bold">+${r.diff_actual_vs_forecast}</span>`
                    : `<span class="text-danger font-weight-bold">${r.diff_actual_vs_forecast}</span>`;

                let accPctBadge = r.accuracy_percent >= 100 
                    ? `<span class="badge badge-success">+${r.accuracy_percent}%</span>`
                    : `<span class="badge badge-warning text-dark">${r.accuracy_percent}%</span>`;

                let arDisplay = (groupByMode === 'SKU_ONLY')
                    ? `<span class="badge badge-light border text-muted">ทุกลูกค้า</span>`
                    : `<strong>${escapeHtml(r.ar_name)}</strong> <br><small class="text-muted">${escapeHtml(r.ar_code)}</small>`;

                let skuDisplay = (groupByMode === 'AR_ONLY') 
                    ? `<span class="badge badge-light border text-muted">รวมทุกสินค้า</span>`
                    : `<strong>${escapeHtml(r.sku_code)}</strong>`;

                let skuNameDisplay = (groupByMode === 'AR_ONLY')
                    ? `<span class="text-muted">รวมทุกสินค้าของลูกค้า</span>`
                    : `<small>${escapeHtml(r.sku_name)}</small>`;

                html += `
                    <tr>
                        <td class="text-center">${i + 1}</td>
                        <td class="text-center">${skuDisplay}</td>
                        <td>${skuNameDisplay}</td>
                        <td class="text-center"><span class="badge badge-info">${escapeHtml(r.brand)}</span></td>
                        <td>${arDisplay}</td>
                        <td class="text-right">${numberWithCommas(r.qty_m3)}</td>
                        <td class="text-right">${numberWithCommas(r.qty_m2)}</td>
                        <td class="text-right">${numberWithCommas(r.qty_m1)}</td>
                        <td class="text-right font-weight-bold text-primary">${numberWithCommas(r.avg_3m)}</td>
                        <td class="text-right">${numberWithCommas(r.qty_yoy)}</td>
                        <td class="text-right forecast-highlight">
                            <input type="number" class="form-control form-control-sm text-right font-weight-bold text-success border-success" 
                                   value="${r.forecast_qty}" onchange="updateItemForecast(${i}, this.value)">
                        </td>
                        <td class="text-right actual-highlight">${numberWithCommas(r.qty_actual_target)}</td>
                        <td class="text-right">${diffBadge}</td>
                        <td class="text-center">${accPctBadge}</td>
                        <td class="text-right font-weight-bold">${numberWithCommas(r.est_value)} ฿</td>
                    </tr>
                `;
            });
            tbody.html(html);
        }

        function updateItemForecast(index, val) {
            let newQty = parseFloat(val) || 0;
            if (newQty < 0) newQty = 0;

            calculatedItems[index].forecast_qty = newQty;
            calculatedItems[index].diff_qty = Math.round((newQty - calculatedItems[index].avg_3m) * 100) / 100;
            calculatedItems[index].diff_percent = calculatedItems[index].avg_3m > 0 
                ? Math.round(((calculatedItems[index].diff_qty / calculatedItems[index].avg_3m) * 100) * 100) / 100 
                : 0;
            calculatedItems[index].diff_actual_vs_forecast = Math.round((calculatedItems[index].qty_actual_target - newQty) * 100) / 100;
            calculatedItems[index].accuracy_percent = newQty > 0 
                ? Math.round(((calculatedItems[index].qty_actual_target / newQty) * 100) * 100) / 100 
                : 0;
            calculatedItems[index].est_value = Math.round((newQty * calculatedItems[index].unit_price) * 100) / 100;

            // Recalculate Totals
            let totalQty = 0, totalVal = 0, totalAvg = 0, totalActual = 0;
            $.each(calculatedItems, function (i, r) {
                totalQty += r.forecast_qty;
                totalVal += r.est_value;
                totalAvg += r.avg_3m;
                totalActual += r.qty_actual_target;
            });

            $('#kpi_forecast_qty').text(numberWithCommas(totalQty));
            $('#kpi_actual_target_qty').text(numberWithCommas(totalActual));
            $('#kpi_forecast_val').text(numberWithCommas(totalVal) + ' ฿');
        }

        function renderChart(items, groupByMode, monthLabels) {
            let topItems = items.slice(0, 10);
            let labels = topItems.map(x => (groupByMode === 'AR_ONLY') ? x.ar_name : x.sku_code);
            let dataM3 = topItems.map(x => x.qty_m3);
            let dataM2 = topItems.map(x => x.qty_m2);
            let dataM1 = topItems.map(x => x.qty_m1);
            let dataAvg = topItems.map(x => x.avg_3m);
            let dataForecast = topItems.map(x => x.forecast_qty);
            let dataActual = topItems.map(x => x.qty_actual_target);

            let lblM3 = (monthLabels && monthLabels.m3) ? monthLabels.m3 : 'ม.-3';
            let lblM2 = (monthLabels && monthLabels.m2) ? monthLabels.m2 : 'ม.-2';
            let lblM1 = (monthLabels && monthLabels.m1) ? monthLabels.m1 : 'ม.-1';
            let lblTarget = (monthLabels && monthLabels.target) ? monthLabels.target : 'เดือนเป้าหมาย';

            let ctx = document.getElementById('chartForecast').getContext('2d');
            if (forecastChart) forecastChart.destroy();

            forecastChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [
                        { label: lblM3, data: dataM3, backgroundColor: '#9e9e9e' },
                        { label: lblM2, data: dataM2, backgroundColor: '#64b5f6' },
                        { label: lblM1, data: dataM1, backgroundColor: '#2196f3' },
                        { label: 'เฉลี่ย 3ด.', data: dataAvg, backgroundColor: '#ff9800', type: 'line', borderColor: '#ff9800', borderWidth: 2, fill: false },
                        { label: 'พยากรณ์ (' + lblTarget + ')', data: dataForecast, backgroundColor: '#4caf50' },
                        { label: 'ขายจริง (' + lblTarget + ')', data: dataActual, backgroundColor: '#e91e63' }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'top' },
                        title: { display: true, text: (groupByMode === 'AR_ONLY') ? 'เปรียบเทียบยอดขาย Top 10 ลูกค้า (AR)' : 'เปรียบเทียบยอดขาย Top 10 สินค้า (SKU)' }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });
        }

        function saveToSystem() {
            if (calculatedItems.length === 0) return;

            Swal.fire({
                title: 'ยืนยันบันทึกเข้าระบบ?',
                text: `คุณต้องการบันทึกแผนการพยากรณ์จำนวน ${calculatedItems.length} รายการ เข้าระบบวางแผนการขายใช่หรือไม่?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '<i class="fas fa-check"></i> บันทึกเข้าระบบ',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#spinner_loading').show();

                    let payload = {
                        action: 'save',
                        target_month: $('#target_month').val(),
                        target_year: $('#target_year').val(),
                        group_by: $('#group_by').val(),
                        algorithm: $('#algorithm').val(),
                        growth_factor: $('#growth_factor').val(),
                        items: calculatedItems
                    };

                    $.ajax({
                        url: 'export_process/forecast_auto_process.php',
                        type: 'POST',
                        contentType: 'application/json',
                        data: JSON.stringify(payload),
                        dataType: 'json',
                        success: function (res) {
                            $('#spinner_loading').hide();
                            if (res.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'บันทึกสำเร็จ!',
                                    text: res.message,
                                    confirmButtonText: 'ตกลง'
                                });
                            } else {
                                Swal.fire('ข้อผิดพลาด', res.message || 'ไม่สามารถบันทึกข้อมูลได้', 'error');
                            }
                        },
                        error: function (xhr, status, error) {
                            $('#spinner_loading').hide();
                            Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการบันทึก: ' + error, 'error');
                        }
                    });
                }
            });
        }

        function exportForecastCSV() {
            let form = $('#form_auto_forecast');
            let origAction = form.attr('action');
            form.attr('action', 'export_process/forecast_auto_process.php?action=export_csv');
            form.attr('target', '_blank');
            form.submit();
            form.removeAttr('target');
            form.attr('action', origAction || '');
        }

        function resetForm() {
            $('#form_auto_forecast')[0].reset();
            $('.select2').val('-').trigger('change');
            $('#kpi_section').fadeOut();
            $('#results_container').fadeOut();
            $('#btnSaveSystem').prop('disabled', true);
            $('#btnExportCSV').prop('disabled', true);
            calculatedItems = [];
        }

        function numberWithCommas(x) {
            if (x === null || x === undefined) return "0";
            return x.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }

        function escapeHtml(text) {
            if (!text) return "";
            return text
                .replace(/&/g, "&amp;")
                .replace(/</g, "&lt;")
                .replace(/>/g, "&gt;")
                .replace(/"/g, "&quot;")
                .replace(/'/g, "&#039;");
        }
    </script>
    </body>
    </html>
<?php } ?>
