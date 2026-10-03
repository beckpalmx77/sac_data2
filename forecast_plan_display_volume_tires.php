<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
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

$stmt_filters = $conn->prepare("SELECT sale_name, take_name, ar_name, province, amphur, zone, brand_code, product_type FROM ims_tires_plan_forecast_volume_tires $where_cust");
$stmt_filters->execute($params_cust);
$filter_rows = $stmt_filters->fetchAll(PDO::FETCH_ASSOC);

$sales_set = [];
$takes_set = [];
$cust_set = [];
$prov_set = [];
$amphur_set = [];
$zone_set = [];
$brand_set = [];
$type_set = [];

foreach ($filter_rows as $r) {
    if (!empty($r['sale_name'])) $sales_set[$r['sale_name']] = true;
    if (!empty($r['take_name'])) $takes_set[$r['take_name']] = true;
    if (!empty($r['ar_name'])) $cust_set[$r['ar_name']] = true;
    if (!empty($r['province'])) $prov_set[$r['province']] = true;
    if (!empty($r['amphur'])) $amphur_set[$r['amphur']] = true;
    if (!empty($r['zone'])) $zone_set[$r['zone']] = true;
    if (!empty($r['brand_code'])) $brand_set[$r['brand_code']] = true;
    if (!empty($r['product_type'])) $type_set[$r['product_type']] = true;
}

$sales_list = array_keys($sales_set); sort($sales_list);
$takes_list = array_keys($takes_set); sort($takes_list);
$customers_list = array_keys($cust_set); sort($customers_list);
$provinces_list = array_keys($prov_set); sort($provinces_list);
$amphurs_list = array_keys($amphur_set); sort($amphurs_list);
$zones_list = array_keys($zone_set); sort($zones_list);
$brands_list = array_keys($brand_set); sort($brands_list);
$types_list = array_keys($type_set); sort($types_list);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข้อมูล Tires Plan Forecast (Volume Tires - ยาง Volume)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/fixedcolumns/4.3.0/css/fixedColumns.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Prompt', sans-serif; background-color: #f8f9fa; margin: 15px; }
        .card { border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .card-header { background: linear-gradient(135deg, #1e293b 0%, #334155 100%); color: white; border-radius: 12px 12px 0 0 !important; border-bottom: 3px solid #f59e0b; }
        th, td { white-space: nowrap; font-size: 0.84rem; vertical-align: middle; }
        th { background-color: #f8f9fa !important; }
        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .compare-1, .compare-pct { background-color: #e9ecef !important; }
        .locked-input, input[readonly].form-control {
            background-color: #f8f9fa !important;
            color: #6c757d !important;
            cursor: not-allowed !important;
        }
        .btn-group-responsive .btn {
            white-space: nowrap !important;
            font-size: 0.8rem !important;
            padding: 0.375rem 0.4rem !important;
        }
        .badge-vol {
            background-color: #fef3c7;
            color: #92400e;
            font-weight: 700;
            padding: 4px 8px;
            border-radius: 6px;
        }
        .badge-dept {
            background-color: #f59e0b;
            color: #ffffff;
            font-size: 0.78rem;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 600;
        }
        .swal2-popup h2::before { display: none !important; content: none !important; }
        /* Ensure fixed columns have a solid background color when scrolling */
        th.dtfc-fixed-left, td.dtfc-fixed-left {
            background-color: #ffffff !important;
            z-index: 1;
        }
        th.dtfc-fixed-left {
            background-color: #f8f9fa !important;
            z-index: 2;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="card">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap py-1">
                    <div class="d-flex align-items-center">
                        <h4 class="mb-0 me-2"><i class="fas fa-boxes me-2 text-warning"></i> Tires Plan วางแผนการขาย (ยางใหญ่ 2 )</h4>
                    </div>
                    <div class="d-flex flex-wrap gap-2 justify-content-md-end mt-2 mt-md-0">
                        <button type="button" class="btn btn-success btn-sm fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#uploadRealtimeModal" style="background: linear-gradient(135deg, #198754 0%, #146c43 100%); border: 1px solid #146c43;">
                            <i class="fas fa-file-import me-1"></i> นำเข้า Realtime Excel
                        </button>
                        <a href="import_process/template/template_v_tires.xlsx" class="btn btn-outline-light btn-sm fw-bold shadow-sm">
                            <i class="fas fa-file-download me-1"></i> Download Template
                        </a>
                        <a href="export_data_v_template.php" class="btn btn-warning btn-sm fw-bold shadow-sm text-dark" title="ดึงข้อมูลยอดขายสำหรับเตรียมทำ PLAN ยาง (ตาม template_v_tires.xlsx)">
                            <i class="fas fa-file-export me-1"></i> Export ข้อมูลยอดขาย สำหรับ PLAN ยาง
                        </a>
                        <a href="plan_forecast_tires_manual_realtime.html" target="_blank" class="btn btn-outline-warning btn-sm fw-bold">
                            <i class="fas fa-info-circle me-1"></i> คู่มือการใช้งานระบบ
                        </a>
                        <button type="button" id="btnExportExcel" class="btn btn-light text-success btn-sm fw-bold shadow-sm">
                            <i class="fas fa-file-excel me-1"></i> Export Excel
                        </button>
                        <button type="button" id="btnDeleteByConditions" class="btn btn-danger btn-sm fw-bold">
                            <i class="fas fa-trash-alt me-1"></i> ลบข้อมูลตามเงื่อนไข
                        </button>
                        <?php if ($account_type === 'admin' || $account_type === 'sale_sup'): ?>
                            <button type="button" id="btnUnlockMonth" class="btn btn-outline-success btn-sm fw-bold shadow-sm" title="ปลดล็อคข้อมูลเดือนที่เลือก เพื่อเปิดให้แก้ไขได้">
                                <i class="fas fa-lock-open me-1"></i> ปลดล็อคเดือนนี้
                            </button>
                        <?php endif; ?>
                        <button type="button" id="btnLockMonth" class="btn btn-outline-danger btn-sm fw-bold shadow-sm" title="ยืนยันล็อคข้อมูลเดือนที่เลือก เพื่อป้องกันการแก้ไข">
                            <i class="fas fa-lock me-1"></i> ยืนยันล็อคเดือนนี้
                        </button>
                        <button type="button" id="btnSaveAll" class="btn btn-warning btn-sm fw-bold">
                            <i class="fas fa-save me-1"></i> บันทึกการแก้ไขทั้งหมด
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" onclick="window.close();">
                            <i class="fas fa-times me-1"></i> Close
                        </button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <!-- Status Banner -->
                <div id="monthLockBanner" class="alert alert-secondary d-none py-2 px-3 mb-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span id="monthLockIcon" class="me-2"><i class="fas fa-info-circle"></i></span>
                        <span id="monthLockText">กำลังตรวจสอบสถานะข้อมูล...</span>
                    </div>
                    <span id="monthLockBadge" class="badge bg-secondary">-</span>
                </div>

                <!-- Filter Row 1 -->
                <div class="row g-3 mb-2">
                    <div class="col-md-2">
                        <label class="form-label fw-bold">เลือกเดือน:</label>
                        <select id="filterMonth" class="form-select">
                            <option value="">-- เลือกเดือน --</option>
                            <?php 
                            $current_month = intval(date('m'));
                            foreach ($thai_months as $num => $name): 
                                $selected = ($num == $current_month) ? 'selected' : '';
                            ?>
                                <option value="<?= $num ?>" <?= $selected ?>><?= $name ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">เลือกปี:</label>
                        <select id="filterYear" class="form-select">
                            <?php 
                            $current_year = date('Y');
                            for ($y = $current_year + 1; $y >= $current_year - 2; $y--): 
                                $selected = ($y == $current_year) ? 'selected' : '';
                            ?>
                                <option value="<?= $y ?>" <?= $selected ?>><?= $y ?></option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">พนักงานขาย:</label>
                        <select id="filterSale" class="form-select">
                            <option value="">-- ทั้งหมด --</option>
                            <?php foreach ($sales_list as $sale): 
                                $is_locked = ($account_type === 'sale_user');
                                $is_me = $is_locked && ($sale === $first_name);
                                $selected = $is_me ? 'selected' : '';
                                $disabled = ($is_locked && !$is_me) ? 'disabled' : '';
                                echo "<option value='".htmlspecialchars($sale)."' $selected $disabled>".htmlspecialchars($sale)."</option>";
                            endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">คนเทค:</label>
                        <select id="filterTake" class="form-select">
                            <option value="">-- ทั้งหมด --</option>
                            <?php foreach ($takes_list as $take): 
                                $is_locked = ($account_type === 'take');
                                $is_me = $is_locked && ($take === $first_name);
                                $selected = $is_me ? 'selected' : '';
                                $disabled = ($is_locked && !$is_me) ? 'disabled' : '';
                                echo "<option value='".htmlspecialchars($take)."' $selected $disabled>".htmlspecialchars($take)."</option>";
                            endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">ชื่อลูกค้า:</label>
                        <select id="filterCustomer" class="form-select">
                            <option value="">-- ทั้งหมด --</option>
                            <?php foreach ($customers_list as $cust): ?>
                                <option value="<?= htmlspecialchars($cust) ?>"><?= htmlspecialchars($cust) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Filter Row 2 -->
                <div class="row g-2 mb-3">
                    <div class="col-md-2">
                        <label class="form-label fw-bold">ยี่ห้อ (Brand):</label>
                        <select id="filterBrand" class="form-select form-select-sm">
                            <option value="">-- ทั้งหมด --</option>
                            <?php foreach ($brands_list as $b): ?>
                                <option value="<?= htmlspecialchars($b) ?>"><?= htmlspecialchars($b) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">ประเภทสินค้า:</label>
                        <select id="filterProductType" class="form-select form-select-sm">
                            <option value="">-- ทั้งหมด --</option>
                            <?php foreach ($types_list as $t): ?>
                                <option value="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($t) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">แพลนยาง:</label>
                        <select id="filterQtyCondition" class="form-select form-select-sm">
                            <option value="all">-- ทั้งหมด (รวมค่า 0) --</option>
                            <option value="gt0_forecast">แพลนยาง > 0</option>
                            <option value="eq0_both">แพลนยาง = 0</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">จังหวัด:</label>
                        <select id="filterProvince" class="form-select form-select-sm">
                            <option value="">-- ทั้งหมด --</option>
                            <?php foreach ($provinces_list as $prov): ?>
                                <option value="<?= htmlspecialchars($prov) ?>"><?= htmlspecialchars($prov) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">อำเภอ:</label>
                        <select id="filterAmphur" class="form-select form-select-sm">
                            <option value="">-- ทั้งหมด --</option>
                            <?php foreach ($amphurs_list as $amphur): ?>
                                <option value="<?= htmlspecialchars($amphur) ?>"><?= htmlspecialchars($amphur) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2 align-self-end">
                        <div class="btn-group w-100 btn-group-responsive">
                            <button type="button" id="btnFilter" class="btn btn-primary btn-sm px-2">
                                <i class="fas fa-search me-1"></i> กรองข้อมูล
                            </button>
                            <button type="button" id="btnShowSummary" class="btn btn-warning btn-sm fw-bold px-2">
                                <i class="fas fa-list-alt me-1"></i> สรุปยี่ห้อ
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Customer Info Card -->
                <div id="customerInfoCard" class="card mb-3 d-none border-primary">
                    <div class="card-body bg-light shadow-sm py-2">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h6 class="mb-1 text-primary fw-bold">
                                    <i class="fas fa-store me-2"></i> <span id="infoCustName">-</span>
                                </h6>
                                <div class="small text-muted">
                                    <span class="badge bg-primary me-2"><span id="infoCustCode"></span></span>
                                    พนักงานขาย: <span id="infoSaleName" class="fw-bold"></span> | 
                                    พื้นที่: <span id="infoLocation" class="fw-bold"></span>
                                </div>
                            </div>
                            <div class="col-md-6 border-start-md">
                                <div class="row g-2 text-center">
                                    <div class="col-4">
                                        <div class="small text-muted fw-bold">รวมแพลนยาง</div>
                                        <div class="h5 mb-0 fw-bold text-dark" id="infoTotalForecast">0</div>
                                    </div>
                                    <div class="col-4 border-start">
                                        <div class="small text-muted fw-bold">รวม Volume (ลบ.ม.)</div>
                                        <div class="h5 mb-0 fw-bold text-warning text-dark" id="infoTotalVolume">0.00</div>
                                    </div>
                                    <div class="col-4 border-start">
                                        <div class="small text-muted fw-bold">เฉลี่ย 3 เดือน</div>
                                        <div class="h5 mb-0 fw-bold text-primary" id="infoTotalAvg3M">0.00</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Modal -->
                <div class="modal fade" id="summaryModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header bg-warning text-dark">
                                <h5 class="modal-title fw-bold"><i class="fas fa-chart-bar me-2"></i> สรุปยอดแผนการขายตามยี่ห้อ (Volume Tires)</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div id="summaryContent" class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>ยี่ห้อ (Brand)</th>
                                                <th class="text-center">จำนวนร้านค้า</th>
                                                <th class="text-center">จำนวนรายการ</th>
                                                <th class="text-end">แพลนยาง (เส้น)</th>
                                                <th class="text-end">รวม Volume (ลบ.ม.)</th>
                                                <th class="text-end">เฉลี่ย 3 เดือน</th>
                                                <th class="text-end">ผลต่าง</th>
                                            </tr>
                                        </thead>
                                        <tbody id="summaryTableBody">
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Delete by Conditions Modal -->
                <div class="modal fade" id="deleteConditionsModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                    <div class="modal-dialog">
                        <div class="modal-content border-danger shadow-lg">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title fw-bold"><i class="fas fa-trash-alt me-2"></i> ลบข้อมูลตามเงื่อนไข (Volume Tires)</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="deleteConditionsForm">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">เดือน / ปี ที่ต้องการลบ:</label>
                                        <div class="input-group">
                                            <select id="delMonth" class="form-select" required>
                                                <?php foreach ($thai_months as $num => $name): ?>
                                                    <option value="<?= $num ?>" <?= ($num == $current_month) ? 'selected' : '' ?>><?= $name ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <select id="delYear" class="form-select" required>
                                                <?php for ($y = $current_year + 1; $y >= $current_year - 2; $y--): ?>
                                                    <option value="<?= $y ?>" <?= ($y == $current_year) ? 'selected' : '' ?>><?= $y ?></option>
                                                <?php endfor; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">พนักงานขาย:</label>
                                        <select id="delSale" class="form-select">
                                            <option value="">-- ทั้งหมด --</option>
                                            <?php foreach ($sales_list as $sale): ?>
                                                <option value="<?= htmlspecialchars($sale) ?>"><?= htmlspecialchars($sale) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">ยี่ห้อ (Brand):</label>
                                        <select id="delBrand" class="form-select">
                                            <option value="all">-- ทั้งหมด --</option>
                                            <?php foreach ($brands_list as $b): ?>
                                                <option value="<?= htmlspecialchars($b) ?>"><?= htmlspecialchars($b) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">ประเภทสินค้า:</label>
                                        <select id="delProductType" class="form-select">
                                            <option value="all">-- ทั้งหมด --</option>
                                            <?php foreach ($types_list as $t): ?>
                                                <option value="<?= htmlspecialchars($t) ?>"><?= htmlspecialchars($t) ?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3 p-3 bg-light border rounded text-center">
                                        <div class="small text-muted fw-bold mb-1">จำนวนรายการที่จะถูกลบ</div>
                                        <div class="h3 mb-1 fw-bold text-danger" id="delRecordsCount">0</div>
                                        <button type="button" id="btnPreviewDelete" class="btn btn-outline-primary btn-sm mt-1" disabled>
                                            <i class="fas fa-eye me-1"></i> ดูรายการที่จะถูกลบ
                                        </button>
                                    </div>
                                    <div class="alert alert-warning mb-0">
                                        <i class="fas fa-exclamation-triangle me-1"></i> <strong>ข้อควรระวัง:</strong> การลบข้อมูลไม่สามารถยกเลิกได้ กรุณาตรวจสอบเงื่อนไขก่อนดำเนินการ
                                    </div>
                                </form>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                <button type="button" id="btnConfirmDeleteConditions" class="btn btn-danger">
                                    <i class="fas fa-check me-1"></i> ยืนยันการลบ
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Preview Delete Modal -->
                <div class="modal fade" id="previewDeleteModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content shadow-lg border-danger">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title fw-bold"><i class="fas fa-eye me-2"></i> รายการที่จะถูกลบตามเงื่อนไข (Volume Tires)</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="table-responsive">
                                    <table id="previewDeleteTable" class="table table-bordered table-striped table-hover w-100">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>รหัสร้านค้า</th>
                                                <th>ชื่อร้านค้า</th>
                                                <th>พนักงานขาย</th>
                                                <th>ยี่ห้อ</th>
                                                <th>ประเภท</th>
                                                <th>รหัสสินค้า (SKU)</th>
                                                <th>รายละเอียดสินค้า</th>
                                                <th class="text-end">แพลนยาง</th>
                                                <th class="text-center">สถานะล็อค</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิดหน้าต่าง</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Main Data Table -->
                <div class="table-responsive">
                    <table id="planTable" class="table table-bordered table-striped table-hover w-100">
                        <thead>
                            <tr>
                                <th>รหัสลูกค้า</th>
                                <th>ชื่อลูกค้า</th>
                                <th>พนักงานขาย</th>
                                <th>คนเทค</th>
                                <th>จังหวัด</th>
                                <th>ประเภท</th>
                                <th>รหัสสินค้า</th>
                                <th>รายละเอียดสินค้า</th>
                                <th>ยี่ห้อ</th>
                                <th>เฉลี่ย 12ด.</th>
                                <th>MAX (ปีก่อน)</th>
                                <th>MIN (ปีก่อน)</th>
                                <th>ยอดขาย M-3</th>
                                <th>ยอดขาย M-2</th>
                                <th>ยอดขาย M-1</th>
                                <th>เฉลี่ย 3ด.</th>
                                <th>แพลนยาง</th>
                                <th>เทียบ แพลน ลบ เฉลี่ย</th>
                                <th>เทียบ แพลน ลบ เฉลี่ย %</th>
                                <th>ยอดขายจริง</th>
                                <th><span class="text-danger">ขาด</span>/<span class="text-primary">เกิน</span></th>
                                <th>ผู้รับผิดชอบ</th>
                                <th>หมายเหตุ</th>
                                <th>วันที่นำเข้า</th>
                                <th>จัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Realtime Modal -->
    <div class="modal fade" id="uploadRealtimeModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content border-success shadow-lg">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold"><i class="fas fa-file-import me-2"></i> นำเข้าข้อมูล Realtime Excel (Volume Tires)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="realtimeUploadForm" enctype="multipart/form-data">
                        <div class="mb-3 text-center">
                            <p class="text-muted small">กรุณาอัปโหลดไฟล์ Excel (.xls, .xlsx) ตามรูปแบบ Template <code>template_v_tires.xlsx</code></p>
                            <a href="import_process/template/template_v_tires.xlsx" class="btn btn-outline-success btn-sm">
                                <i class="fas fa-download me-1"></i> ดาวน์โหลด Template (template_v_tires.xlsx)
                            </a>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold">เดือนที่ต้องการนำเข้า:</label>
                                <select name="forcast_month" class="form-select" required>
                                    <?php foreach ($thai_months as $num => $name): ?>
                                        <option value="<?= $num ?>" <?= ($num == $current_month) ? 'selected' : '' ?>><?= $name ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold">ปีที่ต้องการนำเข้า:</label>
                                <select name="forcast_year" class="form-select" required>
                                    <?php for ($y = $current_year + 1; $y >= $current_year - 2; $y--): ?>
                                        <option value="<?= $y ?>" <?= ($y == $current_year) ? 'selected' : '' ?>><?= $y ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">เลือกไฟล์ Excel:</label>
                            <input type="file" name="excel_file" class="form-control" accept=".xls,.xlsx" required>
                        </div>
                        <div id="uploadProgress" class="progress d-none mb-3" style="height: 20px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width: 100%">กำลังประมวลผลข้อมูล...</div>
                        </div>
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                            <button type="submit" id="btnSubmitUpload" class="btn btn-success fw-bold">
                                <i class="fas fa-upload me-1"></i> เริ่มต้นนำเข้าข้อมูล
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
    $(document).ready(function() {
        $('#filterCustomer, #filterSale, #filterTake').select2({
            theme: 'bootstrap-5',
            width: '100%'
        });

        let isCurrentMonthLocked = false;
        let modifiedRows = new Map();

        const table = $('#planTable').DataTable({
            processing: true,
            serverSide: true,
            responsive: false,
            scrollX: true,
            scrollCollapse: true,
            fixedColumns: {
                left: 7
            },
            order: [[0, 'asc']],
            pageLength: 10,
            lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, "ทั้งหมด"]],
            ajax: {
                url: 'forecast_plan_fetch_volume_tires.php',
                type: 'POST',
                data: function(d) {
                    d.month = $('#filterMonth').val();
                    d.year = $('#filterYear').val();
                    d.sale_name = $('#filterSale').val();
                    d.take_name = $('#filterTake').val();
                    d.ar_name = $('#filterCustomer').val();
                    d.brand_code = $('#filterBrand').val();
                    d.product_type = $('#filterProductType').val();
                    d.qty_condition = $('#filterQtyCondition').val();
                    d.province = $('#filterProvince').val();
                    d.amphur = $('#filterAmphur').val();
                },
                dataSrc: function(json) {
                    isCurrentMonthLocked = (json.is_month_locked === 1);
                    updateMonthLockBanner(json.is_month_locked, json.lock_info);
                    return json.data;
                }
            },
            columns: [
                { data: 'ar_code', className: 'text-center' },
                { data: 'ar_name' },
                { data: 'sale_name' },
                { data: 'take_name' },
                { data: 'province' },
                { data: 'product_type' },
                { data: 'sku_code', className: 'text-center' },
                { data: 'sku_name' },
                { data: 'brand_code', className: 'text-center' },
                { data: 'avg_per_month_year', className: 'text-end' },
                { data: 'max_qty_year', className: 'text-end' },
                { data: 'min_qty_year', className: 'text-end' },
                { data: 'sales_month_1', className: 'text-end' },
                { data: 'sales_month_2', className: 'text-end' },
                { data: 'sales_month_3', className: 'text-end' },
                { data: 'avg_3_month', className: 'text-end' },
                { data: 'forecast_qty_month_1', className: 'text-end' },
                { data: 'compare_forecast_month_1', className: 'text-end' },
                { data: 'compare_sales_percent', className: 'text-end' },
                { data: 'sales_actual_month', className: 'text-end' },
                { data: 'sales_diff_actual', className: 'text-end' },
                { data: 'responsible_person' },
                { data: 'remark' },
                { data: 'import_date', className: 'text-center' },
                { data: 'actions', orderable: false, className: 'text-center' }
            ],
            drawCallback: function() {
                updateTableHeaders();
            },
            language: {
                search: "ค้นหา:",
                lengthMenu: "แสดง _MENU_ รายการ",
                info: "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
                infoEmpty: "ไม่มีรายการ",
                zeroRecords: "ไม่พบข้อมูล",
                processing: "กำลังโหลดข้อมูล...",
                paginate: {
                    first: "หน้าแรก",
                    previous: "ก่อนหน้า",
                    next: "ถัดไป",
                    last: "หน้าสุดท้าย"
                }
            }
        });

        function updateTableHeaders() {
            const month = parseInt($('#filterMonth').val());
            const year = parseInt($('#filterYear').val()) || new Date().getFullYear();

            const prevYearBE = (year - 1) + 543;
            $(table.column(9).header()).text('เฉลี่ย 12ด. (' + prevYearBE + ')');
            $(table.column(10).header()).text('MAX (' + prevYearBE + ')');
            $(table.column(11).header()).text('MIN (' + prevYearBE + ')');

            if (!month) return;

            const thai_months = [
                '', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.',
                'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'
            ];

            let m1 = month - 1; if (m1 <= 0) m1 += 12;
            let m2 = month - 2; if (m2 <= 0) m2 += 12;
            let m3 = month - 3; if (m3 <= 0) m3 += 12;

            $(table.column(12).header()).text('ยอดขายเดือน ' + thai_months[m3]);
            $(table.column(13).header()).text('ยอดขายเดือน ' + thai_months[m2]);
            $(table.column(14).header()).text('ยอดขายเดือน ' + thai_months[m1]);
            $(table.column(15).header()).text('เฉลี่ย 3ด.');
            $(table.column(16).header()).text('แพลนยาง ' + (thai_months[month] || ''));
            $(table.column(19).header()).text('ยอดขายจริง ' + (thai_months[month] || ''));
        }

        updateTableHeaders();

        $('#filterMonth, #filterYear').on('change', function() {
            updateTableHeaders();
        });

        function updateMonthLockBanner(isLocked, lockInfo) {
            const banner = $('#monthLockBanner');
            banner.removeClass('d-none alert-success alert-danger alert-secondary');
            
            if (isLocked) {
                banner.addClass('alert-danger');
                $('#monthLockIcon').html('<i class="fas fa-lock fa-lg text-danger"></i>');
                const lockTime = lockInfo && lockInfo.last_locked_at ? lockInfo.last_locked_at : '-';
                const lockUser = lockInfo && lockInfo.last_locked_by ? lockInfo.last_locked_by : 'ระบบ';
                $('#monthLockText').html(`<strong>สถานะข้อมูลเดือนนี้: ล็อคแล้ว</strong> (ล็อคเมื่อ ${lockTime} โดย ${lockUser}) — ข้อมูลถูกป้องกันไม่ให้แก้ไข`);
                $('#monthLockBadge').removeClass('bg-success').addClass('bg-danger').text('LOCKED');
                $('#btnLockMonth').prop('disabled', true).addClass('opacity-50');
                $('#btnSaveAll').prop('disabled', true).addClass('opacity-50');
            } else {
                banner.addClass('alert-success');
                $('#monthLockIcon').html('<i class="fas fa-lock-open fa-lg text-success"></i>');
                $('#monthLockText').html(`<strong>สถานะข้อมูลเดือนนี้: เปิดให้แก้ไข</strong> — สามารถแก้ไขแพลนยางและบันทึกได้ตามปกติ (กด "ยืนยันล็อคเดือนนี้" เมื่อต้องการล็อค)`);
                $('#monthLockBadge').removeClass('bg-danger').addClass('bg-success').text('OPEN');
                $('#btnLockMonth').prop('disabled', false).removeClass('opacity-50');
                $('#btnSaveAll').prop('disabled', false).removeClass('opacity-50');
            }
        }

        // Live calculation on forecast edit
        $(document).on('input change', '.forecast-qty-1', function() {
            const row = $(this).closest('tr');
            const id = $(this).data('id');
            const qty = parseFloat($(this).val()) || 0;
            const avg3m = parseFloat(row.find('.avg-3m').data('val')) || 0;

            const diff = qty - avg3m;
            row.find('.compare-1').val(diff.toFixed(2));

            const pct = (avg3m > 0) ? (diff / avg3m) * 100 : 0;
            row.find('.compare-pct').val(pct.toFixed(1) + '%');

            // Save in modified map
            modifiedRows.set(id, {
                id: id,
                forecast_qty: qty,
                compare_1: diff,
                compare_pct: pct,
                remark: row.find('.remark-input').val(),
                responsible_person: row.find('.resp-input').val()
            });

            row.addClass('table-warning');
        });

        $(document).on('input change', '.remark-input, .resp-input', function() {
            const row = $(this).closest('tr');
            const id = $(this).data('id');
            const forecastInput = row.find('.forecast-qty-1');
            const qty = parseFloat(forecastInput.val()) || 0;
            const avg3m = parseFloat(row.find('.avg-3m').data('val')) || 0;

            modifiedRows.set(id, {
                id: id,
                forecast_qty: qty,
                compare_1: qty - avg3m,
                compare_pct: (avg3m > 0) ? ((qty - avg3m) / avg3m) * 100 : 0,
                remark: row.find('.remark-input').val(),
                responsible_person: row.find('.resp-input').val()
            });
            row.addClass('table-warning');
        });

        // Single row save (Does NOT lock)
        $(document).on('click', '.btn-save-row', function() {
            const row = $(this).closest('tr');
            const id = $(this).data('id');
            const qty = parseFloat(row.find('.forecast-qty-1').val()) || 0;
            const avg3m = parseFloat(row.find('.avg-3m').data('val')) || 0;
            const diff = qty - avg3m;
            const pct = (avg3m > 0) ? (diff / avg3m) * 100 : 0;
            const remark = row.find('.remark-input').val();
            const resp = row.find('.resp-input').val();

            const payload = [{
                id: id,
                forecast_qty: qty,
                compare_1: diff,
                compare_pct: pct,
                remark: remark,
                responsible_person: resp
            }];

            const btn = $(this);
            btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

            $.ajax({
                url: 'forecast_update_plan_volume_tires.php',
                type: 'POST',
                data: { updates: JSON.stringify(payload) },
                dataType: 'json',
                success: function(res) {
                    btn.prop('disabled', false).html('<i class="fas fa-save"></i> บันทึก');
                    if (res.success) {
                        modifiedRows.delete(id);
                        row.removeClass('table-warning');
                        Swal.fire({
                            icon: 'success',
                            title: 'บันทึกสำเร็จ',
                            text: 'ข้อมูลรายการนี้ถูกบันทึกเรียบร้อยแล้ว',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'ไม่สามารถบันทึกได้',
                            text: res.message
                        });
                    }
                },
                error: function() {
                    btn.prop('disabled', false).html('<i class="fas fa-save"></i> บันทึก');
                    Swal.fire('ข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
                }
            });
        });

        // Batch save (Does NOT lock)
        $('#btnSaveAll').click(function() {
            if (modifiedRows.size === 0) {
                Swal.fire('แจ้งเตือน', 'ไม่มีรายการที่มีการแก้ไข', 'info');
                return;
            }

            const updates = Array.from(modifiedRows.values());
            Swal.fire({
                title: 'ยืนยันบันทึกการแก้ไข?',
                text: `คุณต้องการบันทึกการแก้ไขทั้งหมด ${updates.length} รายการใช่หรือไม่?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'บันทึก',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'กำลังบันทึกข้อมูล...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    $.ajax({
                        url: 'forecast_update_plan_volume_tires.php',
                        type: 'POST',
                        data: { updates: JSON.stringify(updates) },
                        dataType: 'json',
                        success: function(res) {
                            if (res.success) {
                                modifiedRows.clear();
                                table.ajax.reload(null, false);
                                Swal.fire('สำเร็จ', 'บันทึกข้อมูลการแก้ไขทั้งหมดเรียบร้อยแล้ว', 'success');
                            } else {
                                Swal.fire('ไม่สามารถบันทึกได้', res.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('ข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
                        }
                    });
                }
            });
        });

        // Confirm Lock Month
        $('#btnLockMonth').click(function() {
            const month = $('#filterMonth').val();
            const year = $('#filterYear').val();

            if (!month || !year) {
                Swal.fire('แจ้งเตือน', 'กรุณาเลือกเดือนและปีก่อนทำการล็อค', 'warning');
                return;
            }

            Swal.fire({
                title: 'ยืนยันล็อคข้อมูลเดือนนี้?',
                html: `คุณกำลังจะทำการ <strong class="text-danger">ล็อคข้อมูล Volume Tires ประจำเดือน ${month}/${year}</strong><br><br>เมื่อล็อคแล้ว ข้อมูลจะไม่สามารถแก้ไขหรือนำเข้าใหม่ได้ (ต้องให้ Admin ปลดล็อคเท่านั้น)`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: '<i class="fas fa-lock me-1"></i> ยืนยันล็อคข้อมูล',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'กำลังล็อคข้อมูล...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    $.ajax({
                        url: 'forecast_unlock_plan_volume_tires.php',
                        type: 'POST',
                        data: {
                            action: 'lock',
                            month: month,
                            year: year,
                            sale_name: $('#filterSale').val(),
                            ar_name: $('#filterCustomer').val()
                        },
                        dataType: 'json',
                        success: function(res) {
                            if (res.success) {
                                modifiedRows.clear();
                                table.ajax.reload();
                                Swal.fire('ล็อคข้อมูลสำเร็จ', res.message, 'success');
                            } else {
                                Swal.fire('ผิดพลาด', res.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('ข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
                        }
                    });
                }
            });
        });

        // Unlock Month (Admin / Sale Sup)
        $('#btnUnlockMonth').click(function() {
            const month = $('#filterMonth').val();
            const year = $('#filterYear').val();

            if (!month || !year) {
                Swal.fire('แจ้งเตือน', 'กรุณาเลือกเดือนและปีก่อนทำการปลดล็อค', 'warning');
                return;
            }

            Swal.fire({
                title: 'ยืนยันปลดล็อคข้อมูลเดือนนี้?',
                html: `คุณต้องการ <strong class="text-success">ปลดล็อคข้อมูล Volume Tires ประจำเดือน ${month}/${year}</strong> เพื่อเปิดให้แก้ไขข้อมูลใช่หรือไม่?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                confirmButtonText: '<i class="fas fa-lock-open me-1"></i> ยืนยันปลดล็อค',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'กำลังปลดล็อคข้อมูล...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    $.ajax({
                        url: 'forecast_unlock_plan_volume_tires.php',
                        type: 'POST',
                        data: {
                            action: 'unlock',
                            month: month,
                            year: year,
                            sale_name: $('#filterSale').val(),
                            ar_name: $('#filterCustomer').val()
                        },
                        dataType: 'json',
                        success: function(res) {
                            if (res.success) {
                                table.ajax.reload();
                                Swal.fire('ปลดล็อคสำเร็จ', res.message, 'success');
                            } else {
                                Swal.fire('ผิดพลาด', res.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('ข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
                        }
                    });
                }
            });
        });

        // Delete Single Row
        $(document).on('click', '.btn-delete-row', function() {
            const id = $(this).data('id');
            const cust = $(this).data('customer');
            const sku = $(this).data('sku');

            Swal.fire({
                title: 'ยืนยันการลบรายการ?',
                html: `ต้องการลบรายการนี้ใช่หรือไม่?<br><strong>ลูกค้า:</strong> ${cust}<br><strong>สินค้า:</strong> ${sku}`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'ลบข้อมูล',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: 'forecast_delete_record_volume_tires.php',
                        type: 'POST',
                        data: { id: id },
                        dataType: 'json',
                        success: function(res) {
                            if (res.success) {
                                modifiedRows.delete(id);
                                table.ajax.reload(null, false);
                                Swal.fire('ลบสำเร็จ', res.message, 'success');
                            } else {
                                Swal.fire('ไม่สามารถลบได้', res.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('ข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
                        }
                    });
                }
            });
        });

        // Filter button
        $('#btnFilter').click(function() {
            modifiedRows.clear();
            table.ajax.reload();
            updateCustomerCard();
        });

        // Customer selection changes
        $('#filterCustomer').change(function() {
            updateCustomerCard();
        });

        function updateCustomerCard() {
            const cust = $('#filterCustomer').val();
            const month = $('#filterMonth').val();
            const year = $('#filterYear').val();

            if (!cust) {
                $('#customerInfoCard').addClass('d-none');
                return;
            }

            $.ajax({
                url: 'forecast_fetch_customer_info_volume_tires.php',
                type: 'POST',
                data: {
                    month: month,
                    year: year,
                    ar_name: cust
                },
                dataType: 'json',
                success: function(res) {
                    if (res.success && res.data) {
                        const d = res.data;
                        $('#infoCustName').text(d.ar_name || cust);
                        $('#infoCustCode').text(d.ar_code || '-');
                        $('#infoSaleName').text(d.sale_name || '-');
                        $('#infoLocation').text((d.amphur || '') + ' ' + (d.province || ''));
                        $('#infoTotalForecast').text(Number(d.total_forecast || 0).toLocaleString());
                        $('#infoTotalVolume').text(Number(d.total_volume || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                        $('#infoTotalAvg3M').text(Number(d.total_avg3m || 0).toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2}));
                        $('#customerInfoCard').removeClass('d-none');
                    } else {
                        $('#customerInfoCard').addClass('d-none');
                    }
                }
            });
        }

        // Export Excel
        $('#btnExportExcel').click(function() {
            const month = $('#filterMonth').val();
            const year = $('#filterYear').val();

            if (!month || !year) {
                Swal.fire('แจ้งเตือน', 'กรุณาเลือกเดือนและปีก่อนทำการ Export', 'warning');
                return;
            }

            const params = $.param({
                month: month,
                year: year,
                sale_name: $('#filterSale').val(),
                take_name: $('#filterTake').val(),
                ar_name: $('#filterCustomer').val(),
                brand_code: $('#filterBrand').val(),
                product_type: $('#filterProductType').val(),
                qty_condition: $('#filterQtyCondition').val(),
                province: $('#filterProvince').val(),
                amphur: $('#filterAmphur').val()
            });

            window.location.href = 'forecast_plan_export_excel_volume_tires.php?' + params;
        });

        // Summary Modal
        $('#btnShowSummary').click(function() {
            const month = $('#filterMonth').val();
            const year = $('#filterYear').val();

            if (!month || !year) {
                Swal.fire('แจ้งเตือน', 'กรุณาเลือกเดือนและปีก่อนดูสรุป', 'warning');
                return;
            }

            Swal.fire({
                title: 'กำลังคำนวณข้อมูลสรุป...',
                allowOutsideClick: false,
                didOpen: () => { Swal.showLoading(); }
            });

            $.ajax({
                url: 'forecast_fetch_brand_summary_volume_tires.php',
                type: 'POST',
                data: {
                    month: month,
                    year: year,
                    sale_name: $('#filterSale').val(),
                    take_name: $('#filterTake').val(),
                    ar_name: $('#filterCustomer').val(),
                    product_type: $('#filterProductType').val()
                },
                dataType: 'json',
                success: function(res) {
                    Swal.close();
                    if (res.success && res.data) {
                        let html = '';
                        let totalQty = 0;
                        let totalVol = 0;
                        let totalAvg = 0;
                        let totalDiff = 0;

                        res.data.forEach(function(item) {
                            const qty = parseFloat(item.total_forecast_qty) || 0;
                            const vol = parseFloat(item.total_volume) || 0;
                            const avg = parseFloat(item.total_avg3m) || 0;
                            const diff = parseFloat(item.total_diff) || 0;

                            totalQty += qty;
                            totalVol += vol;
                            totalAvg += avg;
                            totalDiff += diff;

                            html += `<tr>
                                <td class="fw-bold">${item.brand}</td>
                                <td class="text-center">${Number(item.total_customers).toLocaleString()}</td>
                                <td class="text-center">${Number(item.total_items).toLocaleString()}</td>
                                <td class="text-end fw-bold">${qty.toLocaleString()}</td>
                                <td class="text-end text-success fw-bold">${vol.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                <td class="text-end">${avg.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                                <td class="text-end ${diff < 0 ? 'text-danger' : 'text-primary'} fw-bold">${diff.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                            </tr>`;
                        });

                        html += `<tr class="table-dark fw-bold">
                            <td>รวมทั้งหมด</td>
                            <td class="text-center">-</td>
                            <td class="text-center">-</td>
                            <td class="text-end">${totalQty.toLocaleString()}</td>
                            <td class="text-end text-warning">${totalVol.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                            <td class="text-end">${totalAvg.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                            <td class="text-end">${totalDiff.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})}</td>
                        </tr>`;

                        $('#summaryTableBody').html(html);
                        $('#summaryModal').modal('show');
                    } else {
                        Swal.fire('ข้อผิดพลาด', res.message || 'ไม่พบข้อมูล', 'error');
                    }
                },
                error: function() {
                    Swal.close();
                    Swal.fire('ข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
                }
            });
        });

        // Realtime Upload Excel
        $('#realtimeUploadForm').submit(function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            $('#uploadProgress').removeClass('d-none');
            $('#btnSubmitUpload').prop('disabled', true);

            $.ajax({
                url: 'import_process/import_forecast_volume_tires_process.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(res) {
                    $('#uploadProgress').addClass('d-none');
                    $('#btnSubmitUpload').prop('disabled', false);

                    if (res.success) {
                        $('#uploadRealtimeModal').modal('hide');
                        $('#realtimeUploadForm')[0].reset();
                        table.ajax.reload();
                        Swal.fire({
                            icon: 'success',
                            title: 'นำเข้าสำเร็จ!',
                            text: `นำเข้าข้อมูล Volume Tires ทั้งหมด ${res.count} รายการเรียบร้อยแล้ว`,
                            confirmButtonText: 'ตกลง'
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'เกิดข้อผิดพลาดในการนำเข้า',
                            text: res.message
                        });
                    }
                },
                error: function() {
                    $('#uploadProgress').addClass('d-none');
                    $('#btnSubmitUpload').prop('disabled', false);
                    Swal.fire('ข้อผิดพลาด', 'เกิดข้อผิดพลาดในการส่งข้อมูลไปยังเซิร์ฟเวอร์', 'error');
                }
            });
        });

        // Delete by Conditions
        $('#btnDeleteByConditions').click(function() {
            $('#deleteConditionsModal').modal('show');
            checkDelCount();
        });

        $('#delMonth, #delYear, #delSale, #delBrand, #delProductType').change(function() {
            checkDelCount();
        });

        function checkDelCount() {
            const month = $('#delMonth').val();
            const year = $('#delYear').val();
            const sale = $('#delSale').val();
            const brand = $('#delBrand').val();
            const type = $('#delProductType').val();

            $.ajax({
                url: 'forecast_fetch_delete_preview_volume_tires.php',
                type: 'POST',
                data: {
                    month: month,
                    year: year,
                    sale_name: sale,
                    brand_code: brand,
                    product_type: type
                },
                dataType: 'json',
                success: function(res) {
                    if (res.success && res.data) {
                        const cnt = res.data.length;
                        $('#delRecordsCount').text(cnt);
                        $('#btnPreviewDelete').prop('disabled', cnt === 0);
                        window.previewDeleteRows = res.data;
                    } else {
                        $('#delRecordsCount').text('0');
                        $('#btnPreviewDelete').prop('disabled', true);
                        window.previewDeleteRows = [];
                    }
                }
            });
        }

        $('#btnPreviewDelete').click(function() {
            const rows = window.previewDeleteRows || [];
            let html = '';
            rows.forEach(function(r) {
                html += `<tr>
                    <td>${r.ar_code}</td>
                    <td>${r.ar_name}</td>
                    <td>${r.sale_name}</td>
                    <td>${r.brand_code}</td>
                    <td>${r.product_type}</td>
                    <td>${r.sku_code}</td>
                    <td>${r.sku_name}</td>
                    <td class="text-end">${Number(r.forecast_qty).toLocaleString()}</td>
                    <td class="text-center">${r.is_locked == 1 ? '<span class="badge bg-danger">ล็อคแล้ว</span>' : '<span class="badge bg-success">เปิด</span>'}</td>
                </tr>`;
            });
            $('#previewDeleteTable tbody').html(html);
            $('#previewDeleteModal').modal('show');
        });

        $('#btnConfirmDeleteConditions').click(function() {
            const count = parseInt($('#delRecordsCount').text()) || 0;
            if (count === 0) {
                Swal.fire('แจ้งเตือน', 'ไม่พบข้อมูลที่ตรงกับเงื่อนไขที่จะลบ', 'warning');
                return;
            }

            Swal.fire({
                title: 'ยืนยันลบข้อมูลตามเงื่อนไข?',
                html: `<span class="text-danger">ข้อมูลจำนวน <strong>${count}</strong> รายการจะถูกลบถาวร</span><br>คุณแน่ใจหรือไม่ที่จะดำเนินการต่อ?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                confirmButtonText: 'ยืนยันการลบ',
                cancelButtonText: 'ยกเลิก'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'กำลังลบข้อมูล...',
                        allowOutsideClick: false,
                        didOpen: () => { Swal.showLoading(); }
                    });

                    $.ajax({
                        url: 'forecast_delete_by_conditions_volume_tires.php',
                        type: 'POST',
                        data: {
                            month: $('#delMonth').val(),
                            year: $('#delYear').val(),
                            sale_name: $('#delSale').val(),
                            brand_code: $('#delBrand').val(),
                            product_type: $('#delProductType').val()
                        },
                        dataType: 'json',
                        success: function(res) {
                            if (res.success) {
                                $('#deleteConditionsModal').modal('hide');
                                table.ajax.reload();
                                Swal.fire('สำเร็จ', res.message, 'success');
                            } else {
                                Swal.fire('ไม่สามารถลบได้', res.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('ข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
                        }
                    });
                }
            });
        });

    });
    </script>
</body>
</html>
