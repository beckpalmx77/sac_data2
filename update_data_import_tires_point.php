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
                    <!-- Column for Form Upload -->
                    <div class="col-lg-12 mb-4">
                        <div class="card">
                            <div class="card-header py-3 bg-primary text-white">
                                <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-file-import me-1"></i> นำเข้าข้อมูลไฟล์ Excel (คะแนนสะสมยาง - Tires Point)</h6>
                            </div>
                            <div class="card-body">
                                <form id="uploadForm" enctype="multipart/form-data">
                                    <div class="mb-3">
                                        <label for="excelFile" class="form-label font-weight-bold text-dark">เลือกไฟล์ Excel (.xlsx, .xls) <span class="text-danger">*</span></label>
                                        <input class="form-control" type="file" id="excelFile" name="excelFile" accept=".xlsx, .xls" required>
                                    </div>
                                    <div class="mb-3 d-flex flex-wrap">
                                        <button type="submit" class="btn btn-primary mr-2 me-2 mb-2"><i class="fas fa-upload mr-1 me-1"></i> Import</button>
                                        <button type="button" id="showImageBtn" class="btn btn-success mr-2 me-2 mb-2"><i class="fas fa-file-excel mr-1 me-1"></i> ตัวอย่างไฟล์ Excel Format Data สำหรับนำเข้า Update</button>
                                        <a href="manual_tires_point.html" target="_blank" class="btn btn-outline-danger font-weight-bold mb-2"><i class="fas fa-info-circle mr-1 me-1"></i> คู่มือการใช้งานระบบ</a>
                                    </div>
                                    <div class="mb-3">
                                        <div id="input_text" style="white-space: pre-wrap;" class="small text-muted font-weight-bold text-success"></div>
                                    </div>
                                </form>
                                <div id="spinner" class="text-center my-3" style="display: none;">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="sr-only">Loading...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Card -->
                <div class="row">
                    <div class="col-lg-12">
                        <div class="card mb-4">
                            <div class="card-header py-3 bg-light">
                                <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-table me-1"></i> ข้อมูลแต้มสะสมคะแนนยางแยกตามรุ่นสินค้าในระบบ</h6>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table id="TableRecordList" class="table table-bordered table-striped table-hover w-100">
                                        <thead>
                                            <tr>
                                                <th>รหัสสินค้า</th>
                                                <th>ขนาดยาง/ดอกยาง</th>
                                                <th>ยี่ห้อ</th>
                                                <th>กลุ่มสินค้า</th>
                                                <th>ขอบ</th>
                                                <th>คะแนนร้านทั่วไป</th>
                                                <th>คะแนน Shop</th>
                                            </tr>
                                        </thead>
                                        <tfoot>
                                            <tr>
                                                <th>รหัสสินค้า</th>
                                                <th>ขนาดยาง/ดอกยาง</th>
                                                <th>ยี่ห้อ</th>
                                                <th>กลุ่มสินค้า</th>
                                                <th>ขอบ</th>
                                                <th>คะแนนร้านทั่วไป</th>
                                                <th>คะแนน Shop</th>
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
        let formData = {screen_name: "update_data_import_tires_point", table_name: "log_import_data"};
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
                title: 'กำลังนำเข้าข้อมูลคะแนนยาง',
                html: `
                    <div class="mb-2 text-muted text-center" id="swalStatusText">กำลังเริ่มต้นเตรียมการ...</div>
                    <div class="progress" style="height: 25px; border-radius: 12px; overflow: hidden; background-color: #e9ecef; box-shadow: inset 0 1px 3px rgba(0,0,0,0.1);">
                        <div id="swalProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" role="progressbar" style="width: 0%; font-size: 14px; font-weight: bold; line-height: 25px; transition: width 0.3s ease;">0%</div>
                    </div>
                `,
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Fast progress bar animation
            let progressWidth = 0;
            let progressInterval = setInterval(function() {
                if (progressWidth < 40) {
                    progressWidth += Math.random() * 15 + 10;
                    $('#swalStatusText').html('<i class="fas fa-spinner fa-spin me-2"></i>กำลังอัปโหลดและอ่านไฟล์...');
                } else if (progressWidth < 90) {
                    progressWidth += Math.random() * 10 + 5;
                    $('#swalStatusText').html('<i class="fas fa-database me-2"></i>กำลังอัปเดตข้อมูลแต้มคะแนนยาง (Bulk UPSERT)...');
                }
                progressWidth = Math.min(progressWidth, 95);
                $('#swalProgressBar').css('width', progressWidth.toFixed(0) + '%').text(progressWidth.toFixed(0) + '%');
            }, 150);

            $.ajax({
                url: 'import_process/update_data_tires_point_process.php',
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

                        let countdown = 6;
                        Swal.fire({
                            icon: 'success',
                            title: 'นำเข้าข้อมูลคะแนนยางสำเร็จ',
                            html: `
                                <div class="text-start small p-3 bg-light border rounded mb-3" style="font-size: 0.88rem; line-height: 1.6;">
                                    <p class="mb-1"><strong><i class="fas fa-file-excel me-1 text-success"></i> ไฟล์:</strong> ${response.file_name}</p>
                                    <p class="mb-1"><strong><i class="fas fa-list-ol me-1 text-primary"></i> จำนวนรายการทั้งหมด:</strong> ${(response.count || 0).toLocaleString()} รายการ</p>
                                    <p class="mb-1"><strong><i class="fas fa-edit me-1 text-warning"></i> อัปเดตรายการเดิม:</strong> ${(response.updated || 0).toLocaleString()} รายการ</p>
                                    <p class="mb-1"><strong><i class="fas fa-plus-circle me-1 text-success"></i> เพิ่มรายการใหม่:</strong> ${(response.inserted || 0).toLocaleString()} รายการ</p>
                                    <p class="mb-1"><strong><i class="fas fa-sync me-1 text-info"></i> อัปเดตตารางประวัติขาย (Transaction):</strong> ${(response.transaction_updated || 0).toLocaleString()} แถว</p>
                                    <hr class="my-2">
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
            "ajax": "model/fetch_data_tires_point.php",
            "order": [[0, 'desc']],
            "columns": [
                {"data": "SKU_CODE"},
                {"data": "SKU_NAME"},
                {"data": "BRAND"},
                {"data": "SKU_CAT"},
                {"data": "TIRES_EDGE"},
                {"data": "TRD_U_POINT"},
                {"data": "TRD_S_POINT"}
            ]
        });
    });
</script>

<script>
    $(document).ready(function () {
        $('#showImageBtn').on('click', function () {
            window.open("import_process/template/tires_point_template.xlsx", "_blank");
        });
    });
</script>

</body>
</html>