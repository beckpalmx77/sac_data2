<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    ?>

    <!DOCTYPE html>
    <html lang="th">
    <body id="page-top">
    <div id="wrapper">
        <?php
        include('includes/Side-Bar.php');
        ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php
                include('includes/Top-Bar.php');
                ?>
                <!-- Container Fluid-->
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h4 mb-0 text-gray-800"><?php echo urldecode($_GET['s']) ?></h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a>
                            </li>
                            <li class="breadcrumb-item"><?php echo urldecode($_GET['m']) ?></li>
                            <li class="breadcrumb-item active"
                                aria-current="page"><?php echo urldecode($_GET['s']) ?></li>
                        </ol>
                    </div>

                    <div class="row">
                        <!-- Column for Table Master CRUD -->
                        <div class="col-lg-12 mb-4">
                            <div class="card">
                                <div class="card-header py-3 bg-primary text-white d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-white"><i class="fas fa-boxes me-1"></i> จัดการระบบคลังรับเข้าสินค้า (Stock Product Receive Management)</h6>
                                </div>
                                <div class="card-body">
                                    <div class="mb-3 d-flex flex-wrap">
                                        <button type='button' name='btnExportExcel' id='btnExportExcel' class='btn btn-success mr-2 me-2 mb-2'>
                                            Export Excel <i class="fa fa-file-excel ms-1"></i>
                                        </button>
                                        <button type='button' name='btnExportExcelTires' id='btnExportExcelTires' class='btn btn-primary mr-2 me-2 mb-2'>
                                            Export Excel หมายเลขยาง <i class="fa fa-file-excel ms-1"></i>
                                        </button>
                                        <a href="manual_stock_product_receive.html" target="_blank" class="btn btn-outline-danger font-weight-bold mb-2">
                                            <i class="fas fa-info-circle mr-1 me-1"></i> คู่มือการใช้งานระบบ
                                        </a>
                                    </div>

                                    <div class="table-responsive">
                                        <table id='TableRecordList' class='table table-bordered table-striped table-hover w-100'>
                                            <thead>
                                                <tr>
                                                    <th>ชื่อผู้บันทึก</th>
                                                    <th>ชื่อลูกค้า</th>
                                                    <th>เลขทะเบียนรถ</th>
                                                    <th>รหัสสินค้า</th>
                                                    <th>วันที่บันทึก</th>
                                                    <th>Info</th>
                                                    <th>Delete</th>
                                                </tr>
                                            </thead>
                                            <tfoot>
                                                <tr>
                                                    <th>ชื่อผู้บันทึก</th>
                                                    <th>ชื่อลูกค้า</th>
                                                    <th>เลขทะเบียนรถ</th>
                                                    <th>รหัสสินค้า</th>
                                                    <th>วันที่บันทึก</th>
                                                    <th>Info</th>
                                                    <th>Delete</th>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                    <div id="result"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Add/Edit/Delete -->
                    <div class="modal fade" id="recordModal">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h4 class="modal-title font-weight-bold text-white">Modal title</h4>
                                    <button type="button" class="close text-white" data-dismiss="modal" aria-hidden="true">×</button>
                                </div>
                                <form method="post" id="recordForm">
                                    <div class="modal-body">
                                        <input type="hidden" class="form-control" id="user_id" name="user_id">

                                        <div class="form-group mb-3 d-flex align-items-end" style="gap: 15px;">
                                            <div style="flex: 1;">
                                                <label class="control-label font-weight-bold text-dark">ชื่อผู้บันทึก</label>
                                                <input type="text" class="form-control" id="display_name" name="display_name" readonly>
                                            </div>
                                            <div style="flex: 1;">
                                                <label class="control-label font-weight-bold text-dark">วันที่บันทึก</label>
                                                <input type="text" class="form-control" id="checkin_time" name="checkin_time" readonly>
                                            </div>
                                        </div>

                                        <div class="form-group mb-3 d-flex align-items-end" style="gap: 15px;">
                                            <div style="flex: 1;">
                                                <label class="control-label font-weight-bold text-dark">ชื่อลูกค้า</label>
                                                <input type="text" class="form-control" id="customer_name" name="customer_name" readonly>
                                            </div>
                                            <div style="flex: 1;">
                                                <label class="control-label font-weight-bold text-dark">เลขทะเบียนรถ</label>
                                                <input type="text" class="form-control" id="car_no" name="car_no" readonly>
                                            </div>
                                            <div style="flex: 1;">
                                                <label class="control-label font-weight-bold text-dark">รหัสสินค้า (Product ID)</label>
                                                <input type="text" class="form-control" id="product_id" name="product_id" readonly>
                                            </div>
                                        </div>

                                        <div class="form-group mb-3">
                                            <label class="control-label font-weight-bold text-dark">หมายเลขยาง/Bar Code <span id="remark_count" class="badge badge-info"></span></label>
                                            <textarea class="form-control" id="remark" name="remark" rows="5" placeholder="หมายเลขยาง/Bar Code" readonly></textarea>
                                        </div>

                                        <!-- Photo section (shown only on edit/view) -->
                                        <div class="form-group mb-3" id="photo_section" style="display:none;">
                                            <label class="control-label font-weight-bold text-dark">รูปภาพ</label>
                                            <div id="photo_gallery" class="d-flex flex-wrap gap-2 mt-2">
                                                <!-- images rendered by JS -->
                                            </div>
                                            <small class="text-muted" id="photo_path_raw"></small>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <input type="hidden" name="id" id="id"/>
                                        <input type="hidden" name="action" id="action" value=""/>
                                        <input type="submit" name="save" id="save" class="btn btn-primary" value="Save"/>
                                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close <i class="fa fa-times ms-1"></i></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <!-- /Modal -->

                    <!-- Modal Export Excel (General) -->
                    <div class="modal fade" id="exportExcelModal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-md" role="document">
                            <div class="modal-content">
                                <div class="modal-header bg-success text-white">
                                    <h5 class="modal-title font-weight-bold text-white"><i class="fa fa-file-excel me-2"></i> Export ข้อมูลเป็น Excel</h5>
                                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form method="POST" action="export_process/export_process_product_receive.php" target="_blank">
                                    <div class="modal-body">
                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-dark">จากวันที่บันทึก</label>
                                            <input type="date" class="form-control" name="date_start" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-dark">ถึงวันที่บันทึก</label>
                                            <input type="date" class="form-control" name="date_end" required>
                                        </div>
                                        <hr>
                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-dark">ชื่อลูกค้า (ระบุเพียงบางส่วนได้)</label>
                                            <input type="text" class="form-control" name="filter_customer" placeholder="ปล่อยว่างเพื่อเลือกทั้งหมด">
                                        </div>
                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-dark">หมายเลขทะเบียนรถ</label>
                                            <input type="text" class="form-control" name="filter_car_no" placeholder="เช่น กข-1234">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-success"><i class="fa fa-download me-1"></i> ดึงข้อมูลและดาวน์โหลด</button>
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิดหน้าต่าง</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Export Excel (Tires Serial barcodes) -->
                    <div class="modal fade" id="exportExcelTiresModal" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-md" role="document">
                            <div class="modal-content">
                                <div class="modal-header bg-primary text-white">
                                    <h5 class="modal-title font-weight-bold text-white"><i class="fa fa-file-excel me-2"></i> Export ข้อมูลหมายเลขยางเป็น Excel</h5>
                                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form method="POST" action="export_process/export_process_product_receive_tires.php" target="_blank">
                                    <div class="modal-body">
                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-dark">จากวันที่บันทึก</label>
                                            <input type="date" class="form-control" name="date_start" required>
                                        </div>
                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-dark">ถึงวันที่บันทึก</label>
                                            <input type="date" class="form-control" name="date_end" required>
                                        </div>
                                        <hr>
                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-dark">ชื่อลูกค้า (ระบุเพียงบางส่วนได้)</label>
                                            <input type="text" class="form-control" name="filter_customer" placeholder="ปล่อยว่างเพื่อเลือกทั้งหมด">
                                        </div>
                                        <div class="form-group mb-3">
                                            <label class="font-weight-bold text-dark">หมายเลขทะเบียนรถ</label>
                                            <input type="text" class="form-control" name="filter_car_no" placeholder="เช่น กข-1234">
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="submit" class="btn btn-primary"><i class="fa fa-download me-1"></i> ดึงข้อมูลและดาวน์โหลด</button>
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">ปิดหน้าต่าง</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Modal Image Preview (lightbox) -->
                    <div class="modal fade" id="imagePreviewModal" tabindex="-1">
                        <div class="modal-dialog modal-dialog-centered modal-lg">
                            <div class="modal-content bg-dark">
                                <div class="modal-header border-0">
                                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <div class="modal-body text-center p-0 pb-4">
                                    <img id="previewImg" src="" style="max-width: 90%; max-height: 80vh; border-radius: 8px;">
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

    <!-- Datatables Scripts -->
    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>

    <!-- Custom Style -->
    <link href="css/spinner_over.css" rel="stylesheet"/>

    <style>
        .photo-thumb {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 6px;
            cursor: pointer;
            border: 2px solid #ddd;
            transition: all 0.2s;
        }
        .photo-thumb:hover {
            border-color: #10b981;
            transform: scale(1.05);
        }
    </style>

    <script>
        $(document).ready(function () {
            let formData = {action: "GET_RECEIVE", sub_action: "GET_MASTER"};
            let dataRecords = $('#TableRecordList').DataTable({
                'lengthMenu': [[6, 10, 20, 50, 100], [6, 10, 20, 50, 100]],
                'language': {
                    search: 'ค้นหา', lengthMenu: 'แสดง _MENU_ รายการ',
                    info: 'หน้าที่ _PAGE_ จาก _PAGES_',
                    infoEmpty: 'ไม่มีข้อมูล',
                    zeroRecords: "ไม่มีข้อมูลตามเงื่อนไข",
                    infoFiltered: '(กรองข้อมูลจากทั้งหมด _MAX_ รายการ)',
                    paginate: {
                        previous: 'ก่อนหน้า',
                        last: 'สุดท้าย',
                        next: 'ต่อไป'
                    }
                },
                'processing': true,
                'serverSide': true,
                'serverMethod': 'post',
                'ajax': {
                    'url': 'model/manage_stock_product_receive_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'display_name'},
                    {data: 'customer_name'},
                    {data: 'car_no'},
                    {data: 'product_id'},
                    {data: 'checkin_time'},
                    {data: 'info'},
                    {data: 'delete'}
                ]
            });

            // Submit Form
            $("#recordForm").on('submit', function (event) {
                event.preventDefault();
                let currentRemark = $('#remark').val();
                let remarkForDB = currentRemark.split('\n').filter(n => n.trim() !== '').join(',');

                let postData = $(this).serializeArray();
                postData.forEach(item => {
                    if (item.name === "remark") item.value = remarkForDB;
                });

                $.ajax({
                    url: 'model/manage_stock_product_receive_process.php',
                    method: "POST",
                    data: postData,
                    success: function (data) {
                        $('#recordModal').modal('hide');
                        dataRecords.ajax.reload(null, false);
                    },
                    error: function (err) {
                        alert("เกิดข้อผิดพลาดในการบันทึกข้อมูล");
                    }
                });
            });
        });
    </script>

    <!-- Helper: render photo gallery -->
    <script>
        const IMG_BASE_URL = 'http://171.100.56.194:8999/img_product_receive/img/';

        function renderPhotos(photoPathValue) {
            let gallery = $('#photo_gallery');
            gallery.html('');
            if (!photoPathValue || photoPathValue.trim() === '') {
                $('#photo_section').hide();
                return;
            }
            let files = photoPathValue.split(',');
            let hasImage = false;
            files.forEach(function (filename) {
                filename = filename.trim();
                if (filename !== '') {
                    hasImage = true;
                    let imgUrl = IMG_BASE_URL + filename;
                    let img = $('<img>')
                        .addClass('photo-thumb')
                        .attr('src', imgUrl)
                        .attr('alt', filename)
                        .attr('title', filename)
                        .on('click', function () {
                            $('#previewImg').attr('src', imgUrl);
                            $('#imagePreviewModal').modal('show');
                        })
                        .on('error', function () {
                            $(this).attr('src', 'vendor/images/no-image.png').off('error');
                        });
                    gallery.append(img);
                }
            });
            if (hasImage) {
                $('#photo_section').show();
            } else {
                $('#photo_section').hide();
            }
        }
    </script>

    <script>
        $(document).ready(function () {
            // ปุ่มเปิด Popup Export
            $('#btnExportExcel').click(function () {
                $('.modal-title').html("<i class='fa fa-file-excel'></i> Export ข้อมูลเป็น Excel");
                $('#exportExcelModal').modal('show');
            });
            
            // ปุ่มเปิด Popup Export หมายเลขยาง
            $('#btnExportExcelTires').click(function () {
                $('#exportExcelTiresModal').modal('show');
            });
        });
    </script>

    <!-- Update button -->
    <script>
        $("#TableRecordList").on('click', '.info', function () {
            let id = $(this).attr("id");
            let formData = {action: "GET_DATA", id: id};
            $.ajax({
                type: "POST",
                url: 'model/manage_stock_product_receive_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let r = response[i];
                        $('#recordModal').modal('show');
                        $('#id').val(r.id);
                        $('#user_id').val(r.user_id);
                        $('#display_name').val(r.display_name);
                        $('#checkin_time').val(r.checkin_time);
                        $('#customer_name').val(r.customer_name);
                        $('#car_no').val(r.car_no);
                        $('#product_id').val(r.product_id || '');
                        if (r.remark) {
                            let items = r.remark.split(',').filter(item => item.trim() !== "");
                            $('#remark').val(items.join('\n'));
                            $('#remark_count').text("จำนวนรายการ: " + items.length);
                        } else {
                            $('#remark').val('');
                            $('#remark_count').text("จำนวนรายการ: 0");
                        }
                        renderPhotos(r.photo_path);
                        $('.modal-title').html("<i class='fa fa-edit'></i> View Detail Record");
                        $('#action').val('INFO');
                        $('#save').val('Save');
                        $('#save').prop('disabled', true);
                    }
                },
                error: function (response) {
                    alertify.error("error : " + response);
                }
            });
        });
    </script>

    <!-- Delete button -->
    <script>
        $("#TableRecordList").on('click', '.delete', function () {
            let id = $(this).attr("id");
            let formData = {action: "GET_DATA", id: id};
            $.ajax({
                type: "POST",
                url: 'model/manage_stock_product_receive_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let r = response[i];
                        $('#recordModal').modal('show');
                        $('#id').val(r.id);
                        $('#user_id').val(r.user_id);
                        $('#display_name').val(r.display_name);
                        $('#customer_name').val(r.customer_name);
                        $('#car_no').val(r.car_no);
                        if (r.remark) {
                            let items = r.remark.split(',').filter(item => item.trim() !== "");
                            $('#remark').val(items.join('\n'));
                            $('#remark_count').text("จำนวนรายการ: " + items.length);
                        } else {
                            $('#remark').val('');
                            $('#remark_count').text("จำนวนรายการ: 0");
                        }
                        renderPhotos(r.photo_path);
                        $('.modal-title').html("<i class='fa fa-trash'></i> Delete Record");
                        $('#action').val('DELETE');
                        $('#save').prop('disabled', false);
                        $('#save').val('Confirm Delete');
                    }
                },
                error: function (response) {
                    alertify.error("error : " + response);
                }
            });
        });
    </script>

    </body>
    </html>

<?php } ?>
