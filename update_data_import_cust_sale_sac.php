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
                                        <div class="mb-3">
                                            <label for="excelFile" class="form-label">Select Excel File</label>
                                            <input class="form-control" type="file" id="excelFile" name="excelFile"
                                                   accept=".xlsx, .xls">
                                        </div>
                                        <div class="mb-3 d-flex flex-wrap">
                                            <button type="submit" class="btn btn-primary mr-2 me-2 mb-2"><i class="fas fa-upload mr-1 me-1"></i> Import</button>
                                            <button type="button" id="showImageBtn" class="btn btn-success mr-2 me-2 mb-2"><i class="fas fa-file-excel mr-1 me-1"></i> ตัวอย่างไฟล์ Excel Format Data สำหรับนำเข้า Update</button>
                                            <a href="manual_cupdate_customer.html" target="_blank" class="btn btn-outline-danger font-weight-bold mb-2"><i class="fas fa-info-circle mr-1 me-1"></i> คู่มือการใช้งานระบบ</a>
                                        </div>
                                        <div class="mb-12">
                                            <span>
                                                <div id="input_text" style="white-space: pre-wrap;"></div>
                                                <!-- เพิ่ม style white-space -->
                                            </span>
                                        </div>
                                    </form>
                                    <br>
                                    <div id="spinner" class="text-center my-3" style="display: none;">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="sr-only">Loading...</span>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <table id='TableRecordList' class='display dataTable'>
                                            <thead>
                                            <tr>
                                                <th>รหัสลูกค้า</th>
                                                <th>ชื่อลูกค้า</th>
                                                <th>ชื่อ Sale</th>
                                                <th>ชื่อ Take</th>
                                                <th>อำเภอ</th>
                                                <th>จังหวัด</th>
                                                <th>SKU_CAT</th>
                                                <th>BRAND</th>
                                            </tr>
                                            </thead>
                                            <tfoot>
                                            <tr>
                                                <th>รหัสลูกค้า</th>
                                                <th>ชื่อลูกค้า</th>
                                                <th>ชื่อ Sale</th>
                                                <th>ชื่อ Take</th>
                                                <th>อำเภอ</th>
                                                <th>จังหวัด</th>
                                                <th>SKU_CAT</th>
                                                <th>BRAND</th>
                                            </tr>
                                            </tfoot>
                                        </table>
                                    </div>
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
    $(document).ready(function () {
        let formData = {screen_name: "update_data_import_cust_sale_sac", table_name: "log_import_data"};
        $.ajax({
            type: "POST",
            url: 'model/get_last_import.php',
            data: formData,
            success: function (response) {
                $('#input_text').html(response);  // ใส่ response ลงใน div
            },
            error: function (response) {
                alertify.error("error : " + response);
            }
        });
    });
</script>

<!-- Custom Script -->
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

            // Open SweetAlert Progress
            Swal.fire({
                title: 'กำลังนำเข้าข้อมูล',
                html: `
                    <div class="mb-2 text-muted text-center" id="swalStatusText">กำลังเริ่มต้นเตรียมการ...</div>
                    <div class="progress" style="height: 25px; border-radius: 12px; overflow: hidden; background-color: #e9ecef; box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);">
                        <div id="swalProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%; font-size: 14px; font-weight: bold; line-height: 25px; transition: width 0.4s ease;">0%</div>
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
                    $('#swalStatusText').html('<i class="fas fa-spinner fa-spin me-2"></i>กำลังอัปโหลดไฟล์...');
                } else if (progressWidth < 75) {
                    progressWidth += Math.random() * 3 + 1;
                    $('#swalStatusText').html('<i class="fas fa-database me-2"></i>กำลังอ่านและปรับปรุงฐานข้อมูล...');
                } else if (progressWidth < 98) {
                    progressWidth += Math.random() * 0.5 + 0.1;
                    $('#swalStatusText').html('<i class="fas fa-sync fa-spin me-2 text-warning"></i>กำลังบันทึกข้อมูลและอัปเดตแผนงาน (Sync Forecast)...');
                }
                progressWidth = Math.min(progressWidth, 98);
                $('#swalProgressBar').css('width', progressWidth.toFixed(0) + '%').text(progressWidth.toFixed(0) + '%');
            }, 300);

            $.ajax({
                url: 'import_process/update_data_cust_sale_sac_process.php',
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
                        $('#swalProgressBar').css('width', '100%').removeClass('bg-primary').addClass('bg-success').text('100%');
                        $('#swalStatusText').text('นำเข้าข้อมูลเรียบร้อย!');

                        let countdown = 8;
                        Swal.fire({
                            icon: 'success',
                            title: 'นำเข้าข้อมูลสำเร็จ',
                            html: `
                                <div class="text-start small p-2 bg-light border rounded mb-3" style="font-size: 0.85rem;">
                                    <p class="mb-1"><strong><i class="fas fa-file-excel me-1 text-success"></i> ไฟล์:</strong> ${response.file_name}</p>
                                    <p class="mb-1"><strong><i class="fas fa-list-ol me-1 text-primary"></i> จำนวนรายการทั้งหมด:</strong> ${response.count.toLocaleString()} รายการ</p>
                                    <p class="mb-1"><strong><i class="fas fa-check-circle me-1 text-success"></i> อัปเดตสำเร็จ:</strong> ${response.updated.toLocaleString()} รายการ</p>
                                    <p class="mb-1"><strong><i class="fas fa-database me-1 text-info"></i> ตารางและฟิลด์ที่อัปเดต:</strong></p>
                                    <div class="mb-2 ps-2 text-muted" style="font-size: 0.78rem; line-height: 1.4;">
                                        • <span class="badge bg-secondary" style="font-size: 0.7rem;">ims_data_sale_sac_all</span> : <code>AR_NAME</code>, <code>SALE_NAME</code>, <code>TAKE_NAME</code>, <code>TRD_AMPHUR</code>, <code>TRD_PROVINCE</code><br>
                                        • <span class="badge bg-success" style="font-size: 0.7rem;">ims_tires_plan_forecast</span> : <code>sale_name</code>, <code>take_name</code>, <code>amphur</code>, <code>province</code>
                                    </div>
                                    <p class="mb-1"><strong><i class="fas fa-clock me-1 text-secondary"></i> เวลาเริ่มต้น:</strong> ${startTimeStr}</p>
                                    <p class="mb-1"><strong><i class="fas fa-clock me-1 text-secondary"></i> เวลาสิ้นสุด:</strong> ${endTimeStr}</p>
                                    <p class="mb-0"><strong><i class="fas fa-stopwatch me-1 text-warning"></i> เวลาที่ใช้:</strong> ${durationSeconds} วินาที</p>
                                </div>
                                <div class="alert alert-info py-2 mb-0 text-center" style="font-size: 0.85rem;">
                                    <i class="fas fa-info-circle me-1"></i> 
                                    ระบบจะรีเฟรชหน้าจอหลักในอีก <span id="countdownText" class="fw-bold text-danger fs-6">${countdown}</span> วินาที...
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
                                $('#TableRecordList').DataTable().ajax.reload();
                            }
                        });

                    } else {
                        Swal.fire('เกิดข้อผิดพลาด', response.message, 'error');
                    }
                },
                error: function (xhr, status, error) {
                    clearInterval(progressInterval);
                    Swal.fire('เกิดข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้: ' + error, 'error');
                }
            });
        });

        $('#TableRecordList').DataTable({
            "lengthMenu": [[6, 10, 20, 50, 100], [6, 10, 20, 50, 100]],
            "ajax": "model/fetch_data_cust_sale_sac.php",
            "order": [[0, 'desc']],
            "columns": [
                {"data": "AR_CODE"},
                {"data": "AR_NAME"},
                {"data": "SALE_NAME"},
                {"data": "TAKE_NAME"},
                {"data": "TRD_AMPHUR"},
                {"data": "TRD_PROVINCE"},
                {"data": "SKU_CAT"},
                {"data": "BRAND"}
            ]
        });
    });
</script>

<script>
    $(document).ready(function () {
        $('#showImageBtn').on('click', function () {
            window.open("import_process/template/template_cust_data.xlsx", "_blank");
        });
    });
</script>

</body>
</html>
