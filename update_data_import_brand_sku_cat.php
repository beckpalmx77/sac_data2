<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin'] ?? '') == "") {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <style>
        .table-custom-header th {
            background-color: #0f172a !important;
            color: #ffffff !important;
            text-align: center;
            vertical-align: middle;
        }
        .badge-brand {
            background-color: #e0f2fe;
            color: #0369a1;
            font-weight: 600;
            border: 1px solid #bae6fd;
            padding: 4px 8px;
            border-radius: 4px;
        }
        .badge-cat {
            background-color: #f0fdf4;
            color: #15803d;
            font-weight: 600;
            border: 1px solid #bbf7d0;
            padding: 4px 8px;
            border-radius: 4px;
        }
        .swal2-popup h2::before { display: none !important; content: none !important; }
    </style>
</head>
<body id="page-top">
<div id="wrapper">
    <?php include('includes/Side-Bar.php'); ?>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <?php include('includes/Top-Bar.php'); ?>
            <div class="container-fluid" id="container-wrapper">
                
                <!-- Page Breadcrumb -->
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h4 mb-0 text-gray-800">
                        <i class="fas fa-tags me-2" style="color: #0d9488;"></i> ปรับปรุง BRAND , SKU_CAT , DETAIL ตาม SKU_CODE
                    </h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?? 'dashboard.php'; ?>">Home</a></li>
                        <li class="breadcrumb-item">จัดการข้อมูลสินค้า</li>
                        <li class="breadcrumb-item active" aria-current="page">ปรับปรุง Brand & SKU_CAT</li>
                    </ol>
                </div>

                <div class="row">
                    <!-- Column for Form Upload -->
                    <div class="col-lg-12 mb-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-header py-3 text-white" style="background: linear-gradient(135deg, #0d9488 0%, #0f766e 100%);">
                                <h6 class="m-0 font-weight-bold text-white">
                                    <i class="fas fa-file-import me-1"></i> นำเข้าข้อมูลไฟล์ Excel (ปรับปรุง BRAND, SKU_CAT ตาม SKU_CODE ลงตาราง ims_data_sale_sac_all)
                                </h6>
                            </div>
                            <div class="card-body">
                                <form id="uploadForm" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label for="excelFile" class="form-label font-weight-bold text-dark">
                                            เลือกไฟล์ Excel (.xlsx, .xls) <span class="text-danger">*</span>
                                        </label>
                                        <input class="form-control" type="file" id="excelFile" name="excelFile" accept=".xlsx, .xls" required>
                                        <small class="text-muted mt-1 d-block">
                                            <i class="fas fa-info-circle me-1 text-info"></i> รองรับไฟล์ Excel ที่มี <strong>3 ฟิลด์หลัก (3 Columns)</strong>: คอลัมน์ A: <strong>รหัสสินค้า (SKU_CODE)</strong>, คอลัมน์ B: <strong>ยี่ห้อ (BRAND)</strong>, คอลัมน์ C: <strong>ประเภทสินค้า (SKU_CAT)</strong>
                                        </small>
                                    </div>
                                    <div class="mb-3 d-flex flex-wrap">
                                        <button type="submit" class="btn btn-primary px-3 mr-2 me-2 mb-2 shadow-sm">
                                            <i class="fas fa-upload mr-1 me-1"></i> Import ปรับปรุงข้อมูล
                                        </button>
                                        <button type="button" id="showImageBtn" class="btn btn-success px-3 mr-2 me-2 mb-2 shadow-sm">
                                            <i class="fas fa-file-excel mr-1 me-1"></i> ตัวอย่างไฟล์ Excel Format Data สำหรับนำเข้า Update
                                        </button>
                                        <a href="HOW_TO_UPDATE_BRAND.html" target="_blank" class="btn btn-outline-info font-weight-bold mr-2 me-2 mb-2 shadow-sm">
                                            <i class="fas fa-info-circle mr-1 me-1"></i> สรุปแนวคิดและขั้นตอนการทำงาน (HOW_TO_UPDATE_BRAND.html)
                                        </a>
                                    </div>
                                    <div class="mb-2">
                                        <div id="input_text" style="white-space: pre-wrap;" class="small text-muted font-weight-bold text-success"></div>
                                    </div>
                                </form>
                                <div id="spinner" class="text-center my-3" style="display: none;">
                                    <div class="spinner-border text-teal" style="color: #0d9488;" role="status">
                                        <span class="sr-only">กำลังโหลด...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Card -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-4 shadow-sm border-0">
                            <div class="card-header py-3 bg-light d-flex justify-content-between align-items-center">
                                <h6 class="m-0 font-weight-bold text-dark">
                                    <i class="fas fa-table me-1 text-teal" style="color: #0d9488;"></i> ข้อมูลสินค้า ยี่ห้อ และประเภทสินค้าล่าสุดในระบบ (ims_data_sale_sac_all)
                                </h6>
                                <button type="button" class="btn btn-sm btn-outline-secondary" onclick="$('#TableRecordList').DataTable().ajax.reload();">
                                    <i class="fas fa-sync-alt me-1"></i> รีเฟรชตาราง
                                </button>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="TableRecordList" class="table table-bordered table-striped table-hover w-100">
                                        <thead class="table-custom-header">
                                            <tr>
                                                <th style="width: 18%;">รหัสสินค้า (SKU_CODE)</th>
                                                <th>ชื่อสินค้า (SKU_NAME)</th>
                                                <th style="width: 15%;">ยี่ห้อ (BRAND)</th>
                                                <th style="width: 20%;">ประเภทสินค้า (SKU_CAT)</th>
                                                <th style="width: 18%;">วันที่อัปเดตล่าสุด</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <?php include('includes/Footer.php'); ?>
            </div>
        </div>
    </div>
</div>

<?php include('includes/Modal-Logout.php'); ?>

<!-- Scroll to top -->
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<!-- Vendor Scripts -->
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/myadmin.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- Datatables Scripts -->
<script src="vendor/datatables/v11/bootbox.min.js"></script>
<script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
<link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

<!-- Custom Style -->
<link href="css/spinner_over.css" rel="stylesheet"/>

<script>
    // Fetch last import status log
    function loadLastImportLog() {
        let formData = {screen_name: "update_brand_sku_cat", table_name: "log_import_data"};
        $.ajax({
            type: "POST",
            url: 'model/get_last_import.php',
            data: formData,
            success: function (response) {
                if (response && response.trim() !== '') {
                    $('#input_text').html('<i class="fas fa-history me-1"></i> ' + response);
                } else {
                    $('#input_text').html('<span class="text-muted"><i class="fas fa-clock me-1"></i> ยังไม่มีประวัติการนำเข้าในระบบ</span>');
                }
            },
            error: function (response) {
                console.error("error loading last import log: " + response);
            }
        });
    }

    $(document).ready(function () {
        loadLastImportLog();
    });
</script>

<script>
    $(document).ready(function () {
        let countdownInterval;

        $('#uploadForm').on('submit', function (e) {
            e.preventDefault();
            
            if (!$('#excelFile').val()) {
                Swal.fire('แจ้งเตือน', 'กรุณาเลือกไฟล์ Excel ก่อนนำเข้า', 'warning');
                return;
            }

            let formData = new FormData(this);
            let startTime = new Date();
            let startTimeStr = String(startTime.getHours()).padStart(2, '0') + ':' + 
                               String(startTime.getMinutes()).padStart(2, '0') + ':' + 
                               String(startTime.getSeconds()).padStart(2, '0');

            // Open SweetAlert Progress Dialog
            Swal.fire({
                title: 'กำลังนำเข้าและอัปเดตข้อมูล',
                html: `
                    <div class="mb-2 text-muted text-center" id="swalStatusText">กำลังเริ่มต้นเตรียมการ...</div>
                    <div class="progress" style="height: 25px; border-radius: 12px; overflow: hidden; background-color: #e9ecef; box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);">
                        <div id="swalProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-teal" role="progressbar" style="background-color: #0d9488; width: 0%; font-size: 14px; font-weight: bold; line-height: 25px; transition: width 0.4s ease;">0%</div>
                    </div>
                `,
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Simulate progress bar increment
            let progressWidth = 0;
            let progressInterval = setInterval(function() {
                if (progressWidth < 30) {
                    progressWidth += Math.random() * 8 + 4;
                    $('#swalStatusText').html('<i class="fas fa-spinner fa-spin me-2"></i>กำลังอัปโหลดไฟล์ Excel...');
                } else if (progressWidth < 75) {
                    progressWidth += Math.random() * 3 + 1;
                    $('#swalStatusText').html('<i class="fas fa-database me-2"></i>กำลังอ่านและปรับปรุงฐานข้อมูล ims_data_sale_sac_all...');
                } else if (progressWidth < 98) {
                    progressWidth += Math.random() * 0.5 + 0.1;
                    $('#swalStatusText').html('<i class="fas fa-sync fa-spin me-2 text-warning"></i>กำลังบันทึกและซิงค์ข้อมูล Brand & Category...');
                }
                progressWidth = Math.min(progressWidth, 98);
                $('#swalProgressBar').css('width', progressWidth.toFixed(0) + '%').text(progressWidth.toFixed(0) + '%');
            }, 300);

            $.ajax({
                url: 'import_process/update_exc_data_brand_sku_cat_process.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function (response) {
                    clearInterval(progressInterval);
                    let endTime = new Date();
                    let endTimeStr = String(endTime.getHours()).padStart(2, '0') + ':' + 
                                     String(endTime.getMinutes()).padStart(2, '0') + ':' + 
                                     String(endTime.getSeconds()).padStart(2, '0');
                    let durationSeconds = ((endTime - startTime) / 1000).toFixed(2);

                    if (response.success) {
                        $('#swalProgressBar').css('width', '100%').css('background-color', '#10b981').text('100%');
                        $('#swalStatusText').text('นำเข้าข้อมูลเรียบร้อย!');

                        let countdown = 8;
                        Swal.fire({
                            icon: 'success',
                            title: 'นำเข้าข้อมูลสำเร็จ',
                            html: `
                                <div class="text-start small p-3 bg-light border rounded mb-3" style="font-size: 0.88rem;">
                                    <p class="mb-1"><strong><i class="fas fa-file-excel me-1 text-success"></i> ไฟล์:</strong> ${response.file_name}</p>
                                    <p class="mb-1"><strong><i class="fas fa-list-ol me-1 text-primary"></i> จำนวนรายการที่ประมวลผล:</strong> ${response.count.toLocaleString()} รายการ</p>
                                    <p class="mb-1"><strong><i class="fas fa-check-circle me-1 text-success"></i> อัปเดตสำเร็จในระบบ:</strong> ${response.updated.toLocaleString()} รายการ</p>
                                    <p class="mb-1"><strong><i class="fas fa-database me-1 text-info"></i> ตารางและฟิลด์ที่อัปเดต:</strong></p>
                                    <div class="mb-2 ps-2 text-muted" style="font-size: 0.8rem; line-height: 1.5;">
                                        • <span class="badge bg-secondary">ims_data_sale_sac_all</span> : <code>BRAND</code>, <code>SKU_CAT</code>
                                    </div>
                                    <p class="mb-1"><strong><i class="fas fa-clock me-1 text-secondary"></i> เวลาเริ่มต้น:</strong> ${startTimeStr}</p>
                                    <p class="mb-1"><strong><i class="fas fa-clock me-1 text-secondary"></i> เวลาสิ้นสุด:</strong> ${endTimeStr}</p>
                                    <p class="mb-0"><strong><i class="fas fa-stopwatch me-1 text-warning"></i> เวลาที่ใช้:</strong> ${durationSeconds} วินาที</p>
                                </div>
                                <div class="alert alert-info py-2 mb-0 text-center" style="font-size: 0.85rem;">
                                    <i class="fas fa-info-circle me-1"></i> 
                                    ระบบจะรีเฟรชหน้าจอในอีก <span id="countdownText" class="fw-bold text-danger fs-6">${countdown}</span> วินาที...
                                </div>
                            `,
                            showCancelButton: true,
                            confirmButtonText: 'รีเฟรชทันที',
                            cancelButtonText: 'อยู่หน้านี้ต่อ',
                            allowOutsideClick: false,
                            didOpen: () => {
                                countdownInterval = setInterval(function() {
                                    countdown--;
                                    $('#countdownText').text(countdown);
                                    if (countdown <= 0) {
                                        clearInterval(countdownInterval);
                                        Swal.close();
                                        location.reload();
                                    }
                                }, 1000);
                            }
                        }).then((result) => {
                            clearInterval(countdownInterval);
                            if (result.isConfirmed) {
                                location.reload();
                            } else {
                                $('#uploadForm')[0].reset();
                                loadLastImportLog();
                                $('#TableRecordList').DataTable().ajax.reload();
                            }
                        });

                    } else {
                        Swal.fire('เกิดข้อผิดพลาด', response.message || 'ไม่สามารถอัปเดตข้อมูลได้', 'error');
                    }
                },
                error: function (xhr, status, error) {
                    clearInterval(progressInterval);
                    Swal.fire('เกิดข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้: ' + error, 'error');
                }
            });
        });

        // Initialize DataTable
        $('#TableRecordList').DataTable({
            "lengthMenu": [[10, 25, 50, 100, 200], [10, 25, 50, 100, 200]],
            "ajax": "model/fetch_data_brand_sku_cat.php",
            "order": [[4, 'desc']],
            "columns": [
                {
                    "data": "SKU_CODE",
                    "render": function(data) {
                        return '<span class="font-monospace font-weight-bold text-primary">' + (data || '-') + '</span>';
                    }
                },
                {
                    "data": "SKU_NAME",
                    "render": function(data) {
                        return data || '-';
                    }
                },
                {
                    "data": "BRAND",
                    "render": function(data) {
                        return data ? '<span class="badge-brand">' + data + '</span>' : '<span class="text-muted">-</span>';
                    }
                },
                {
                    "data": "SKU_CAT",
                    "render": function(data) {
                        return data ? '<span class="badge-cat">' + data + '</span>' : '<span class="text-muted">-</span>';
                    }
                },
                {
                    "data": "update_date",
                    "render": function(data) {
                        return data ? '<small class="text-muted">' + data + '</small>' : '<small class="text-muted">-</small>';
                    }
                }
            ],
            "language": {
                "search": "ค้นหาข้อมูล:",
                "lengthMenu": "แสดง _MENU_ รายการต่อหน้า",
                "info": "แสดง _START_ ถึง _END_ จากทั้งหมด _TOTAL_ รายการ",
                "infoEmpty": "ไม่มีข้อมูลแสดง",
                "infoFiltered": "(กรองจากทั้งหมด _MAX_ รายการ)",
                "paginate": {
                    "first": "หน้าแรก",
                    "last": "หน้าสุดท้าย",
                    "next": "ถัดไป",
                    "previous": "ก่อนหน้า"
                },
                "zeroRecords": "ไม่พบข้อมูลที่ตรงกัน"
            }
        });

        // Open Template Download
        $('#showImageBtn').on('click', function () {
            window.open("import_process/template/import_brand_sku_cat_template.xlsx", "_blank");
        });
    });
</script>

</body>
</html>
