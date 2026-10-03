<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['sale_name_id']) == "") {
    header("Location: index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<body id="page-top">
<div id="wrapper">
    <?php include('includes/Side-Bar.php'); ?>

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <?php include('includes/Top-Bar.php'); ?>
            <div class="container-fluid" id="container-wrapper">
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h4 mb-0 text-gray-800"><?php echo urldecode($_GET['s']) ?></h1>
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a></li>
                        <li class="breadcrumb-item"><?php echo urldecode($_GET['m']) ?></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo urldecode($_GET['s']) ?></li>
                    </ol>
                </div>

                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-12">
                            <div class="card-body">
                                <section class="container-fluid">
                                    <form id="uploadForm" enctype="multipart/form-data">
                                        <div id="importFormContainer">
                                            <div class="mb-3">
                                                <label for="excelFile" class="form-label">Select Excel File</label>
                                                <input class="form-control" type="file" id="excelFile" name="excelFile"
                                                       accept=".xlsx, .xls">
                                            </div>
                                            <div class="mb-12 mb-3">
                                                <button type="button" class="btn btn-primary" id="uploadBtn"
                                                        name="uploadBtn"><i class="fas fa-file-import me-1"></i> Import
                                                </button>
                                                <button type="button" id="showImageBtn" class="btn btn-success">ตัวอย่างไฟล์
                                                    Excel Format Data สำหรับนำเข้า
                                                </button>
                                                <a href="user_import_datasale.html" target="_blank" class="btn btn-warning" id="viewManualBtn">
                                                    <i class="fas fa-book"></i> คู่มือการใช้งาน
                                                </a>
                                            </div>
                                            <div class="mb-12 mb-3">
                                                <span>
                                                    <div id="input_text" style="white-space: pre-wrap;"></div>
                                                </span>
                                            </div>

                                            <div class="row mb-4">
                                                <div class="col-md-3">
                                                    <label for="deleteDate" class="form-label">เลือกวันที่ที่จะลบ</label>
                                                    <input type="date" class="form-control" id="deleteDate">
                                                </div>

                                                <div class="col-md-auto align-self-end">
                                                    <button type="button" id="deleteButton" class="btn btn-danger">
                                                        <i class="fas fa-trash"></i> ลบข้อมูลตามวันที่
                                                    </button>
                                                </div>

                                                <div class="col-md-auto align-self-end">
                                                    <button type="button" id="displayButton" class="btn btn-info">
                                                        <i class="fas fa-search"></i> ดูข้อมูลตามวันที่
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </form>

                                    <!-- Progress Display Area -->
                                    <div id="progressArea" class="mt-4" style="display:none;">
                                        <h5 class="text-center mb-3 text-secondary fw-bold"><i class="fas fa-spinner fa-spin me-2 text-primary"></i>กำลังประมวลผลข้อมูลการนำเข้า</h5>
                                        <div class="progress mb-2">
                                            <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%">0%</div>
                                        </div>
                                        <p id="statusText" class="text-center mt-2 small text-muted">กำลังเตรียมการ...</p>
                                    </div>

                                    <!-- Result Display Area -->
                                    <div id="resultArea" class="mt-4" style="display:none;">
                                        <!-- Will be populated dynamically via JavaScript -->
                                    </div>

                                    <div id="spinner" class="text-center my-3" style="display: none;">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <table id='TableRecordList' class='display dataTable' style="width:100%">
                                            <thead>
                                            <tr>
                                                <th>วันที่</th>
                                                <th>เลขที่เอกสาร</th>
                                                <th>ชื่อลูกค้า</th>
                                                <th>ชื่อSale</th>
                                                <th>รหัสสินค้า</th>
                                                <th>รายละเอียด</th>
                                                <th>จำนวน</th>
                                                <th>ประเภท</th>
                                            </tr>
                                            </thead>
                                        </table>
                                    </div>
                                </section>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
                include('includes/Footer.php');
                ?>
            </div>
        </div>
    </div>
</div>

<?php
include('includes/Modal-Logout.php');
?>
<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/myadmin.min.js"></script>

<script src="vendor/datatables/v11/bootbox.min.js"></script>
<script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
<link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>
<link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

<link href="css/spinner_over.css" rel="stylesheet"/>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>

<style>
    input[type="date"].form-control {
        padding: 10px;
        border: 1px solid #ccc;
        border-radius: 8px;
        font-size: 16px;
        color: #333;
        background-color: #f9f9f9;
        transition: border-color 0.3s, box-shadow 0.3s;
    }

    input[type="date"].form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        outline: none;
    }

    ::-webkit-calendar-picker-indicator {
        filter: invert(0.5) sepia(1) saturate(5) hue-rotate(175deg);
        opacity: 0.8;
        cursor: pointer;
        font-size: 20px;
        padding: 5px;
    }

    /* --- แก้ไขจุดที่ 2: CSS บังคับไม่ให้ตัดคำ (Nowrap) --- */
    #TableRecordList th, #TableRecordList td {
        white-space: nowrap;
    }
    /* ------------------------------------------------ */
    
    .progress {
        height: 25px;
        border-radius: 12px;
        background-color: #e9ecef;
        box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);
        overflow: hidden;
    }
    .progress-bar {
        font-size: 14px;
        font-weight: bold;
        line-height: 25px;
        transition: width 0.4s ease;
    }
    .summary-box {
        transition: all 0.3s ease;
    }
    .summary-box:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 20px rgba(0,0,0,0.05);
    }
</style>

<script>
    // *** ฟังก์ชันสำคัญ: แปลงวันที่จาก YYYY-MM-DD เป็น DD-MM-YYYY ***
    function formatToDDMMYYYY(dateString) {
        if (!dateString) return "";
        let parts = dateString.split("-");
        if (parts.length === 3) {
            // parts[0]=Year, parts[1]=Month, parts[2]=Day
            return parts[2] + "-" + parts[1] + "-" + parts[0];
        }
        return dateString;
    }

    $(document).ready(function () {
        var table = $('#TableRecordList').DataTable({
            "scrollX": true, // --- แก้ไขจุดที่ 3: เปิดใช้งาน Scroll แนวนอน ---
            "lengthMenu": [[5, 10, 20, 50, 100], [5, 10, 20, 50, 100]],
            "ajax": {
                "url": "model/fetch_data_sale_sac.php",
                "type": "GET",
                "data": function (d) {
                    d.t = new Date().getTime(); // ป้องกัน Cache

                    // รับค่าจาก input (YYYY-MM-DD)
                    let searchDateRaw = $('#searchDate').val();
                    // แปลงเป็น DD-MM-YYYY เพื่อส่งไปค้นหา
                    d.search_date = formatToDDMMYYYY(searchDateRaw);
                }
            },
            "order": [[0, 'desc']],
            "columns": [
                {"data": "DI_DATE"},
                {"data": "DI_REF"},
                {"data": "AR_NAME"},
                {"data": "SALE_NAME"},
                {"data": "SKU_CODE"},
                {"data": "SKU_NAME"},
                {"data": "TRD_QTY"},
                {"data": "SKU_CAT"}
            ]
        });

        // ปุ่มค้นหา
        $('#btnSearch').click(function () {
            table.ajax.reload();
        });

        // ปุ่ม Reset
        $('#btnReset').click(function () {
            $('#searchDate').val('');
            table.ajax.reload();
        });
    });
</script>

<script>
    // Global variables/functions accessible by all script blocks on this page
    let progressInterval;
    let countdownInterval;
    
    function formatTime(date) {
        let hours = String(date.getHours()).padStart(2, '0');
        let minutes = String(date.getMinutes()).padStart(2, '0');
        let seconds = String(date.getSeconds()).padStart(2, '0');
        return `${hours}:${minutes}:${seconds}`;
    }

    function resetImportForm() {
        $('#excelFile').val('');
        $('#resultArea').fadeOut(function() {
            $('#importFormContainer').slideDown();
            $('#uploadBtn').prop('disabled', false);
        });
    }

    // ส่วน Import / Upload
    $(document).ready(function () {
        $('#showImageBtn').on('click', function () {
            window.open("import_process/template/ims_data_sale_sac_all.xlsx", "_blank");
        });

        let lastImportData = {screen_name: "ims_data_sale_sac_all", table_name: "log_import_data"};
        $.ajax({
            type: "POST",
            url: 'model/get_last_import.php',
            data: lastImportData,
            success: function (response) {
                $('#input_text').html(response);
            },
            error: function (response) {
                alertify.error("error : " + response);
            }
        });

        $('#uploadBtn').on('click', function () {
            if (!$('#excelFile').val()) {
                alertify.alert("Notification", "กรุณาเลือกไฟล์ Excel ก่อนทำการนำเข้าข้อมูล");
                return;
            }

            let startTime = new Date();
            let startTimeStr = formatTime(startTime);

            $('#importFormContainer').slideUp();
            $('#progressArea').fadeIn();
            $('#uploadBtn').prop('disabled', true);

            // Start simulated progress bar
            let width = 0;
            $('#progressBar').css('width', '0%').text('0%').removeClass('bg-success bg-danger').addClass('bg-primary');
            $('#statusText').html('<i class="fas fa-spinner fa-spin me-2"></i>กำลังอัปโหลดไฟล์ไปยังเซิร์ฟเวอร์...');

            progressInterval = setInterval(function() {
                if (width < 30) {
                    width += Math.random() * 5 + 3;
                    $('#statusText').html('<i class="fas fa-spinner fa-spin me-2"></i>กำลังอัปโหลดและเริ่มเปิดไฟล์...');
                } else if (width < 75) {
                    width += Math.random() * 2 + 1;
                    $('#statusText').html('<i class="fas fa-database me-2 text-primary"></i>กำลังประมวลผลข้อมูลและตรวจหาค่าซ้ำ...');
                } else if (width < 96) {
                    width += Math.random() * 0.5 + 0.1;
                    $('#statusText').html('<i class="fas fa-sync fa-spin me-2 text-warning"></i>กำลังบันทึกข้อมูลและปรับปรุงตารางระบบ... โปรดรอสักครู่...');
                } else if (width < 99) {
                    width += 0.05;
                }
                width = Math.min(width, 99);
                $('#progressBar').css('width', width.toFixed(0) + '%').text(width.toFixed(0) + '%');
            }, 400);

            let formData = new FormData($('#uploadForm')[0]);
            $.ajax({
                url: 'import_process/import_data_sale_sac_process.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function (response) {
                    clearInterval(progressInterval);
                    let endTime = new Date();
                    let endTimeStr = formatTime(endTime);
                    let durationMs = endTime - startTime;
                    let durationSeconds = (durationMs / 1000).toFixed(2);

                    if (response.success) {
                        $('#progressBar').css('width', '100%').removeClass('bg-primary').addClass('bg-success').text('100%');
                        $('#statusText').html('<i class="fas fa-check-circle text-success me-1"></i>นำเข้าข้อมูลเสร็จสมบูรณ์!');

                        setTimeout(function() {
                            $('#progressArea').fadeOut(function() {
                                let resultHtml = `
                                    <div class="card border-0 shadow-sm mb-4">
                                        <div class="card-body p-4 text-center">
                                            <div class="mb-4">
                                                <div class="bg-success text-white rounded-circle d-inline-flex align-items-center justify-content-center animate__animated animate__bounceIn" style="width: 70px; height: 70px; font-size: 35px;">
                                                    <i class="fas fa-check"></i>
                                                </div>
                                                <h3 class="mt-3 text-success fw-bold">นำเข้าข้อมูลสำเร็จ</h3>
                                                <p class="text-muted">ระบบได้บันทึกข้อมูลลงฐานข้อมูล ims_data_sale_sac_all เรียบร้อยแล้ว</p>
                                            </div>
                                            
                                            <div class="row g-3 mb-4 text-start">
                                                <div class="col-6 col-md-3">
                                                    <div class="p-3 bg-light rounded border summary-box text-center">
                                                        <span class="text-muted d-block small mb-1">ข้อมูลทั้งหมดใน Excel</span>
                                                        <span class="fs-5 fw-bold text-dark">${response.total_rows.toLocaleString()}</span> <small class="text-muted">รายการ</small>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <div class="p-3 bg-light rounded border summary-box text-center">
                                                        <span class="text-muted d-block small mb-1">นำเข้าใหม่สำเร็จ</span>
                                                        <span class="fs-5 fw-bold text-success">${response.imported_rows.toLocaleString()}</span> <small class="text-muted">รายการ</small>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <div class="p-3 bg-light rounded border summary-box text-center">
                                                        <span class="text-muted d-block small mb-1">มีข้อมูลซ้ำ (ข้าม)</span>
                                                        <span class="fs-5 fw-bold text-warning">${response.duplicate_rows.toLocaleString()}</span> <small class="text-muted">รายการ</small>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-md-3">
                                                    <div class="p-3 bg-light rounded border summary-box text-center">
                                                        <span class="text-muted d-block small mb-1">ใช้เวลารันทั้งหมด</span>
                                                        <span class="fs-5 fw-bold text-primary">${durationSeconds}</span> <small class="text-muted">วินาที</small>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-md-6">
                                                    <div class="p-3 bg-light rounded border summary-box text-center">
                                                        <span class="text-muted d-block small mb-1">เวลาเริ่มต้นนำเข้า</span>
                                                        <span class="fs-6 fw-bold text-secondary">${startTimeStr}</span>
                                                    </div>
                                                </div>
                                                <div class="col-6 col-md-6">
                                                    <div class="p-3 bg-light rounded border summary-box text-center">
                                                        <span class="text-muted d-block small mb-1">เวลาสิ้นสุดการประมวลผล</span>
                                                        <span class="fs-6 fw-bold text-secondary">${endTimeStr}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="alert alert-info d-flex flex-column flex-md-row justify-content-between align-items-center mb-0 p-3">
                                                <div class="mb-3 mb-md-0 text-md-start">
                                                    <i class="fas fa-info-circle me-2"></i>
                                                    กำลังจะรีเฟรชข้อมูลตารางในอีก <span id="countdownText" class="fw-bold fs-5 text-danger">8</span> วินาที...
                                                </div>
                                                <div>
                                                    <button id="btnCancelRedirect" class="btn btn-sm btn-outline-secondary me-2">ดูรายละเอียดต่อ</button>
                                                    <button id="btnConfirmReload" class="btn btn-sm btn-primary px-3">รีเฟรชตารางทันที</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                `;
                                $('#resultArea').html(resultHtml).fadeIn();

                                let countdown = 8;
                                countdownInterval = setInterval(function() {
                                    countdown--;
                                    $('#countdownText').text(countdown);
                                    if (countdown <= 0) {
                                        clearInterval(countdownInterval);
                                        $('#TableRecordList').DataTable().ajax.reload(null, false);
                                        resetImportForm();
                                    }
                                }, 1000);

                                $('#btnConfirmReload').on('click', function() {
                                    clearInterval(countdownInterval);
                                    $('#TableRecordList').DataTable().ajax.reload(null, false);
                                    resetImportForm();
                                });

                                $('#btnCancelRedirect').on('click', function() {
                                    clearInterval(countdownInterval);
                                    $(this).parent().parent().removeClass('alert-info').addClass('alert-warning').html(`
                                        <div class="text-start">
                                            <i class="fas fa-exclamation-triangle me-2"></i>
                                            ยกเลิกการรีเฟรชอัตโนมัติแล้ว คุณสามารถปิดรายงานสรุปนี้เมื่อต้องการกลับสู่หน้าจอการทำงานปกติ
                                        </div>
                                        <div class="mt-2 mt-md-0 text-md-end w-100">
                                            <button id="btnCloseResult" class="btn btn-sm btn-primary px-3">ปิดหน้าสรุปและโหลดตารางใหม่</button>
                                        </div>
                                    `);
                                    
                                    $('#btnCloseResult').on('click', function() {
                                        $('#TableRecordList').DataTable().ajax.reload(null, false);
                                        resetImportForm();
                                    });
                                });
                            });
                        }, 800);
                    } else {
                        $('#progressBar').css('width', '100%').removeClass('bg-primary').addClass('bg-danger').text('เกิดข้อผิดพลาด');
                        $('#statusText').text(response.message);
                        alertify.alert("เกิดข้อผิดพลาด", response.message, function() {
                            $('#progressArea').fadeOut(function() {
                                $('#importFormContainer').slideDown();
                                $('#uploadBtn').prop('disabled', false);
                            });
                        });
                    }
                },
                error: function (xhr, status, error) {
                    clearInterval(progressInterval);
                    $('#progressBar').css('width', '100%').removeClass('bg-primary').addClass('bg-danger').text('เกิดข้อผิดพลาด');
                    $('#statusText').text("เกิดข้อผิดพลาด: " + error);
                    alertify.alert("ผิดพลาด", "เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์", function() {
                        $('#progressArea').fadeOut(function() {
                            $('#importFormContainer').slideDown();
                            $('#uploadBtn').prop('disabled', false);
                        });
                    });
                }
            });
        });
    });
</script>

<script>
    // ส่วนลบข้อมูล
    $(document).ready(function () {
        $('#deleteButton').on('click', function () {
            let di_date_raw = $('#deleteDate').val(); // ค่าดิบ: YYYY-MM-DD

            if (di_date_raw === "") {
                alert("กรุณาเลือกวันที่ที่จะลบ");
                return;
            }

            // แปลงเป็น DD-MM-YYYY เพื่อส่งไปลบ
            let formattedDate = formatToDDMMYYYY(di_date_raw);

            if (confirm("คุณต้องการลบข้อมูลวันที่ " + formattedDate + " จริงหรือไม่?")) {
                let startTime = new Date();
                let startTimeStr = formatTime(startTime);

                $('#importFormContainer').slideUp();
                $('#progressArea h5').html('<i class="fas fa-spinner fa-spin me-2 text-danger"></i>กำลังดำเนินการลบข้อมูลตามวันที่');
                $('#progressArea').fadeIn();

                // Start simulated progress bar
                let width = 0;
                $('#progressBar').css('width', '0%').text('0%').removeClass('bg-success bg-primary bg-danger').addClass('bg-warning');
                $('#statusText').html('<i class="fas fa-trash fa-spin me-2 text-danger"></i>กำลังเตรียมการลบข้อมูลตามเงื่อนไขวันที่...');

                progressInterval = setInterval(function() {
                    if (width < 45) {
                        width += Math.random() * 8 + 4;
                        $('#statusText').html('<i class="fas fa-trash-alt me-2 text-danger"></i>กำลังค้นหาและลบข้อมูลจากตาราง ims_data_sale_sac_all...');
                    } else if (width < 80) {
                        width += Math.random() * 3 + 1;
                        $('#statusText').html('<i class="fas fa-sync fa-spin me-2 text-danger"></i>กำลังจัดเรียงดัชนีตารางฐานข้อมูลและเคลียร์หน่วยความจำ...');
                    } else if (width < 99) {
                        width += 0.2;
                    }
                    width = Math.min(width, 99);
                    $('#progressBar').css('width', width.toFixed(0) + '%').text(width.toFixed(0) + '%');
                }, 300);

                $.ajax({
                    url: 'model/manage_data_sale_sac.php',
                    type: 'POST',
                    data: {
                        action: 'DELETE_BY_DATE', 
                        DI_DATE: formattedDate,
                        format: 'json'
                    },
                    dataType: 'json',
                    success: function (response) {
                        clearInterval(progressInterval);
                        let endTime = new Date();
                        let endTimeStr = formatTime(endTime);
                        let durationMs = endTime - startTime;
                        let durationSeconds = (durationMs / 1000).toFixed(2);

                        if (response.success) {
                            $('#progressBar').css('width', '100%').removeClass('bg-warning').addClass('bg-success').text('100%');
                            $('#statusText').html('<i class="fas fa-check-circle text-success me-1"></i>ดำเนินการลบข้อมูลเรียบร้อย!');

                            setTimeout(function() {
                                $('#progressArea').fadeOut(function() {
                                    let resultHtml = `
                                        <div class="card border-0 shadow-sm mb-4">
                                            <div class="card-body p-4 text-center">
                                                <div class="mb-4">
                                                    <div class="bg-danger text-white rounded-circle d-inline-flex align-items-center justify-content-center animate__animated animate__bounceIn" style="width: 70px; height: 70px; font-size: 35px;">
                                                        <i class="fas fa-trash-alt"></i>
                                                    </div>
                                                    <h3 class="mt-3 text-danger fw-bold">ลบข้อมูลสำเร็จ</h3>
                                                    <p class="text-muted">ระบบได้ทำการลบข้อมูลทั้งหมดตามเงื่อนไขที่ระบุเรียบร้อยแล้ว</p>
                                                </div>
                                                
                                                <div class="row g-3 mb-4 text-start">
                                                    <div class="col-6 col-md-4">
                                                        <div class="p-3 bg-light rounded border summary-box text-center">
                                                            <span class="text-muted d-block small mb-1">เงื่อนไขวันที่ลบข้อมูล</span>
                                                            <span class="fs-5 fw-bold text-danger">${formattedDate}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-6 col-md-4">
                                                        <div class="p-3 bg-light rounded border summary-box text-center">
                                                            <span class="text-muted d-block small mb-1">จำนวนรายการที่ลบจริง</span>
                                                            <span class="fs-5 fw-bold text-dark">${response.count.toLocaleString()}</span> <small class="text-muted">รายการ</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-12 col-md-4">
                                                        <div class="p-3 bg-light rounded border summary-box text-center">
                                                            <span class="text-muted d-block small mb-1">ใช้เวลาในการดำเนินการ</span>
                                                            <span class="fs-5 fw-bold text-primary">${durationSeconds}</span> <small class="text-muted">วินาที</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="p-3 bg-light rounded border summary-box text-center">
                                                            <span class="text-muted d-block small mb-1">เวลาเริ่มต้นลบ</span>
                                                            <span class="fs-6 fw-bold text-secondary">${startTimeStr}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="p-3 bg-light rounded border summary-box text-center">
                                                            <span class="text-muted d-block small mb-1">เวลาสิ้นสุดการทำงาน</span>
                                                            <span class="fs-6 fw-bold text-secondary">${endTimeStr}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="alert alert-info d-flex flex-column flex-md-row justify-content-between align-items-center mb-0 p-3">
                                                    <div class="mb-3 mb-md-0 text-md-start">
                                                        <i class="fas fa-info-circle me-2"></i>
                                                        กำลังจะรีเฟรชข้อมูลตารางในอีก <span id="countdownText" class="fw-bold fs-5 text-danger">8</span> วินาที...
                                                    </div>
                                                    <div>
                                                        <button id="btnCancelRedirect" class="btn btn-sm btn-outline-secondary me-2">ดูรายละเอียดต่อ</button>
                                                        <button id="btnConfirmReload" class="btn btn-sm btn-primary px-3">รีเฟรชตารางทันที</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    `;
                                    $('#resultArea').html(resultHtml).fadeIn();

                                    let countdown = 8;
                                    countdownInterval = setInterval(function() {
                                        countdown--;
                                        $('#countdownText').text(countdown);
                                        if (countdown <= 0) {
                                            clearInterval(countdownInterval);
                                            $('#TableRecordList').DataTable().ajax.reload(null, false);
                                            resetImportForm();
                                        }
                                    }, 1000);

                                    $('#btnConfirmReload').on('click', function() {
                                        clearInterval(countdownInterval);
                                        $('#TableRecordList').DataTable().ajax.reload(null, false);
                                        resetImportForm();
                                    });

                                    $('#btnCancelRedirect').on('click', function() {
                                        clearInterval(countdownInterval);
                                        $(this).parent().parent().removeClass('alert-info').addClass('alert-warning').html(`
                                            <div class="text-start">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                ยกเลิกการรีเฟรชอัตโนมัติแล้ว คุณสามารถปิดรายงานสรุปนี้เมื่อต้องการกลับสู่หน้าจอการทำงานปกติ
                                            </div>
                                            <div class="mt-2 mt-md-0 text-md-end w-100">
                                                <button id="btnCloseResult" class="btn btn-sm btn-primary px-3">ปิดหน้าสรุปและโหลดตารางใหม่</button>
                                            </div>
                                        `);
                                        
                                        $('#btnCloseResult').on('click', function() {
                                            $('#TableRecordList').DataTable().ajax.reload(null, false);
                                            resetImportForm();
                                        });
                                    });
                                });
                            }, 800);
                        } else {
                            $('#progressBar').css('width', '100%').removeClass('bg-warning').addClass('bg-danger').text('เกิดข้อผิดพลาด');
                            $('#statusText').text(response.message);
                            alertify.alert("เกิดข้อผิดพลาด", response.message, function() {
                                $('#progressArea').fadeOut(function() {
                                    $('#importFormContainer').slideDown();
                                });
                            });
                        }
                    },
                    error: function (xhr, status, error) {
                        clearInterval(progressInterval);
                        $('#progressBar').css('width', '100%').removeClass('bg-warning').addClass('bg-danger').text('เกิดข้อผิดพลาด');
                        $('#statusText').text("เกิดข้อผิดพลาด: " + error);
                        alertify.alert("เกิดข้อผิดพลาด", "เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์", function() {
                            $('#progressArea').fadeOut(function() {
                                $('#importFormContainer').slideDown();
                            });
                        });
                    }
                });
            }
        });
    });
</script>

<script>
    $(document).ready(function () {
        // เมื่อคลิกปุ่ม "ดูข้อมูลตามวันที่" (id="displayButton")
        $('#displayButton').click(function () {
            // เปิดไฟล์ display_import_data_sale_sac.php ใน Tab ใหม่ (_blank)
            window.open('display_import_data_sale_sac', '_blank');
        });
    });
</script>

</body>
</html>