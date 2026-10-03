<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index");
    exit();
} else {
    include("config/connect_db.php");

    // Pre-fetch years from ims_data_sale_sac_all
    $sql_year = "SELECT DISTINCT(DI_YEAR) AS DI_YEAR FROM ims_data_sale_sac_all WHERE DI_YEAR IS NOT NULL AND DI_YEAR != '' ORDER BY DI_YEAR DESC";
    $stmt_year = $conn->prepare($sql_year);
    $stmt_year->execute();
    $YearRecords = $stmt_year->fetchAll(PDO::FETCH_ASSOC);

    // Fetch months list
    $sql_month = "SELECT * FROM ims_month ORDER BY month_id ASC";
    $stmt_month = $conn->prepare($sql_month);
    $stmt_month->execute();
    $MonthRecords = $stmt_month->fetchAll(PDO::FETCH_ASSOC);

    // Fetch distinct SKU Categories
    $sql_sku_cat = "SELECT DISTINCT(SKU_CAT) AS SKU_CAT FROM ims_data_sale_sac_all WHERE SKU_CAT IS NOT NULL AND SKU_CAT != '' ORDER BY SKU_CAT ASC";
    $stmt_sku_cat = $conn->prepare($sql_sku_cat);
    $stmt_sku_cat->execute();
    $SkuCatRecords = $stmt_sku_cat->fetchAll(PDO::FETCH_ASSOC);

    // Pre-fetch distinct Sale/Take names for initial dropdown
    $sql_st_all = "SELECT DISTINCT name FROM (
        SELECT SALE_NAME AS name FROM ims_data_sale_sac_all WHERE SALE_NAME IS NOT NULL AND SALE_NAME != '' AND SALE_NAME != '-'
        UNION
        SELECT TAKE_NAME AS name FROM ims_data_sale_sac_all WHERE TAKE_NAME IS NOT NULL AND TAKE_NAME != '' AND TAKE_NAME != '-'
    ) t ORDER BY name ASC";
    $stmt_st_all = $conn->prepare($sql_st_all);
    $stmt_st_all->execute();
    $SaleTakeAllRecords = $stmt_st_all->fetchAll(PDO::FETCH_ASSOC);

    $current_month = date('n');
    $current_year = date('Y');
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <!-- ApexCharts for modern high-performance charts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <style>
        .page-header-box {
            background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%);
            color: #ffffff;
            border-radius: 12px;
            padding: 20px 24px;
            box-shadow: 0 4px 20px rgba(30, 60, 114, 0.2);
            margin-bottom: 24px;
        }
        .page-header-box h1 {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .page-header-box p {
            margin: 4px 0 0 0;
            opacity: 0.85;
            font-size: 0.9rem;
        }
        .filter-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            background: #ffffff;
            margin-bottom: 24px;
        }
        .filter-card .card-header {
            background: #f8f9fc;
            border-bottom: 1px solid #e3e6f0;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
            font-weight: 700;
            color: #4e73df;
        }
        .btn-filter-type {
            border-radius: 8px;
            font-weight: 600;
            padding: 8px 16px;
            transition: all 0.2s ease;
        }
        .stat-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            background: #ffffff;
            position: relative;
            overflow: hidden;
        }
        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
        }
        .stat-card .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
        }
        .stat-card.blue { border-left: 5px solid #4e73df; }
        .stat-card.blue .stat-icon { background: rgba(78, 115, 223, 0.1); color: #4e73df; }
        .stat-card.green { border-left: 5px solid #1cc88a; }
        .stat-card.green .stat-icon { background: rgba(28, 200, 138, 0.1); color: #1cc88a; }
        .stat-card.orange { border-left: 5px solid #f6c23e; }
        .stat-card.orange .stat-icon { background: rgba(246, 194, 62, 0.1); color: #f6c23e; }
        .stat-card.purple { border-left: 5px solid #6f42c1; }
        .stat-card.purple .stat-icon { background: rgba(111, 66, 193, 0.1); color: #6f42c1; }
        
        .stat-title {
            font-size: 0.82rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #858796;
        }
        .stat-value {
            font-size: 1.4rem;
            font-weight: 800;
            color: #2e59d9;
            margin-top: 4px;
        }
        .stat-sub {
            font-size: 0.8rem;
            color: #6e707e;
        }
        .rank-badge {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            font-size: 0.9rem;
        }
        .rank-1 { background: #ffeaa7; color: #d63031; border: 2px solid #fdcb6e; }
        .rank-2 { background: #dfe6e9; color: #2d3436; border: 2px solid #b2bec3; }
        .rank-3 { background: #fab1a0; color: #d63031; border: 2px solid #e17055; }
        .rank-normal { background: #e9ecef; color: #495057; }
        
        .chart-card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            background: #fff;
            margin-bottom: 24px;
        }
        .chart-card .card-header {
            background: transparent;
            border-bottom: 1px solid #eaecf4;
            font-weight: 700;
            color: #2e59d9;
        }
        .table-responsive {
            border-radius: 10px;
        }
        .table thead th {
            background-color: #4e73df;
            color: #ffffff;
            border: none;
            font-weight: 600;
            font-size: 0.85rem;
            white-space: nowrap;
            vertical-align: middle;
        }
        .table tbody td {
            vertical-align: middle;
            font-size: 0.88rem;
        }
        .progress-share {
            height: 8px;
            border-radius: 4px;
            background-color: #eaecf4;
        }
        .progress-share .progress-bar {
            border-radius: 4px;
            background: linear-gradient(90deg, #4e73df 0%, #36b9cc 100%);
        }
        .nav-tabs .nav-link {
            font-weight: 600;
            color: #4e73df;
            border-radius: 8px 8px 0 0;
        }
        .nav-tabs .nav-link.active {
            font-weight: 700;
            color: #1e3c72;
            background-color: #fff;
            border-color: #dee2e6 #dee2e6 #fff;
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
                
                <!-- Page Header Banner -->
                <div class="page-header-box d-flex justify-content-between align-items-center flex-wrap">
                    <div>
                        <h1><i class="fas fa-trophy text-warning"></i> รายงานร้านค้าที่มียอดขายสูงสุด (Top Sales Stores)</h1>
                        <p>วิเคราะห์และจัดอันดับยอดขายร้านค้า พร้อมสรุปยอดขายยางแยกตาม SKU (20 อันดับ) กรองตาม Sale หรือ Take ได้</p>
                    </div>
                    <ol class="breadcrumb bg-transparent p-0 m-0 text-white" style="font-size:0.85rem;">
                        <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>" class="text-white">Home</a></li>
                        <li class="breadcrumb-item text-white active" aria-current="page">Top Stores Report</li>
                    </ol>
                </div>

                <!-- Filter Controls Card -->
                <div class="card filter-card">
                    <div class="card-header d-flex align-items-center justify-content-between">
                        <span><i class="fas fa-filter me-2"></i> ตัวกรองข้อมูล (Filters)</span>
                        <span class="badge badge-primary px-3 py-2" id="activeFilterBadge">แสดงข้อมูลทั้งหมด</span>
                    </div>
                    <div class="card-body">
                        <form id="filterForm" onsubmit="return false;">
                            <!-- Filter Mode Selector -->
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="font-weight-bold text-dark mb-2">ประเภทการกรองพนักงาน (Filter Mode):</label>
                                    <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                        <label class="btn btn-outline-primary active btn-filter-type py-2" id="lbl_mode_all">
                                            <input type="radio" name="filter_type" id="mode_all" value="all" checked> <i class="fas fa-users me-1"></i> ทั้งหมด (All)
                                        </label>
                                        <label class="btn btn-outline-primary btn-filter-type py-2" id="lbl_mode_sale">
                                            <input type="radio" name="filter_type" id="mode_sale" value="sale"> <i class="fas fa-user-tie me-1"></i> พนักงานขาย (Sale)
                                        </label>
                                        <label class="btn btn-outline-primary btn-filter-type py-2" id="lbl_mode_take">
                                            <input type="radio" name="filter_type" id="mode_take" value="take"> <i class="fas fa-handshake me-1"></i> พนักงาน Take (Take)
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <!-- Sale / Take Name Dropdown -->
                                <div class="col-md-4 mb-3">
                                    <label for="filter_name" class="font-weight-bold text-dark" id="label_name">เลือกชื่อ Sale / Take:</label>
                                    <select class="form-control" id="filter_name" name="name" style="width: 100%;">
                                        <option value="-">-- ทั้งหมด --</option>
                                        <?php foreach ($SaleTakeAllRecords as $st): ?>
                                            <option value="<?php echo htmlentities($st['name']); ?>"><?php echo htmlentities($st['name']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Year Selector -->
                                <div class="col-md-2 mb-3">
                                    <label for="filter_year" class="font-weight-bold text-dark">ปี (DI_YEAR):</label>
                                    <select class="form-control" id="filter_year" name="year">
                                        <option value="-">-- ทุกปี --</option>
                                        <?php foreach ($YearRecords as $y): ?>
                                            <option value="<?php echo $y['DI_YEAR']; ?>"><?php echo $y['DI_YEAR']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Month Selector -->
                                <div class="col-md-2 mb-3">
                                    <label for="filter_month" class="font-weight-bold text-dark">เดือน (DI_MONTH):</label>
                                    <select class="form-control" id="filter_month" name="month">
                                        <option value="-">-- ทุกเดือน --</option>
                                        <?php foreach ($MonthRecords as $m): ?>
                                            <option value="<?php echo $m['month_id']; ?>"><?php echo $m['month_name']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>

                                <!-- Top Limit -->
                                <div class="col-md-2 mb-3">
                                    <label for="filter_top_limit" class="font-weight-bold text-dark">แสดงอันดับสูงสุด:</label>
                                    <select class="form-control" id="filter_top_limit" name="top_limit">
                                        <option value="10" selected>Top 10 ร้านค้า</option>
                                        <option value="20">Top 20 ร้านค้า</option>
                                        <option value="50">Top 50 ร้านค้า</option>
                                        <option value="100">Top 100 ร้านค้า</option>
                                        <option value="0">ร้านค้าทั้งหมด</option>
                                    </select>
                                </div>

                                <!-- Category Filter -->
                                <div class="col-md-2 mb-3">
                                    <label for="filter_sku_cat" class="font-weight-bold text-dark">หมวดสินค้า (SKU_CAT):</label>
                                    <select class="form-control" id="filter_sku_cat" name="sku_cat">
                                        <option value="-">-- ทุกหมวดหมู่ --</option>
                                        <?php foreach ($SkuCatRecords as $cat): ?>
                                            <option value="<?php echo $cat['SKU_CAT']; ?>"><?php echo $cat['SKU_CAT']; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-end flex-wrap gap-2 mt-2">
                                <button type="button" class="btn btn-secondary px-3 me-2 mb-2" id="btnReset">
                                    <i class="fas fa-undo me-1"></i> ล้างตัวกรอง
                                </button>
                                <button type="button" class="btn btn-info px-3 me-2 mb-2 text-white" id="btnExportMatrix">
                                    <i class="fas fa-cubes me-1"></i> Export 20 SKU แยกตามร้านค้า
                                </button>
                                <button type="button" class="btn btn-success px-3 me-2 mb-2" id="btnExport">
                                    <i class="fas fa-file-excel me-1"></i> Export สรุปร้านค้า
                                </button>
                                <button type="button" class="btn btn-primary px-4 mb-2" id="btnSearch">
                                    <i class="fas fa-search me-1"></i> ค้นหาข้อมูล
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- KPI Summary Cards Row -->
                <div class="row mb-4">
                    <!-- Grand Total Sales -->
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card stat-card blue h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="stat-title">ยอดขายรวมทั้งหมด</div>
                                        <div class="stat-value" id="kpi_total_amount">฿0.00</div>
                                        <div class="stat-sub mt-1" id="kpi_total_qty"><i class="fas fa-box me-1"></i>0 ชิ้น</div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="stat-icon"><i class="fas fa-coins"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Total Stores -->
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card stat-card green h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="stat-title">จำนวนร้านค้าที่มีการซื้อ</div>
                                        <div class="stat-value" id="kpi_total_stores">0</div>
                                        <div class="stat-sub mt-1"><i class="fas fa-store me-1"></i>ร้านค้าทั้งหมดตามตัวกรอง</div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="stat-icon"><i class="fas fa-store-alt"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Top Store #1 -->
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card stat-card orange h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="stat-title">ร้านค้ายอดขายอันดับ 1 🥇</div>
                                        <div class="stat-value text-truncate" id="kpi_top_store_name" style="max-width: 180px;" title="-">-</div>
                                        <div class="stat-sub mt-1 text-success font-weight-bold" id="kpi_top_store_amount">฿0.00</div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="stat-icon"><i class="fas fa-crown"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Average per Store -->
                    <div class="col-xl-3 col-md-6 mb-3">
                        <div class="card stat-card purple h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="stat-title">ยอดขายเฉลี่ยต่อร้านค้า</div>
                                        <div class="stat-value" id="kpi_avg_amount">฿0.00</div>
                                        <div class="stat-sub mt-1"><i class="fas fa-chart-line me-1"></i>ค่าเฉลี่ยต่อราย</div>
                                    </div>
                                    <div class="col-auto">
                                        <div class="stat-icon"><i class="fas fa-calculator"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Section -->
                <div class="row">
                    <!-- Top 10 Bar Chart -->
                    <div class="col-xl-8 col-lg-7">
                        <div class="card chart-card">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-chart-bar me-2"></i> กราฟ 10 อันดับร้านค้าที่มียอดขายสูงสุด (Top 10 Stores)</h6>
                            </div>
                            <div class="card-body">
                                <div id="topStoreBarChart" style="min-height: 350px;"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Category Donut Chart -->
                    <div class="col-xl-4 col-lg-5">
                        <div class="card chart-card">
                            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-chart-pie me-2"></i> สัดส่วนยอดขายตามหมวดสินค้า (SKU Category)</h6>
                            </div>
                            <div class="card-body">
                                <div id="catDonutChart" style="min-height: 350px;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Table Section -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card chart-card">
                            <div class="card-header py-3 d-flex align-items-center justify-content-between">
                                <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-list-ol me-2"></i> ตารางตารางสรุปอันดับร้านค้ายอดขายสูงสุด</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover align-middle" id="topStoreTable" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th class="text-center" width="5%">อันดับ</th>
                                                <th width="20%">ชื่อร้านค้า (AR_NAME)</th>
                                                <th width="12%">Sale ที่ดูแล</th>
                                                <th width="12%">Take ที่ดูแล</th>
                                                <th class="text-center" width="8%">จำนวนเอกสาร</th>
                                                <th class="text-right" width="10%">จำนวนสินค้า (ชิ้น)</th>
                                                <th class="text-right" width="13%">ยอดขายรวม (บาท)</th>
                                                <th width="12%">สัดส่วนยอดขาย (%)</th>
                                                <th class="text-center" width="8%">รายละเอียด (20 SKU)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Loaded via AJAX -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <?php include('includes/Modal-Logout.php'); ?>
        <?php include('includes/Footer.php'); ?>
    </div>
</div>

<!-- Store Details Breakdown & Top 20 SKUs Modal -->
<div class="modal fade" id="storeDetailModal" tabindex="-1" role="dialog" aria-labelledby="storeDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title font-weight-bold" id="storeDetailModalLabel">
                    <i class="fas fa-store me-2"></i> รายละเอียดร้านค้า: <span id="modalStoreName" class="text-warning"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <!-- Summary Header Cards -->
                <div class="row mb-3">
                    <div class="col-md-3 mb-2">
                        <div class="p-2 border rounded bg-light">
                            <span class="text-muted d-block small">จำนวนบิลเอกสาร:</span>
                            <span class="h6 font-weight-bold text-dark" id="modalTotalItems">0 รายการ</span>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="p-2 border rounded bg-light">
                            <span class="text-muted d-block small">จำนวน SKU ยางที่ซื้อ:</span>
                            <span class="h6 font-weight-bold text-primary" id="modalTotalSkuCount">0 SKU</span>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="p-2 border rounded bg-light">
                            <span class="text-muted d-block small">จำนวนสินค้ารวม (ชิ้น):</span>
                            <span class="h6 font-weight-bold text-info" id="modalTotalQty">0 ชิ้น</span>
                        </div>
                    </div>
                    <div class="col-md-3 mb-2">
                        <div class="p-2 border rounded bg-light">
                            <span class="text-muted d-block small">ยอดขายรวมทั้งหมด:</span>
                            <span class="h6 font-weight-bold text-success" id="modalTotalAmount">฿0.00</span>
                        </div>
                    </div>
                </div>

                <!-- Navigation Tabs inside Modal -->
                <ul class="nav nav-tabs mb-3" id="storeModalTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="top-sku-tab" data-toggle="tab" href="#topSkuTabContent" role="tab" aria-controls="topSkuTabContent" aria-selected="true">
                            <i class="fas fa-award text-warning me-1"></i> 20 อันดับ SKU ยางที่ซื้อสูงสุด (Top 20 Tire SKUs)
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="all-doc-tab" data-toggle="tab" href="#allDocTabContent" role="tab" aria-controls="allDocTabContent" aria-selected="false">
                            <i class="fas fa-file-invoice-dollar text-primary me-1"></i> รายการบิลเอกสารทั้งหมด (All Transactions)
                        </a>
                    </li>
                </ul>

                <div class="tab-content" id="storeModalTabContent">
                    <!-- Tab 1: Top 20 SKUs Breakdown -->
                    <div class="tab-pane fade show active" id="topSkuTabContent" role="tabpanel" aria-labelledby="top-sku-tab">
                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <div class="card border-0 bg-light p-2 mb-3">
                                    <h6 class="font-weight-bold text-primary mb-2"><i class="fas fa-chart-bar me-1"></i> กราฟ 20 อันดับ SKU ยางที่มียอดขายสูงสุดของร้านค้านี้</h6>
                                    <div id="modalSkuBarChart" style="min-height: 280px;"></div>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered table-sm align-middle" id="storeTopSkuTable" width="100%">
                                        <thead>
                                            <tr class="bg-dark text-white">
                                                <th class="text-center" width="5%">#</th>
                                                <th>รหัส SKU</th>
                                                <th>ชื่อยาง / รายการสินค้า (SKU Name)</th>
                                                <th>แบรนด์</th>
                                                <th>หมวดหมู่</th>
                                                <th class="text-right">จำนวนสั่งซื้อ (ชิ้น)</th>
                                                <th class="text-right">จำนวนแถม (ชิ้น)</th>
                                                <th class="text-right">ยอดขายรวม (บาท)</th>
                                                <th width="15%">สัดส่วนยอดขาย (%)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Loaded via AJAX -->
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tab 2: All Document Transactions -->
                    <div class="tab-pane fade" id="allDocTabContent" role="tabpanel" aria-labelledby="all-doc-tab">
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered table-sm align-middle" id="storeDetailTable" width="100%">
                                <thead>
                                    <tr class="bg-dark text-white">
                                        <th class="text-center">วันที่</th>
                                        <th>เลขที่เอกสาร (DI_REF)</th>
                                        <th>รหัสสินค้า</th>
                                        <th>ชื่อสินค้า</th>
                                        <th>แบรนด์</th>
                                        <th>หมวดหมู่</th>
                                        <th>Sale</th>
                                        <th>Take</th>
                                        <th class="text-right">จำนวน</th>
                                        <th class="text-right">แถม</th>
                                        <th class="text-right">ยอดเงิน (บาท)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิดหน้าต่าง</button>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/myadmin.min.js"></script>

<script>
    let barChartInstance = null;
    let donutChartInstance = null;
    let modalSkuChartInstance = null;
    let dataTableInstance = null;
    let detailTableInstance = null;
    let topSkuTableInstance = null;

    $(document).ready(function () {
        // Initialize Select2 on Name filter
        $('#filter_name').select2({
            placeholder: "-- ทั้งหมด --",
            allowClear: true,
            width: '100%'
        });

        // Mode Switch logic (All vs Sale vs Take)
        $('input[name="filter_type"]').change(function () {
            let mode = $(this).val();
            updateLabelAndOptions(mode);
        });

        // Initial Load of Sale/Take options
        updateLabelAndOptions('all');

        // Initial Search
        loadTopStoreData();

        // Button Listeners
        $('#btnSearch').click(function () {
            loadTopStoreData();
        });

        $('#btnReset').click(function () {
            $('#filterForm')[0].reset();
            $('#mode_all').prop('checked', true).trigger('change');
            $('#filter_year').val('-');
            $('#filter_month').val('-');
            $('#filter_top_limit').val('10');
            $('#filter_sku_cat').val('-');
            loadTopStoreData();
        });

        $('#btnExport').click(function () {
            let filter_type = $('input[name="filter_type"]:checked').val();
            let name = $('#filter_name').val() || '-';
            let year = $('#filter_year').val();
            let month = $('#filter_month').val();
            let top_limit = $('#filter_top_limit').val();
            let sku_cat = $('#filter_sku_cat').val();

            let exportUrl = 'export_process/export_top_store_sales_excel.php?' +
                'filter_type=' + encodeURIComponent(filter_type) +
                '&name=' + encodeURIComponent(name) +
                '&year=' + encodeURIComponent(year) +
                '&month=' + encodeURIComponent(month) +
                '&top_limit=' + encodeURIComponent(top_limit) +
                '&sku_cat=' + encodeURIComponent(sku_cat);

            window.open(exportUrl, '_blank');
        });

        $('#btnExportMatrix').click(function () {
            let filter_type = $('input[name="filter_type"]:checked').val();
            let name = $('#filter_name').val() || '-';
            let year = $('#filter_year').val();
            let month = $('#filter_month').val();
            let top_limit = $('#filter_top_limit').val();
            let sku_cat = $('#filter_sku_cat').val();

            let exportUrl = 'export_process/export_store_sku_matrix_excel.php?' +
                'filter_type=' + encodeURIComponent(filter_type) +
                '&name=' + encodeURIComponent(name) +
                '&year=' + encodeURIComponent(year) +
                '&month=' + encodeURIComponent(month) +
                '&top_limit=' + encodeURIComponent(top_limit) +
                '&sku_cat=' + encodeURIComponent(sku_cat);

            window.open(exportUrl, '_blank');
        });
    });

    // Dynamic dropdown loader based on Filter Mode
    function updateLabelAndOptions(mode) {
        let labelText = "เลือกชื่อ Sale / Take:";
        let placeholderText = "-- ทั้งหมด --";
        if (mode === 'sale') {
            labelText = "เลือกชื่อพนักงานขาย (Sale):";
            placeholderText = "ค้นหาชื่อ Sale";
        } else if (mode === 'take') {
            labelText = "เลือกชื่อพนักงาน Take (Take):";
            placeholderText = "ค้นหาชื่อ Take";
        }
        $('#label_name').text(labelText);

        let select = $('#filter_name');

        $.ajax({
            url: 'model/get_sale_take_list.php',
            type: 'POST',
            data: { filter_type: mode },
            dataType: 'json',
            success: function (res) {
                if (select.hasClass("select2-hidden-accessible")) {
                    select.select2('destroy');
                }

                select.empty();
                select.append($('<option>', { value: '-', text: '-- ทั้งหมด --' }));

                if (res.status === 'success' && res.data) {
                    $.each(res.data, function (i, item) {
                        select.append($('<option>', {
                            value: item.id,
                            text: item.text
                        }));
                    });
                }

                select.select2({
                    placeholder: placeholderText,
                    allowClear: true,
                    width: '100%'
                });
                select.val('-').trigger('change');
            },
            error: function(err) {
                console.error("Error loading sale/take list:", err);
            }
        });
    }

    // Main AJAX function to fetch Top Stores
    function loadTopStoreData() {
        let filter_type = $('input[name="filter_type"]:checked').val();
        let name = $('#filter_name').val();
        let year = $('#filter_year').val();
        let month = $('#filter_month').val();
        let top_limit = $('#filter_top_limit').val();
        let sku_cat = $('#filter_sku_cat').val();

        // Update Active Filter Badge
        let badgeText = "โหมด: " + (filter_type === 'sale' ? 'Sale' : (filter_type === 'take' ? 'Take' : 'ทั้งหมด'));
        if (name && name !== '-') badgeText += " | " + name;
        if (year && year !== '-') badgeText += " | ปี " + year;
        if (month && month !== '-') badgeText += " | เดือน " + $('#filter_month option:selected').text();
        $('#activeFilterBadge').text(badgeText);

        $.ajax({
            url: 'model/fetch_top_store_sales.php',
            type: 'POST',
            data: {
                filter_type: filter_type,
                name: name,
                year: year,
                month: month,
                top_limit: top_limit,
                sku_cat: sku_cat
            },
            dataType: 'json',
            beforeSend: function () {
                $('#kpi_total_amount').text('กำลังโหลด...');
                $('#kpi_total_stores').text('...');
            },
            success: function (res) {
                if (res.status === 'success') {
                    renderKPIs(res.summary);
                    renderCharts(res.data, res.summary.cat_totals);
                    renderTable(res.data);
                } else {
                    alertify.error(res.message || 'เกิดข้อผิดพลาดในการโหลดข้อมูล');
                }
            },
            error: function (err) {
                console.error(err);
                alertify.error('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
            }
        });
    }

    // Render KPI Cards
    function renderKPIs(summary) {
        $('#kpi_total_amount').text('฿' + formatCurrency(summary.grand_total_amount));
        $('#kpi_total_qty').html('<i class="fas fa-box me-1"></i>' + formatNumber(summary.grand_total_qty) + ' ชิ้น');
        $('#kpi_total_stores').text(formatNumber(summary.total_stores) + ' ร้าน');
        
        if (summary.top_1_store && summary.top_1_store.name !== '-') {
            $('#kpi_top_store_name').text(summary.top_1_store.name).attr('title', summary.top_1_store.name);
            $('#kpi_top_store_amount').text('฿' + formatCurrency(summary.top_1_store.amount));
        } else {
            $('#kpi_top_store_name').text('-');
            $('#kpi_top_store_amount').text('฿0.00');
        }

        $('#kpi_avg_amount').text('฿' + formatCurrency(summary.avg_amount));
    }

    // Render Bar Chart & Donut Chart
    function renderCharts(storesData, catTotals) {
        // Prepare Top 10 for Bar Chart
        let top10 = storesData.slice(0, 10);
        let categories = top10.map(item => item.AR_NAME.length > 22 ? item.AR_NAME.substring(0, 22) + '...' : item.AR_NAME);
        let seriesData = top10.map(item => item.TOTAL_AMOUNT);

        // Bar Chart Options
        let barOptions = {
            series: [{
                name: 'ยอดขายรวม (บาท)',
                data: seriesData
            }],
            chart: {
                type: 'bar',
                height: 360,
                toolbar: { show: false }
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 6,
                    barHeight: '60%',
                    distributed: true
                }
            },
            colors: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#6f42c1', '#fd7e14', '#20c997', '#6c757d', '#5a5c69'],
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    return '฿' + formatCompact(val);
                },
                style: { fontSize: '11px', colors: ['#fff'] }
            },
            xaxis: {
                categories: categories,
                labels: {
                    formatter: function (val) {
                        return '฿' + formatCompact(val);
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return '฿' + formatCurrency(val);
                    }
                }
            },
            legend: { show: false }
        };

        if (barChartInstance) barChartInstance.destroy();
        barChartInstance = new ApexCharts(document.querySelector("#topStoreBarChart"), barOptions);
        barChartInstance.render();

        // Donut Chart Options
        let donutLabels = [];
        let donutSeries = [];
        $.each(catTotals, function (cat, val) {
            if (val > 0) {
                donutLabels.push(cat);
                donutSeries.push(val);
            }
        });

        if (donutSeries.length === 0) {
            donutLabels = ['ไม่มีข้อมูล'];
            donutSeries = [1];
        }

        let donutOptions = {
            series: donutSeries,
            labels: donutLabels,
            chart: {
                type: 'donut',
                height: 360
            },
            colors: ['#4e73df', '#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b', '#6f42c1'],
            plotOptions: {
                pie: {
                    donut: {
                        size: '65%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'ยอดขายรวม',
                                formatter: function (w) {
                                    let sum = w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                                    return '฿' + formatCompact(sum);
                                }
                            }
                        }
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return '฿' + formatCurrency(val);
                    }
                }
            },
            legend: {
                position: 'bottom'
            }
        };

        if (donutChartInstance) donutChartInstance.destroy();
        donutChartInstance = new ApexCharts(document.querySelector("#catDonutChart"), donutOptions);
        donutChartInstance.render();
    }

    // Render Data Table
    function renderTable(data) {
        if ($.fn.DataTable.isDataTable('#topStoreTable')) {
            $('#topStoreTable').DataTable().destroy();
        }

        let tbody = '';
        $.each(data, function (idx, item) {
            let rankClass = 'rank-normal';
            let rankBadge = item.rank;
            if (item.rank === 1) { rankClass = 'rank-1'; rankBadge = '🥇 1'; }
            else if (item.rank === 2) { rankClass = 'rank-2'; rankBadge = '🥈 2'; }
            else if (item.rank === 3) { rankClass = 'rank-3'; rankBadge = '🥉 3'; }

            tbody += '<tr>';
            tbody += '<td class="text-center"><span class="rank-badge ' + rankClass + '">' + rankBadge + '</span></td>';
            tbody += '<td><div class="font-weight-bold text-dark">' + escapeHtml(item.AR_NAME) + '</div><small class="text-muted">รหัส: ' + escapeHtml(item.AR_CODE) + '</small></td>';
            tbody += '<td><span class="badge badge-light border text-dark">' + escapeHtml(item.SALE_NAMES) + '</span></td>';
            tbody += '<td><span class="badge badge-light border text-info">' + escapeHtml(item.TAKE_NAMES) + '</span></td>';
            tbody += '<td class="text-center font-weight-bold">' + formatNumber(item.TOTAL_DOCS) + '</td>';
            tbody += '<td class="text-right font-weight-bold text-info">' + formatNumber(item.TOTAL_QTY) + '</td>';
            tbody += '<td class="text-right font-weight-bold text-success">฿' + formatCurrency(item.TOTAL_AMOUNT) + '</td>';
            tbody += '<td>' +
                '<div class="d-flex align-items-center">' +
                '<span class="me-2 font-weight-bold mr-2" style="width:45px;">' + item.SHARE_PERCENT.toFixed(1) + '%</span>' +
                '<div class="progress progress-share flex-grow-1 mr-2">' +
                '<div class="progress-bar" style="width: ' + Math.min(item.SHARE_PERCENT, 100) + '%"></div>' +
                '</div>' +
                '</div>' +
                '</td>';
            tbody += '<td class="text-center">' +
                '<button type="button" class="btn btn-sm btn-outline-primary btn-detail px-2 py-1" data-name="' + escapeHtml(item.AR_NAME) + '" title="ดู 20 อันดับ SKU ยาง">' +
                '<i class="fas fa-list-alt me-1"></i> 20 SKU' +
                '</button>' +
                '</td>';
            tbody += '</tr>';
        });

        $('#topStoreTable tbody').html(tbody);

        dataTableInstance = $('#topStoreTable').DataTable({
            "language": {
                "search": "ค้นหาในตาราง:",
                "lengthMenu": "แสดง _MENU_ แถวต่อหน้า",
                "info": "แสดงรายการ _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                "infoEmpty": "ไม่พบข้อมูล",
                "paginate": {
                    "first": "หน้าแรก",
                    "last": "หน้าสุดท้าย",
                    "next": "ถัดไป",
                    "previous": "ก่อนหน้า"
                }
            },
            "pageLength": 10,
            "order": [[0, "asc"]],
            "responsive": true
        });

        // Bind click event on detail buttons
        $('#topStoreTable').off('click', '.btn-detail').on('click', '.btn-detail', function () {
            let ar_name = $(this).data('name');
            showStoreDetails(ar_name);
        });
    }

    // Show Store Detail Modal (Top 20 SKUs + All Documents)
    function showStoreDetails(ar_name) {
        let filter_type = $('input[name="filter_type"]:checked').val();
        let name = $('#filter_name').val();
        let year = $('#filter_year').val();
        let month = $('#filter_month').val();
        let sku_cat = $('#filter_sku_cat').val();

        $('#modalStoreName').text(ar_name);
        $('#modalTotalItems').text('กำลังโหลด...');
        $('#modalTotalSkuCount').text('...');
        $('#modalTotalQty').text('...');
        $('#modalTotalAmount').text('...');

        if ($.fn.DataTable.isDataTable('#storeTopSkuTable')) {
            $('#storeTopSkuTable').DataTable().destroy();
        }
        if ($.fn.DataTable.isDataTable('#storeDetailTable')) {
            $('#storeDetailTable').DataTable().destroy();
        }

        $('#storeTopSkuTable tbody').html('<tr><td colspan="9" class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><br>กำลังคำนวณ Top 20 SKUs...</td></tr>');
        $('#storeDetailTable tbody').html('<tr><td colspan="11" class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><br>กำลังดึงข้อมูลบิลทั้งหมด...</td></tr>');

        $('#top-sku-tab').tab('show');
        $('#storeDetailModal').modal('show');

        $.ajax({
            url: 'model/fetch_store_sales_detail.php',
            type: 'POST',
            data: {
                ar_name: ar_name,
                filter_type: filter_type,
                name: name,
                year: year,
                month: month,
                sku_cat: sku_cat
            },
            dataType: 'json',
            success: function (res) {
                if (res.status === 'success') {
                    $('#modalTotalItems').text(formatNumber(res.summary.total_items) + ' บิล');
                    $('#modalTotalSkuCount').text(formatNumber(res.summary.total_sku_count) + ' SKU');
                    $('#modalTotalQty').text(formatNumber(res.summary.total_qty) + ' ชิ้น');
                    $('#modalTotalAmount').text('฿' + formatCurrency(res.summary.total_amount));

                    // Render Top 20 SKUs Table & Chart
                    renderTop20Skus(res.top_20_skus);

                    // Render All Document Transactions Table
                    renderAllDocTransactions(res.data);
                } else {
                    $('#storeTopSkuTable tbody').html('<tr><td colspan="9" class="text-center text-danger py-3">' + escapeHtml(res.message) + '</td></tr>');
                    $('#storeDetailTable tbody').html('<tr><td colspan="11" class="text-center text-danger py-3">' + escapeHtml(res.message) + '</td></tr>');
                }
            },
            error: function () {
                $('#storeTopSkuTable tbody').html('<tr><td colspan="9" class="text-center text-danger py-3">เกิดข้อผิดพลาดในการโหลดข้อมูล</td></tr>');
                $('#storeDetailTable tbody').html('<tr><td colspan="11" class="text-center text-danger py-3">เกิดข้อผิดพลาดในการโหลดข้อมูล</td></tr>');
            }
        });
    }

    // Render Top 20 SKUs Table & Chart inside Modal
    function renderTop20Skus(topSkus) {
        let tbody = '';
        let categories = [];
        let seriesData = [];

        $.each(topSkus, function (i, item) {
            let rClass = 'rank-normal';
            if (item.rank === 1) rClass = 'rank-1';
            else if (item.rank === 2) rClass = 'rank-2';
            else if (item.rank === 3) rClass = 'rank-3';

            tbody += '<tr>';
            tbody += '<td class="text-center"><span class="rank-badge ' + rClass + '">' + item.rank + '</span></td>';
            tbody += '<td class="font-weight-bold text-dark">' + escapeHtml(item.SKU_CODE) + '</td>';
            tbody += '<td>' + escapeHtml(item.SKU_NAME) + '</td>';
            tbody += '<td>' + escapeHtml(item.BRAND) + '</td>';
            tbody += '<td><span class="badge badge-info">' + escapeHtml(item.SKU_CAT) + '</span></td>';
            tbody += '<td class="text-right font-weight-bold text-primary">' + formatNumber(item.TOTAL_QTY) + '</td>';
            tbody += '<td class="text-right text-muted">' + formatNumber(item.TOTAL_FREE) + '</td>';
            tbody += '<td class="text-right font-weight-bold text-success">฿' + formatCurrency(item.TOTAL_AMOUNT) + '</td>';
            tbody += '<td>' +
                '<div class="d-flex align-items-center">' +
                '<span class="me-2 font-weight-bold mr-2" style="width:45px;">' + item.SHARE_PERCENT.toFixed(1) + '%</span>' +
                '<div class="progress progress-share flex-grow-1 mr-2">' +
                '<div class="progress-bar bg-success" style="width: ' + Math.min(item.SHARE_PERCENT, 100) + '%"></div>' +
                '</div>' +
                '</div>' +
                '</td>';
            tbody += '</tr>';

            // Top 10 for Modal Bar Chart
            if (i < 10) {
                let skuLabel = (item.SKU_NAME.length > 25 ? item.SKU_NAME.substring(0, 25) + '...' : item.SKU_NAME);
                categories.push(skuLabel);
                seriesData.push(item.TOTAL_AMOUNT);
            }
        });

        if (topSkus.length === 0) {
            tbody = '<tr><td colspan="9" class="text-center py-3 text-muted">ไม่พบข้อมูล SKU</td></tr>';
        }

        $('#storeTopSkuTable tbody').html(tbody);

        if (topSkus.length > 0) {
            topSkuTableInstance = $('#storeTopSkuTable').DataTable({
                "pageLength": 10,
                "order": [[0, "asc"]],
                "language": {
                    "search": "ค้นหา SKU:",
                    "paginate": { "next": "ถัดไป", "previous": "ก่อนหน้า" }
                }
            });
        }

        // Render Horizontal Bar Chart for Modal Top SKUs
        let skuChartOptions = {
            series: [{
                name: 'ยอดขายรวม (บาท)',
                data: seriesData
            }],
            chart: {
                type: 'bar',
                height: 280,
                toolbar: { show: false }
            },
            plotOptions: {
                bar: {
                    horizontal: true,
                    borderRadius: 4,
                    barHeight: '55%',
                    distributed: true
                }
            },
            colors: ['#1cc88a', '#4e73df', '#36b9cc', '#f6c23e', '#e74a3b', '#6f42c1', '#fd7e14', '#20c997', '#6c757d', '#5a5c69'],
            dataLabels: {
                enabled: true,
                formatter: function (val) {
                    return '฿' + formatCompact(val);
                },
                style: { fontSize: '10px', colors: ['#fff'] }
            },
            xaxis: {
                categories: categories,
                labels: {
                    formatter: function (val) {
                        return '฿' + formatCompact(val);
                    }
                }
            },
            legend: { show: false }
        };

        if (modalSkuChartInstance) modalSkuChartInstance.destroy();
        modalSkuChartInstance = new ApexCharts(document.querySelector("#modalSkuBarChart"), skuChartOptions);
        modalSkuChartInstance.render();
    }

    // Render All Document Transactions Table
    function renderAllDocTransactions(docRows) {
        let tbody = '';
        $.each(docRows, function (i, item) {
            tbody += '<tr>';
            tbody += '<td class="text-center">' + escapeHtml(item.DI_DATE || '-') + '</td>';
            tbody += '<td>' + escapeHtml(item.DI_REF || '-') + '</td>';
            tbody += '<td>' + escapeHtml(item.SKU_CODE || '-') + '</td>';
            tbody += '<td>' + escapeHtml(item.SKU_NAME || '-') + '</td>';
            tbody += '<td>' + escapeHtml(item.BRAND || '-') + '</td>';
            tbody += '<td><span class="badge badge-info">' + escapeHtml(item.SKU_CAT || '-') + '</span></td>';
            tbody += '<td>' + escapeHtml(item.SALE_NAME || '-') + '</td>';
            tbody += '<td>' + escapeHtml(item.TAKE_NAME || '-') + '</td>';
            tbody += '<td class="text-right font-weight-bold">' + formatNumber(item.TRD_QTY) + '</td>';
            tbody += '<td class="text-right text-muted">' + formatNumber(item.TRD_Q_FREE) + '</td>';
            tbody += '<td class="text-right font-weight-bold text-success">฿' + formatCurrency(item.TRD_AMOUNT_PRICE) + '</td>';
            tbody += '</tr>';
        });

        if (docRows.length === 0) {
            tbody = '<tr><td colspan="11" class="text-center py-3 text-muted">ไม่พบข้อมูลบิล</td></tr>';
        }

        $('#storeDetailTable tbody').html(tbody);

        if (docRows.length > 0) {
            detailTableInstance = $('#storeDetailTable').DataTable({
                "pageLength": 10,
                "order": [[0, "desc"]],
                "language": {
                    "search": "ค้นหาบิล:",
                    "paginate": { "next": "ถัดไป", "previous": "ก่อนหน้า" }
                }
            });
        }
    }

    // Helper Utility Functions
    function formatCurrency(num) {
        return parseFloat(num || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function formatNumber(num) {
        return parseFloat(num || 0).toLocaleString('en-US');
    }

    function formatCompact(num) {
        if (num >= 1000000) return (num / 1000000).toFixed(1) + 'M';
        if (num >= 1000) return (num / 1000).toFixed(1) + 'K';
        return num.toFixed(0);
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
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
