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

$stmt_provinces = $conn->prepare("SELECT DISTINCT province FROM ims_tires_plan_forecast $where_cust AND province IS NOT NULL AND province != '' ORDER BY province");
$stmt_provinces->execute($params_cust);
$provinces_list = $stmt_provinces->fetchAll(PDO::FETCH_COLUMN);

$stmt_amphurs = $conn->prepare("SELECT DISTINCT amphur FROM ims_tires_plan_forecast $where_cust AND amphur IS NOT NULL AND amphur != '' ORDER BY amphur");
$stmt_amphurs->execute($params_cust);
$amphurs_list = $stmt_amphurs->fetchAll(PDO::FETCH_COLUMN);

$stmt_zones = $conn->prepare("SELECT DISTINCT zone FROM ims_tires_plan_forecast $where_cust AND zone IS NOT NULL AND zone != '' ORDER BY zone");
$stmt_zones->execute($params_cust);
$zones_list = $stmt_zones->fetchAll(PDO::FETCH_COLUMN);
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
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center flex-wrap py-1">
                    <h4 class="mb-2 mb-lg-0"><i class="fas fa-edit me-2"></i> Tires Plan วางแผนการขาย (Interactive)</h4>
                    <div class="d-flex flex-wrap gap-2 justify-content-md-end">
                        <a href="forecast_import_plan" class="btn btn-primary btn-sm">
                            <i class="fas fa-file-import me-1"></i> นำเข้าข้อมูล
                        </a>
                        <a href="forecast_report_list_new" target="_blank" class="btn btn-light text-success btn-sm fw-bold">
                            <i class="fas fa-history me-1"></i> ประวัติการนำเข้า
                        </a>
                        <a href="import_plan_howto.html" target="_blank" class="btn btn-outline-dark btn-sm">
                            <i class="fas fa-book me-1"></i> คู่มือการนำเข้า
                        </a>
                        <a href="plan_forecast_tires_manual.html" target="_blank" class="btn btn-outline-danger btn-sm fw-bold">
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
                                <li><a class="dropdown-item text-muted small" href="report_plan_method.html" target="_blank">
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
                    <div class="col-md-4">
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
                <div class="row g-3 mb-4">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">จังหวัด:</label>
                        <select id="filterProvince" class="form-select">
                            <option value="">-- ทั้งหมด --</option>
                            <?php foreach ($provinces_list as $prov): ?>
                                <option value="<?= htmlspecialchars($prov) ?>"><?= htmlspecialchars($prov) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">อำเภอ:</label>
                        <select id="filterAmphur" class="form-select">
                            <option value="">-- ทั้งหมด --</option>
                            <?php foreach ($amphurs_list as $amphur): ?>
                                <option value="<?= htmlspecialchars($amphur) ?>"><?= htmlspecialchars($amphur) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold">เขต:</label>
                        <select id="filterZone" class="form-select">
                            <option value="">-- ทั้งหมด --</option>
                            <?php foreach ($zones_list as $zone): ?>
                                <option value="<?= htmlspecialchars($zone) ?>"><?= htmlspecialchars($zone) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 align-self-end">
                        <div class="btn-group w-100">
                            <button type="button" id="btnFilter" class="btn btn-primary">
                                <i class="fas fa-search me-1"></i> กรองข้อมูล
                            </button>
                            <button type="button" id="btnShowSummary" class="btn btn-warning fw-bold">
                                <i class="fas fa-list-alt me-1"></i> สรุปยี่ห้อ - ประเภท
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
                                        <div class="small text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">เป้าหมายรวม (เป้า 1)</div>
                                        <div class="h4 mb-0 fw-bold text-dark" id="infoTotalTarget">0</div>
                                    </div>
                                    <div class="col-6 border-start">
                                        <div class="small text-muted text-uppercase fw-bold" style="font-size: 0.75rem;">เป้าหมายรวม (เป้า 2)</div>
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
                                <th>อำเภอ</th>
                                <th>จังหวัด</th>
                                <th>เขต</th>
                                <th>เฉลี่ย 12ด.</th>
                                <th>MAX</th>
                                <th>MIN</th>
                                <th>ยอดขาย M-1</th>
                                <th>ยอดขาย M-2</th>
                                <th>ยอดขาย M-3</th>
                                <th>เฉลี่ย 3ด.</th>
                                <th>คาดการณ์</th>
                                <th>ยอดขายจริง</th>
                                <th>เทียบคาดการณ์</th>
                                <th>เทียบยอดขาย%</th>
                                <th>หมายเหตุ</th>
                                <th>คาดการณ์2</th>
                                <th>เทียบคาดการณ์2</th>
                                <th>เทียบยอดขาย%2</th>
                                <th>พนักงานขาย</th>
                                <th>คนเทค</th>
                                <th>ทีม</th>
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
                                <label class="form-label fw-bold">เป้าหมายรวมร้าน (เป้า 1)</label>
                                <input type="number" class="form-control" id="addTargetQty" value="0">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-success">คาดการณ์ขายสินค้านี้</label>
                                <input type="number" class="form-control" id="addForecastQty" value="0">
                            </div>
                        </div>
                        <div class="row g-3 mb-3 bg-light p-2 rounded">
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
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">เป้า2:</label>
                                <input type="number" class="form-control" id="editTarget2">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">วางแผนการขาย2:</label>
                                <input type="number" class="form-control" id="editForecast2">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">เทียบคาดการณ์2:</label>
                                <input type="number" class="form-control" id="editCompare2">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">เทียบยอดขาย%2:</label>
                                <input type="number" class="form-control" id="editComparePct2">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">หมายเหตุ:</label>
                            <textarea class="form-control" id="editRemark" rows="2"></textarea>
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
                $('#filterSale, #filterCustomer, #filterProvince, #filterAmphur, #filterZone').select2({
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
                $('#filterSale, #filterCustomer, #filterProvince, #filterAmphur, #filterZone').select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: '-- ทั้งหมด --',
                    allowClear: true
                });
            }

            const table = $('#planTable').DataTable({
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
                    { "data": "amphur" },
                    { "data": "province" },
                    { "data": "zone" },
                    { "data": "avg_per_month_year", "className": "text-end" },
                    { "data": "max_qty_year", "className": "text-end" },
                    { "data": "min_qty_year", "className": "text-end" },
                    { "data": "sales_month_1", "className": "text-end" },
                    { "data": "sales_month_2", "className": "text-end" },
                    { "data": "sales_month_3", "className": "text-end" },
                    { "data": "avg_3_month", "className": "text-end" },
                    { "data": "forecast_qty_month_1", "className": "text-end" },
                    { "data": "sales_actual_month", "className": "text-end" },
                    { "data": "compare_forecast_month_1", "className": "text-end" },
                    { "data": "compare_sales_percent", "className": "text-end" },
                    { "data": "remark" },
                    { "data": "forecast_qty_month_2", "className": "text-end" },
                    { "data": "compare_forecast_month_2", "className": "text-end" },
                    { "data": "compare_sales_percent_2", "className": "text-end" },
                    { "data": "sale_name" },
                    { "data": "take_name" },
                    { "data": "team_name" },
                    { "data": "import_date" },
                    { "data": "actions", "orderable": false }
                ],
                "order": [[25, "desc"]],
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
                    $('.forecast-qty, .forecast-qty-2').on('input', function() {
                        calculateRow($(this).closest('tr'));
                    });
                }
            });

            function calculateRow($row) {
                const avg3m = parseFloat($row.find('.avg-3m').data('val')) || 0;
                
                // Set 1
                const forecast = parseFloat($row.find('.forecast-qty').val().replace(/,/g, '')) || 0;
                const diff = forecast - avg3m;
                const pct = (avg3m !== 0) ? (diff / avg3m) * 100 : 0;
                $row.find('.compare-1').val(diff.toLocaleString('en-US', {minimumFractionDigits: 0}));
                $row.find('.compare-pct').val(pct.toFixed(0) + '%');

                // Set 2
                const forecast2 = parseFloat($row.find('.forecast-qty-2').val().replace(/,/g, '')) || 0;
                const diff2 = forecast2 - avg3m;
                const pct2 = (avg3m !== 0) ? (diff2 / avg3m) * 100 : 0;
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
                    url: 'forecast_fetch_customer_info.php',
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
                    url: 'forecast_count_delete_records.php',
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
                        url: 'forecast_fetch_delete_preview.php',
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

            // Export Excel button click
            $('#btnExportExcel').click(function() {
                const month = $('#filterMonth').val();
                const year = $('#filterYear').val();
                if (!month || !year) {
                    Swal.fire('แจ้งเตือน', 'กรุณาเลือกเดือนและปีเพื่อทำรายการ Export', 'warning');
                    return;
                }

                const sale_name = $('#filterSale').val();
                const ar_name = $('#filterCustomer').val();
                const province = $('#filterProvince').val();
                const amphur = $('#filterAmphur').val();
                const zone = $('#filterZone').val();

                // Open export file in new window with parameters
                const params = $.param({
                    month: month,
                    year: year,
                    sale_name: sale_name,
                    ar_name: ar_name,
                    province: province,
                    amphur: amphur,
                    zone: zone
                });
                
                window.open('forecast_plan_export_excel.php?' + params, '_blank');
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
                            url: 'forecast_delete_by_conditions.php',
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
                    url: 'forecast_fetch_brand_summary.php',
                    type: 'POST',
                    data: {
                        month: month,
                        year: year,
                        sale_name: sale_name,
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
                const year = parseInt($('#filterYear').val());
                
                if (!month || !year) return;

                const thai_months = [
                    '', 'ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.',
                    'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'
                ];

                // Previous Year (BE)
                const prevYearBE = (year - 1) + 543;
                $(table.column(7).header()).text('เฉลี่ย 12ด. (' + prevYearBE + ')');

                // M-1, M-2, M-3
                let m1 = month - 1; if (m1 <= 0) m1 += 12;
                let m2 = month - 2; if (m2 <= 0) m2 += 12;
                let m3 = month - 3; if (m3 <= 0) m3 += 12;

                $(table.column(10).header()).text('ยอดขายเดือน ' + thai_months[m1]);
                $(table.column(11).header()).text('ยอดขายเดือน ' + thai_months[m2]);
                $(table.column(12).header()).text('ยอดขายเดือน ' + thai_months[m3]);
                $(table.column(13).header()).text('เฉลี่ย 3ด.');
                $(table.column(15).header()).text('ยอดขายจริงเดือน ' + thai_months[month]);
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
                
                $('#editRowModal').modal('show');
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
                            url: 'forecast_delete_record.php',
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
                    target_qty_2: cleanVal('#editTarget2'),
                    forecast_qty_2: cleanVal('#editForecast2'),
                    compare_2: cleanVal('#editCompare2'),
                    compare_pct_2: cleanVal('#editComparePct2')
                }];

                $.ajax({
                    url: 'forecast_update_plan_new.php',
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
                    url: 'forecast_copy_month_process.php',
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
                        url: 'forecast_fetch_customer_info.php',
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
                $('#addArCode').val(data.id);
                $('#addArName').val(data.ar_name);
                if (data.sale_name) $('#addSaleName').val(data.sale_name);
                $('#addTakeName').val(data.take_name || '');
                $('#addTeamName').val(data.team_name || '');
                $('#addAmphur').val(data.amphur || '');
                $('#addProvince').val(data.province || '');
                $('#addZone').val(data.zone || '');
            });

            $('#addSkuCodeSearch').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#addRecordModal'),
                ajax: {
                    url: 'forecast_search_sku.php',
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
                    forecast_qty_2: $('#addForecastQty2').val()
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
                    url: 'forecast_add_record.php',
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
                            if (!val) return 0;
                            return parseFloat(val.toString().replace(/,/g, '').replace(/%/g, '')) || 0;
                        }

                        updates.push({
                            id: id,
                            forecast_qty: cleanNum($row.find('.forecast-qty').val()),
                            compare_1: cleanNum($row.find('.compare-1').val()),
                            compare_pct: cleanNum($row.find('.compare-pct').val()),
                            remark: $row.find('.remark').val(),
                            forecast_qty_2: cleanNum($row.find('.forecast-qty-2').val()),
                            compare_2: cleanNum($row.find('.compare-2').val()),
                            compare_pct_2: cleanNum($row.find('.compare-pct-2').val())
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
