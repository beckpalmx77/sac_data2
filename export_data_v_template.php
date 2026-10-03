<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
    exit;
}

include('config/connect_db.php');

$account_type = $_SESSION['account_type'] ?? '';
$first_name = $_SESSION['first_name'] ?? '';

// Thai Month Names
$thai_months = [
    1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม',
    4 => 'เมษายน', 5 => 'พฤษภาคม', 6 => 'มิถุนายน',
    7 => 'กรกฎาคม', 8 => 'สิงหาคม', 9 => 'กันยายน',
    10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
];

$current_month = intval(date('n'));
$current_year = intval(date('Y'));

// Default 6 months date range
$default_date_to = date('d-m-Y');
$default_date_start = date('d-m-Y', strtotime('-6 months'));

// Get distinct SALE_NAME list for filter based on BRAND, SKU_CAT, and DI_REF conditions
$sales_list = [];
try {
    $stmt_sales = $conn->query("
        SELECT DISTINCT TRIM(s.SALE_NAME) AS SALE_NAME 
        FROM ims_data_sale_sac_all s
        WHERE s.SALE_NAME IS NOT NULL AND TRIM(s.SALE_NAME) != '' AND TRIM(s.SALE_NAME) != '-'
        AND s.SALE_NAME NOT LIKE '%BTC%' 
        AND s.SALE_NAME NOT LIKE '%RQ%'
        AND s.SALE_NAME NOT LIKE '%ส่วนกลาง%' 
        AND s.SALE_NAME NOT LIKE '%READY%QUICK%'
        AND s.BRAND IN ('VB', 'ATLAS', 'AT', 'BS/FS/DT', 'BS/FS', 'BS', '')
        AND (s.SKU_CAT IN ('TBR/LTR (ยางใหญ่)', 'LSR', 'TBB/LTB (ยางผ้าใบ)', 'TBR') OR TRIM(s.SKU_CAT) IN ('TBR/LTR (ยางใหญ่)', 'LSR', 'TBB/LTB (ยางผ้าใบ)', 'TBR'))
        AND s.DI_REF NOT LIKE 'DS%'
        ORDER BY s.SALE_NAME ASC
    ");
    $sales_list = $stmt_sales->fetchAll(PDO::FETCH_COLUMN);
} catch (Exception $e) {}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <style>
        .select2-container {
            width: 100% !important;
        }
        .select2-selection--single {
            height: 38px !important;
            padding: 0.375rem 0.75rem !important;
            font-size: 0.95rem !important;
            line-height: 1.5 !important;
        }
        .select2-selection__rendered {
            line-height: 26px !important;
        }
        .select2-selection__arrow {
            height: 36px !important;
        }
        .select2-container--default .select2-selection--multiple {
            border: 1px solid #d1d3e2;
            border-radius: 0.35rem;
            min-height: 38px;
            padding: 2px 6px;
        }
        .select2-container--default.select2-container--focus .select2-selection--multiple {
            border-color: #bac8f3;
            outline: 0;
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #e8f5e9;
            border: 1px solid #a5d6a7;
            color: #1b5e20;
            font-weight: 500;
            padding: 2px 8px;
            margin-top: 4px;
            border-radius: 4px;
            font-size: 0.85rem;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #c62828;
            margin-right: 5px;
            font-weight: bold;
        }
        .dual-listbox-box {
            border-radius: 6px;
            border: 1px solid #ced4da;
            padding: 4px;
            overflow-y: auto;
        }
        .dual-listbox-box option {
            padding: 6px 10px;
            border-radius: 4px;
            margin-bottom: 2px;
            cursor: pointer;
            transition: background 0.15s ease;
        }
        .dual-listbox-box option:hover {
            background-color: #e3f2fd;
        }
        #sales_available option:checked {
            background-color: #0d6efd !important;
            color: #fff !important;
        }
        #sales_selected option:checked {
            background-color: #198754 !important;
            color: #fff !important;
        }
        .btn-dual-action {
            min-width: 120px;
            font-size: 0.85rem;
        }
        .card-custom-header {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: white;
            border-bottom: 3px solid #198754;
        }
        .template-note-box {
            background-color: #f0fdf4;
            border-left: 4px solid #16a34a;
            border-radius: 6px;
            padding: 12px 16px;
            margin-bottom: 20px;
        }
        .note-condition-banner {
            background: linear-gradient(135deg, #eef2ff 0%, #f0fdf4 100%);
            border: 1px solid #c7d2fe;
            border-left: 4px solid #4f46e5;
            border-radius: 6px;
            padding: 12px 16px;
            margin-bottom: 16px;
        }
        .quick-pill {
            cursor: pointer;
            transition: all 0.15s ease-in-out;
            user-select: none;
            font-size: 0.78rem;
            padding: 3px 8px;
            margin: 2px 2px 2px 0;
            border-radius: 4px;
            display: inline-block;
        }
        .quick-pill:hover {
            transform: translateY(-1px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.15);
            background-color: #198754 !important;
            color: white !important;
            border-color: #198754 !important;
        }
        .quick-pill-brand:hover {
            background-color: #0d6efd !important;
            color: white !important;
            border-color: #0d6efd !important;
        }
        .format-card {
            cursor: pointer;
            transition: all 0.2s ease;
            border-radius: 8px;
        }
        .format-card:hover {
            border-color: #198754 !important;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            transform: translateY(-2px);
        }
        .spinner-overlay {
            display: none;
            position: fixed;
            z-index: 9999;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.4);
        }
        .spinner-box {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 25px 35px;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.2);
            text-align: center;
        }
        .date-input-custom {
            cursor: pointer;
            background-color: #ffffff !important;
        }
        .badge-date-info {
            font-size: 0.88rem;
            font-weight: 500;
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
                
                <!-- Page Title -->
                <div class="d-sm-flex align-items-center justify-content-between mb-3">
                    <h1 class="h4 mb-0 text-gray-800">
                        <i class="fas fa-file-excel text-success me-2"></i> Export ข้อมูลตามเทมเพลต template_v_tires.xlsx (สำหรับนำเข้า Forecast)
                    </h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a></li>
                        <li class="breadcrumb-item">นำข้อมูลออกจากระบบ</li>
                        <li class="breadcrumb-item active" aria-current="page">Export Volume Tires</li>
                    </ol>
                </div>

                <!-- Template Note & Info -->
                <div class="template-note-box mb-3">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                        <div>
                            <strong class="text-success"><i class="fas fa-info-circle me-1"></i> รายละเอียดการดึงข้อมูล (Template Volume Tires - template_v_tires.xlsx):</strong>
                            ดึงข้อมูลยอดขายจากตาราง <code>ims_data_sale_sac_all</code> ลงตามโครงสร้างเทมเพลต <code>import_process/template/template_v_tires.xlsx</code> (18 คอลัมน์ A ถึง R)
                            พร้อม <strong>รหัสสินค้า (SKU_CODE)</strong>, <strong>รายละเอียด (SKU_NAME)</strong>, <strong>ยี่ห้อ (BRAND)</strong>, ยอดขายสูงสุด/ต่ำสุด และสถิติ 3 เดือนล่าสุด เพื่อใช้เป็นไฟล์นำเข้า (Import) แพลน Forecast ได้ทันที
                        </div>
                        <div>
                            <a href="import_process/template/template_v_tires.xlsx" class="btn btn-sm btn-outline-success font-weight-bold" download>
                                <i class="fas fa-download me-1"></i> โหลดเทมเพลตต้นแบบ (template_v_tires.xlsx)
                            </a>
                        </div>
                    </div>
                    <!-- Note เงื่อนไขการดึงข้อมูล -->
                    <div class="alert alert-info py-2 px-3 mb-0" style="background-color: #f0f7fd; border: 1px solid #b8daff; border-left: 4px solid #007bff; border-radius: 6px;">
                        <div class="row align-items-center">
                            <div class="col-md-auto font-weight-bold text-primary mb-1 mb-md-0">
                                <i class="fas fa-tag me-1"></i> <u>หมายเหตุเงื่อนไขการดึงข้อมูล</u> :
                            </div>
                            <div class="col-md">
                                <span class="badge badge-light border text-dark py-1 px-2 me-2 my-1" style="font-size: 0.9rem;">
                                    <i class="fas fa-cubes text-success me-1"></i> <strong>ประเภท (SKU_CAT):</strong> 'TBR/LTR (ยางใหญ่)', 'LSR', 'TBB/LTB (ยางผ้าใบ)', 'TBR'
                                </span>
                                <span class="badge badge-light border text-dark py-1 px-2 me-2 my-1" style="font-size: 0.9rem;">
                                    <i class="fas fa-tags text-primary me-1"></i> <strong>ยี่ห้อ (BRAND):</strong> 'VB', 'ATLAS', 'AT', 'BS/FS/DT', 'BS/FS', 'BS', ''
                                </span>
                                <span class="badge badge-light border text-danger py-1 px-2 my-1" style="font-size: 0.9rem;">
                                    <i class="fas fa-ban text-danger me-1"></i> <strong>เอกสาร:</strong> DI_REF NOT LIKE 'DS%'
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Form Card -->
                <div class="card mb-4 shadow-sm border-0">
                    <div class="card-header card-custom-header py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-filter me-2"></i> กำหนดช่วงวันที่และเงื่อนไขการดึงข้อมูล</h6>
                    </div>
                    <div class="card-body">
                        <form id="v_template_form" method="POST" action="export_process/export_data_v_template_process.php" target="_blank">
                            <input type="hidden" name="file_type" id="file_type" value="xls">

                            <!-- Row 1: Date Range & Target Month -->
                            <div class="row mb-2">
                                <!-- จากวันที่ -->
                                <div class="col-md-3 mb-2">
                                    <label for="doc_date_start" class="form-label font-weight-bold">
                                        <i class="fas fa-calendar-alt text-success me-1"></i> จากวันที่เริ่มต้น <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="text" class="form-control date-input-custom" id="doc_date_start" name="doc_date_start"
                                               value="<?php echo $default_date_start; ?>" placeholder="วว-ดด-ปปปป" autocomplete="off" required>
                                        <div class="input-group-append" style="cursor: pointer;" onclick="$('#doc_date_start').focus();">
                                            <span class="input-group-text"><i class="fa fa-calendar text-success"></i></span>
                                        </div>
                                    </div>
                                    <small class="text-muted">คลิกเลือกปฏิทิน หรือพิมพ์วันที่</small>
                                </div>

                                <!-- ถึงวันที่ -->
                                <div class="col-md-3 mb-2">
                                    <label for="doc_date_to" class="form-label font-weight-bold">
                                        <i class="fas fa-calendar-check text-primary me-1"></i> ถึงวันที่สิ้นสุด <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="text" class="form-control date-input-custom" id="doc_date_to" name="doc_date_to"
                                               value="<?php echo $default_date_to; ?>" placeholder="วว-ดด-ปปปป" autocomplete="off" required>
                                        <div class="input-group-append" style="cursor: pointer;" onclick="$('#doc_date_to').focus();">
                                            <span class="input-group-text"><i class="fa fa-calendar text-primary"></i></span>
                                        </div>
                                    </div>
                                    <small class="text-muted">คลิกเลือกปฏิทิน หรือพิมพ์วันที่</small>
                                </div>

                                <!-- เดือนเป้าหมายวางแผน -->
                                <div class="col-md-3 mb-2">
                                    <label for="target_month" class="form-label font-weight-bold">เดือนเป้าหมาย (แพลน) <span class="text-danger">*</span></label>
                                    <select id="target_month" name="target_month" class="form-control">
                                        <?php foreach ($thai_months as $m_num => $m_name): ?>
                                            <option value="<?php echo $m_num; ?>" <?php echo ($m_num === $current_month) ? 'selected' : ''; ?>>
                                                <?php echo $m_num . ' - ' . $m_name; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <small class="text-muted">เดือนที่ต้องการทำแพลนขาย</small>
                                </div>

                                <!-- ปีเป้าหมาย -->
                                <div class="col-md-3 mb-2">
                                    <label for="target_year" class="form-label font-weight-bold">ปีเป้าหมาย <span class="text-danger">*</span></label>
                                    <select id="target_year" name="target_year" class="form-control">
                                        <?php for ($y = $current_year - 1; $y <= $current_year + 1; $y++): ?>
                                            <option value="<?php echo $y; ?>" <?php echo ($y === $current_year) ? 'selected' : ''; ?>>
                                                <?php echo $y; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                    <small class="text-muted">ปี ค.ศ. เป้าหมาย</small>
                                </div>
                            </div>

                            <!-- Live Date Range Feedback Badge -->
                            <div class="row mb-3">
                                <div class="col-12">
                                    <span class="badge badge-success badge-date-info py-2 px-3 shadow-sm" id="dateRangeBadge">
                                        <i class="fas fa-clock me-1"></i> <span id="dateRangeText">กำลังคำนวณช่วงเวลา...</span>
                                    </span>
                                </div>
                            </div>

                            <!-- Quick Date Selection Buttons -->
                            <div class="row mb-3">
                                <div class="col-12">
                                    <span class="text-muted me-2 font-weight-bold small"><i class="fas fa-bolt text-warning me-1"></i> ปุ่มลัดเลือกช่วงเวลา:</span>
                                    <button type="button" class="btn btn-sm btn-outline-success mr-1 me-1 mb-1 font-weight-bold" onclick="setDateRangeMonths(6)">
                                        <i class="fas fa-history mr-1 me-1"></i> ย้อนหลัง 6 เดือน (ค่าเริ่มต้น)
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-primary mr-1 me-1 mb-1 font-weight-bold" onclick="setDateRangeMonths(3)">
                                        <i class="fas fa-history mr-1 me-1"></i> ย้อนหลัง 3 เดือน
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-info mr-1 me-1 mb-1" onclick="setDateRangeMonths(1)">
                                        <i class="fas fa-history mr-1 me-1"></i> ย้อนหลัง 1 เดือน
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary mr-1 me-1 mb-1" onclick="setDateRangeMonths(12)">
                                        <i class="fas fa-history mr-1 me-1"></i> ย้อนหลัง 12 เดือน (1 ปี)
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-dark mr-1 me-1 mb-1" onclick="setDateYTD()">
                                        <i class="fas fa-calendar-check mr-1 me-1"></i> ตั้งแต่ต้นปีนี้ (YTD)
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-warning text-dark mr-1 me-1 mb-1" onclick="setDateLastMonth()">
                                        <i class="fas fa-calendar-minus mr-1 me-1"></i> เดือนก่อนหน้า (เต็มเดือน)
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger mr-1 me-1 mb-1" onclick="setDateThisMonth()">
                                        <i class="fas fa-calendar-day mr-1 me-1"></i> เดือนปัจจุบัน (ต้นเดือน - วันนี้)
                                    </button>
                                </div>
                            </div>







                            <!-- Row 2: Salesperson Dual Listbox Selection -->
                            <div class="row mb-4">
                                <div class="col-12">
                                    <label class="form-label font-weight-bold text-dark mb-2">
                                        <i class="fas fa-user-tie text-primary me-1"></i> เลือกพนักงานขาย (SALE_NAME) <span class="text-danger">*</span>
                                    </label>
                                    <div class="card border shadow-sm p-3" style="background-color: #f8fafc; border-radius: 8px;">
                                        <div class="row align-items-center">
                                            
                                            <!-- ฝั่งซ้าย: รายชื่อที่เลือกได้ -->
                                            <div class="col-md-5">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="font-weight-bold text-secondary small">
                                                        <i class="fas fa-list-ul me-1"></i> รายชื่อพนักงานขายที่เลือกได้ (<span id="availableCount"><?php echo count($sales_list); ?></span> ท่าน)
                                                    </span>
                                                    <a href="javascript:void(0);" class="small text-decoration-none text-primary font-weight-bold" onclick="highlightAll('sales_available')">
                                                        <i class="fas fa-check-double me-1"></i>เลือกทั้งหมด
                                                    </a>
                                                </div>
                                                <div class="input-group input-group-sm mb-2">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                                    </div>
                                                    <input type="text" id="search_available" class="form-control" placeholder="พิมพ์ค้นหาชื่อเซลล์..." onkeyup="filterListbox('sales_available', this.value)">
                                                </div>
                                                <select multiple id="sales_available" class="form-control dual-listbox-box shadow-sm" style="height: 220px; font-size: 0.92rem;">
                                                    <?php foreach ($sales_list as $s_name): ?>
                                                        <option value="<?php echo htmlspecialchars($s_name); ?>"><?php echo htmlspecialchars($s_name); ?></option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <small class="text-muted d-block mt-1">
                                                    <i class="fas fa-mouse-pointer text-info me-1"></i> กดเลือกหรือกด <kbd>Ctrl</kbd>/<kbd>Shift</kbd> เพื่อเลือกหลายคน (ดับเบิ้ลคลิกเพื่อเพิ่มทันที)
                                                </small>
                                            </div>

                                            <!-- ตรงกลาง: ปุ่มเพิ่ม / ย้าย -->
                                            <div class="col-md-2 text-center my-3 my-md-0 d-flex flex-md-column justify-content-center align-items-center">
                                                <button type="button" class="btn btn-outline-success btn-sm font-weight-bold mb-2 w-100 shadow-sm btn-dual-action" onclick="moveSelected('sales_available', 'sales_selected')" title="เพิ่มพนักงานขายที่ไฮไลท์เลือก">
                                                    เพิ่มที่เลือก <i class="fas fa-chevron-right d-none d-md-inline ms-1"></i><i class="fas fa-chevron-down d-inline d-md-none ms-1"></i>
                                                </button>
                                                <button type="button" class="btn btn-success btn-sm font-weight-bold mb-3 w-100 shadow-sm btn-dual-action" onclick="moveAll('sales_available', 'sales_selected')" title="เพิ่มพนักงานขายทั้งหมด">
                                                    เพิ่มทั้งหมด <i class="fas fa-angle-double-right d-none d-md-inline ms-1"></i><i class="fas fa-angle-double-down d-inline d-md-none ms-1"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger btn-sm font-weight-bold mb-2 w-100 shadow-sm btn-dual-action" onclick="moveSelected('sales_selected', 'sales_available')" title="นำพนักงานขายที่ไฮไลท์เลือกออก">
                                                    <i class="fas fa-chevron-left d-none d-md-inline me-1"></i><i class="fas fa-chevron-up d-inline d-md-none me-1"></i> นำออกที่เลือก
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm font-weight-bold w-100 shadow-sm btn-dual-action" onclick="moveAll('sales_selected', 'sales_available')" title="นำพนักงานขายทั้งหมดออก">
                                                    <i class="fas fa-angle-double-left d-none d-md-inline me-1"></i><i class="fas fa-angle-double-up d-inline d-md-none me-1"></i> นำออกทั้งหมด
                                                </button>
                                            </div>

                                            <!-- ฝั่งขวา: รายชื่อที่ต้องการส่งออก -->
                                            <div class="col-md-5">
                                                <div class="d-flex justify-content-between align-items-center mb-1">
                                                    <span class="font-weight-bold text-success small">
                                                        <i class="fas fa-check-circle me-1"></i> รายชื่อพนักงานขายที่จะ Export (<span id="selectedCount" class="badge badge-success">0</span> ท่าน) <span class="text-danger">*</span>
                                                    </span>
                                                    <a href="javascript:void(0);" class="small text-decoration-none text-danger font-weight-bold" onclick="moveAll('sales_selected', 'sales_available')">
                                                        <i class="fas fa-trash-alt me-1"></i>ล้างทั้งหมด
                                                    </a>
                                                </div>
                                                <div class="input-group input-group-sm mb-2">
                                                    <div class="input-group-prepend">
                                                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                                    </div>
                                                    <input type="text" id="search_selected" class="form-control" placeholder="ค้นหาในรายการที่เลือก..." onkeyup="filterListbox('sales_selected', this.value)">
                                                </div>
                                                <select multiple id="sales_selected" name="SALE_NAME[]" class="form-control dual-listbox-box shadow-sm border-success" style="height: 220px; font-size: 0.92rem; background-color: #f0fdf4;">
                                                </select>
                                                <small class="text-muted d-block mt-1">
                                                    <i class="fas fa-info-circle text-success me-1"></i> ดับเบิ้ลคลิกที่ชื่อเพื่อนำออกจากรายการได้ทันที
                                                </small>
                                            </div>

                                        </div>
                                    </div>
                                    <small class="text-muted mt-1 d-block">
                                        <i class="fas fa-filter text-primary me-1"></i> กรองรายชื่อพนักงานขายตาม BRAND, SKU_CAT, และ DI_REF NOT LIKE 'DS%' โดยไม่รวมบัญชีกลาง/RQ
                                    </small>
                                </div>
                            </div>

                            <!-- Row 4: Export Format Options -->
                            <div class="row mb-4">
                                <div class="col-md-12">
                                    <label class="form-label font-weight-bold"><i class="fas fa-file-invoice text-success me-1"></i> รูปแบบการแสดงผลและส่งออก (Export Format):</label>
                                    <div class="row g-3">
                                        <!-- Option 1: Template Volume Tires (18 Columns) -->
                                        <div class="col-md-6 mb-2">
                                            <div class="card h-100 border border-success p-3 bg-light format-card" onclick="$('#fmt_v_template').prop('checked', true);">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="export_format" id="fmt_v_template" value="v_template" checked>
                                                    <label class="form-check-label font-weight-bold text-success" for="fmt_v_template">
                                                        <i class="fas fa-file-excel me-1"></i> เทมเพลต Volume Tires 18 คอลัมน์ (ตาม template_v_tires.xlsx)
                                                        <span class="badge bg-success text-white ms-1">แนะนำ / ใช้ Import แพลนได้ทันที</span>
                                                    </label>
                                                </div>
                                                <small class="text-muted mt-1 d-block">
                                                    โครงสร้างตรงตาม <code>template_v_tires.xlsx</code> แถวที่ 1 เป็นหัวคอลัมน์ แถวที่ 2 เป็นต้นไปเป็นข้อมูล พร้อมคอลัมน์ A ถึง R ครบถ้วน (เซลล์, เทค, ลูกค้า, จังหวัด, ประเภท, รหัสสินค้า, รายละเอียด, ยี่ห้อ, ยอดขาย Max/Min, สถิติ 3 เดือนล่าสุด, ค่าเฉลี่ย 3 เดือน, ช่องกรอกแพลน, สูตรผลต่าง, และผู้รับผิดชอบ)
                                                </small>
                                            </div>
                                        </div>
                                        <!-- Option 2: Detailed Months -->
                                        <div class="col-md-6 mb-2">
                                            <div class="card h-100 border p-3 bg-light format-card" onclick="$('#fmt_6_months').prop('checked', true);">
                                                <div class="form-check">
                                                    <input class="form-check-input" type="radio" name="export_format" id="fmt_6_months" value="6_months">
                                                    <label class="form-check-label font-weight-bold text-secondary" for="fmt_6_months">
                                                        <i class="fas fa-calendar-alt me-1"></i> ขยายรายเดือนครบตามช่วงวันที่ (Detailed Months)
                                                    </label>
                                                </div>
                                                <small class="text-muted mt-1 d-block">
                                                    แสดงแจกแจงยอดขายแยกเป็นรายเดือนครบทุกเดือนตามช่วงวันที่เริ่มต้น - สิ้นสุดที่กำหนด สำหรับดูสถิติและแนวโน้มย้อนหลังอย่างละเอียด
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <hr>

                            <!-- Buttons -->
                            <div class="d-flex flex-wrap justify-content-between align-items-center">
                                <div class="mb-2">
                                    <button type="button" class="btn btn-info mr-2 me-2 mb-1 shadow-sm font-weight-bold" id="btnPreview" onclick="loadPreviewData();">
                                        <i class="fa fa-search mr-1 me-1"></i> ค้นหา / ดูตัวอย่างข้อมูล
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary mr-2 me-2 mb-1 shadow-sm" onclick="resetFilters();">
                                        <i class="fas fa-undo mr-1 me-1"></i> ล้างตัวกรอง
                                    </button>
                                </div>

                                <div class="d-flex flex-wrap mb-2">
                                    <button type="button" class="btn btn-success mr-2 me-2 mb-1 shadow-sm font-weight-bold" id="btnExportXls" onclick="submitExport('xls');">
                                        <i class="fa fa-file-excel mr-1 me-1"></i> Export Excel (.xls เทมเพลต)
                                    </button>
                                    <button type="button" class="btn btn-primary mr-2 me-2 mb-1 shadow-sm font-weight-bold" id="btnExportXlsx" onclick="submitExport('xlsx');">
                                        <i class="fa fa-file-excel mr-1 me-1"></i> Export Excel (.xlsx)
                                    </button>
                                    <button type="button" class="btn btn-secondary mr-2 me-2 mb-1 shadow-sm font-weight-bold" id="btnExportCsv" onclick="submitExport('csv');">
                                        <i class="fa fa-file-csv mr-1 me-1"></i> Export CSV
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Preview Result Card (Hidden until preview loaded) -->
                <div class="card mb-4 shadow-sm border-0" id="previewCard" style="display:none;">
                    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                        <h6 class="m-0 font-weight-bold text-primary">
                            <i class="fas fa-table me-2"></i> ตัวอย่างข้อมูลที่ค้นพบ (แสดง 50 รายการแรก)
                        </h6>
                        <span class="badge badge-primary py-2 px-3" id="badgeTotalFound">0 รายการ</span>
                    </div>
                    <div class="card-body">
                        
                        <!-- Summary Cards -->
                        <div class="row mb-4" id="summaryStats">
                            <div class="col-xl-3 col-md-6 mb-3">
                                <div class="card border-left-primary shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">จำนวนรายการทั้งหมด</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="statRecords">0</div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-list-ol fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6 mb-3">
                                <div class="card border-left-success shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">ยอดขายรวมช่วงเวลา (เส้น)</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="statTotalQty">0.00</div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-cubes fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6 mb-3">
                                <div class="card border-left-info shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">จำนวนลูกค้า (ร้านค้า)</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="statCustomers">0</div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-users fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-md-6 mb-3">
                                <div class="card border-left-warning shadow h-100 py-2">
                                    <div class="card-body">
                                        <div class="row no-gutters align-items-center">
                                            <div class="col mr-2">
                                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">จำนวน SKU สินค้า</div>
                                                <div class="h5 mb-0 font-weight-bold text-gray-800" id="statSkus">0</div>
                                            </div>
                                            <div class="col-auto">
                                                <i class="fas fa-box-open fa-2x text-gray-300"></i>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Table -->
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover table-sm text-center" id="previewTable" width="100%">
                                <thead class="thead-light">
                                    <tr>
                                        <th>ลำดับ</th>
                                        <th>เซลล์</th>
                                        <th>เทค</th>
                                        <th class="text-start">รายชื่อลูกค้า</th>
                                        <th>จังหวัด</th>
                                        <th>ประเภท</th>
                                        <th class="bg-success text-white">รหัสสินค้า</th>
                                        <th class="text-start">รายละเอียด</th>
                                        <th class="bg-success text-white">ยี่ห้อ</th>
                                        <th>ยอดขายสูงสุด</th>
                                        <th>ยอดขายต่ำสุด</th>
                                        <th id="th_m3">เดือน -3</th>
                                        <th id="th_m2">เดือน -2</th>
                                        <th id="th_m1">เดือน -1</th>
                                        <th class="bg-light text-success font-weight-bold">เฉลี่ย 3 ด.</th>
                                        <th>ผู้รับผิดชอบ</th>
                                    </tr>
                                </thead>
                                <tbody id="previewTbody">
                                </tbody>
                            </table>
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

<!-- Spinner Overlay -->
<div id="spinnerOverlay" class="spinner-overlay">
    <div class="spinner-box">
        <div class="spinner-border text-success" style="width: 3rem; height: 3rem;" role="status">
            <span class="sr-only">กำลังประมวลผล...</span>
        </div>
        <h5 class="mt-3 font-weight-bold text-dark" id="spinnerText">กำลังประมวลผลข้อมูล...</h5>
        <p class="text-muted small mb-0">กรุณารอสักครู่ ระบบกำลังจัดเตรียมไฟล์ Excel</p>
    </div>
</div>

<!-- Scroll to top -->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<!-- Scripts -->
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
<script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
<link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="js/myadmin.min.js"></script>

<script>
function pad2(n) { return n < 10 ? '0' + n : n; }

// Parse Date string 'dd-mm-yyyy' to Date object
function parseDateDMY(str) {
    if (!str) return null;
    let parts = str.split('-');
    if (parts.length === 3) {
        let d = parseInt(parts[0], 10);
        let m = parseInt(parts[1], 10) - 1;
        let y = parseInt(parts[2], 10);
        return new Date(y, m, d);
    }
    return null;
}

// Calculate and display live date range info
function updateDateRangeInfo() {
    let sStr = $('#doc_date_start').val();
    let eStr = $('#doc_date_to').val();

    let dStart = parseDateDMY(sStr);
    let dEnd = parseDateDMY(eStr);

    if (dStart && dEnd) {
        if (dStart > dEnd) {
            $('#dateRangeBadge').removeClass('badge-success').addClass('badge-danger');
            $('#dateRangeText').html('<i class="fas fa-exclamation-triangle me-1"></i> วันที่เริ่มต้นต้องไม่มากกว่าวันที่สิ้นสุด');
            return;
        }

        $('#dateRangeBadge').removeClass('badge-danger').addClass('badge-success');

        let diffTime = Math.abs(dEnd - dStart);
        let diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;

        let monthsApprox = (dEnd.getFullYear() - dStart.getFullYear()) * 12 + (dEnd.getMonth() - dStart.getMonth()) + 1;
        if (monthsApprox < 1) monthsApprox = 1;

        $('#dateRangeText').html(
            `ช่วงเวลาที่เลือก: <strong>${sStr}</strong> ถึง <strong>${eStr}</strong> (ประมาณ ${monthsApprox} เดือน / รวม ${diffDays} วัน)`
        );

        // Auto update target month & year to the month of or right after dEnd
        let targetM = dEnd.getMonth() + 1;
        let targetY = dEnd.getFullYear();
        $('#target_month').val(targetM);
        $('#target_year').val(targetY);
    }
}

// Quick Date Range Functions
function setDateRangeMonths(monthsBack) {
    let today = new Date();
    let pastDate = new Date();
    pastDate.setMonth(pastDate.getMonth() - monthsBack);

    let startStr = pad2(pastDate.getDate()) + '-' + pad2(pastDate.getMonth() + 1) + '-' + pastDate.getFullYear();
    let toStr = pad2(today.getDate()) + '-' + pad2(today.getMonth() + 1) + '-' + today.getFullYear();

    $('#doc_date_start').datepicker('setDate', pastDate);
    $('#doc_date_to').datepicker('setDate', today);
    $('#doc_date_start').val(startStr);
    $('#doc_date_to').val(toStr);

    updateDateRangeInfo();
}

function setDateYTD() {
    let today = new Date();
    let startYear = new Date(today.getFullYear(), 0, 1);

    let startStr = '01-01-' + today.getFullYear();
    let toStr = pad2(today.getDate()) + '-' + pad2(today.getMonth() + 1) + '-' + today.getFullYear();

    $('#doc_date_start').datepicker('setDate', startYear);
    $('#doc_date_to').datepicker('setDate', today);
    $('#doc_date_start').val(startStr);
    $('#doc_date_to').val(toStr);

    updateDateRangeInfo();
}

function setDateLastMonth() {
    let today = new Date();
    let firstDayLastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
    let lastDayLastMonth = new Date(today.getFullYear(), today.getMonth(), 0);

    let startStr = pad2(firstDayLastMonth.getDate()) + '-' + pad2(firstDayLastMonth.getMonth() + 1) + '-' + firstDayLastMonth.getFullYear();
    let toStr = pad2(lastDayLastMonth.getDate()) + '-' + pad2(lastDayLastMonth.getMonth() + 1) + '-' + lastDayLastMonth.getFullYear();

    $('#doc_date_start').datepicker('setDate', firstDayLastMonth);
    $('#doc_date_to').datepicker('setDate', lastDayLastMonth);
    $('#doc_date_start').val(startStr);
    $('#doc_date_to').val(toStr);

    updateDateRangeInfo();
}

function setDateThisMonth() {
    let today = new Date();
    let firstDayThisMonth = new Date(today.getFullYear(), today.getMonth(), 1);

    let startStr = '01-' + pad2(today.getMonth() + 1) + '-' + today.getFullYear();
    let toStr = pad2(today.getDate()) + '-' + pad2(today.getMonth() + 1) + '-' + today.getFullYear();

    $('#doc_date_start').datepicker('setDate', firstDayThisMonth);
    $('#doc_date_to').datepicker('setDate', today);
    $('#doc_date_start').val(startStr);
    $('#doc_date_to').val(toStr);

    updateDateRangeInfo();
}

// Dual Listbox Helper Functions
function sortSelectOptions(selectId) {
    let $select = $('#' + selectId);
    let options = $select.children('option').get();
    options.sort(function(a, b) {
        return $(a).text().localeCompare($(b).text(), 'th');
    });
    $.each(options, function(idx, item) {
        $select.append(item);
    });
}

function updateListCounts() {
    let availCount = $('#sales_available option').length;
    let selCount = $('#sales_selected option').length;
    $('#availableCount').text(availCount);
    $('#selectedCount').text(selCount);
}

function moveSelected(fromId, toId) {
    let $selected = $('#' + fromId + ' option:selected');
    if ($selected.length === 0) {
        Swal.fire({
            icon: 'info',
            title: 'ยังไม่ได้เลือกรายการ',
            text: 'กรุณาคลิกเลือกชื่อพนักงานขายที่ต้องการย้ายก่อนครับ',
            timer: 1500,
            showConfirmButton: false
        });
        return;
    }
    $selected.appendTo('#' + toId).prop('selected', false);
    sortSelectOptions(toId);
    $('#search_' + (fromId === 'sales_available' ? 'available' : 'selected')).val('');
    $('#' + fromId + ' option').show();
    $('#search_' + (toId === 'sales_available' ? 'available' : 'selected')).val('');
    $('#' + toId + ' option').show();
    updateListCounts();
}

function moveAll(fromId, toId) {
    let $all = $('#' + fromId + ' option');
    if ($all.length === 0) {
        return;
    }
    $all.appendTo('#' + toId).prop('selected', false).show();
    sortSelectOptions(toId);
    $('#search_available, #search_selected').val('');
    $('#' + fromId + ' option, #' + toId + ' option').show();
    updateListCounts();
}

function filterListbox(selectId, query) {
    let q = (query || '').trim().toLowerCase();
    $('#' + selectId + ' option').each(function() {
        let txt = $(this).text().toLowerCase();
        if (!q || txt.indexOf(q) !== -1) {
            $(this).show();
        } else {
            $(this).hide();
        }
    });
}

function highlightAll(selectId) {
    $('#' + selectId + ' option:visible').prop('selected', true);
}

$(document).ready(function () {
    // Initialize datepickers (allows typing or selecting)
    $('#doc_date_start, #doc_date_to').datepicker({
        format: "dd-mm-yyyy",
        todayHighlight: true,
        language: "th",
        autoclose: true
    }).on('changeDate change', function () {
        updateDateRangeInfo();
    });

    $('#doc_date_start, #doc_date_to').on('keyup blur', function () {
        updateDateRangeInfo();
    });

    // Run initial range text update
    updateDateRangeInfo();

    // Double click to move items between lists
    $('#sales_available').on('dblclick', 'option', function() {
        $(this).appendTo('#sales_selected').prop('selected', false);
        sortSelectOptions('sales_selected');
        updateListCounts();
    });

    $('#sales_selected').on('dblclick', 'option', function() {
        $(this).appendTo('#sales_available').prop('selected', false);
        sortSelectOptions('sales_available');
        updateListCounts();
    });

    updateListCounts();
});

function resetFilters() {
    setDateRangeMonths(6);
    moveAll('sales_selected', 'sales_available');
    $('#search_available, #search_selected').val('');
    $('#sales_available option, #sales_selected option').show();
    $('#fmt_v_template').prop('checked', true);
    $('#previewCard').slideUp();
}

// Load Preview Data
function loadPreviewData() {
    let sStr = $('#doc_date_start').val();
    let eStr = $('#doc_date_to').val();
    let dStart = parseDateDMY(sStr);
    let dEnd = parseDateDMY(eStr);

    if (dStart && dEnd && dStart > dEnd) {
        Swal.fire('แจ้งเตือน', 'วันที่เริ่มต้นต้องไม่มากกว่าวันที่สิ้นสุด', 'warning');
        return;
    }

    // Validation: Require at least 1 salesperson in the selected list
    if ($('#sales_selected option').length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'ยังไม่ได้เลือกพนักงานขาย',
            text: 'กรุณาเลือกชื่อพนักงานขายจากรายการทางซ้าย แล้วกดปุ่ม [เพิ่มที่เลือก >] หรือ [เพิ่มทั้งหมด >>] เพื่อนำไปแสดงผล/ส่งออก',
            confirmButtonText: 'ตกลง'
        });
        return;
    }

    // Ensure all options in sales_selected are selected so serialize() captures SALE_NAME[]
    $('#sales_selected option').prop('selected', true);

    $('#spinnerText').text('กำลังค้นหาและประมวลผลตัวอย่างข้อมูล...');
    $('#spinnerOverlay').fadeIn(200);

    let formData = $('#v_template_form').serialize();

    $.ajax({
        url: 'export_process/preview_data_v_template.php',
        type: 'POST',
        data: formData,
        dataType: 'json',
        success: function(resp) {
            $('#spinnerOverlay').fadeOut(200);

            if (resp.status === 'success') {
                $('#previewCard').show();
                $('#statRecords').text(resp.total_records.toLocaleString());
                $('#statTotalQty').text(resp.total_qty);
                $('#statCustomers').text(resp.unique_customers.toLocaleString());
                $('#statSkus').text(resp.unique_skus.toLocaleString());
                $('#badgeTotalFound').text(resp.total_records.toLocaleString() + ' รายการ');

                $('#th_m3').text(resp.m3_label);
                $('#th_m2').text(resp.m2_label);
                $('#th_m1').text(resp.m1_label);

                let tbody = $('#previewTbody');
                tbody.empty();

                if (resp.preview_rows.length === 0) {
                    tbody.append('<tr><td colspan="16" class="text-muted py-4">ไม่พบข้อมูลตามเงื่อนไขที่ระบุ</td></tr>');
                } else {
                    $.each(resp.preview_rows, function(idx, r) {
                        tbody.append(`
                            <tr>
                                <td>${idx + 1}</td>
                                <td>${r.sale_name || '-'}</td>
                                <td>${r.take_name || '-'}</td>
                                <td class="text-start">${r.ar_name || '-'}</td>
                                <td>${r.province || '-'}</td>
                                <td><span class="badge badge-light border text-dark">${r.sku_cat || '-'}</span></td>
                                <td><span class="badge bg-light text-primary border font-monospace font-weight-bold">${r.sku_code || '-'}</span></td>
                                <td class="text-start font-weight-bold">${r.sku_name || '-'}</td>
                                <td><span class="badge bg-light text-success border font-weight-bold">${r.brand || '-'}</span></td>
                                <td>${r.max_qty}</td>
                                <td>${r.min_qty}</td>
                                <td class="table-primary">${r.qty_m3}</td>
                                <td class="table-info">${r.qty_m2}</td>
                                <td class="table-success">${r.qty_m1}</td>
                                <td class="bg-light font-weight-bold text-success">${r.avg_3m}</td>
                                <td>${r.responsible_person || r.sale_name || '-'}</td>
                            </tr>
                        `);
                    });
                }

                // Scroll to preview card
                $('html, body').animate({
                    scrollTop: $("#previewCard").offset().top - 80
                }, 500);

            } else {
                Swal.fire('เกิดข้อผิดพลาด', resp.message || 'ไม่สามารถดึงข้อมูลได้', 'error');
            }
        },
        error: function(xhr, status, err) {
            $('#spinnerOverlay').fadeOut(200);
            Swal.fire('เกิดข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้: ' + err, 'error');
        }
    });
}

// Export Submit
function submitExport(type) {
    let sStr = $('#doc_date_start').val();
    let eStr = $('#doc_date_to').val();
    let dStart = parseDateDMY(sStr);
    let dEnd = parseDateDMY(eStr);

    if (dStart && dEnd && dStart > dEnd) {
        Swal.fire('แจ้งเตือน', 'วันที่เริ่มต้นต้องไม่มากกว่าวันที่สิ้นสุด', 'warning');
        return;
    }

    // Validation: Require at least 1 salesperson in the selected list
    if ($('#sales_selected option').length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'ยังไม่ได้เลือกพนักงานขาย',
            text: 'กรุณาเลือกชื่อพนักงานขายจากรายการทางซ้าย แล้วกดปุ่ม [เพิ่มที่เลือก >] หรือ [เพิ่มทั้งหมด >>] เพื่อนำไปส่งออก',
            confirmButtonText: 'ตกลง'
        });
        return;
    }

    // Ensure all options in sales_selected are selected so form submission sends SALE_NAME[]
    $('#sales_selected option').prop('selected', true);

    $('#file_type').val(type);
    $('#spinnerText').text('กำลังสร้างไฟล์ ' + type.toUpperCase() + ' กรุณารอสักครู่...');
    $('#spinnerOverlay').fadeIn(200);

    let form = document.getElementById('v_template_form');
    form.action = 'export_process/export_data_v_template_process.php';
    form.submit();

    // Auto hide spinner after 5 seconds
    setTimeout(function () {
        $('#spinnerOverlay').fadeOut(300);
    }, 5000);
}
</script>

</body>
</html>
