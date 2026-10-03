<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <head>
        <meta charset="UTF-8">
        <title>ปรับปรุงข้อมูลที่อยู่ลูกค้า (อำเภอ/จังหวัด) - Forecast Plan</title>
        <!-- SweetAlert2 CDN -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <style>
            .premium-card {
                border: none;
                border-radius: 15px;
                box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
                overflow: hidden;
                transition: transform 0.3s ease;
            }
            .premium-card:hover {
                transform: translateY(-2px);
            }
            .card-gradient-header {
                background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
                color: white;
                padding: 25px;
                font-weight: 600;
            }
            .progress-container {
                display: none;
                margin-top: 25px;
                padding: 20px;
                background-color: #f8f9fa;
                border-radius: 10px;
                border: 1px solid #e9ecef;
            }
            .btn-start-sync {
                background: linear-gradient(135deg, #198754 0%, #157347 100%);
                color: white;
                border: none;
                padding: 14px 28px;
                font-size: 1.1rem;
                font-weight: 600;
                border-radius: 50px;
                box-shadow: 0 4px 15px rgba(25, 135, 84, 0.3);
                transition: all 0.3s ease;
                cursor: pointer;
            }
            .btn-start-sync:hover {
                background: linear-gradient(135deg, #157347 0%, #146c43 100%);
                color: white;
                transform: scale(1.02);
                box-shadow: 0 6px 20px rgba(25, 135, 84, 0.4);
            }
            .btn-start-sync:disabled {
                background: #6c757d !important;
                box-shadow: none !important;
                cursor: not-allowed;
                transform: none !important;
            }
            .log-box {
                background-color: #212529;
                color: #0f5;
                font-family: 'Courier New', Courier, monospace;
                padding: 15px;
                border-radius: 8px;
                height: 150px;
                overflow-y: auto;
                font-size: 0.9rem;
                margin-top: 15px;
                box-shadow: inset 0 2px 8px rgba(0,0,0,0.5);
            }
            .status-badge {
                font-size: 0.9rem;
                padding: 6px 12px;
                border-radius: 50px;
            }
            .info-label {
                font-weight: bold;
                color: #495057;
            }
            .swal2-actions {
                border-top: none !important;
            }
            /* ยกเลิกเส้นขีดกลางบนหัวข้อของ SweetAlert2 h2 */
            .swal2-popup h2::before {
                display: none !important;
                content: none !important;
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
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">จัดการข้อมูลที่อยู่ลูกค้า (Forecast Plan)</h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="Dashboard_admin.php">หน้าแรก</a></li>
                            <li class="breadcrumb-item active" aria-current="page">ปรับปรุงข้อมูลที่อยู่</li>
                        </ol>
                    </div>

                    <div class="row justify-content-center">
                        <div class="col-lg-8">
                            <div class="card premium-card mb-4">
                                <div class="card-gradient-header d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="m-0"><i class="fas fa-sync-alt fa-spin mr-2"></i> ระบบปรับปรุงข้อมูลลูกค้า (ที่อยู่ & พนักงานขาย & คนเทค)</h5>
                                        <small class="text-white-50">เชื่อมโยงข้อมูลและอัปเดตอัตโนมัติด้วยรหัสลูกค้า (AR_CODE)</small>
                                    </div>
                                    <span id="overallStatusBadge" class="badge badge-warning status-badge text-dark font-weight-bold">รอดำเนินการ</span>
                                </div>
                                <div class="card-body p-4">
                                    
                                    <!-- Table Information Section -->
                                    <div class="row mb-4">
                                        <div class="col-md-5 border-right">
                                            <h6 class="text-primary font-weight-bold"><i class="fas fa-database"></i> ตารางเป้าหมาย (Destination)</h6>
                                            <p class="mb-1"><span class="info-label">ตาราง:</span> <code>ims_tires_plan_forecast</code></p>
                                            <p class="mb-1"><span class="info-label">คีย์หลัก:</span> <code>ar_code</code> (รหัสลูกค้า)</p>
                                            <p class="mb-0"><span class="info-label">ฟิลด์ที่จะถูกแก้ไข:</span></p>
                                            <div class="mt-1">
                                                <span class="badge badge-info text-dark">amphur</span>
                                                <span class="badge badge-info text-dark">province</span>
                                                <span class="badge badge-success text-white">sale_name</span>
                                                <span class="badge badge-success text-white">take_name</span>
                                            </div>
                                        </div>
                                        <div class="col-md-7 pl-md-4">
                                            <h6 class="text-success font-weight-bold"><i class="fas fa-file-invoice"></i> วิวมุมมองอ้างอิง (Sources)</h6>
                                            <p class="mb-2">
                                                <strong>1) ข้อมูลที่อยู่:</strong> <code>v_customer_data_sale_all_zone</code>
                                                <br>&nbsp;&nbsp;&nbsp;&bull; ฟิลด์ที่ใช้: <code>TRD_AMPHUR</code>, <code>TRD_PROVINCE</code>
                                            </p>
                                            <p class="mb-0">
                                                <strong>2) ข้อมูลพนักงาน/คนเทค:</strong> <code>v_sale_cust_sac</code>
                                                <br>&nbsp;&nbsp;&nbsp;&bull; ฟิลด์ที่ใช้: <code>SALE_NAME</code>, <code>TAKE_NAME</code>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> <strong>คำอธิบาย:</strong> เมื่อกดเริ่มปุ่มอัปเดต ระบบจะทำการเปรียบเทียบค่าอ้างอิงจากข้อมูลระบบกลางและปรับปรุงข้อมูล <strong>อำเภอ จังหวัด พนักงานขาย และคนเทคสินค้า</strong> ให้กับทุกบรรทัดแผนงานที่ตรงกันในตารางแผนงานโดยทันที
                                    </div>

                                     <div class="text-center my-4">
                                         <button type="button" id="btnStartSync" class="btn-start-sync">
                                             <i class="fas fa-play mr-2"></i> เริ่มอัปเดตข้อมูล (Start Sync)
                                         </button>
                                         <a href="forecast_plan_display_new" id="btnCancelSync" class="btn btn-outline-secondary ml-3" style="border-radius: 50px; font-weight: 600; font-size: 1.1rem; padding: 12px 28px; display: inline-block; vertical-align: middle; transition: all 0.3s ease; border: 2px solid #6c757d;">
                                             <i class="fas fa-arrow-left mr-2"></i> ยกเลิก/ย้อนกลับ
                                         </a>
                                     </div>

                                    <!-- Progress and Log Section -->
                                    <div id="syncProgressContainer" class="progress-container">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="font-weight-bold text-dark" id="progressStatusText">กำลังเริ่มต้นทำงาน...</span>
                                            <span class="font-weight-bold text-primary" id="progressPercentage">0%</span>
                                        </div>
                                        <div class="progress" style="height: 25px; border-radius: 50px;">
                                            <div id="syncProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
                                        </div>
                                        
                                        <div class="log-box" id="logBox">
                                            [System Log Waiting...]
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            
            <?php include('includes/Footer.php'); ?>
        </div>
    </div>

    <!-- Scroll to top -->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>

    <script>
        $(document).ready(function() {
            let logBox = $('#logBox');

            function addLog(message) {
                let timestamp = new Date().toLocaleTimeString();
                logBox.append('[' + timestamp + '] ' + message + '\n');
                logBox.scrollTop(logBox[0].scrollHeight);
            }

            $('#btnStartSync').click(function() {
                // Disable button and prepare UI
                $('#btnStartSync').prop('disabled', true);
                $('#btnCancelSync').addClass('disabled').attr('href', 'javascript:void(0);').css({
                    'pointer-events': 'none',
                    'opacity': '0.5'
                });
                $('#syncProgressContainer').fadeIn(400);
                $('#overallStatusBadge').removeClass('badge-warning').addClass('badge-info').text('กำลังประมวลผล');
                logBox.html('');
                
                addLog('เริ่มต้นเชื่อมต่อข้อมูลฐานข้อมูล...');
                
                // Step 1: Simulated Progress start (0% to 30%)
                animateProgress(0, 30, 400, 'กำลังตรวจสอบคีย์ความถูกต้อง...', function() {
                    addLog('กำลังดึงตารางเปรียบเทียบจาก v_customer_data_sale_all_zone...');
                    
                    animateProgress(30, 60, 500, 'กำลังทำการ Join ข้อมูลและบันทึก Transaction...', function() {
                        addLog('ส่งข้อมูลเพื่อประมวลผลคำสั่งหลังบ้าน (AJAX Query Execution)...');
                        
                        // Fire AJAX call
                        $.ajax({
                            type: 'POST',
                            url: 'sync_address_from_zone_ajax.php',
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    addLog('การประมวลผลคำสั่ง UPDATE เรียบร้อยแล้ว!');
                                    addLog('จำนวนบรรทัดที่อัปเดต: ' + response.affected_rows + ' รายการ');
                                    
                                    // Complete progress animation
                                    animateProgress(60, 100, 300, 'อัปเดตเสร็จสมบูรณ์!', function() {
                                        $('#overallStatusBadge').removeClass('badge-info').addClass('badge-success').text('สำเร็จ');
                                        addLog('ระบบซิงก์ข้อมูลเสร็จสิ้นเรียบร้อย!');
                                        
                                        // Show Success Dialog
                                        Swal.fire({
                                            title: 'สำเร็จ!',
                                            html: 'อัปเดตข้อมูลที่อยู่เรียบร้อยแล้ว<br>จำนวนรายการที่ได้รับผลกระทบ: <b>' + response.affected_rows.toLocaleString() + '</b> รายการ',
                                            icon: 'success',
                                            confirmButtonColor: '#198754',
                                            confirmButtonText: 'กลับไปยังหน้าแผนงาน'
                                        }).then((result) => {
                                            window.location.href = 'forecast_plan_display_new';
                                        });
                                    });
                                } else {
                                    handleError(response.message);
                                }
                            },
                            error: function(xhr, status, error) {
                                handleError(error || 'การเชื่อมต่อขัดข้อง');
                            }
                        });
                    });
                });
            });

            function animateProgress(start, end, duration, statusText, callback) {
                $('#progressStatusText').text(statusText);
                let current = start;
                let stepTime = Math.abs(Math.floor(duration / (end - start)));
                let timer = setInterval(function() {
                    current++;
                    $('#syncProgressBar').css('width', current + '%').attr('aria-valuenow', current).text(current + '%');
                    $('#progressPercentage').text(current + '%');
                    if (current >= end) {
                        clearInterval(timer);
                        if (callback) callback();
                    }
                }, stepTime);
            }

            function handleError(errorMessage) {
                $('#overallStatusBadge').removeClass('badge-info').addClass('badge-danger').text('เกิดข้อผิดพลาด');
                addLog('❌ เกิดข้อผิดพลาด: ' + errorMessage);
                $('#progressStatusText').text('เกิดข้อผิดพลาดในการอัปเดต!');
                $('#syncProgressBar').removeClass('bg-success').addClass('bg-danger');
                $('#btnStartSync').prop('disabled', false);
                
                Swal.fire({
                    title: 'เกิดข้อผิดพลาด!',
                    text: errorMessage,
                    icon: 'error',
                    confirmButtonColor: '#dc3545',
                    confirmButtonText: 'ตกลง'
                });
            }
        });
    </script>
    </body>
    </html>
<?php } ?>
