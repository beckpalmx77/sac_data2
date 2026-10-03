<?php
session_start();
date_default_timezone_set("Asia/Bangkok");
include('config/connect_db.php');

if (!isset($_SESSION['alogin']) || $_SESSION['alogin'] == '') {
    header('Location: login.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Import Tires Plan Forecast</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css"/>
    <style>
        body { 
            font-family: 'Prompt', sans-serif; 
            background-color: #f0f2f5; 
        }
        .card { 
            border: none;
            border-radius: 16px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.06); 
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card-header { 
            background: linear-gradient(135deg, #0d6efd 0%, #0052cc 100%); 
            color: white; 
            border-radius: 16px 16px 0 0 !important; 
            padding: 20px;
        }
        .btn-import { 
            background: linear-gradient(135deg, #198754 0%, #157347 100%); 
            color: white; 
            border: none;
            border-radius: 25px; 
            padding: 12px 30px; 
            font-weight: 500;
            transition: all 0.3s ease;
            box-shadow: 0 4px 10px rgba(25, 135, 84, 0.2);
        }
        .btn-import:hover:not(:disabled) { 
            background: linear-gradient(135deg, #157347 0%, #146c43 100%); 
            color: white; 
            transform: translateY(-1px);
            box-shadow: 0 6px 15px rgba(25, 135, 84, 0.3);
        }
        .btn-import:disabled {
            background: #6c757d;
            box-shadow: none;
            cursor: not-allowed;
        }
        .upload-area { 
            border: 2px dashed #0d6efd; 
            padding: 40px 20px; 
            border-radius: 12px; 
            text-align: center; 
            background: #fdfdfd; 
            cursor: pointer; 
            transition: all 0.3s ease;
        }
        .upload-area:hover {
            background-color: #f8fafd;
            border-color: #0b5ed7;
        }
        #fileInput { display: none; }
        
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
        /* ยกเลิกเส้นขีดกลางบนหัวข้อของ SweetAlert2 h2 */
        .swal2-popup h2::before {
            display: none !important;
            content: none !important;
        }
    </style>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0"><i class="fas fa-file-import me-2"></i> นำเข้าข้อมูล Tires Plan Forecast</h4>
                        <a href="forecast_plan_display_new.php" class="btn btn-sm btn-outline-light"><i class="fas fa-arrow-left me-1"></i> กลับหน้าหลัก</a>
                    </div>
                    <div class="card-body p-4">
                        <div id="importFormContainer">
                            <div class="mb-4 text-center">
                                <p class="text-muted">กรุณาอัปโหลดไฟล์ Excel (.xlsx) ตามรูปแบบ Template ที่กำหนด</p>
                                <a href="import_process/template/template_tire_plan.xlsx" class="btn btn-outline-primary btn-sm mb-3">
                                    <i class="fas fa-download me-1"></i> ดาวน์โหลด Template
                                </a>
                                <a href="import_plan_howto.html" target="_blank" class="btn btn-outline-info btn-sm mb-3 ms-2">
                                    <i class="fas fa-question-circle me-1"></i> วิธีการใช้งาน
                                </a>
                            </div>

                            <form id="importForm" enctype="multipart/form-data">
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">เลือกเดือน:</label>
                                        <select name="forcast_month" class="form-select" required>
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
                                    <div class="col-md-6">
                                        <label class="form-label fw-bold">เลือกปี:</label>
                                        <select name="forcast_year" class="form-select" required>
                                            <?php
                                            $current_year = date('Y');
                                            for($y = $current_year - 1; $y <= $current_year + 2; $y++) {
                                                echo "<option value='$y' " . ($y == $current_year ? 'selected' : '') . ">$y</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="upload-area" onclick="document.getElementById('fileInput').click()">
                                    <i class="fas fa-cloud-upload-alt fa-3x text-primary mb-3"></i>
                                    <h5 id="fileNameDisplay">คลิกเพื่อเลือกไฟล์ หรือลากไฟล์มาวางที่นี่</h5>
                                    <input type="file" name="excel_file" id="fileInput" accept=".xls,.xlsx">
                                </div>

                                <div class="d-grid gap-2 mt-4">
                                    <button type="submit" class="btn btn-import btn-lg">
                                        <i class="fas fa-check-circle me-1"></i> เริ่มนำเข้าข้อมูล
                                    </button>
                                    <a href="forecast_plan_display_new.php" class="btn btn-light">ยกเลิก</a>
                                </div>
                            </form>
                        </div>

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
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {
            // Drag and drop events for upload area
            let uploadArea = document.querySelector('.upload-area');
            if (uploadArea) {
                ['dragenter', 'dragover'].forEach(eventName => {
                    uploadArea.addEventListener(eventName, (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        uploadArea.style.borderColor = '#28a745';
                        uploadArea.style.backgroundColor = '#f1fcf4';
                    }, false);
                });

                ['dragleave', 'drop'].forEach(eventName => {
                    uploadArea.addEventListener(eventName, (e) => {
                        e.preventDefault();
                        e.stopPropagation();
                        uploadArea.style.borderColor = '#0d6efd';
                        uploadArea.style.backgroundColor = '#fdfdfd';
                    }, false);
                });

                uploadArea.addEventListener('drop', (e) => {
                    let dt = e.dataTransfer;
                    let files = dt.files;
                    if (files.length) {
                        document.getElementById('fileInput').files = files;
                        $('#fileInput').trigger('change');
                    }
                }, false);
            }

            $('#fileInput').change(function() {
                var fileName = $(this).val().split('\\').pop();
                if (fileName) {
                    $('#fileNameDisplay').text(fileName).addClass('text-success fw-bold');
                }
            });

            let progressInterval;
            let countdownInterval;
            
            function formatTime(date) {
                let hours = String(date.getHours()).padStart(2, '0');
                let minutes = String(date.getMinutes()).padStart(2, '0');
                let seconds = String(date.getSeconds()).padStart(2, '0');
                return `${hours}:${minutes}:${seconds}`;
            }

            $('#importForm').on('submit', function(e) {
                e.preventDefault();
                var formData = new FormData(this);
                
                var month = $('select[name="forcast_month"]').val();
                var year = $('select[name="forcast_year"]').val();

                if (!month) {
                    Swal.fire('แจ้งเตือน', 'กรุณาเลือกเดือนที่ต้องการนำเข้า', 'warning');
                    return;
                }
                if (!year) {
                    Swal.fire('แจ้งเตือน', 'กรุณาเลือกปีที่ต้องการนำเข้า', 'warning');
                    return;
                }
                if (!$('#fileInput').val()) {
                    Swal.fire('แจ้งเตือน', 'กรุณาเลือกไฟล์ก่อนนำเข้า', 'warning');
                    return;
                }

                // Capture start time
                let startTime = new Date();
                let startTimeStr = formatTime(startTime);
                
                // UI changes
                $('#importFormContainer').slideUp();
                $('#progressArea').fadeIn();
                $('button[type="submit"]').prop('disabled', true);

                // Start simulated progress bar
                let width = 0;
                $('#progressBar').css('width', '0%').text('0%').removeClass('bg-success bg-danger').addClass('bg-primary');
                $('#statusText').html('<i class="fas fa-spinner fa-spin me-2"></i>กำลังอัปโหลดไฟล์ไปยังเซิร์ฟเวอร์...');

                progressInterval = setInterval(function() {
                    if (width < 30) {
                        width += Math.random() * 5 + 3;
                        $('#statusText').html('<i class="fas fa-spinner fa-spin me-2"></i>กำลังตรวจสอบและอัปโหลดไฟล์...');
                    } else if (width < 75) {
                        width += Math.random() * 2 + 1;
                        $('#statusText').html('<i class="fas fa-database me-2 text-primary"></i>กำลังอ่านไฟล์และบันทึกลงฐานข้อมูล...');
                    } else if (width < 96) {
                        width += Math.random() * 0.5 + 0.1;
                        $('#statusText').html('<i class="fas fa-sync fa-spin me-2 text-warning"></i>กำลังจัดการข้อมูลภายในระบบ... โปรดรอสักครู่...');
                    } else if (width < 99) {
                        width += 0.05;
                    }
                    width = Math.min(width, 99);
                    $('#progressBar').css('width', width.toFixed(0) + '%').text(width.toFixed(0) + '%');
                }, 400);

                $.ajax({
                    url: 'import_process/import_forecast_process.php',
                    type: 'POST',
                    data: formData,
                    contentType: false,
                    processData: false,
                    dataType: 'json',
                    success: function(response) {
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
                                                    <p class="text-muted">ระบบได้ทำการตรวจสอบและบันทึกข้อมูลเรียบร้อยแล้ว</p>
                                                </div>
                                                
                                                <div class="row g-3 mb-4 text-start">
                                                    <div class="col-6 col-md-3">
                                                        <div class="p-3 bg-light rounded border summary-box text-center">
                                                            <span class="text-muted d-block small mb-1">จำนวนข้อมูลนำเข้า</span>
                                                            <span class="fs-4 fw-bold text-success">${response.count.toLocaleString()}</span> <small class="text-muted">แถว</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-6 col-md-3">
                                                        <div class="p-3 bg-light rounded border summary-box text-center">
                                                            <span class="text-muted d-block small mb-1">ใช้เวลานำเข้าทั้งหมด</span>
                                                            <span class="fs-4 fw-bold text-primary">${durationSeconds}</span> <small class="text-muted">วินาที</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-6 col-md-3">
                                                        <div class="p-3 bg-light rounded border summary-box text-center">
                                                            <span class="text-muted d-block small mb-1">เวลาเริ่มต้น</span>
                                                            <span class="fs-5 fw-bold text-secondary">${startTimeStr}</span>
                                                        </div>
                                                    </div>
                                                    <div class="col-6 col-md-3">
                                                        <div class="p-3 bg-light rounded border summary-box text-center">
                                                            <span class="text-muted d-block small mb-1">เวลาสิ้นสุด</span>
                                                            <span class="fs-5 fw-bold text-secondary">${endTimeStr}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <div class="alert alert-info d-flex flex-column flex-md-row justify-content-between align-items-center mb-0 p-3">
                                                    <div class="mb-3 mb-md-0 text-md-start">
                                                        <i class="fas fa-info-circle me-2"></i>
                                                        กำลังนำทางกลับไปยังหน้าหลักอัตโนมัติในอีก <span id="countdownText" class="fw-bold fs-5 text-danger">8</span> วินาที...
                                                    </div>
                                                    <div>
                                                        <button id="btnCancelRedirect" class="btn btn-sm btn-outline-secondary me-2">อยู่หน้านี้ต่อ</button>
                                                        <a href="forecast_plan_display_new.php" class="btn btn-sm btn-primary px-3">ไปยังหน้าหลักทันที</a>
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
                                            window.location.href = 'forecast_plan_display_new.php';
                                        }
                                    }, 1000);

                                    $('#btnCancelRedirect').on('click', function() {
                                        clearInterval(countdownInterval);
                                        $(this).parent().parent().removeClass('alert-info').addClass('alert-warning').html(`
                                            <div class="text-start">
                                                <i class="fas fa-exclamation-triangle me-2"></i>
                                                ยกเลิกการนำทางอัตโนมัติแล้ว คุณสามารถตรวจสอบรายละเอียดด้านบน หรือกดปุ่มเมื่อต้องการกลับหน้าหลัก
                                            </div>
                                            <div class="mt-2 mt-md-0 text-md-end w-100">
                                                <a href="forecast_plan_display_new.php" class="btn btn-sm btn-primary px-3">กลับหน้าหลัก</a>
                                                <button onclick="location.reload()" class="btn btn-sm btn-outline-secondary ms-2">นำเข้าไฟล์ใหม่</button>
                                            </div>
                                        `);
                                    });
                                });
                            }, 800);

                        } else {
                            $('#progressBar').css('width', '100%').removeClass('bg-primary').addClass('bg-danger').text('เกิดข้อผิดพลาด');
                            $('#statusText').text(response.message);
                            Swal.fire('เกิดข้อผิดพลาด', response.message, 'error').then(() => {
                                $('#progressArea').fadeOut(function() {
                                    $('#importFormContainer').slideDown();
                                    $('button[type="submit"]').prop('disabled', false);
                                });
                            });
                        }
                    },
                    error: function(xhr, status, error) {
                        clearInterval(progressInterval);
                        $('#progressBar').css('width', '100%').removeClass('bg-primary').addClass('bg-danger').text('เกิดข้อผิดพลาด');
                        $('#statusText').text('เกิดข้อผิดพลาด: ' + error);
                        Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'error').then(() => {
                            $('#progressArea').fadeOut(function() {
                                $('#importFormContainer').slideDown();
                                $('button[type="submit"]').prop('disabled', false);
                            });
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>
