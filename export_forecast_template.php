<?php
session_start();
date_default_timezone_set("Asia/Bangkok");
include('config/connect_db.php');

if (!isset($_SESSION['alogin']) || $_SESSION['alogin'] == '') {
    header('Location: login.php');
    exit;
}

$account_type = $_SESSION['account_type'] ?? '';
$first_name = $_SESSION['first_name'] ?? '';

$thai_months = [
    1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม',
    4 => 'เมษายน', 5 => 'พฤษภาคม', 6 => 'มิถุนายน',
    7 => 'กรกฎาคม', 8 => 'สิงหาคม', 9 => 'กันยายน',
    10 => 'ตุลาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
];

// Fetch Sales List directly from ims_data_sale_sac_all (Excluding BTC, RQ, '-', ส่วนกลาง, READYQUICK, READY QUICK)
$sql_sales = "SELECT DISTINCT SALE_NAME FROM ims_data_sale_sac_all 
              WHERE SALE_NAME IS NOT NULL 
              AND TRIM(SALE_NAME) != '' 
              AND TRIM(SALE_NAME) != '-' 
              AND SALE_NAME NOT LIKE '%BTC%' 
              AND SALE_NAME NOT LIKE '%RQ%' 
              AND SALE_NAME NOT LIKE '%ส่วนกลาง%' 
              AND SALE_NAME NOT LIKE '%READYQUICK%' 
              AND SALE_NAME NOT LIKE '%READY QUICK%' 
              AND SALE_NAME NOT LIKE '%READY%QUICK%'
              AND CAST(TRD_QTY AS DECIMAL(10,2)) > 0
              AND (TRD_Q_FREE IS NULL OR CAST(TRD_Q_FREE AS DECIMAL(10,2)) = 0)
              AND (SKU_CAT IS NULL OR SKU_CAT NOT LIKE '%ของแถม%') ";
$params_sales = [];

if ($account_type === 'sale_user') {
    $sql_sales .= " AND SALE_NAME = :first_name";
    $params_sales[':first_name'] = $first_name;
} elseif ($account_type === 'take') {
    $sql_sales .= " AND (SALE_NAME = :first_name OR TAKE_NAME = :first_name)";
    $params_sales[':first_name'] = $first_name;
}

$sql_sales .= " ORDER BY SALE_NAME ASC";
$stmt_sales = $conn->prepare($sql_sales);
$stmt_sales->execute($params_sales);
$sales_list = $stmt_sales->fetchAll(PDO::FETCH_COLUMN);

$current_month = intval(date('m'));
$current_year = intval(date('Y'));
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>หน้าจอ Export Template เพื่อการนำเข้า Forecast (Realtime)</title>
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Select2 CSS for searchable dropdowns -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
    
    <style>
        body {
            font-family: 'Prompt', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #f4f6f9;
            color: #333;
        }

        .header-banner {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            border-bottom: 4px solid #198754;
            color: white;
            padding: 30px 0;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .card-custom {
            border: none;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0,0,0,0.06);
            background: #ffffff;
        }

        .form-section-title {
            color: #198754;
            border-left: 4px solid #198754;
            padding-left: 10px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .btn-export-template {
            background: linear-gradient(135deg, #198754 0%, #146c43 100%);
            border: none;
            color: white;
            font-weight: 600;
            padding: 12px 24px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(25, 135, 84, 0.3);
            transition: all 0.2s;
        }

        .btn-export-template:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(25, 135, 84, 0.4);
            color: white;
        }

        .btn-export-report {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            border: none;
            color: white;
            font-weight: 600;
            padding: 12px 24px;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(13, 110, 253, 0.3);
            transition: all 0.2s;
        }

        .btn-export-report:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(13, 110, 253, 0.4);
            color: white;
        }

        .info-box {
            background: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            padding: 15px;
        }
    </style>
</head>
<body>

    <!-- Top Header Banner -->
    <div class="header-banner mb-4">
        <div class="container">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h3 class="fw-bold mb-1"><i class="fas fa-file-excel me-2 text-success"></i> หน้าจอ Export Template สำหรับนำเข้า Forecast</h3>
                    <p class="mb-0 text-white-50">เลือกเงื่อนไข (เดือน, ปี, พนักงานขาย) เพื่อส่งออกไฟล์ Excel/CSV และนำไปปรับปรุงข้อมูล Forecast</p>
                </div>
                <div>
                    <a href="forecast_plan_display_realtime.php" class="btn btn-outline-light btn-sm fw-bold">
                        <i class="fas fa-arrow-left me-1"></i> กลับหน้า Forecast Realtime
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Container -->
    <div class="container pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card card-custom p-4 p-md-5">
                    
                    <form id="exportForm" method="GET" target="_blank">
                        
                        <!-- Section 1: Month and Year Selection -->
                        <h5 class="form-section-title"><i class="fas fa-calendar-alt me-2"></i> 1. เลือกช่วงเวลา (เดือน และ ปี)</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">เลือกเดือนเป้าหมาย <span class="text-danger">*</span></label>
                                <select name="month" id="exportMonth" class="form-select form-select-lg" required>
                                    <option value="">-- เลือกเดือน --</option>
                                    <?php foreach ($thai_months as $num => $name): ?>
                                        <option value="<?= $num ?>" <?= ($num === $current_month) ? 'selected' : '' ?>><?= $name ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold text-dark">เลือกปีเป้าหมาย <span class="text-danger">*</span></label>
                                <select name="year" id="exportYear" class="form-select form-select-lg" required>
                                    <?php for($y = $current_year - 1; $y <= $current_year + 2; $y++): ?>
                                        <option value="<?= $y ?>" <?= ($y === $current_year) ? 'selected' : '' ?>><?= $y ?></option>
                                    <?php endfor; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Section 2: Sale Selection -->
                        <h5 class="form-section-title"><i class="fas fa-user-tie me-2"></i> 2. เลือกพนักงานขาย</h5>
                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label class="form-label fw-bold text-dark">พนักงานขาย (Sale Name)</label>
                                <select name="sale_name" id="exportSale" class="form-select select2">
                                    <option value="">-- พนักงานขายทั้งหมด --</option>
                                    <?php foreach ($sales_list as $sale): 
                                        $is_locked = ($account_type !== 'admin' && $account_type !== 'sale_sup');
                                        $is_me = $is_locked && ($sale === $first_name);
                                        $selected = $is_me ? 'selected' : '';
                                        $disabled = ($is_locked && !$is_me) ? 'disabled' : '';
                                        echo "<option value='".htmlspecialchars($sale)."' $selected $disabled>".htmlspecialchars($sale)."</option>";
                                    endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex flex-wrap gap-3 justify-content-center pt-2">
                            <button type="button" id="btnExportTemplate" class="btn btn-export-template btn-lg">
                                <i class="fas fa-file-csv me-2"></i> ดาวน์โหลด Template นำเข้าแผนการขาย (Export Template)
                            </button>
                            <button type="button" id="btnExportReport" class="btn btn-export-report btn-lg">
                                <i class="fas fa-file-excel me-2"></i> ดาวน์โหลด รายงานแผนการขาย Realtime (Export Plan Report)
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {
            // Initialize Select2 on dropdowns
            $('.select2').select2({
                theme: 'bootstrap-5',
                width: '100%'
            });

            // Action 1: Export Template for Import
            $('#btnExportTemplate').click(function() {
                const month = $('#exportMonth').val();
                const year = $('#exportYear').val();

                if (!month || !year) {
                    Swal.fire('แจ้งเตือน', 'กรุณาเลือกเดือนและปีที่ต้องการ Export', 'warning');
                    return;
                }

                const form = $('#exportForm');
                form.attr('action', 'forecast_plan_export_template_realtime.php');
                form.submit();
            });

            // Action 2: Export Plan Report
            $('#btnExportReport').click(function() {
                const month = $('#exportMonth').val();
                const year = $('#exportYear').val();

                if (!month || !year) {
                    Swal.fire('แจ้งเตือน', 'กรุณาเลือกเดือนและปีที่ต้องการ Export', 'warning');
                    return;
                }

                const form = $('#exportForm');
                form.attr('action', 'forecast_plan_export_excel_realtime.php');
                form.submit();
            });
        });
    </script>
</body>
</html>
