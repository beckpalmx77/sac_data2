<?php
session_start();
date_default_timezone_set("Asia/Bangkok");
include('config/connect_db.php');

if (!isset($_SESSION['alogin']) || $_SESSION['alogin'] == '') {
    header('Location: login.php');
    exit;
}

$first_name = $_SESSION['first_name'] ?? '';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>แก้ไขข้อมูลลูกค้าประจำแผนงาน (Tires Plan)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Prompt', sans-serif; background-color: #f4f6f9; margin: 20px; }
        .card { border-radius: 15px; border: none; box-shadow: 0 4px 15px rgba(0,0,0,0.08); }
        .card-header { background-color: #0d6efd; color: white; border-radius: 15px 15px 0 0 !important; padding: 15px 20px; }
        .form-label { font-weight: 500; color: #495057; }
        .btn-save { background-color: #0d6efd; color: white; font-weight: 500; padding: 10px 25px; border-radius: 8px; border: none; }
        .btn-save:hover { background-color: #0b5ed7; color: white; }
        .btn-back { border-radius: 8px; padding: 10px 20px; }
        /* ยกเลิกเส้นขีดกลางบนหัวข้อของ SweetAlert2 h2 */
        .swal2-popup h2::before {
            display: none !important;
            content: none !important;
        }
    </style>
</head>
<body>
    <div class="container" style="max-width: 800px; margin-top: 30px;">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold"><i class="fas fa-user-edit me-2"></i> แก้ไขข้อมูลลูกค้าในแผนงานขาย (Tires Plan)</h5>
                <a href="forecast_plan_display_new.php" class="btn btn-light btn-sm text-dark fw-bold btn-back">
                    <i class="fas fa-arrow-left me-1"></i> กลับหน้าจัดการแผน
                </a>
            </div>
            <div class="card-body p-4">
                <div class="alert alert-info py-2 small" role="alert">
                    <i class="fas fa-info-circle me-1"></i> เมื่อทำการแก้ไข ชื่อลูกค้า, อำเภอ, จังหวัด หรือเขต ในหน้านี้ <b>ระบบจะอัปเดตข้อมูลทุกแผนงานของลูกค้ารายนี้ให้โดยอัตโนมัติ</b> ที่มีรหัสตรงกันในตารางแผนงานวางแผนการขายยาง
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">ค้นหาและเลือกลูกค้าที่ต้องการแก้ไข <span class="text-danger">*</span></label>
                    <select class="form-select" id="arCodeSearch" style="width: 100%;">
                        <option value="">-- ค้นหารหัส หรือ ชื่อร้านค้า --</option>
                    </select>
                </div>

                <hr class="my-4">

                <form id="editCustomerForm">
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">รหัสร้านค้า (AR Code)</label>
                            <input type="text" class="form-control bg-light fw-bold text-primary" id="ar_code" name="ar_code" readonly placeholder="จะแสดงอัตโนมัติเมื่อเลือกร้าน">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">พนักงานขายที่ดูแล (ล่าสุด)</label>
                            <input type="text" class="form-control bg-light" id="sale_name" readonly placeholder="จะแสดงอัตโนมัติเมื่อเลือกร้าน">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">ชื่อร้านค้า (Customer Name) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="ar_name" name="ar_name" required placeholder="ระบุชื่อร้านค้า">
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">อำเภอ (Amphur) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="amphur" name="amphur" required placeholder="เช่น อ.ดอนตูม">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">จังหวัด (Province) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="province" name="province" required placeholder="เช่น จ.นครปฐม">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">เขตพื้นที่ (Zone)</label>
                            <input type="text" class="form-control" id="zone" name="zone" placeholder="เช่น ภาคกลาง">
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 border-top pt-3">
                        <button type="reset" class="btn btn-secondary px-4 btn-back">ล้างค่า</button>
                        <button type="button" id="btnSubmitUpdate" class="btn btn-save">
                            <i class="fas fa-save me-1"></i> บันทึกข้อมูลทั้งหมด
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        $(document).ready(function() {
            // Initialize Select2 for searching customer
            $('#arCodeSearch').select2({
                theme: 'bootstrap-5',
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
                // Populate inputs
                $('#ar_code').val(data.id);
                $('#ar_name').val(data.ar_name);
                $('#sale_name').val(data.sale_name || '');
                $('#amphur').val(data.amphur || '');
                $('#province').val(data.province || '');
                $('#zone').val(data.zone || '');
            });

            // Reset form handles
            $('#editCustomerForm').on('reset', function() {
                $('#arCodeSearch').val(null).trigger('change');
            });

            // Submit logic
            $('#btnSubmitUpdate').click(function() {
                const ar_code = $('#ar_code').val();
                const ar_name = $('#ar_name').val().trim();
                const amphur = $('#amphur').val().trim();
                const province = $('#province').val().trim();
                const zone = $('#zone').val().trim();

                if (!ar_code) {
                    Swal.fire('แจ้งเตือน', 'กรุณาค้นหาและเลือกลูกค้าก่อนทำรายการ', 'warning');
                    return;
                }

                if (!ar_name || !amphur || !province) {
                    Swal.fire('แจ้งเตือน', 'กรุณากรอกข้อมูลที่มีเครื่องหมาย * ให้ครบถ้วน', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'ยืนยันการบันทึกแก้ไข',
                    text: 'ข้อมูลที่แก้ไขจะถูกนำไปอัปเดตทุกรายการวางแผนของลูกค้ารายนี้ทั้งหมด คุณต้องการดำเนินการต่อหรือไม่?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'ตกลง',
                    cancelButtonText: 'ยกเลิก'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'กำลังบันทึกข้อมูล',
                            allowOutsideClick: false,
                            didOpen: () => { Swal.showLoading(); }
                        });

                        $.ajax({
                            url: 'forecast_customer_update_process.php',
                            type: 'POST',
                            data: {
                                ar_code: ar_code,
                                ar_name: ar_name,
                                amphur: amphur,
                                province: province,
                                zone: zone
                            },
                            dataType: 'json',
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        title: 'สำเร็จ',
                                        text: response.message,
                                        icon: 'success'
                                    }).then(() => {
                                        // Optional: Clear selection after save or leave as is
                                    });
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
