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

$stmt_filters = $conn->prepare("SELECT sale_name, take_name, ar_name, province, amphur, zone, brand_code FROM ims_tires_plan_forecast_realtime $where_cust");
$stmt_filters->execute($params_cust);
$filter_rows = $stmt_filters->fetchAll(PDO::FETCH_ASSOC);

$sales_set = [];
$takes_set = [];
$cust_set = [];
$prov_set = [];
$amphur_set = [];
$zone_set = [];
$brand_set = [];

foreach ($filter_rows as $r) {
    if (!empty($r['sale_name'])) $sales_set[$r['sale_name']] = true;
    if (!empty($r['take_name'])) $takes_set[$r['take_name']] = true;
    if (!empty($r['ar_name'])) $cust_set[$r['ar_name']] = true;
    if (!empty($r['province'])) $prov_set[$r['province']] = true;
    if (!empty($r['amphur'])) $amphur_set[$r['amphur']] = true;
    if (!empty($r['zone'])) $zone_set[$r['zone']] = true;
    if (!empty($r['brand_code'])) $brand_set[$r['brand_code']] = true;
}

$sales_list = array_keys($sales_set); sort($sales_list);
$takes_list = array_keys($takes_set); sort($takes_list);
$customers_list = array_keys($cust_set); sort($customers_list);
$provinces_list = array_keys($prov_set); sort($provinces_list);
$amphurs_list = array_keys($amphur_set); sort($amphurs_list);
$zones_list = array_keys($zone_set); sort($zones_list);
$brands_list = array_keys($brand_set); sort($brands_list);
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ข้อมูล Tires Plan Forecast</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/fixedcolumns/4.3.0/css/fixedColumns.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Prompt', sans-serif; background-color: #f8f9fa; margin: 20px; }
        .card { border-radius: 15px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .card-header { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: white; border-radius: 15px 15px 0 0 !important; border-bottom: 3px solid #198754; }
        th, td { white-space: nowrap; font-size: 0.85rem; vertical-align: middle; }
        th { background-color: #f8f9fa !important; }
        #top20SkuTable thead tr th {
            background-color: #e2e8f0 !important;
            color: #000000 !important;
            font-weight: 700 !important;
            font-size: 0.85rem !important;
            text-align: center !important;
            vertical-align: middle !important;
            border-color: #cbd5e1 !important;
            white-space: nowrap !important;
        }
        #top20SkuOffcanvas {
            width: 100vw !important;
            max-width: 100vw !important;
        }
        .table-responsive { overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .form-control-sm { font-size: 0.8rem; }
        .compare-1, .compare-pct { background-color: #e9ecef !important; }
        .locked-input, input[readonly].form-control {
            background-color: #f8f9fa !important;
            color: #6c757d !important;
            cursor: not-allowed !important;
        }
        .btn-edit-row { display: none !important; }
        .btn-group-responsive .btn {
            white-space: nowrap !important;
            font-size: 0.8rem !important;
            padding: 0.375rem 0.35rem !important;
        }

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
                    <h4 class="mb-2 mb-lg-0"><i class="fas fa-edit me-2"></i> Tires Plan วางแผนการขาย</h4>
                    <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                        <button type="button" class="btn btn-success btn-sm fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#uploadRealtimeModal" style="background: linear-gradient(135deg, #198754 0%, #146c43 100%); border: 1px solid #146c43;">
                            <i class="fas fa-file-import me-1"></i> นำเข้าข้อมูล Realtime Excel
                        </button>
                        <a href="export_forecast_template.php" target="_blank" class="btn btn-primary btn-sm fw-bold text-white shadow-sm">
                            <i class="fas fa-file-download me-1"></i> download ข้อมูลยอดขาย
                        </a>
                        <a href="forecast_report_list_new" target="_blank" class="btn btn-light text-success btn-sm fw-bold">
                            <i class="fas fa-history me-1"></i> ประวัติการนำเข้า
                        </a>
                        <a href="import_plan_howto_realtime.html" target="_blank" class="btn btn-warning btn-sm fw-bold text-dark">
                            <i class="fas fa-book me-1"></i> คู่มือการนำเข้า (Realtime)
                        </a>
                        <a href="plan_forecast_tires_manual_realtime.html" target="_blank" class="btn btn-outline-danger btn-sm fw-bold">
                            <i class="fas fa-info-circle me-1"></i> คู่มือการใช้งานระบบ
                        </a>
                        <button type="button" id="btnAddRecord" class="btn btn-primary btn-sm fw-bold">
                            <i class="fas fa-plus me-1"></i> เพิ่มข้อมูลด้วยตนเอง
                        </button>
                        <a href="forecast_customer_edit" class="btn btn-warning btn-sm fw-bold text-dark">
                            <i class="fas fa-user-edit me-1"></i> แก้ไขข้อมูลลูกค้า
                        </a>
                        <?php if ($account_type === 'admin' || $account_type === 'sale_sup'): ?>
                            <a href="update_addr_cust_display" class="btn btn-dark btn-sm fw-bold">
                                <i class="fas fa-map-marker-alt me-1"></i> ปรับปรุงที่อยู่ลูกค้า
                            </a>
                        <?php endif; ?>
                        <button type="button" id="btnSyncData" class="btn btn-info btn-sm fw-bold text-white">
                            <i class="fas fa-sync-alt me-1"></i> ปรับปรุงข้อมูลยอดขาย
                        </button>
                        <button type="button" id="btnCopyNextMonth" class="btn btn-secondary btn-sm fw-bold">
                            <i class="fas fa-copy me-1"></i> คัดลอกไปเดือนถัดไป
                        </button>
                        <button type="button" class="btn btn-outline-light btn-sm fw-bold" data-bs-toggle="modal" data-bs-target="#columnToggleModal">
                            <i class="fas fa-sliders-h me-1"></i> แสดง/ซ่อน คอลัมน์
                        </button>
                        <div class="btn-group">
                            <button type="button" class="btn btn-warning btn-sm fw-bold dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-chart-pie me-1"></i> รายงานสรุปรายปี
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow">
                                <li><a class="dropdown-item" href="forecast_report_yearly" target="_blank">
                                    <i class="fas fa-user-tie me-2 text-primary"></i> สรุปรายปี (แยกตามพนักงาน)
                                </a></li>
                                <li><a class="dropdown-item" href="forecast_report_yearly_sku" target="_blank">
                                    <i class="fas fa-boxes me-2 text-success"></i> สรุปรายปี (แยกตาม SKU)
                                </a></li>
                                <li><a class="dropdown-item" href="forecast_report_yearly_sale_sku" target="_blank">
                                    <i class="fas fa-table me-2 text-purple" style="color: #6f42c1;"></i> สรุปรายปี (พนักงาน + SKU)
                                </a></li>
                                <li><a class="dropdown-item" href="forecast_report_yearly_sale_ar_sku" target="_blank">
                                    <i class="fas fa-file-invoice me-2 text-success" style="color: #20c997;"></i> สรุปรายปี (พนักงาน + ลูกค้า + SKU)
                                </a></li>
                                <li><a class="dropdown-item" href="forecast_report_yearly_comparison" target="_blank">
                                    <i class="fas fa-balance-scale me-2 text-info"></i> เปรียบเทียบ ยอดขาย/เป้า/คาดการณ์
                                </a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-muted small" href="report_plan_method_realtime.html" target="_blank">
                                    <i class="fas fa-info-circle me-2"></i> รายละเอียดรูปแบบรายงาน
                                </a></li>
                            </ul>
                        </div>
                        <button type="button" id="btnExportExcel" class="btn btn-light text-success btn-sm fw-bold shadow-sm">
                            <i class="fas fa-file-excel me-1"></i> Export Excel
                        </button>
                        <button type="button" id="btnDeleteByConditions" class="btn btn-danger btn-sm fw-bold">
                            <i class="fas fa-trash-alt me-1"></i> ลบข้อมูลตามเงื่อนไข
                        </button>
                        <?php if (in_array($account_type, ['admin', 'sale_sup'])): ?>
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
                                $is_locked = ($account_type !== 'admin' && $account_type !== 'sale_sup');
                                $is_me = $is_locked && ($sale === $first_name);
                                $selected = $is_me ? 'selected' : '';
                                $disabled = ($is_locked && !$is_me) ? 'disabled' : '';
                                echo "<option value='".htmlspecialchars($sale)."' $selected $disabled>".htmlspecialchars($sale)."</option>";
                            endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">ชื่อพนักงานเทค (Take):</label>
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
                    <div class="col-md-3">
                        <label class="form-label fw-bold">ชื่อลูกค้า:</label>
                        <select id="filterCustomer" class="form-select">
                            <option value="">-- ทั้งหมด --</option>
                            <?php foreach ($customers_list as $cust): ?>
                                <option value="<?= htmlspecialchars($cust) ?>"><?= htmlspecialchars($cust) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row g-2 mb-4">
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
                        <label class="form-label fw-bold">เป้า / แพลนยาง:</label>
                        <select id="filterQtyCondition" class="form-select form-select-sm">
                            <option value="all">-- ทั้งหมด (รวมค่า 0) --</option>
                            <option value="gt0_either">เป้า หรือ แพลน > 0</option>
                            <option value="gt0_target">เป้ารายเดือน > 0</option>
                            <option value="gt0_forecast">แพลนยาง > 0</option>
                            <option value="gt0_both">เป้า และ แพลน > 0</option>
                            <option value="eq0_both">เป้า และ แพลน = 0</option>
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
                    <div class="col-md-1">
                        <label class="form-label fw-bold">เขต:</label>
                        <select id="filterZone" class="form-select form-select-sm">
                            <option value="">-- ทั้งหมด --</option>
                            <?php foreach ($zones_list as $zone): ?>
                                <option value="<?= htmlspecialchars($zone) ?>"><?= htmlspecialchars($zone) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3 align-self-end">
                        <div class="btn-group w-100 btn-group-responsive">
                            <button type="button" id="btnFilter" class="btn btn-primary btn-sm px-2">
                                <i class="fas fa-search me-1"></i> กรองข้อมูล
                            </button>
                            <button type="button" id="btnShowSummary" class="btn btn-warning btn-sm fw-bold px-2">
                                <i class="fas fa-list-alt me-1"></i> สรุปยี่ห้อ - ประเภท
                            </button>
                            <button type="button" id="btnShowTop20Stores" class="btn btn-success btn-sm fw-bold text-white shadow-sm px-2">
                                <i class="fas fa-store me-1"></i> Top 20 ร้านค้ายอดขายสูงสุด
                            </button>
                            <button type="button" id="btnShowTop20" class="btn btn-info btn-sm fw-bold text-white shadow-sm px-2">
                                <i class="fas fa-trophy me-1"></i> Top 20 SKU ยอดขาย
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Customer Info Card (Dynamic) -->
                <div id="customerInfoCard" class="card mb-4 d-none border-primary">
                    <div class="card-body bg-light shadow-sm py-3">
                        <div class="row align-items-center">
                            <div class="col-md-5">
                                <h5 class="mb-1 text-primary fw-bold">
                                    <i class="fas fa-store me-2"></i> <span id="infoCustName">-</span>
                                </h5>
                                <div class="small text-muted">
                                    <span class="badge bg-primary me-2"><span id="infoCustCode"></span></span>
                                    พนักงานขาย: <span id="infoSaleName" class="fw-bold"></span> | 
                                    พื้นที่: <span id="infoLocation" class="fw-bold"></span>
                                </div>
                            </div>
                            <div class="col-md-7 border-start-md">
                                <div class="row g-2 text-center">
                                    <div class="col-6">
                                        <div class="small text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">เป้าหมายรวม</div>
                                        <div class="h4 mb-0 fw-bold text-dark" id="infoTotalTarget">0</div>
                                    </div>
                                    <div class="col-6 border-start">
                                        <div class="small text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">ยอดขายรวม</div>
                                        <div class="h4 mb-0 fw-bold text-primary" id="infoTotalTarget2">0</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Brand Summary Modal -->
                <div class="modal fade" id="summaryModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                    <div class="modal-dialog modal-xl">
                        <div class="modal-content">
                            <div class="modal-header bg-warning text-dark">
                                <h5 class="modal-title fw-bold"><i class="fas fa-chart-bar me-2"></i> สรุปยอดแผนวางแผนการขายแยกตามยี่ห้อ</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="brand-selection-area mb-4 p-3 bg-light border rounded">
                                    <label class="form-label fw-bold me-3">กรองยี่ห้อสำหรับสรุป:</label>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input brand-check-all" type="checkbox" id="brandAll" value="all">
                                        <label class="form-check-label fw-bold text-primary" for="brandAll">ทั้งหมด</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input brand-check" type="checkbox" name="brands[]" value="AT">
                                        <label class="form-check-label">AT</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input brand-check" type="checkbox" name="brands[]" value="LEAO">
                                        <label class="form-check-label">LEAO</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input brand-check" type="checkbox" name="brands[]" value="LLIT">
                                        <label class="form-check-label">LLIT</label>
                                    </div>
                                    <div class="form-check form-check-inline ms-3 border-start ps-3">
                                        <input class="form-check-input" type="checkbox" id="groupATLEAOLLIT" name="group_at_leao_llit" value="Y">
                                        <label class="form-check-label text-success fw-bold" for="groupATLEAOLLIT">รวมกลุ่ม 3 ยี่ห้อ (AT+LEAO+LLIT)</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input brand-check" type="checkbox" name="brands[]" value="BS">
                                        <label class="form-check-label">BS</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input brand-check" type="checkbox" name="brands[]" value="FS">
                                        <label class="form-check-label">FS</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input brand-check" type="checkbox" name="brands[]" value="DT">
                                        <label class="form-check-label">DT</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input brand-check" type="checkbox" name="brands[]" value="ML">
                                        <label class="form-check-label">ML</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input brand-check" type="checkbox" name="brands[]" value="DS">
                                        <label class="form-check-label">DS</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input brand-check" type="checkbox" name="brands[]" value="DL">
                                        <label class="form-check-label">DL</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input brand-check" type="checkbox" name="brands[]" value="PR">
                                        <label class="form-check-label">PR</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input brand-check" type="checkbox" name="brands[]" value="VB">
                                        <label class="form-check-label">VB</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input brand-check" type="checkbox" name="brands[]" value="WESTLAKE">
                                        <label class="form-check-label">WESTLAKE</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input brand-check" type="checkbox" name="brands[]" value="BS/FS">
                                        <label class="form-check-label text-danger fw-bold">BS/FS</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input brand-check" type="checkbox" name="brands[]" value="BS/FS/DT">
                                        <label class="form-check-label text-danger fw-bold">BS/FS/DT</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input brand-check" type="checkbox" name="brands[]" value="DT/FS/DT">
                                        <label class="form-check-label text-danger fw-bold">DT/FS/DT</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input brand-check" type="checkbox" name="brands[]" value="FS/DT">
                                        <label class="form-check-label text-danger fw-bold">FS/DT</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input brand-check" type="checkbox" name="brands[]" value="ยางเล็ก">
                                        <label class="form-check-label">ยางเล็ก</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input brand-check" type="checkbox" name="brands[]" value="TBR/LTR (ยางใหญ่)">
                                        <label class="form-check-label">TBR/LTR (ยางใหญ่)</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input brand-check" type="checkbox" name="brands[]" value="Off The Road">
                                        <label class="form-check-label">Off The Road</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input brand-check" type="checkbox" name="brands[]" value="TBB/LTB (ยางผ้าใบ)">
                                        <label class="form-check-label">TBB/LTB (ยางผ้าใบ)</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input brand-check" type="checkbox" name="brands[]" value="ยางใน">
                                        <label class="form-check-label">ยางใน</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input brand-check" type="checkbox" name="brands[]" value="ยางรอง">
                                        <label class="form-check-label">ยางรอง</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input brand-check" type="checkbox" name="brands[]" value="TBR">
                                        <label class="form-check-label">TBR</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input brand-check" type="checkbox" name="brands[]" value="กระทะล้อ">
                                        <label class="form-check-label">กระทะล้อ</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input brand-check" type="checkbox" name="brands[]" value="LSR">
                                        <label class="form-check-label">LSR</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input brand-check" type="checkbox" name="brands[]" value="TBR/LTB">
                                        <label class="form-check-label">TBR/LTB</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input brand-check" type="checkbox" name="brands[]" value="ยางเคลม">
                                        <label class="form-check-label">ยางเคลม</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input brand-check" type="checkbox" name="brands[]" value="แบตเตอรี่">
                                        <label class="form-check-label">แบตเตอรี่</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input brand-check" type="checkbox" name="brands[]" value="OTHER">
                                        <label class="form-check-label fw-bold">ยี่ห้ออื่นๆ</label>
                                    </div>
                                    <div class="mt-3">
                                        <button type="button" id="btnQuickBrand" class="btn btn-info btn-sm">
                                            <i class="fas fa-check-double me-1"></i> เลือก AT+LEAO+LLIT
                                        </button>
                                        <button type="button" id="btnRefreshSummary" class="btn btn-primary btn-sm ms-2">
                                            <i class="fas fa-sync me-1"></i> อัปเดตตารางสรุป
                                        </button>
                                    </div>
                                </div>
                                <div id="summaryContent" class="table-responsive">
                                    <table class="table table-bordered table-striped table-hover">
                                        <thead class="table-light">
                                            <tr>
                                                <th>ยี่ห้อ</th>
                                                <th class="text-end">เป้าหมายรวม</th>
                                                <th class="text-end">เป้าหมาย2</th>
                                                <th class="text-end">คาดการณ์</th>
                                                <th class="text-end">เฉลี่ย 12ด.</th>
                                                <th class="text-end">ยอดขาย M-3</th>
                                                <th class="text-end">ยอดขาย M-2</th>
                                                <th class="text-end">ยอดขาย M-1</th>
                                                <th class="text-end">เฉลี่ย 3ด.</th>
                                                <th class="text-end">ผลต่าง (คาดการณ์)</th>
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

                <!-- Delete Conditions Modal -->
                <div class="modal fade" id="deleteConditionsModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header bg-danger text-white">
                                <h5 class="modal-title fw-bold"><i class="fas fa-trash-alt me-2"></i> ลบข้อมูลตามเงื่อนไข</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="formDeleteConditions">
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">เดือนที่เลือกลบ:</label>
                                        <input type="text" id="delShowMonth" class="form-control bg-light" readonly>
                                        <input type="hidden" id="delMonth" name="month">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">ปีที่เลือกลบ:</label>
                                        <input type="text" id="delShowYear" class="form-control bg-light" readonly>
                                        <input type="hidden" id="delYear" name="year">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">พนักงานขาย (Sale):</label>
                                        <select id="delSaleName" name="sale_name" class="form-select">
                                            <option value="">-- ทั้งหมด --</option>
                                            <?php foreach ($sales_list as $sale): 
                                                 $is_locked = ($account_type !== 'admin' && $account_type !== 'sale_sup');
                                                 $is_me = $is_locked && ($sale === $first_name);
                                                $selected = $is_me ? 'selected' : '';
                                                $disabled = ($is_locked && !$is_me) ? 'disabled' : '';
                                                echo "<option value='".htmlspecialchars($sale)."' $selected $disabled>".htmlspecialchars($sale)."</option>";
                                            endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold">ยี่ห้อยาง (Brand):</label>
                                        <select id="delBrandCode" name="brand_code" class="form-select">
                                            <option value="all">-- ทั้งหมด --</option>
                                            <option value="AT">AT</option>
                                            <option value="LEAO">LEAO</option>
                                            <option value="LLIT">LLIT</option>
                                            <option value="BS">BS</option>
                                            <option value="FS">FS</option>
                                            <option value="DT">DT</option>
                                            <option value="ML">ML</option>
                                            <option value="DS">DS</option>
                                            <option value="DL">DL</option>
                                            <option value="PR">PR</option>
                                            <option value="VB">VB</option>
                                            <option value="WESTLAKE">WESTLAKE</option>
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
                                <h5 class="modal-title fw-bold"><i class="fas fa-eye me-2"></i> รายการที่จะถูกลบตามเงื่อนไข</h5>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <div class="alert alert-info py-2">
                                    <i class="fas fa-info-circle me-1"></i> แสดงรายการข้อมูลจริงทั้งหมดที่จะถูกลบหากดำเนินการต่อไป
                                </div>
                                <div class="table-responsive">
                                    <table id="previewDeleteTable" class="table table-bordered table-striped table-hover w-100">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>รหัสร้านค้า</th>
                                                <th>ชื่อร้านค้า</th>
                                                <th>พนักงานขาย</th>
                                                <th>ยี่ห้อ</th>
                                                <th>รหัสสินค้า (SKU)</th>
                                                <th class="text-end">คาดการณ์</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Data will be loaded here -->
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

                <div class="table-responsive">
                    <table id="planTable" class="table table-bordered table-striped table-hover w-100">
                        <thead>
                            <tr>
                                <th>รหัสร้านค้า</th>
                                <th>ชื่อร้านค้า</th>
                                <th>ยี่ห้อ</th>
                                <th>รหัสสินค้า</th>
                                <th>เฉลี่ย 12ด.</th>
                                <th>MAX (ปีก่อนหน้า)</th>
                                <th>MIN (ปีก่อนหน้า)</th>
                                <th>ยอดขาย M-3</th>
                                <th>ยอดขาย M-2</th>
                                <th>ยอดขาย M-1</th>
                                <th>เฉลี่ย 3ด.</th>
                                <th>เป้ารายเดือน</th>
                                <th>แพลนยาง</th>
                                <th>เทียบ แพลนยาง ลบ ค่าเฉลี่ย</th>
                                <th>เทียบ แพลนยาง ลบ ค่าเฉลี่ย %</th>
                                <th>ยอดขายจริง</th>
                                <th><span class="text-danger">ขาด</span>/<span class="text-primary">เกิน</span></th>
                                <th>หมายเหตุ</th>
                                <th>พนักงานขาย</th>
                                <th>คนเทค</th>
                                <th>ผู้รับผิดชอบ</th>
                                <th>อำเภอ</th>
                                <th>จังหวัด</th>
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
                    <h5 class="modal-title fw-bold"><i class="fas fa-file-import me-2"></i> นำเข้าข้อมูล Realtime Excel</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="realtimeUploadForm" enctype="multipart/form-data">
                        <div class="mb-3 text-center">
                            <p class="text-muted small">กรุณาอัปโหลดไฟล์ Excel (.xls, .xlsx) ตามรูปแบบ Template <code>plan_tires_r1.xlsx</code></p>
                            <a href="doc/plan_tires_r1.xlsx" class="btn btn-outline-success btn-sm">
                                <i class="fas fa-download me-1"></i> ดาวน์โหลด Template (plan_tires_r1.xlsx)
                            </a>
                            <a href="export_forecast_template.php" target="_blank" class="btn btn-primary btn-sm fw-bold text-white ms-1">
                                <i class="fas fa-file-download me-1"></i> download ข้อมูลยอดขาย
                            </a>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label class="form-label fw-bold">เลือกเดือนที่ต้องการบันทึก:</label>
                                <select name="forcast_month" id="uploadMonth" class="form-select" required>
                                    <option value="">-- เลือกเดือน --</option>
                                    <option value="1">มกราคม</option>
                                    <option value="2">กุมภาพันธ์</option>
                                    <option value="3">มีนาคม</option>
                                    <option value="4">เมษายน</option>
                                    <option value="5">พฤษภาคม</option>
                                    <option value="6">มิถุนายน</option>
                                    <option value="7">กรกฎาคม</option>
                                    <option value="8">สิงหาคม</option>
                                    <option value="9">กันยายน</option>
                                    <option value="10">ตุลาคม</option>
                                    <option value="11">พฤศจิกายน</option>
                                    <option value="12">ธันวาคม</option>
                                </select>
                            </div>
                            <div class="col-6">
                                <label class="form-label fw-bold">เลือกปีที่ต้องการบันทึก:</label>
                                <select name="forcast_year" id="uploadYear" class="form-select" required>
                                    <option value='2025' >2025</option><option value='2026' selected>2026</option><option value='2027' >2027</option><option value='2028' >2028</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">เลือกไฟล์ Excel:</label>
                            <div class="p-4 border border-2 border-dashed border-success rounded bg-light text-center cursor-pointer" id="dragArea" onclick="document.getElementById('realtimeFileInput').click()">
                                <i class="fas fa-cloud-upload-alt fa-2x text-success mb-2"></i>
                                <div class="fw-bold small" id="realtimeFileName">คลิกเพื่อเลือกไฟล์ หรือลากไฟล์มาวางที่นี่ (.xls, .xlsx)</div>
                                <input type="file" name="excel_file" id="realtimeFileInput" accept=".xls,.xlsx" style="display:none;" required>
                            </div>
                        </div>
                        
                        <!-- Progress Area -->
                        <div id="uploadProgressArea" class="mt-3" style="display:none;">
                            <div class="progress mb-1" style="height: 20px;">
                                <div id="uploadProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%">0%</div>
                            </div>
                            <div id="uploadStatusText" class="text-center small text-muted">กำลังเตรียมตัว...</div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" id="btnSubmitUpload" class="btn btn-success fw-bold">
                        <i class="fas fa-check-circle me-1"></i> เริ่มนำเข้าข้อมูล
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Copy Plan Modal -->
    <div class="modal fade" id="copyPlanModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-info text-dark">
                    <h5 class="modal-title"><i class="fas fa-copy me-2"></i> คัดลอกแผนการขาย</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="copyPlanForm">
                        <h6 class="text-primary border-bottom pb-2 mb-3">1. เลือกข้อมูลต้นฉบับ</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">จากเดือน:</label>
                                <select id="copySourceMonth" class="form-select">
                                    <option value="">-- เลือกเดือน --</option>
                                    <?php foreach ($thai_months as $num => $name): ?>
                                        <option value="<?= $num ?>"><?= $name ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">จากปี:</label>
                                <select id="copySourceYear" class="form-select">
                                    <option value="">-- เลือกปี --</option>
                                    <?php
                                    for($y = $current_year - 1; $y <= $current_year + 2; $y++) {
                                        echo "<option value='$y'>$y</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label fw-bold">พนักงานขาย (ต้นฉบับ):</label>
                            <select id="copySourceSale" class="form-select">
                                <option value="">-- คัดลอกทุกคน --</option>
                                <?php foreach ($sales_list as $sale): ?>
                                    <option value="<?= htmlspecialchars($sale) ?>"><?= htmlspecialchars($sale) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <h6 class="text-success border-bottom pb-2 mb-3">2. เลือกเป้าหมายการคัดลอก</h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">ไปยังเดือน:</label>
                                <select id="copyTargetMonth" class="form-select">
                                    <option value="">-- เลือกเดือน --</option>
                                    <?php foreach ($thai_months as $num => $name): ?>
                                        <option value="<?= $num ?>"><?= $name ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">ไปยังปี:</label>
                                <select id="copyTargetYear" class="form-select">
                                    <option value="">-- เลือกปี --</option>
                                    <?php
                                    for($y = $current_year - 1; $y <= $current_year + 2; $y++) {
                                        echo "<option value='$y'>$y</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="mt-3 small text-danger">
                            * หากเดือนเป้าหมายมีข้อมูลแผนของลูกค้า/สินค้านั้นอยู่แล้ว ระบบจะ <b>ข้ามรายการนั้นไป</b> (ไม่เขียนทับข้อมูลเดิม)
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" id="btnConfirmCopy" class="btn btn-info fw-bold text-dark">
                        <i class="fas fa-check me-1"></i> ยืนยันการคัดลอก
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Record Modal -->
    <div class="modal fade" id="addRecordModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i> เพิ่มข้อมูลแผนการขาย (ด้วยตนเอง)</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="addRecordForm">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">สำหรับเดือน <span class="text-danger">*</span></label>
                                <select id="addMonth" class="form-select" required>
                                    <option value="">-- เลือกเดือน --</option>
                                    <?php foreach ($thai_months as $num => $name): ?>
                                        <option value="<?= $num ?>"><?= $name ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">สำหรับปี <span class="text-danger">*</span></label>
                                <select id="addYear" class="form-select" required>
                                    <option value="">-- เลือกปี --</option>
                                    <?php
                                    for($y = $current_year - 1; $y <= $current_year + 2; $y++) {
                                        echo "<option value='$y'>$y</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        
                        <h6 class="text-primary border-bottom pb-2 mb-3 mt-4">ข้อมูลลูกค้าและพื้นที่</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-12">
                                <label class="form-label fw-bold">ค้นหาลูกค้า (รหัส หรือ ชื่อ) <span class="text-danger">*</span></label>
                                <select class="form-select" id="addArCodeSearch" style="width: 100%;" required>
                                    <option value="">-- พิมพ์เพื่อค้นหา --</option>
                                </select>
                                <input type="hidden" id="addArCode">
                                <input type="hidden" id="addArName">
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">พนักงานขาย <span class="text-danger">*</span></label>
                                <input type="text" class="form-control bg-light" id="addSaleName" value="<?= $first_name ?>" required readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">พนักงานเทค</label>
                                <input type="text" class="form-control bg-light" id="addTakeName" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">ทีม</label>
                                <input type="text" class="form-control bg-light" id="addTeamName" readonly>
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">อำเภอ</label>
                                <input type="text" class="form-control bg-light" id="addAmphur" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">จังหวัด</label>
                                <input type="text" class="form-control bg-light" id="addProvince" readonly>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">เขต</label>
                                <input type="text" class="form-control bg-light" id="addZone" readonly>
                            </div>
                        </div>

                        <h6 class="text-success border-bottom pb-2 mb-3 mt-4">ข้อมูลสินค้าและเป้าหมาย</h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-8">
                                <label class="form-label fw-bold">ค้นหารหัสสินค้า (SKU) <span class="text-danger">*</span></label>
                                <select class="form-select" id="addSkuCodeSearch" style="width: 100%;" required>
                                    <option value="">-- พิมพ์เพื่อค้นหา --</option>
                                </select>
                                <input type="hidden" id="addSkuCode">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">ยี่ห้อ (Brand)</label>
                                <input type="text" class="form-control bg-light" id="addBrandCode" readonly>
                            </div>
                        </div>
                        <div class="row g-3 mb-3 bg-light p-2 rounded">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">เป้าหมายรวมร้าน</label>
                                <input type="number" class="form-control" id="addTargetQty" value="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-success">คาดการณ์ขายสินค้านี้</label>
                                <input type="number" class="form-control" id="addForecastQty" value="0">
                            </div>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-12">
                                <label class="form-label fw-bold">ผู้รับผิดชอบ</label>
                                <input type="text" class="form-control" id="addResponsiblePerson" placeholder="ระบุผู้รับผิดชอบ (ถ้ามี)">
                            </div>
                        </div>
                        <!-- Set 2 Hidden -->
                        <div class="row g-3 mb-3 bg-light p-2 rounded" style="display:none;">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">เป้าหมายรวมร้าน (เป้า 2)</label>
                                <input type="number" class="form-control" id="addTargetQty2" value="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-success">คาดการณ์ขายสินค้านี้ 2</label>
                                <input type="number" class="form-control" id="addForecastQty2" value="0">
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" id="btnSaveNewRecord" class="btn btn-primary fw-bold">
                        <i class="fas fa-save me-1"></i> บันทึกข้อมูล
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Record Modal -->
    <div class="modal fade" id="editRowModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title"><i class="fas fa-edit me-2"></i> แก้ไขข้อมูลรายการ</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="editRowForm">
                        <input type="hidden" id="editRowId">
                        <div class="mb-3">
                            <label class="form-label fw-bold">ลูกค้า:</label>
                            <div id="editCustomerName" class="text-primary fw-bold"></div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">ยี่ห้อ / SKU:</label>
                            <div id="editBrandSku"></div>
                        </div>
                        <hr>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">เป้าหมาย:</label>
                                <input type="number" class="form-control" id="editTarget1">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">วางแผนการขาย:</label>
                                <input type="number" class="form-control" id="editForecast1">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">เทียบคาดการณ์:</label>
                                <input type="number" class="form-control" id="editCompare1">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">เทียบยอดขาย%:</label>
                                <input type="number" class="form-control" id="editComparePct1">
                            </div>
                        </div>
                        <!-- Set 2 Hidden (Not used in plan_tires_r1.xlsx) -->
                        <div style="display:none;">
                            <input type="number" id="editTarget2" value="0">
                            <input type="number" id="editForecast2" value="0">
                            <input type="number" id="editCompare2" value="0">
                            <input type="number" id="editComparePct2" value="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">หมายเหตุ:</label>
                            <textarea class="form-control" id="editRemark" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">ผู้รับผิดชอบ:</label>
                            <input type="text" class="form-control" id="editResponsiblePerson" placeholder="ระบุผู้รับผิดชอบ">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="button" id="btnSaveRow" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i> บันทึกการแก้ไข
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal สำหรับ Checkbox ซ่อน/แสดง Column -->
    <div class="modal fade" id="columnToggleModal" tabindex="-1" aria-labelledby="columnToggleModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title" id="columnToggleModalLabel">
                        <i class="fas fa-columns me-2"></i> เลือกแสดง / ซ่อน คอลัมน์ตาราง
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                        <span class="text-muted small">ติ๊กเลือกคอลัมน์ที่ต้องการให้แสดงในตาราง</span>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-primary me-1" id="btnSelectAllCols">เลือกทั้งหมด</button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btnResetCols">ค่าเริ่มต้น</button>
                        </div>
                    </div>

                    <form id="colToggleForm">
                        <!-- กลุ่มที่ 1: ข้อมูลร้านค้าและสินค้า -->
                        <h6 class="fw-bold text-primary border-bottom pb-1 mb-2"><i class="fas fa-info-circle me-1"></i> 1. ข้อมูลร้านค้าและสินค้า</h6>
                        <div class="row g-2 mb-3">
                            <div class="col-md-3 col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input col-toggle-chk" type="checkbox" data-column="0" id="col_0" checked>
                                    <label class="form-check-label" for="col_0">รหัสร้านค้า</label>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input col-toggle-chk" type="checkbox" data-column="1" id="col_1" checked disabled>
                                    <label class="form-check-label" for="col_1">ชื่อร้านค้า (ตรึง)</label>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input col-toggle-chk" type="checkbox" data-column="2" id="col_2" checked>
                                    <label class="form-check-label" for="col_2">ยี่ห้อ</label>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input col-toggle-chk" type="checkbox" data-column="3" id="col_3" checked>
                                    <label class="form-check-label" for="col_3">รหัสสินค้า (SKU)</label>
                                </div>
                            </div>
                        </div>

                        <!-- กลุ่มที่ 2: สถิติและยอดขายย้อนหลัง -->
                        <h6 class="fw-bold text-success border-bottom pb-1 mb-2"><i class="fas fa-chart-line me-1"></i> 2. สถิติย้อนหลัง</h6>
                        <div class="row g-2 mb-3">
                            <div class="col-md-3 col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input col-toggle-chk" type="checkbox" data-column="4" id="col_4" checked>
                                    <label class="form-check-label" for="col_4">เฉลี่ย 12 เดือน</label>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input col-toggle-chk" type="checkbox" data-column="5" id="col_5" checked>
                                    <label class="form-check-label" for="col_5">MAX (ปีก่อนหน้า)</label>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input col-toggle-chk" type="checkbox" data-column="6" id="col_6" checked>
                                    <label class="form-check-label" for="col_6">MIN (ปีก่อนหน้า)</label>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input col-toggle-chk" type="checkbox" data-column="7" id="col_7" checked>
                                    <label class="form-check-label" for="col_7">ยอดขาย M-3</label>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input col-toggle-chk" type="checkbox" data-column="8" id="col_8" checked>
                                    <label class="form-check-label" for="col_8">ยอดขาย M-2</label>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input col-toggle-chk" type="checkbox" data-column="9" id="col_9" checked>
                                    <label class="form-check-label" for="col_9">ยอดขาย M-1</label>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input col-toggle-chk" type="checkbox" data-column="10" id="col_10" checked>
                                    <label class="form-check-label" for="col_10">เฉลี่ย 3 เดือน</label>
                                </div>
                            </div>
                        </div>

                        <!-- กลุ่มที่ 3: แพลนพยากรณ์และเป้าหมาย -->
                        <h6 class="fw-bold text-warning text-dark border-bottom pb-1 mb-2"><i class="fas fa-bullseye me-1"></i> 3. เป้ารายเดือน & แพลนยาง (Forecast)</h6>
                        <div class="row g-2 mb-3">
                            <div class="col-md-3 col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input col-toggle-chk" type="checkbox" data-column="11" id="col_11" checked>
                                    <label class="form-check-label" for="col_11">เป้ารายเดือน</label>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input col-toggle-chk" type="checkbox" data-column="12" id="col_12" checked>
                                    <label class="form-check-label" for="col_12">แพลนยาง</label>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input col-toggle-chk" type="checkbox" data-column="13" id="col_13" checked>
                                    <label class="form-check-label" for="col_13">เทียบ แพลน - เฉลี่ย</label>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input col-toggle-chk" type="checkbox" data-column="14" id="col_14" checked>
                                    <label class="form-check-label" for="col_14">เทียบ แพลน - เฉลี่ย %</label>
                                </div>
                            </div>
                        </div>

                        <!-- กลุ่มที่ 4: ยอดขายจริงและการเปรียบเทียบ -->
                        <h6 class="fw-bold text-danger border-bottom pb-1 mb-2"><i class="fas fa-shopping-cart me-1"></i> 4. ยอดขายจริง (Actual Sales)</h6>
                        <div class="row g-2 mb-3">
                            <div class="col-md-4 col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input col-toggle-chk" type="checkbox" data-column="15" id="col_15" checked>
                                    <label class="form-check-label" for="col_15">ยอดขายจริง</label>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input col-toggle-chk" type="checkbox" data-column="16" id="col_16" checked>
                                    <label class="form-check-label" for="col_16">ผลต่าง ขาด/เกิน</label>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input col-toggle-chk" type="checkbox" data-column="17" id="col_17" checked>
                                    <label class="form-check-label" for="col_17">หมายเหตุ</label>
                                </div>
                            </div>
                        </div>

                        <!-- กลุ่มที่ 5: พนักงานและพื้นที่ -->
                        <h6 class="fw-bold text-secondary border-bottom pb-1 mb-2"><i class="fas fa-user-tag me-1"></i> 5. ข้อมูลพนักงานและพื้นที่</h6>
                        <div class="row g-2">
                            <div class="col-md-3 col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input col-toggle-chk" type="checkbox" data-column="18" id="col_18" checked>
                                    <label class="form-check-label" for="col_18">พนักงานขาย</label>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input col-toggle-chk" type="checkbox" data-column="19" id="col_19" checked>
                                    <label class="form-check-label" for="col_19">คนเทค</label>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input col-toggle-chk" type="checkbox" data-column="20" id="col_20" checked>
                                    <label class="form-check-label" for="col_20">ผู้รับผิดชอบ</label>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input col-toggle-chk" type="checkbox" data-column="21" id="col_21" checked>
                                    <label class="form-check-label" for="col_21">อำเภอ</label>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input col-toggle-chk" type="checkbox" data-column="22" id="col_22" checked>
                                    <label class="form-check-label" for="col_22">จังหวัด</label>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input col-toggle-chk" type="checkbox" data-column="23" id="col_23" checked>
                                    <label class="form-check-label" for="col_23">วันที่นำเข้า</label>
                                </div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="form-check form-switch">
                                    <input class="form-check-input col-toggle-chk" type="checkbox" data-column="23" id="col_23" checked disabled>
                                    <label class="form-check-label" for="col_23">จัดการ (บังคับ)</label>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
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
            // Check for auto-filter from report list
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('auto_filter') === '1') {
                const fMonth = localStorage.getItem('forecast_filter_month');
                const fYear = localStorage.getItem('forecast_filter_year');
                const fSale = localStorage.getItem('forecast_filter_sale');
                const fCust = localStorage.getItem('forecast_filter_customer');

                if (fMonth) $('#filterMonth').val(fMonth);
                if (fYear) $('#filterYear').val(fYear);
                
                // Initialize Select2 first, then set values
                $('#filterSale, #filterTake, #filterCustomer, #filterBrand, #filterProvince, #filterAmphur, #filterZone').select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: '-- ทั้งหมด --',
                    allowClear: true
                });

                if (fSale) $('#filterSale').val(fSale).trigger('change');
                if (fCust) $('#filterCustomer').val(fCust).trigger('change');
                
                // Clear localStorage
                localStorage.removeItem('forecast_filter_month');
                localStorage.removeItem('forecast_filter_year');
                localStorage.removeItem('forecast_filter_sale');
                localStorage.removeItem('forecast_filter_customer');
            } else {
                // Initialize Select2 normally
                $('#filterSale, #filterTake, #filterCustomer, #filterBrand, #filterProvince, #filterAmphur, #filterZone').select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: '-- ทั้งหมด --',
                    allowClear: true
                });
            }

            const table = $('#planTable').DataTable({
                "processing": true,
                "serverSide": true,
                "scrollX": true,
                "scrollCollapse": true,
                "fixedColumns": {
                    "left": 4
                },
                "ajax": {
                    "url": "forecast_plan_fetch_realtime.php",
                    "type": "POST",
                    "data": function(d) {
                        d.month = $('#filterMonth').val();
                        d.year = $('#filterYear').val();
                        d.sale_name = $('#filterSale').val();
                        d.take_name = $('#filterTake').val();
                        d.ar_name = $('#filterCustomer').val();
                        d.brand_code = $('#filterBrand').val();
                        d.qty_condition = $('#filterQtyCondition').val();
                        d.province = $('#filterProvince').val();
                        d.amphur = $('#filterAmphur').val();
                        d.zone = $('#filterZone').val();
                    }
                },
                "columns": [
                    { "data": "ar_code" },
                    { "data": "ar_name" },
                    { "data": "brand_code" },
                    { "data": "sku_code" },
                    { "data": "avg_per_month_year", "className": "text-end" },
                    { "data": "max_qty_year", "className": "text-end" },
                    { "data": "min_qty_year", "className": "text-end" },
                    { "data": "sales_month_3", "className": "text-end" },
                    { "data": "sales_month_2", "className": "text-end" },
                    { "data": "sales_month_1", "className": "text-end" },
                    { "data": "avg_3_month", "className": "text-end" },
                    { "data": "target_qty_month", "className": "text-end" },
                    { "data": "forecast_qty_month_1", "className": "text-end" },
                    { "data": "compare_forecast_month_1", "className": "text-end" },
                    { "data": "compare_sales_percent", "className": "text-end" },
                    { "data": "sales_actual_month", "className": "text-end sales-actual" },
                    { "data": "sales_diff_actual", "className": "text-end" },
                    { "data": "remark" },
                    { "data": "sale_name" },
                    { "data": "take_name" },
                    { "data": "responsible_person" },
                    { "data": "amphur" },
                    { "data": "province" },
                    { "data": "import_date" },
                    { "data": "actions", "orderable": false }
                ],
                "order": [[23, "desc"]],
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
                    $('.forecast-qty, .forecast-qty-2').off('input').on('input', function() {
                        calculateRow($(this).closest('tr'));
                    });

                    // Sync target_qty for same customer across visible rows
                    $('.target-qty').off('input').on('input', function() {
                        const arCode = $(this).attr('data-ar-code');
                        const val = $(this).val();
                        if (arCode) {
                            $(`.target-qty[data-ar-code="${arCode}"]`).not(this).val(val);
                        }
                    });

                    updateTableHeaders();
                }
            });

            // ========================================================
            // ระบบจัดการ ซ่อน / แสดง Column แบบ Hybrid (LocalStorage + DB)
            // ========================================================
            const currentUser = <?php echo json_encode($_SESSION['alogin'] ?? 'default'); ?>;
            const STORAGE_KEY = 'forecast_realtime_columns_visible_' + currentUser;
            let saveDbDebounceTimer = null;

            // ฟังก์ชันตั้งค่าการซ่อน/แสดงตามออปเจกต์ที่ส่งเข้ามา
            function applyColumnVisibility(visibleCols) {
                if (!visibleCols) return;
                $.each(visibleCols, function(colIdx, isVisible) {
                    const column = table.column(colIdx);
                    if (column.length && colIdx != 23 && colIdx != 1) { // ยกเว้นคอลัมน์สำคัญที่ตรึงไว้
                        column.visible(isVisible, false);
                        $(`#col_${colIdx}`).prop('checked', isVisible);
                    }
                });
                table.columns.adjust().draw(false);
            }

            // ฟังก์ชันโหลดค่าการตั้งค่าคอลัมน์ (ทำงานร่วมกัน 2 ระดับ)
            function loadColumnVisibility() {
                // 1. โหลดจาก LocalStorage ก่อนเป็นอันดับแรกเพื่อความรวดเร็วในการแสดงผล (Instant Render)
                const savedState = localStorage.getItem(STORAGE_KEY);
                if (savedState) {
                    try {
                        applyColumnVisibility(JSON.parse(savedState));
                    } catch (e) {
                        console.error("Error loading LocalStorage column settings", e);
                    }
                }

                // 2. ดึงค่าล่าสุดจาก DB ในเบื้องหลังมาซิงก์ข้ามเครื่อง (Cross-device Sync)
                $.ajax({
                    url: 'forecast_save_user_column_config.php',
                    type: 'POST',
                    data: {
                        action: 'load',
                        page_name: 'forecast_plan_display_realtime'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success && response.exists && response.column_settings) {
                            applyColumnVisibility(response.column_settings);
                            // ซิงก์ค่ากลับเข้า LocalStorage ให้ตรงกัน
                            localStorage.setItem(STORAGE_KEY, JSON.stringify(response.column_settings));
                        }
                    },
                    error: function(err) {
                        console.log("DB column settings sync skipped", err);
                    }
                });
            }

            // เมื่อมีการกดสับสวิตช์ Checkbox
            $('.col-toggle-chk').on('change', function () {
                const colIdx = $(this).data('column');
                const isChecked = $(this).is(':checked');

                const column = table.column(colIdx);
                column.visible(isChecked);

                saveColumnVisibility();
            });

            // ฟังก์ชันบันทึกค่า (บันทึกลง LocalStorage ทันที และส่ง AJAX ลง DB)
            function saveColumnVisibility() {
                const colState = {};
                $('.col-toggle-chk').each(function() {
                    const colIdx = $(this).data('column');
                    colState[colIdx] = $(this).is(':checked');
                });

                // บันทึกลง LocalStorage ทันที
                localStorage.setItem(STORAGE_KEY, JSON.stringify(colState));

                // ส่งไปบันทึกลง DB (Debounce 500ms ป้องกันการส่งซ้ำสแปมเซิร์ฟเวอร์)
                clearTimeout(saveDbDebounceTimer);
                saveDbDebounceTimer = setTimeout(function() {
                    $.ajax({
                        url: 'forecast_save_user_column_config.php',
                        type: 'POST',
                        data: {
                            action: 'save',
                            page_name: 'forecast_plan_display_realtime',
                            column_settings: JSON.stringify(colState)
                        },
                        dataType: 'json',
                        success: function(response) {
                            // บันทึก DB สำเร็จ
                        }
                    });
                }, 500);
            }

            // ปุ่มเลือกทั้งหมด
            $('#btnSelectAllCols').on('click', function() {
                $('.col-toggle-chk').not(':disabled').prop('checked', true).trigger('change');
            });

            // ปุ่มคืนค่าเริ่มต้น (ล้างทั้ง LocalStorage และ DB)
            $('#btnResetCols').on('click', function() {
                localStorage.removeItem(STORAGE_KEY);
                $('.col-toggle-chk').not(':disabled').prop('checked', true);
                for (let i = 0; i < 25; i++) {
                    table.column(i).visible(true, false);
                }
                table.columns.adjust().draw(false);

                // ส่งสัญญาณล้างค่าใน DB
                $.ajax({
                    url: 'forecast_save_user_column_config.php',
                    type: 'POST',
                    data: {
                        action: 'reset',
                        page_name: 'forecast_plan_display_realtime'
                    }
                });
            });

            loadColumnVisibility();

            function calculateRow($row) {
                const avg3m = parseFloat($row.find('.avg-3m').data('val')) || 0;
                
                // Set 1 (Based on plan_tires_r1.xlsx formula: AH/AF * 100)
                const forecast = parseFloat($row.find('.forecast-qty').val().replace(/,/g, '')) || 0;
                const diff = forecast - avg3m;
                const pct = (avg3m !== 0) ? (forecast / avg3m) * 100 : 0;
                $row.find('.compare-1').val(diff.toLocaleString('en-US', {minimumFractionDigits: 0}));
                $row.find('.compare-pct').val(pct.toFixed(0) + '%');

                // Recalculate Actual Diff (Plan - Actual)
                const salesActual = parseFloat($row.find('.sales-actual').text().replace(/,/g, '')) || 0;
                const diffActual = salesActual - forecast;
                const $diffField = $row.find('.diff-actual');
                $diffField.val(diffActual.toLocaleString('en-US', {minimumFractionDigits: 0}));
                
                // Clear styling and set colors
                $diffField.removeClass('text-danger text-primary fw-bold');
                if (diffActual < 0) {
                    $diffField.addClass('text-danger fw-bold');
                } else if (diffActual > 0) {
                    $diffField.addClass('text-primary fw-bold');
                }

                // Set 2
                const forecast2 = parseFloat($row.find('.forecast-qty-2').val().replace(/,/g, '')) || 0;
                const diff2 = forecast2 - avg3m;
                const pct2 = (avg3m !== 0) ? (forecast2 / avg3m) * 100 : 0;
                $row.find('.compare-2').val(diff2.toLocaleString('en-US', {minimumFractionDigits: 0}));
                $row.find('.compare-pct-2').val(pct2.toFixed(0) + '%');
            }

            $('#btnFilter').click(function() {
                updateTableHeaders();
                updateCustomerInfo();
                table.draw();
            });

            function updateCustomerInfo() {
                const month = $('#filterMonth').val();
                const year = $('#filterYear').val();
                const ar_name = $('#filterCustomer').val();

                if (!ar_name) {
                    $('#customerInfoCard').addClass('d-none');
                    return;
                }

                $.ajax({
                    url: 'forecast_fetch_customer_info_realtime.php',
                    type: 'POST',
                    data: { month: month, year: year, ar_name: ar_name },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            const d = response.data;
                            $('#infoCustName').text(d.ar_name);
                            $('#infoCustCode').text(d.ar_code);
                            $('#infoSaleName').text(d.sale_name);
                            $('#infoLocation').text(d.amphur + ', ' + d.province + ' (เขต: ' + d.zone + ')');
                            
                            $('#infoTotalTarget').text(numberFormat(d.total_target));
                            $('#infoTotalTarget2').text(numberFormat(d.total_target2));
                            
                            $('#customerInfoCard').removeClass('d-none');
                        } else {
                            $('#customerInfoCard').addClass('d-none');
                        }
                    }
                });
            }

            // Brand Checkbox Logic
            $('#brandAll').change(function() {
                $('.brand-check').prop('checked', $(this).prop('checked'));
            });

            $('.brand-check').change(function() {
                if ($('.brand-check:checked').length === $('.brand-check').length) {
                    $('#brandAll').prop('checked', true);
                } else {
                    $('#brandAll').prop('checked', false);
                }
            });

            $('#btnQuickBrand').click(function() {
                $('.brand-check').prop('checked', false);
                $('#brandAll').prop('checked', false);
                $('.brand-check[value="AT"], .brand-check[value="LLIT"], .brand-check[value="LEAO"]').prop('checked', true);
                fetchBrandSummary();
            });

            $('#groupATLEAOLLIT').change(function() {
                fetchBrandSummary(false);
            });

            let previewTable = null;

            // Function to update the number of records to be deleted in real-time
            function updateDeleteCount() {
                const month = $('#delMonth').val();
                const year = $('#delYear').val();
                const saleName = $('#delSaleName').val();
                const brandCode = $('#delBrandCode').val();

                if (!month || !year) return;

                $('#delRecordsCount').html('<i class="fas fa-spinner fa-spin"></i>');
                $('#btnConfirmDeleteConditions').prop('disabled', true);
                $('#btnPreviewDelete').prop('disabled', true);

                $.ajax({
                    url: 'forecast_count_delete_records_realtime.php',
                    type: 'POST',
                    data: {
                        month: month,
                        year: year,
                        sale_name: saleName,
                        brand_code: brandCode
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#delRecordsCount').text(response.count.toLocaleString());
                            if (response.count === 0) {
                                $('#btnConfirmDeleteConditions').prop('disabled', true);
                                $('#btnPreviewDelete').prop('disabled', true);
                            } else {
                                $('#btnConfirmDeleteConditions').prop('disabled', false);
                                $('#btnPreviewDelete').prop('disabled', false);
                            }
                        } else {
                            $('#delRecordsCount').text('Error');
                        }
                    },
                    error: function() {
                        $('#delRecordsCount').text('Error');
                    }
                });
            }

            // Bind change events to recount
            $('#delSaleName, #delBrandCode').change(function() {
                updateDeleteCount();
            });

            // Preview delete records click event
            $('#btnPreviewDelete').click(function() {
                const month = $('#delMonth').val();
                const year = $('#delYear').val();
                const saleName = $('#delSaleName').val();
                const brandCode = $('#delBrandCode').val();

                if (!month || !year) return;

                // Open the preview modal
                $('#previewDeleteModal').modal('show');

                // Initialize or reload DataTable
                if (previewTable) {
                    previewTable.destroy();
                }

                previewTable = $('#previewDeleteTable').DataTable({
                    processing: true,
                    serverSide: false,
                    ajax: {
                        url: 'forecast_fetch_delete_preview_realtime.php',
                        type: 'POST',
                        data: {
                            month: month,
                            year: year,
                            sale_name: saleName,
                            brand_code: brandCode
                        }
                    },
                    columns: [
                        { data: 'ar_code' },
                        { data: 'ar_name' },
                        { data: 'sale_name' },
                        { data: 'brand_code' },
                        { data: 'sku_code' },
                        { data: 'forecast_qty_month_1', className: 'text-end', render: $.fn.dataTable.render.number(',', '.', 0) }
                    ],
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/th.json'
                    },
                    pageLength: 10,
                    lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "ทั้งหมด"]]
                });
            });

            // Adjust modal-open body class on nested modal close
            $('#previewDeleteModal').on('hidden.bs.modal', function () {
                if ($('#deleteConditionsModal').hasClass('show')) {
                    $('body').addClass('modal-open');
                }
            });


            // Handle Drag and Drop for Realtime Upload Modal
            let dragArea = document.getElementById('dragArea');
            if (dragArea) {
                ['dragenter', 'dragover'].forEach(eventName => {
                    dragArea.addEventListener(eventName, (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        dragArea.style.borderColor = '#198754';
                        dragArea.style.backgroundColor = '#eafaf1';
                    }, false);
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    dragArea.addEventListener(eventName, (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        dragArea.style.borderColor = '#198754';
                        dragArea.style.backgroundColor = '#f8f9fa';
                    }, false);
                });

                dragArea.addEventListener('drop', (e) => {
                    let dt = e.dataTransfer;
                    let files = dt.files;
                    if (files.length) {
                        document.getElementById('realtimeFileInput').files = files;
                        $('#realtimeFileInput').trigger('change');
                    }
                }, false);
            }

            $('#realtimeFileInput').change(function() {
                var fileName = $(this).val().split('\\').pop();
                if (fileName) {
                    $('#realtimeFileName').text(fileName).addClass('text-success fw-bold');
                }
            });

            // Set current month/year to upload form when modal opens
            $('#uploadRealtimeModal').on('show.bs.modal', function() {
                var currentMonth = $('#filterMonth').val();
                var currentYear = $('#filterYear').val();
                if (currentMonth) $('#uploadMonth').val(currentMonth);
                if (currentYear) $('#uploadYear').val(currentYear);
            });

            // Realtime Excel upload submission
            $('#btnSubmitUpload').click(function() {
                var fileInput = document.getElementById('realtimeFileInput');
                var month = $('#uploadMonth').val();
                var year = $('#uploadYear').val();

                if (!month) {
                    Swal.fire('แจ้งเตือน', 'กรุณาเลือกเดือน', 'warning');
                    return;
                }
                if (!year) {
                    Swal.fire('แจ้งเตือน', 'กรุณาเลือกปี', 'warning');
                    return;
                }
                if (fileInput.files.length === 0) {
                    Swal.fire('แจ้งเตือน', 'กรุณาเลือกไฟล์ Excel (.xls, .xlsx)', 'warning');
                    return;
                }

                var formData = new FormData();
                formData.append('excel_file', fileInput.files[0]);
                formData.append('forcast_month', month);
                formData.append('forcast_year', year);

                $('#uploadProgressArea').show();
                $('#btnSubmitUpload, #realtimeUploadForm input, #realtimeUploadForm select').prop('disabled', true);

                let width = 0;
                let progressInterval = setInterval(function() {
                    if (width < 30) {
                        width += Math.random() * 5 + 3;
                        $('#uploadStatusText').html('<i class="fas fa-spinner fa-spin me-2"></i>กำลังอัปโหลดไฟล์...');
                    } else if (width < 75) {
                        width += Math.random() * 2 + 1;
                        $('#uploadStatusText').html('<i class="fas fa-database me-2"></i>กำลังคำนวณและบันทึกลงฐานข้อมูล...');
                    } else if (width < 96) {
                        width += Math.random() * 0.5 + 0.1;
                        $('#uploadStatusText').html('<i class="fas fa-sync fa-spin me-2"></i>กำลังซิงค์และอัปเดตข้อมูลลูกค้า...');
                    } else if (width < 99) {
                        width += 0.05;
                    }
                    width = Math.min(width, 99);
                    $('#uploadProgressBar').css('width', width.toFixed(0) + '%').text(width.toFixed(0) + '%');
                }, 450);

                $.ajax({
                    url: 'import_process/import_forecast_realtime_process.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function(response) {
                        clearInterval(progressInterval);
                        if (response.success) {
                            $('#uploadProgressBar').css('width', '100%').removeClass('bg-success').addClass('bg-success').text('100%');
                            $('#uploadStatusText').html('<i class="fas fa-check-circle text-success me-1"></i>นำเข้าข้อมูลเสร็จสมบูรณ์!');
                            
                            setTimeout(function() {
                                $('#uploadRealtimeModal').modal('hide');
                                Swal.fire('สำเร็จ', 'นำเข้าข้อมูลจาก Excel เรียบร้อยแล้ว ทั้งหมด ' + response.count.toLocaleString() + ' รายการ', 'success').then(() => {
                                    // Set main filters and trigger search
                                    $('#filterMonth').val(month).trigger('change.select2');
                                    $('#filterYear').val(year).trigger('change.select2');
                                    
                                    // Reset Form
                                    $('#realtimeUploadForm')[0].reset();
                                    $('#realtimeFileName').text('คลิกเพื่อเลือกไฟล์ หรือลากไฟล์มาวางที่นี่').removeClass('text-success fw-bold');
                                    $('#uploadProgressArea').hide();
                                    $('#uploadProgressBar').css('width', '0%').text('0%');
                                    $('#btnSubmitUpload, #realtimeUploadForm input, #realtimeUploadForm select').prop('disabled', false);

                                    // Refresh Table
                                    $('#btnFilter').click();
                                });
                            }, 800);
                        } else {
                            clearInterval(progressInterval);
                            $('#uploadProgressArea').hide();
                            $('#btnSubmitUpload, #realtimeUploadForm input, #realtimeUploadForm select').prop('disabled', false);
                            Swal.fire('เกิดข้อผิดพลาด', response.message, 'error');
                        }
                    },
                    error: function(xhr, status, error) {
                        clearInterval(progressInterval);
                        $('#uploadProgressArea').hide();
                        $('#btnSubmitUpload, #realtimeUploadForm input, #realtimeUploadForm select').prop('disabled', false);
                        Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'error');
                    }
                });
            });

            // Export Excel button click
            $('#btnExportExcel').click(function() {
                const month = $('#filterMonth').val();
                const year = $('#filterYear').val();
                if (!month || !year) {
                    Swal.fire('แจ้งเตือน', 'กรุณาเลือกเดือนและปีเพื่อทำรายการ Export', 'warning');
                    return;
                }

                const sale_name = $('#filterSale').val();
                const take_name = $('#filterTake').val();
                const ar_name = $('#filterCustomer').val();
                const brand_code = $('#filterBrand').val();
                const qty_condition = $('#filterQtyCondition').val();
                const province = $('#filterProvince').val();
                const amphur = $('#filterAmphur').val();
                const zone = $('#filterZone').val();
                const search = table.search() || '';

                // Open export file in new window with parameters
                const params = $.param({
                    month: month,
                    year: year,
                    sale_name: sale_name,
                    take_name: take_name,
                    ar_name: ar_name,
                    brand_code: brand_code,
                    qty_condition: qty_condition,
                    province: province,
                    amphur: amphur,
                    zone: zone,
                    search: search
                });
                
                window.open('forecast_plan_export_excel_realtime.php?' + params, '_blank');
            });

            // Delete by Conditions button click
            $('#btnDeleteByConditions').click(function() {
                const month = $('#filterMonth').val();
                const year = $('#filterYear').val();
                if (!month || !year) {
                    Swal.fire('แจ้งเตือน', 'กรุณาเลือกเดือนและปีที่ต้องการลบก่อน', 'warning');
                    return;
                }
                
                // Get month name in Thai
                const monthNames = {
                    "01": "มกราคม", "02": "กุมภาพันธ์", "03": "มีนาคม", "04": "เมษายน",
                    "05": "พฤษภาคม", "06": "มิถุนายน", "07": "กรกฎาคม", "08": "สิงหาคม",
                    "09": "กันยายน", "10": "ตุลาคม", "11": "พฤศจิกายน", "12": "ธันวาคม"
                };
                const monthText = monthNames[month] || month;

                $('#delMonth').val(month);
                $('#delYear').val(year);
                $('#delShowMonth').val(monthText);
                $('#delShowYear').val(year);

                // Set pre-selected Sale Name from the main screen's filter
                const currentSaleFilter = $('#filterSale').val();
                if (currentSaleFilter) {
                    $('#delSaleName').val(currentSaleFilter);
                } else {
                    // Reset to default (first option / empty if not locked)
                    <?php if ($account_type !== 'admin' && $account_type !== 'sale_sup'): ?>
                        // Lock to current sale if not admin/sale_sup (handled by PHP option disabled)
                    <?php else: ?>
                        $('#delSaleName').val('');
                    <?php endif; ?>
                }

                // Reset brand dropdown to "all"
                $('#delBrandCode').val('all');

                // Load initial delete count
                updateDeleteCount();

                // Show modal
                $('#deleteConditionsModal').modal('show');
            });

            // Confirm Delete by Conditions click
            $('#btnConfirmDeleteConditions').click(function() {
                const month = $('#delMonth').val();
                const year = $('#delYear').val();
                const saleName = $('#delSaleName').val();
                const brandCode = $('#delBrandCode').val();

                const saleText = saleName ? saleName : "พนักงานทุกคน";
                const brandText = brandCode !== 'all' ? brandCode : "ทุกยี่ห้อ";
                const monthText = $('#delShowMonth').val();
                const recordsCount = $('#delRecordsCount').text();

                Swal.fire({
                    title: 'ยืนยันการลบข้อมูล?',
                    html: `คุณกำลังจะลบข้อมูลแผนการขายประจำเดือน <b>${monthText} ${year}</b><br>` +
                          `เงื่อนไข: พนักงานขาย = <b>${saleText}</b> | ยี่ห้อ = <b>${brandText}</b><br>` +
                          `จำนวนรายการที่จะลบ: <b class="text-danger h4">${recordsCount} รายการ</b><br><br>` +
                          `<span class="text-danger fw-bold">คำเตือน: ข้อมูลจะถูกลบออกจากระบบทันทีและไม่สามารถกู้คืนได้!</span>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'ใช่, ฉันต้องการลบข้อมูล',
                    cancelButtonText: 'ยกเลิก',
                    allowOutsideClick: false
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'กำลังดำเนินการลบข้อมูล',
                            text: 'กรุณารอสักครู่...',
                            allowOutsideClick: false,
                            didOpen: () => { Swal.showLoading(); }
                        });

                        $.ajax({
                            url: 'forecast_delete_by_conditions_realtime.php',
                            type: 'POST',
                            data: {
                                month: month,
                                year: year,
                                sale_name: saleName,
                                brand_code: brandCode
                            },
                            dataType: 'json',
                            success: function(response) {
                                Swal.close();
                                if (response.success) {
                                    Swal.fire({
                                        title: 'ลบข้อมูลสำเร็จ',
                                        text: response.message,
                                        icon: 'success'
                                    }).then(() => {
                                        $('#deleteConditionsModal').modal('hide');
                                        // Refresh the main table and update headers/targets
                                        if (typeof table !== 'undefined') {
                                            table.draw(false);
                                        }
                                        if (typeof updateCustomerInfo === 'function') {
                                            updateCustomerInfo();
                                        }
                                    });
                                } else {
                                    Swal.fire('เกิดข้อผิดพลาด', response.message, 'error');
                                }
                            },
                            error: function() {
                                Swal.close();
                                Swal.fire('ผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
                            }
                        });
                    }
                });
            });

            $('#btnShowSummary').click(function() {
                const month = $('#filterMonth').val();
                const year = $('#filterYear').val();
                if (!month || !year) {
                    Swal.fire('แจ้งเตือน', 'กรุณาเลือกเดือนและปีเพื่อดูสรุป', 'warning');
                    return;
                }
                fetchBrandSummary(true); // true means show modal after fetch
            });

            $('#btnRefreshSummary').click(function() {
                fetchBrandSummary(false);
            });

            function fetchBrandSummary(showModal = false) {
                const month = $('#filterMonth').val();
                const year = $('#filterYear').val();
                const sale_name = $('#filterSale').val();
                const take_name = $('#filterTake').val();
                const ar_name = $('#filterCustomer').val();
                
                const selectedBrands = [];
                if (!$('#brandAll').prop('checked')) {
                    $('.brand-check:checked').each(function() {
                        selectedBrands.push($(this).val());
                    });
                }

                const group_at_leao_llit = $('#groupATLEAOLLIT').prop('checked') ? 'Y' : 'N';

                Swal.fire({
                    title: 'กำลังประมวลผล',
                    text: 'กรุณารอสักครู่...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: 'forecast_fetch_brand_summary_realtime.php',
                    type: 'POST',
                    data: {
                        month: month,
                        year: year,
                        sale_name: sale_name,
                        take_name: take_name,
                        ar_name: ar_name,
                        brands: selectedBrands,
                        group_at_leao_llit: group_at_leao_llit
                    },
                    dataType: 'json',
                    success: function(response) {
                        Swal.close();
                        if (response.success) {
                            let html = '';
                            const total = { avg12: 0, m1: 0, m2: 0, m3: 0, avg3: 0, forecast: 0, diff: 0, target: 0, target2: 0 };

                            response.data.forEach(function(item) {
                                const avg3 = parseFloat(item.total_avg_3m) || 0;
                                const forecast = parseFloat(item.total_forecast) || 0;
                                const diff = forecast - avg3;
                                const pct = (avg3 !== 0) ? (diff / avg3) * 100 : 0;

                                total.avg12 += parseFloat(item.total_avg_12m) || 0;
                                total.m1 += parseFloat(item.total_m1) || 0;
                                total.m2 += parseFloat(item.total_m2) || 0;
                                total.m3 += parseFloat(item.total_m3) || 0;
                                total.avg3 += avg3;
                                total.forecast += forecast;
                                total.diff += diff;
                                total.target += parseFloat(item.total_target) || 0;
                                total.target2 += parseFloat(item.total_target2) || 0;

                                html += '<tr>' +
                                    '<td>' + (item.brand_code || 'ไม่ระบุ') + '</td>' +
                                    '<td class="text-end fw-bold text-primary">' + numberFormat(item.total_target) + '</td>' +
                                    '<td class="text-end fw-bold text-primary">' + numberFormat(item.total_target2) + '</td>' +
                                    '<td class="text-end text-success fw-bold">' + numberFormat(forecast) + '</td>' +
                                    '<td class="text-end">' + numberFormat(item.total_avg_12m) + '</td>' +
                                    '<td class="text-end">' + numberFormat(item.total_m3) + '</td>' +
                                    '<td class="text-end">' + numberFormat(item.total_m2) + '</td>' +
                                    '<td class="text-end">' + numberFormat(item.total_m1) + '</td>' +
                                    '<td class="text-end">' + numberFormat(avg3) + '</td>' +
                                    '<td class="text-end">' + numberFormat(diff) + '</td>' +
                                    '<td class="text-end">' + pct.toFixed(0) + '%</td>' +
                                    '</tr>';
                            });

                            $('#summaryTableBody').html(html);

                            // Footer Totals
                            const totalPct = (total.avg3 !== 0) ? (total.diff / total.avg3) * 100 : 0;
                            const footHtml = '<tr>' +
                                '<td>รวมทั้งหมด</td>' +
                                '<td class="text-end">' + numberFormat(total.target) + '</td>' +
                                '<td class="text-end">' + numberFormat(total.target2) + '</td>' +
                                '<td class="text-end">' + numberFormat(total.forecast) + '</td>' +
                                '<td class="text-end">' + numberFormat(total.avg12) + '</td>' +
                                '<td class="text-end">' + numberFormat(total.m3) + '</td>' +
                                '<td class="text-end">' + numberFormat(total.m2) + '</td>' +
                                '<td class="text-end">' + numberFormat(total.m1) + '</td>' +
                                '<td class="text-end">' + numberFormat(total.avg3) + '</td>' +
                                '<td class="text-end">' + numberFormat(total.diff) + '</td>' +
                                '<td class="text-end">' + totalPct.toFixed(0) + '%</td>' +
                                '</tr>';
                            $('#summaryTableFoot').html(footHtml);

                            if (showModal) {
                                $('#summaryModal').modal('show');
                            }
                        } else {
                            Swal.fire('ผิดพลาด', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('ผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
                    }
                });
            }

            function numberFormat(num) {
                return parseFloat(num).toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
            }

            function updateTableHeaders() {
                const month = parseInt($('#filterMonth').val());
                const year = parseInt($('#filterYear').val()) || new Date().getFullYear();

                // Previous Year (BE / พ.ศ.)
                const prevYearBE = (year - 1) + 543;
                $(table.column(4).header()).text('เฉลี่ย 12ด. (' + prevYearBE + ')');
                $(table.column(5).header()).text('MAX (' + prevYearBE + ')');
                $(table.column(6).header()).text('MIN (' + prevYearBE + ')');

                if (!month) return;

                const thai_months = [
                    '', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.',
                    'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'
                ];

                // M-1, M-2, M-3
                let m1 = month - 1; if (m1 <= 0) m1 += 12;
                let m2 = month - 2; if (m2 <= 0) m2 += 12;
                let m3 = month - 3; if (m3 <= 0) m3 += 12;

                $(table.column(7).header()).text('ยอดขายเดือน ' + thai_months[m3]);
                $(table.column(8).header()).text('ยอดขายเดือน ' + thai_months[m2]);
                $(table.column(9).header()).text('ยอดขายเดือน ' + thai_months[m1]);
                $(table.column(10).header()).text('เฉลี่ย 3ด.');
                $(table.column(12).header()).text('แพลนยาง ' + thai_months[month]);
                $(table.column(15).header()).text('ยอดขายจริง ' + thai_months[month]);
            }

            // Initial headers update if values exist
            updateTableHeaders();

            // Open Edit Row Modal
            $('#planTable').on('click', '.btn-edit-row', function() {
                const data = $(this).data();
                $('#editRowId').val(data.id);
                $('#editCustomerName').text(data.customer);
                $('#editBrandSku').text(data.brand + ' / ' + data.sku);
                $('#editTarget1').val(data.target1);
                $('#editForecast1').val(data.forecast1);
                $('#editCompare1').val(data.compare1);
                $('#editComparePct1').val(data.comparepct1);
                $('#editTarget2').val(data.target2);
                $('#editForecast2').val(data.forecast2);
                $('#editCompare2').val(data.compare2);
                $('#editComparePct2').val(data.comparepct2);
                $('#editRemark').val(data.remark);
                $('#editResponsiblePerson').val(data.responsible || '');
                
                $('#editRowModal').modal('show');
            });

            // Handle Enter key to move focus down
            $('#planTable').on('keydown', '.forecast-qty, .target-qty, .remark', function(e) {
                if (e.which === 13) { // Enter key
                    e.preventDefault();
                    const $currentRow = $(this).closest('tr');
                    const $nextRow = $currentRow.next('tr');
                    
                    let className = '';
                    if ($(this).hasClass('forecast-qty')) {
                        className = '.forecast-qty';
                    } else if ($(this).hasClass('target-qty')) {
                        className = '.target-qty';
                    } else if ($(this).hasClass('remark')) {
                        className = '.remark';
                    }
                    
                    if (className && $nextRow.length) {
                        const $nextInput = $nextRow.find(className);
                        if ($nextInput.length) {
                            $nextInput.focus().select();
                        }
                    }
                }
            });

            // Delete Row
            $('#planTable').on('click', '.btn-delete-row', function() {
                const data = $(this).data();
                const id = data.id;
                const customer = data.customer;
                const sku = data.sku;

                Swal.fire({
                    title: 'ยืนยันการลบข้อมูล',
                    text: `คุณต้องการลบข้อมูลของลูกค้า "${customer}" (สินค้า: ${sku}) หรือไม่?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'ลบข้อมูล',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'กำลังลบข้อมูล',
                            allowOutsideClick: false,
                            didOpen: () => { Swal.showLoading(); }
                        });

                        $.ajax({
                            url: 'forecast_delete_record_realtime.php',
                            type: 'POST',
                            data: { id: id },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('สำเร็จ', response.message, 'success');
                                    updateCustomerInfo(); // Update total target above table
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

            // Save Single Row
            $('#btnSaveRow').click(function() {
                const id = $('#editRowId').val();
                
                function cleanVal(selector) {
                    const val = $(selector).val();
                    if (val === null || val === undefined) return 0;
                    return parseFloat(val.toString().replace(/,/g, '').replace(/%/g, '')) || 0;
                }

                const updates = [{
                    id: id,
                    target_qty: cleanVal('#editTarget1'),
                    forecast_qty: cleanVal('#editForecast1'),
                    compare_1: cleanVal('#editCompare1'),
                    compare_pct: cleanVal('#editComparePct1'),
                    remark: $('#editRemark').val(),
                    responsible_person: $('#editResponsiblePerson').val(),
                    target_qty_2: cleanVal('#editTarget2'),
                    forecast_qty_2: cleanVal('#editForecast2'),
                    compare_2: cleanVal('#editCompare2'),
                    compare_pct_2: cleanVal('#editComparePct2')
                }];

                $.ajax({
                    url: 'forecast_update_plan_realtime.php',
                    type: 'POST',
                    data: { updates: JSON.stringify(updates) },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('สำเร็จ', 'บันทึกข้อมูลเรียบร้อยแล้ว', 'success');
                            $('#editRowModal').modal('hide');
                            updateCustomerInfo(); // Update numbers above datatables
                            table.draw(false);
                        } else {
                            Swal.fire('ผิดพลาด', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'error');
                    }
                });
            });

            $('#btnSyncData').click(function() {
                const month = $('#filterMonth').val();
                const year = $('#filterYear').val();

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

            $('#btnCopyNextMonth').click(function() {
                const currentMonth = $('#filterMonth').val();
                const currentYear = $('#filterYear').val();
                const currentSale = $('#filterSale').val();

                if (currentMonth) $('#copySourceMonth').val(currentMonth);
                if (currentYear) {
                    $('#copySourceYear').val(currentYear);
                    // Set default target to next month
                    let nextMonth = parseInt(currentMonth) + 1;
                    let nextYear = parseInt(currentYear);
                    if (nextMonth > 12) {
                        nextMonth = 1;
                        nextYear++;
                    }
                    $('#copyTargetMonth').val(nextMonth);
                    $('#copyTargetYear').val(nextYear);
                }
                if (currentSale) $('#copySourceSale').val(currentSale);

                $('#copyPlanModal').modal('show');
            });

            $('#btnConfirmCopy').click(function() {
                const source_month = $('#copySourceMonth').val();
                const source_year = $('#copySourceYear').val();
                const target_month = $('#copyTargetMonth').val();
                const target_year = $('#copyTargetYear').val();
                const sale_name = $('#copySourceSale').val();

                if (!source_month || !source_year || !target_month || !target_year) {
                    Swal.fire('แจ้งเตือน', 'กรุณาเลือกเดือนและปีให้ครบถ้วน', 'warning');
                    return;
                }

                if (source_month === target_month && source_year === target_year) {
                    Swal.fire('แจ้งเตือน', 'เดือนและปีเป้าหมายต้องไม่ซ้ำกับต้นฉบับ', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'กำลังดำเนินการ',
                    text: 'กรุณารอสักครู่ ระบบกำลังคัดลอกข้อมูล...',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: 'forecast_copy_month_process_realtime.php',
                    type: 'POST',
                    data: { 
                        source_month: source_month, 
                        source_year: source_year,
                        target_month: target_month,
                        target_year: target_year,
                        sale_name: sale_name
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#copyPlanModal').modal('hide');
                            Swal.fire({
                                title: 'สำเร็จ',
                                text: response.message,
                                icon: 'success'
                            }).then(() => {
                                // Switch view to target month
                                $('#filterMonth').val(target_month);
                                $('#filterYear').val(target_year);
                                if (sale_name) {
                                    $('#filterSale').val(sale_name).trigger('change');
                                }
                                $('#btnFilter').click();
                            });
                        } else {
                            Swal.fire('ผิดพลาด', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'error');
                    }
                });
            });

            $('#btnAddRecord').click(function() {
                // Pre-fill month/year if filtered
                const fMonth = $('#filterMonth').val();
                const fYear = $('#filterYear').val();
                const fCustomer = $('#filterCustomer').val();
                
                // Clear previous inputs
                $('#addRecordForm')[0].reset();
                if (fMonth) $('#addMonth').val(fMonth);
                if (fYear) $('#addYear').val(fYear);
                $('#addSaleName').val('<?= $first_name ?>'); // Reset to default logged-in user

                // Clear hidden values explicitly (since form.reset() doesn't clear hidden inputs)
                $('#addArCode').val('');
                $('#addArName').val('');
                $('#addSkuCode').val('');
                $('#addBrandCode').val('');

                // Clear Select2 values
                $('#addArCodeSearch').val(null).trigger('change');
                $('#addSkuCodeSearch').val(null).trigger('change');

                // Reset target/forecast inputs
                $('#addTargetQty').val('0');
                $('#addForecastQty').val('0');
                $('#addTargetQty2').val('0');
                $('#addForecastQty2').val('0');

                if (fCustomer && fMonth && fYear) {
                    // Fetch full customer details
                    $.ajax({
                        url: 'forecast_fetch_customer_info_realtime.php',
                        type: 'POST',
                        data: { month: fMonth, year: fYear, ar_name: fCustomer },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                const d = response.data;
                                // Pre-fill fields in the form
                                $('#addArCode').val(d.ar_code);
                                $('#addArName').val(d.ar_name);
                                if (d.sale_name) $('#addSaleName').val(d.sale_name);
                                $('#addTakeName').val(d.take_name || '');
                                $('#addTeamName').val(d.team_name || '');
                                $('#addAmphur').val(d.amphur || '');
                                $('#addProvince').val(d.province || '');
                                $('#addZone').val(d.zone || '');
                                
                                // Set Targets
                                $('#addTargetQty').val(d.total_target || 0);
                                $('#addTargetQty2').val(d.total_target2 || 0);

                                // Pre-select the customer in the select2 dropdown
                                const displayText = d.ar_code + ' - ' + d.ar_name;
                                const newOption = new Option(displayText, d.ar_code, true, true);
                                $(newOption).data('data', {
                                    id: d.ar_code,
                                    text: displayText,
                                    ar_name: d.ar_name,
                                    sale_name: d.sale_name,
                                    take_name: d.take_name,
                                    team_name: d.team_name,
                                    amphur: d.amphur,
                                    province: d.province,
                                    zone: d.zone
                                });
                                $('#addArCodeSearch').append(newOption).trigger('change');
                            }
                        }
                    });
                }

                $('#addRecordModal').modal('show');
            });

            // Initialize Select2 for Add Record Modal
            $('#addArCodeSearch').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#addRecordModal'),
                ajax: {
                    url: 'forecast_search_customer.php',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return { q: params.term };
                    },
                    processResults: function(data) {
                        return { results: data.results };
                    },
                    cache: true
                },
                minimumInputLength: 2
            }).on('select2:select', function(e) {
                const data = e.params.data;
                $('#addSkuCode').val(data.id);
                $('#addBrandCode').val(data.brand_code || '');
            });

            $('#btnSaveNewRecord').click(function() {
                const formData = {
                    month: $('#addMonth').val(),
                    year: $('#addYear').val(),
                    ar_code: $('#addArCode').val().trim(),
                    ar_name: $('#addArName').val().trim(),
                    sale_name: $('#addSaleName').val().trim(),
                    take_name: $('#addTakeName').val().trim(),
                    team_name: $('#addTeamName').val().trim(),
                    amphur: $('#addAmphur').val().trim(),
                    province: $('#addProvince').val().trim(),
                    zone: $('#addZone').val().trim(),
                    brand_code: $('#addBrandCode').val().trim(),
                    sku_code: $('#addSkuCode').val().trim(),
                    target_qty: $('#addTargetQty').val(),
                    forecast_qty: $('#addForecastQty').val(),
                    target_qty_2: $('#addTargetQty2').val(),
                    forecast_qty_2: $('#addForecastQty2').val(),
                    responsible_person: $('#addResponsiblePerson').val().trim()
                };

                if (!formData.month || !formData.year || !formData.ar_code || !formData.ar_name || !formData.sku_code || !formData.sale_name) {
                    Swal.fire('แจ้งเตือน', 'กรุณากรอกข้อมูลที่มีเครื่องหมาย * ให้ครบถ้วน', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'กำลังบันทึกข้อมูล',
                    allowOutsideClick: false,
                    didOpen: () => { Swal.showLoading(); }
                });

                $.ajax({
                    url: 'forecast_add_record_realtime.php',
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#addRecordModal').modal('hide');
                            Swal.fire({
                                title: 'สำเร็จ',
                                text: response.message,
                                icon: 'success'
                            }).then(() => {
                                // Update filters to show the new record
                                $('#filterMonth').val(formData.month);
                                $('#filterYear').val(formData.year);
                                // Optional: You could auto-filter by the newly added customer here
                                $('#btnFilter').click();
                            });
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
                const updates = [];
                table.rows().nodes().each(function(node) {
                    const $row = $(node);
                    const id = $row.find('.forecast-qty').attr('data-id') || $row.find('.forecast-qty-2').attr('data-id') || $row.find('.target-qty').attr('data-id') || $row.find('.remark').attr('data-id');
                    if (id) {
                        function cleanNum(val) {
                            if (val === undefined || val === null) return null;
                            const cleanStr = val.toString().replace(/,/g, '').replace(/%/g, '').trim();
                            if (cleanStr === '') return 0;
                            return parseFloat(cleanStr) || 0;
                        }

                        const $t1 = $row.find('.target-qty');
                        const $t2 = $row.find('.target-qty-2');
                        const $f1 = $row.find('.forecast-qty');
                        const $f2 = $row.find('.forecast-qty-2');
                        const $c1 = $row.find('.compare-1');
                        const $cp1 = $row.find('.compare-pct');
                        const $c2 = $row.find('.compare-2');
                        const $cp2 = $row.find('.compare-pct-2');
                        const $rem = $row.find('.remark');
                        const $resp = $row.find('.responsible-person');

                        updates.push({
                            id: id,
                            target_qty: $t1.length ? cleanNum($t1.val()) : null,
                            forecast_qty: $f1.length ? cleanNum($f1.val()) : null,
                            compare_1: $c1.length ? cleanNum($c1.val()) : null,
                            compare_pct: $cp1.length ? cleanNum($cp1.val()) : null,
                            remark: $rem.length ? $rem.val() : null,
                            responsible_person: $resp.length ? $resp.val() : null,
                            target_qty_2: $t2.length ? cleanNum($t2.val()) : null,
                            forecast_qty_2: $f2.length ? cleanNum($f2.val()) : null,
                            compare_2: $c2.length ? cleanNum($c2.val()) : null,
                            compare_pct_2: $cp2.length ? cleanNum($cp2.val()) : null
                        });
                    }
                });

                if (updates.length === 0) {
                    Swal.fire('แจ้งเตือน', 'ไม่มีข้อมูลให้บันทึกในหน้านี้', 'info');
                    return;
                }

                Swal.fire({
                    title: 'ยืนยันการบันทึกข้อมูล',
                    html: 'คุณต้องการบันทึกการแก้ไขข้อมูลเดือนนี้ใช่หรือไม่?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-save me-1"></i> บันทึกข้อมูล',
                    cancelButtonText: 'ยกเลิก',
                    confirmButtonColor: '#ffc107',
                    cancelButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: 'forecast_update_plan_realtime.php',
                            type: 'POST',
                            data: { updates: JSON.stringify(updates) },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('สำเร็จ', response.message || 'บันทึกข้อมูลเรียบร้อยแล้ว', 'success');
                                    table.draw(false);
                                    if (typeof updateCustomerInfo === 'function') {
                                        updateCustomerInfo();
                                    }
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

            // Save Single Row Inline from Table
            $('#planTable').on('click', '.btn-save-row-inline', function() {
                const $btn = $(this);
                const $row = $btn.closest('tr');
                const id = $btn.data('id');
                if (!id) return;

                function cleanNum(val) {
                    if (val === undefined || val === null) return null;
                    const cleanStr = val.toString().replace(/,/g, '').replace(/%/g, '').trim();
                    if (cleanStr === '') return 0;
                    return parseFloat(cleanStr) || 0;
                }

                const $t1 = $row.find('.target-qty');
                const $t2 = $row.find('.target-qty-2');
                const $f1 = $row.find('.forecast-qty');
                const $f2 = $row.find('.forecast-qty-2');
                const $c1 = $row.find('.compare-1');
                const $cp1 = $row.find('.compare-pct');
                const $c2 = $row.find('.compare-2');
                const $cp2 = $row.find('.compare-pct-2');
                const $rem = $row.find('.remark');
                const $resp = $row.find('.responsible-person');

                const updates = [{
                    id: id,
                    target_qty: $t1.length ? cleanNum($t1.val()) : null,
                    forecast_qty: $f1.length ? cleanNum($f1.val()) : null,
                    compare_1: $c1.length ? cleanNum($c1.val()) : null,
                    compare_pct: $cp1.length ? cleanNum($cp1.val()) : null,
                    remark: $rem.length ? $rem.val() : null,
                    responsible_person: $resp.length ? $resp.val() : null,
                    target_qty_2: $t2.length ? cleanNum($t2.val()) : null,
                    forecast_qty_2: $f2.length ? cleanNum($f2.val()) : null,
                    compare_2: $c2.length ? cleanNum($c2.val()) : null,
                    compare_pct_2: $cp2.length ? cleanNum($cp2.val()) : null
                }];

                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                $.ajax({
                    url: 'forecast_update_plan_realtime.php',
                    type: 'POST',
                    data: { updates: JSON.stringify(updates) },
                    dataType: 'json',
                    success: function(response) {
                        $btn.prop('disabled', false).html('<i class="fas fa-save"></i> บันทึก');
                        if (response.success) {
                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 2000,
                                timerProgressBar: true
                            });
                            Toast.fire({
                                icon: 'success',
                                title: response.message || 'บันทึกรายการเรียบร้อยแล้ว'
                            });
                            if (typeof updateCustomerInfo === 'function') {
                                updateCustomerInfo();
                            }
                        } else {
                            Swal.fire('ผิดพลาด', response.message, 'error');
                        }
                    },
                    error: function() {
                        $btn.prop('disabled', false).html('<i class="fas fa-save"></i> บันทึก');
                        Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'error');
                    }
                });
            });

            $('#btnUnlockMonth').click(function() {
                const month = $('#filterMonth').val();
                const year = $('#filterYear').val();
                const monthText = $('#filterMonth option:selected').text();
                
                if (!month || !year) {
                    Swal.fire('แจ้งเตือน', 'กรุณาเลือกเดือนและปีก่อนทำการปลดล็อค', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'ยืนยันการปลดล็อค',
                    html: `คุณต้องการปลดล็อคข้อมูลประจำเดือน <b>${monthText} ปี ${year}</b><br>เพื่อให้พนักงานสามารถแก้ไขข้อมูลได้ใช่หรือไม่?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-lock-open me-1"></i> ปลดล็อค',
                    cancelButtonText: 'ยกเลิก',
                    confirmButtonColor: '#198754'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'กำลังปลดล็อคข้อมูล...',
                            allowOutsideClick: false,
                            didOpen: () => { Swal.showLoading(); }
                        });

                        $.ajax({
                            url: 'forecast_unlock_plan_realtime.php',
                            type: 'POST',
                            data: {
                                action: 'unlock',
                                month: month,
                                year: year,
                                sale_name: $('#filterSale').val() || '',
                                ar_name: $('#filterCustomer').val() || ''
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('สำเร็จ', response.message, 'success');
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

            $('#btnLockMonth').click(function() {
                const month = $('#filterMonth').val();
                const year = $('#filterYear').val();
                const monthText = $('#filterMonth option:selected').text();
                
                if (!month || !year) {
                    Swal.fire('แจ้งเตือน', 'กรุณาเลือกเดือนและปีก่อนทำการล็อค', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'ยืนยันการล็อคข้อมูล',
                    html: `คุณต้องการล็อคข้อมูลประจำเดือน <b>${monthText} ปี ${year}</b><br>เพื่อไม่ให้แก้ไขได้ใช่หรือไม่?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: '<i class="fas fa-lock me-1"></i> ล็อคข้อมูล',
                    cancelButtonText: 'ยกเลิก',
                    confirmButtonColor: '#6c757d'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'กำลังล็อคข้อมูล...',
                            allowOutsideClick: false,
                            didOpen: () => { Swal.showLoading(); }
                        });

                        $.ajax({
                            url: 'forecast_unlock_plan_realtime.php',
                            type: 'POST',
                            data: {
                                action: 'lock',
                                month: month,
                                year: year,
                                sale_name: $('#filterSale').val() || '',
                                ar_name: $('#filterCustomer').val() || ''
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('สำเร็จ', response.message, 'success');
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

            // Top 20 SKU Offcanvas Side Popup
            let top20Table = null;

            // Initialize Select2 inside Offcanvas when shown
            $('#top20SkuOffcanvas').on('shown.bs.offcanvas', function () {
                if (!$('#top20FilterSale').hasClass("select2-hidden-accessible")) {
                    $('#top20FilterSale, #top20FilterTake').select2({
                        theme: 'bootstrap-5',
                        width: '100%',
                        placeholder: '-- ทั้งหมด --',
                        allowClear: true,
                        dropdownParent: $('#top20SkuOffcanvas')
                    });

                    // Trigger data reload on filter change inside offcanvas
                    $('#top20FilterSale, #top20FilterTake').on('change.top20', function() {
                        const month = $('#filterMonth').val();
                        const year = $('#filterYear').val();
                        const sale_name = $('#top20FilterSale').val() || '';
                        const take_name = $('#top20FilterTake').val() || '';
                        const ar_name = $('#filterCustomer').val() || '';
                        loadTop20Data(month, year, sale_name, take_name, ar_name);
                    });
                }
            });

            // Button 1: Top 20 SKU ยอดขาย
            $('#btnShowTop20').on('click', function(e) {
                e.preventDefault();
                const month = $('#filterMonth').val();
                const year = $('#filterYear').val();

                if (!month || !year) {
                    Swal.fire('แจ้งเตือน', 'กรุณาเลือกเดือนและปีเพื่อดู Top 20 SKU ยอดขาย', 'warning');
                    return;
                }

                const mainSale = $('#filterSale').val() || '';
                const mainTake = $('#filterTake').val() || '';
                const mainCust = $('#filterCustomer').val() || '';
                const mainProv = $('#filterProvince').val() || '';
                const mainAmph = $('#filterAmphur').val() || '';
                const mainZone = $('#filterZone').val() || '';

                const url = `forecast_top20_popup_realtime.php?month=${month}&year=${year}&sale_name=${encodeURIComponent(mainSale)}&take_name=${encodeURIComponent(mainTake)}&ar_name=${encodeURIComponent(mainCust)}&province=${encodeURIComponent(mainProv)}&amphur=${encodeURIComponent(mainAmph)}&zone=${encodeURIComponent(mainZone)}`;
                
                const width = Math.min(1450, window.screen.width * 0.95);
                const height = Math.min(900, window.screen.height * 0.9);
                const left = (window.screen.width - width) / 2;
                const top = (window.screen.height - height) / 2;

                const popupWin = window.open(url, 'Top20SkuReportWindow', `width=${width},height=${height},top=${top},left=${left},scrollbars=yes,resizable=yes,status=yes`);
                
                if (popupWin && !popupWin.closed) {
                    popupWin.focus();
                } else {
                    const monthName = $('#filterMonth option:selected').text();
                    $('#top20ShowMonthYear').text(`${monthName} ${year}`);
                    initTop20Select2();
                    $('#top20FilterSale').val(mainSale).trigger('change.select2');
                    $('#top20FilterTake').val(mainTake).trigger('change.select2');

                    const offcanvasEl = document.getElementById('top20SkuOffcanvas');
                    const bsOffcanvas = bootstrap.Offcanvas.getOrCreateInstance(offcanvasEl);
                    bsOffcanvas.show();

                    loadTop20Data(month, year, mainSale, mainTake, mainCust);
                }
            });

            // Button 2: Top 20 ร้านค้ายอดขายสูงสุด
            $('#btnShowTop20Stores').on('click', function(e) {
                e.preventDefault();
                const month = $('#filterMonth').val();
                const year = $('#filterYear').val();

                if (!month || !year) {
                    Swal.fire('แจ้งเตือน', 'กรุณาเลือกเดือนและปีเพื่อดู Top 20 ร้านค้ายอดขายสูงสุด', 'warning');
                    return;
                }

                const mainSale = $('#filterSale').val() || '';
                const mainTake = $('#filterTake').val() || '';
                const mainCust = $('#filterCustomer').val() || '';
                const mainProv = $('#filterProvince').val() || '';
                const mainAmph = $('#filterAmphur').val() || '';
                const mainZone = $('#filterZone').val() || '';

                const url = `forecast_top20_stores_popup_realtime.php?month=${month}&year=${year}&sale_name=${encodeURIComponent(mainSale)}&take_name=${encodeURIComponent(mainTake)}&ar_name=${encodeURIComponent(mainCust)}&province=${encodeURIComponent(mainProv)}&amphur=${encodeURIComponent(mainAmph)}&zone=${encodeURIComponent(mainZone)}`;
                
                const width = Math.min(1450, window.screen.width * 0.95);
                const height = Math.min(900, window.screen.height * 0.9);
                const left = (window.screen.width - width) / 2;
                const top = (window.screen.height - height) / 2;

                const popupWin = window.open(url, 'Top20StoresReportWindow', `width=${width},height=${height},top=${top},left=${left},scrollbars=yes,resizable=yes,status=yes`);
                
                if (popupWin && !popupWin.closed) {
                    popupWin.focus();
                } else {
                    Swal.fire('แจ้งเตือน', 'กรุณาลองอนุญาตให้เปิด Pop-up ในเบราว์เซอร์ของคุณเพื่อดูรายงาน', 'warning');
                }
            });

            function loadTop20Data(month, year, sale_name, take_name, ar_name) {
                const prov = $('#filterProvince').val() || '';
                const amph = $('#filterAmphur').val() || '';
                const zone = $('#filterZone').val() || '';

                Swal.fire({
                    title: 'กำลังโหลดข้อมูล Top 20 SKU',
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
                        province: prov,
                        amphur: amph,
                        zone: zone
                    },
                    dataType: 'json',
                    success: function(response) {
                        Swal.close();
                        if (response.success) {
                            const s = response.summary || {};
                            $('#top20TotalActual').text((s.total_actual || 0).toLocaleString('en-US', {maximumFractionDigits: 0}));
                            $('#top20TotalForecast').text((s.total_forecast || 0).toLocaleString('en-US', {maximumFractionDigits: 0}));
                            $('#top20TotalTarget').text((s.total_target || 0).toLocaleString('en-US', {maximumFractionDigits: 0}));
                            $('#top20StoreCount').text((s.store_count || 0).toLocaleString('en-US'));
                            $('#top20SkuCount').text((s.sku_count || 0).toLocaleString('en-US'));

                            if (top20Table) {
                                top20Table.clear();
                                top20Table.rows.add(response.data);
                                top20Table.draw();
                            } else {
                                top20Table = $('#top20SkuTable').DataTable({
                                    data: response.data,
                                    destroy: true,
                                    pageLength: 20,
                                    order: [[9, 'desc'], [8, 'desc']],
                                    columns: [
                                        { data: 'rank', className: 'text-center fw-bold' },
                                        { data: 'ar_code', className: 'fw-bold' },
                                        { data: 'ar_name' },
                                        { data: 'sale_name' },
                                        { data: 'take_name', render: function(val) { return val || '-'; } },
                                        { data: 'brand_code', className: 'text-center' },
                                        { data: 'sku_code', className: 'fw-bold text-primary' },
                                        { 
                                            data: 'target_qty_month', 
                                            className: 'text-end',
                                            render: $.fn.dataTable.render.number(',', '.', 0)
                                        },
                                        { 
                                            data: 'forecast_qty_month_1', 
                                            className: 'text-end fw-bold text-primary',
                                            render: $.fn.dataTable.render.number(',', '.', 0)
                                        },
                                        { 
                                            data: 'sales_actual_month', 
                                            className: 'text-end fw-bold text-success',
                                            render: $.fn.dataTable.render.number(',', '.', 0)
                                        }
                                    ],
                                    language: {
                                        search: "ค้นหาในตาราง:",
                                        lengthMenu: "แสดง _MENU_ รายการ",
                                        info: "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
                                        paginate: {
                                            first: "แรก",
                                            last: "สุดท้าย",
                                            next: "ถัดไป",
                                            previous: "ย้อนกลับ"
                                        }
                                    }
                                });
                            }
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

    <!-- Top 20 SKU Offcanvas (Side Popup) -->
    <div class="offcanvas offcanvas-end shadow-lg" tabindex="-1" id="top20SkuOffcanvas" aria-labelledby="top20SkuOffcanvasLabel" style="width: 88%; max-width: 1150px;">
        <div class="offcanvas-header bg-gradient bg-primary text-white border-bottom py-3">
            <div class="d-flex align-items-center">
                <div class="bg-white text-primary rounded-circle p-2 me-3 shadow-sm d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                    <i class="fas fa-trophy fa-lg text-warning"></i>
                </div>
                <div>
                    <h5 class="offcanvas-title fw-bold mb-0" id="top20SkuOffcanvasLabel">
                        สรุปยี่ห้อ - ประเภท (Top 20 SKU ยอดขายแต่ละร้าน)
                    </h5>
                    <div class="small opacity-75" id="top20FilterInfo">
                        ประจำเดือน: <span id="top20ShowMonthYear" class="fw-bold"></span>
                    </div>
                </div>
            </div>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        
        <div class="offcanvas-body bg-light p-3 p-md-4">
            <!-- Active Filter Badge Strip -->
            <div class="card mb-3 border-0 shadow-sm">
                <div class="card-body py-2 px-3 bg-white rounded">
                    <div class="d-flex flex-wrap gap-2 align-items-center small">
                        <span class="fw-bold text-secondary me-1"><i class="fas fa-filter me-1"></i>เงื่อนไขที่กรอง:</span>
                        <span class="badge bg-primary px-2 py-1"><i class="fas fa-calendar-alt me-1"></i>เดือน/ปี: <span id="badgeTop20Period">-</span></span>
                        <span class="badge bg-success px-2 py-1"><i class="fas fa-user-tie me-1"></i>พนักงานขาย: <span id="badgeTop20Sale">ทั้งหมด</span></span>
                        <span class="badge bg-info text-dark px-2 py-1"><i class="fas fa-user-tag me-1"></i>พนักงานเทค: <span id="badgeTop20Take">ทั้งหมด</span></span>
                        <span class="badge bg-warning text-dark px-2 py-1"><i class="fas fa-store me-1"></i>ร้านค้า: <span id="badgeTop20Cust">ทั้งหมด</span></span>
                    </div>
                </div>
            </div>

            <!-- Summary Stat Cards -->
            <div class="row g-3 mb-3 text-center">
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm h-100 bg-white">
                        <div class="card-body py-2 px-3">
                            <div class="text-muted small fw-bold">ยอดขายจริงรวม</div>
                            <div class="fs-4 fw-bold text-success" id="top20TotalActual">0</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm h-100 bg-white">
                        <div class="card-body py-2 px-3">
                            <div class="text-muted small fw-bold">ยอดแพลนรวม</div>
                            <div class="fs-4 fw-bold text-primary" id="top20TotalForecast">0</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm h-100 bg-white">
                        <div class="card-body py-2 px-3">
                            <div class="text-muted small fw-bold">เป้ารวม</div>
                            <div class="fs-4 fw-bold text-warning" id="top20TotalTarget">0</div>
                        </div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="card border-0 shadow-sm h-100 bg-white">
                        <div class="card-body py-2 px-3">
                            <div class="text-muted small fw-bold">จำนวนร้านค้า / SKU</div>
                            <div class="fs-4 fw-bold text-dark"><span id="top20StoreCount">0</span> <small class="fs-6 text-muted">ร้าน</small> / <span id="top20SkuCount">0</span> <small class="fs-6 text-muted">SKU</small></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- DataTables Container -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-3 bg-white rounded">
                    <div class="table-responsive">
                        <table id="top20SkuTable" class="table table-bordered table-striped table-hover align-middle w-100 small">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th style="width: 5%;">อันดับ</th>
                                    <th style="width: 10%;">รหัสร้านค้า</th>
                                    <th style="width: 20%;">ชื่อร้านค้า</th>
                                    <th style="width: 12%;">พนักงานขาย</th>
                                    <th style="width: 10%;">พนักงานเทค</th>
                                    <th style="width: 8%;">ยี่ห้อ</th>
                                    <th style="width: 15%;">รหัสสินค้า (SKU)</th>
                                    <th style="width: 7%;" class="text-end">เป้าหมาย</th>
                                    <th style="width: 7%;" class="text-end">แพลนยาง</th>
                                    <th style="width: 8%;" class="text-end">ยอดขายจริง</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Loaded dynamically via DataTables -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
