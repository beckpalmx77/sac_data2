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

// GET Parameters from main page
$init_month = $_GET['month'] ?? date('m');
$init_year = $_GET['year'] ?? date('Y');
$init_sale = $_GET['sale_name'] ?? '';
$init_take = $_GET['take_name'] ?? '';
$init_cust = $_GET['ar_name'] ?? '';
$init_prov = $_GET['province'] ?? '';
$init_amph = $_GET['amphur'] ?? '';
$init_zone = $_GET['zone'] ?? '';

// Filter options for dropdowns
$where_cust = "WHERE 1=1";
$params_cust = [];

if ($account_type === 'sale_user') {
    $where_cust .= " AND sale_name = :first_name";
    $params_cust[':first_name'] = $first_name;
} elseif ($account_type === 'take') {
    $where_cust .= " AND take_name = :first_name";
    $params_cust[':first_name'] = $first_name;
}

$stmt_filters = $conn->prepare("SELECT sale_name, take_name FROM ims_tires_plan_forecast_realtime $where_cust");
$stmt_filters->execute($params_cust);
$filter_rows = $stmt_filters->fetchAll(PDO::FETCH_ASSOC);

$sales_set = [];
$takes_set = [];
foreach ($filter_rows as $fr) {
    if (!empty($fr['sale_name'])) $sales_set[$fr['sale_name']] = true;
    if (!empty($fr['take_name'])) $takes_set[$fr['take_name']] = true;
}
$sales_list = array_keys($sales_set); sort($sales_list);
$takes_list = array_keys($takes_set); sort($takes_list);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สรุป 20 ร้านค้ายอดขายสูงสุด (ของแต่ละ Sale / Take)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Prompt', sans-serif; background-color: #f1f5f9; margin: 15px; }
        .card { border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .top-header { background: linear-gradient(135deg, #064e3b 0%, #059669 100%); color: white; border-radius: 12px; padding: 15px 20px; }
        .filter-label { color: #0f172a; font-weight: 700; font-size: 0.9rem; }
        .stat-card-title { color: #334155; font-weight: 700; font-size: 0.85rem; }
        
        table.dataTable thead tr th {
            background-color: #e2e8f0 !important;
            color: #000000 !important;
            font-weight: 700 !important;
            font-size: 0.85rem !important;
            text-align: center !important;
            vertical-align: middle !important;
            border-color: #cbd5e1 !important;
            white-space: nowrap !important;
        }
        table.dataTable tbody td {
            white-space: nowrap !important;
            color: #0f172a !important;
            font-size: 0.85rem !important;
        }
        .select2-container--bootstrap-5 .select2-selection {
            border-color: #94a3b8 !important;
            color: #0f172a !important;
            font-weight: 600 !important;
        }
        .select2-container--bootstrap-5 .select2-selection__rendered {
            color: #0f172a !important;
            font-weight: 600 !important;
        }
    </style>
</head>
<body>

    <!-- Header Section -->
    <div class="top-header mb-3 shadow-sm d-flex flex-wrap align-items-center justify-content-between">
        <div class="d-flex align-items-center mb-2 mb-md-0">
            <div class="bg-white text-success rounded-circle p-2 me-3 shadow-sm d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                <i class="fas fa-store fa-lg text-success"></i>
            </div>
            <div>
                <h4 class="fw-bold mb-0 text-white">
                    สรุป 20 ร้านค้ายอดขายสูงสุด (ของแต่ละ Sale / Take)
                </h4>
                <div class="small text-white-50">
                    ประจำเดือน: <span id="popupShowMonthYear" class="fw-bold text-warning"></span>
                </div>
            </div>
        </div>
        <div>
            <button type="button" onclick="closeAndReturnMain()" class="btn btn-danger fw-bold shadow-sm px-3">
                <i class="fas fa-times-circle me-1"></i> ปิดหน้าต่าง / กลับหน้าหลัก
            </button>
        </div>
    </div>

    <!-- Active Auto-Complete Filters -->
    <div class="card mb-3 border border-secondary-subtle shadow-sm bg-white">
        <div class="card-body p-3">
            <div class="row g-3 align-items-center">
                <div class="col-md-3">
                    <label class="form-label filter-label mb-1">
                        <i class="fas fa-calendar-alt text-primary me-1"></i> เลือกเดือน:
                    </label>
                    <select id="popFilterMonth" class="form-select form-select-sm fw-bold text-dark">
                        <?php foreach ($thai_months as $m_num => $m_name): 
                            $sel = ($m_num == (int)$init_month) ? 'selected' : '';
                            echo "<option value='$m_num' $sel>$m_name</option>";
                        endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label filter-label mb-1">
                        <i class="fas fa-calendar text-primary me-1"></i> เลือกปี:
                    </label>
                    <select id="popFilterYear" class="form-select form-select-sm fw-bold text-dark">
                        <?php 
                        $cur_y = (int)date('Y');
                        for ($y = $cur_y - 2; $y <= $cur_y + 2; $y++): 
                            $sel = ($y == (int)$init_year) ? 'selected' : '';
                            echo "<option value='$y' $sel>$y</option>";
                        endfor; 
                        ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label filter-label mb-1">
                        <i class="fas fa-user-tie text-primary me-1"></i> ชื่อพนักงานขาย (Sale):
                    </label>
                    <select id="popFilterSale" class="form-select form-select-sm fw-bold text-dark border-secondary">
                        <option value="">-- ทั้งหมด --</option>
                        <?php foreach ($sales_list as $sale): 
                            $is_locked = ($account_type !== 'admin' && $account_type !== 'sale_sup');
                            $is_me = $is_locked && ($sale === $first_name);
                            $selected = ($sale === $init_sale) ? 'selected' : ($is_me ? 'selected' : '');
                            $disabled = ($is_locked && !$is_me) ? 'disabled' : '';
                            echo "<option value='".htmlspecialchars($sale)."' $selected $disabled>".htmlspecialchars($sale)."</option>";
                        endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label filter-label mb-1">
                        <i class="fas fa-user-tag text-info me-1"></i> ชื่อพนักงานเทค (Take):
                    </label>
                    <select id="popFilterTake" class="form-select form-select-sm fw-bold text-dark border-secondary">
                        <option value="">-- ทั้งหมด --</option>
                        <?php foreach ($takes_list as $take): 
                            $is_locked = ($account_type === 'take');
                            $is_me = $is_locked && ($take === $first_name);
                            $selected = ($take === $init_take) ? 'selected' : ($is_me ? 'selected' : '');
                            $disabled = ($is_locked && !$is_me) ? 'disabled' : '';
                            echo "<option value='".htmlspecialchars($take)."' $selected $disabled>".htmlspecialchars($take)."</option>";
                        endforeach; ?>
                    </select>
                </div>
            </div>
        </div>
    </div>



    <!-- Main Table Container -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-3 bg-white rounded">
            <div class="table-responsive">
                <table id="popStoreTable" class="table table-bordered table-striped table-hover align-middle w-100 small">
                    <thead class="table-light text-dark text-center align-middle" style="background-color: #e2e8f0 !important; color: #000000 !important;">
                        <tr style="color: #000000 !important;">
                            <th style="color: #000000 !important; font-weight: 700;">อันดับ</th>
                            <th style="color: #000000 !important; font-weight: 700;">รหัสร้านค้า</th>
                            <th style="color: #000000 !important; font-weight: 700;">ชื่อร้านค้า</th>
                            <th style="color: #000000 !important; font-weight: 700;">พนักงานขาย (Sale)</th>
                            <th style="color: #000000 !important; font-weight: 700;">พนักงานเทค (Take)</th>
                            <th style="color: #000000 !important; font-weight: 700;" class="text-center">จำนวน SKU</th>
                            <th style="color: #000000 !important; font-weight: 700;" class="text-end">เฉลี่ย 12ด.</th>
                            <th style="color: #000000 !important; font-weight: 700;" class="text-end">เฉลี่ย 3ด.</th>
                            <th style="color: #000000 !important; font-weight: 700;" class="text-end" id="thPopStoreActualSales">ยอดขายจริงรวม</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Loaded dynamically via DataTables -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function closeAndReturnMain() {
            if (window.opener && !window.opener.closed) {
                window.opener.focus();
            }
            window.close();
        }

        window.addEventListener('beforeunload', function() {
            if (window.opener && !window.opener.closed) {
                window.opener.focus();
            }
        });

        $(document).ready(function() {
            let popStoreTable = null;

            $('#popFilterSale, #popFilterTake, #popFilterMonth, #popFilterYear').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: '-- ทั้งหมด --',
                allowClear: true
            });

            $('#popFilterSale, #popFilterTake, #popFilterMonth, #popFilterYear').on('change', function() {
                loadPopupData();
            });

            loadPopupData();

            function loadPopupData() {
                const month = $('#popFilterMonth').val() || '<?= $init_month ?>';
                const year = $('#popFilterYear').val() || '<?= $init_year ?>';
                const sale_name = $('#popFilterSale').val() || '';
                const take_name = $('#popFilterTake').val() || '';
                const ar_name = '<?= htmlspecialchars($init_cust) ?>';
                const province = '<?= htmlspecialchars($init_prov) ?>';
                const amphur = '<?= htmlspecialchars($init_amph) ?>';
                const zone = '<?= htmlspecialchars($init_zone) ?>';

                const monthName = $('#popFilterMonth option:selected').text();
                $('#popupShowMonthYear').text(`${monthName} ${year}`);
                $('#popActualCardTitle').text(`ยอดขายจริงรวม (${monthName})`);
                $('#thPopStoreActualSales').text(`ยอดขายจริงรวม (${monthName})`);

                Swal.fire({
                    title: 'กำลังโหลดข้อมูล Top 20 ร้านค้า',
                    text: 'กรุณารอสักครู่...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: 'forecast_fetch_top20_sku_realtime.php',
                    type: 'POST',
                    data: {
                        month: month,
                        year: year,
                        sale_name: sale_name,
                        take_name: take_name,
                        ar_name: ar_name,
                        province: province,
                        amphur: amphur,
                        zone: zone
                    },
                    dataType: 'json',
                    success: function(response) {
                        Swal.close();
                        if (response.success) {
                            const topStores = response.top_stores || [];

                            let storeActualSum = 0;
                            topStores.forEach(s => {
                                storeActualSum += s.total_actual || 0;
                            });

                            $('#popTotalActual').text(storeActualSum.toLocaleString('en-US', {maximumFractionDigits: 0}));
                            $('#popStoreCount').text(topStores.length.toLocaleString('en-US'));

                            if (popStoreTable) {
                                popStoreTable.clear();
                                popStoreTable.rows.add(topStores);
                                popStoreTable.draw();
                            } else {
                                popStoreTable = $('#popStoreTable').DataTable({
                                    data: topStores,
                                    destroy: true,
                                    pageLength: 20,
                                    scrollX: true,
                                    order: [[8, 'desc']],
                                    columns: [
                                        { data: 'rank', className: 'text-center fw-bold' },
                                        { data: 'ar_code', className: 'fw-bold' },
                                        { data: 'ar_name', className: 'fw-bold text-dark' },
                                        { data: 'sale_name' },
                                        { data: 'take_name', render: function(val) { return val || '-'; } },
                                        { data: 'sku_count', className: 'text-center fw-bold text-info' },
                                        { data: 'total_avg_12m', className: 'text-end', render: $.fn.dataTable.render.number(',', '.', 0) },
                                        { data: 'total_avg_3m', className: 'text-end', render: $.fn.dataTable.render.number(',', '.', 0) },
                                        { data: 'total_actual', className: 'text-end fw-bold text-success', render: $.fn.dataTable.render.number(',', '.', 0) }
                                    ],
                                    language: {
                                        search: "ค้นหาในตาราง:",
                                        lengthMenu: "แสดง _MENU_ รายการ",
                                        info: "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
                                        paginate: { first: "แรก", last: "สุดท้าย", next: "ถัดไป", previous: "ย้อนกลับ" }
                                    }
                                });
                            }

                            setTimeout(() => {
                                $.fn.dataTable.tables({ visible: true, api: true }).columns.adjust();
                            }, 200);

                        } else {
                            Swal.fire('ข้อผิดพลาด', response.message || 'ไม่สามารถโหลดข้อมูลได้', 'error');
                        }
                    },
                    error: function() {
                        Swal.close();
                        Swal.fire('ข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
                    }
                });
            }
        });
    </script>
</body>
</html>
