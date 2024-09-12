<?php

include('includes/Header.php');
$curr_date = date("d-m-Y");

if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    $create_by = $_SESSION['user_id'];
    $doc_user_id = $_SESSION['doc_user_id'];
    ?>

    <!DOCTYPE html>
    <html lang="th">
    <body id="page-top">
    <div id="wrapper">

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- Container Fluid-->
                <div class="container-fluid" id="container-wrapper">

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-12">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                </div>
                                <div class="card-body">
                                    <section class="container-fluid">

                                        <div class="row">
                                            <div class="col-md-6 mb-3"> <!-- เพิ่ม mb-3 ที่นี่ -->
                                                <span><b>รายการย้ายสินค้า <?php echo $_SESSION['first_name'] . " " . $_SESSION['last_name'] ?></b></span>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-6 mb-3"> <!-- เพิ่ม mb-3 ที่นี่ -->
                                                <button type='button' name='btnAdd' id='btnAdd'
                                                        class='btn btn-primary btn-xs'>Add
                                                    <i class="fa fa-plus"></i>
                                                </button>
                                                <button type='button' name='backBtn' id='backBtn'
                                                        class='btn btn-danger btn-xs'>กลับหน้าแรก
                                                    <i class="fa fa-reply"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="col-md-12 col-md-offset-2">
                                            <table id='TableRecordList' class='display dataTable'>
                                                <thead>
                                                <tr>
                                                    <th>วันที่</th>
                                                    <th>รหัสสินค้า</th>
                                                    <th>รายละเอียด</th>
                                                    <th>จำนวน</th>
                                                    <th>คลังปี</th>
                                                    <th>สัปดาห์</th>
                                                    <th>จากตำแหน่ง</th>
                                                    <th>ไปตำแหน่ง</th>
                                                    <th>Action</th>

                                                </tr>
                                                </thead>
                                                <tfoot>
                                                <tr>
                                                    <th>วันที่</th>
                                                    <th>รหัสสินค้า</th>
                                                    <th>รายละเอียด</th>
                                                    <th>จำนวน</th>
                                                    <th>คลังปี</th>
                                                    <th>สัปดาห์</th>
                                                    <th>จากตำแหน่ง</th>
                                                    <th>ไปตำแหน่ง</th>
                                                    <th>Action</th>
                                                </tr>
                                                </tfoot>
                                            </table>

                                            <div id="result"></div>

                                        </div>

                                        <div class="modal fade" id="recordModal">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Modal title</h4>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                    </div>
                                                    <form method="post" id="recordForm">
                                                        <div class="modal-body">
                                                            <div class="container-fluid">
                                                                <!-- ใช้ container-fluid เพื่อให้เต็มความกว้างของ modal -->
                                                                <!-- กลุ่มฟอร์มที่ 1 -->
                                                                <div class="row">
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="doc_date" class="control-label">วันที่</label>
                                                                            <div class="input-group">
                                                                                <input type="text" class="form-control"
                                                                                       id="doc_date" name="doc_date"
                                                                                       readonly="true"
                                                                                       value="<?php echo $curr_date; ?>">
                                                                                <div class="input-group-append">
                                                                                    <span class="input-group-text"><i
                                                                                                class="glyphicon glyphicon-th"></i></span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <!-- กลุ่มฟอร์มที่ 2 -->
                                                                <div class="row">
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="product_id"
                                                                                   class="control-label">รหัสสินค้า</label>
                                                                            <select class="form-control" id="product_id"
                                                                                    name="product_id" required>
                                                                                <option value="">เลือกรหัสสินค้า
                                                                                </option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="product_name"
                                                                                   class="control-label">รายละเอียด</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="product_name" name="product_name"
                                                                                   readonly placeholder="รายละเอียด"
                                                                                   required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="qty"
                                                                                   class="control-label">จำนวน</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="qty" name="qty" placeholder=""
                                                                                   required>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <!-- กลุ่มฟอร์มที่ 3 -->
                                                                <div class="row">
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="wh_org" class="control-label">คลังปี</label>
                                                                            <select class="form-control" id="wh_org"
                                                                                    name="wh_org" required>
                                                                                <option value="">เลือกคลังปี</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="wh_week_id"
                                                                                   class="control-label">สัปดาห์</label>
                                                                            <select class="form-control"
                                                                                    id="wh_week_id"
                                                                                    name="wh_week_id" required>
                                                                                <option value="">สัปดาห์</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="location_org"
                                                                                   class="control-label">จากตำแหน่ง</label>
                                                                            <select class="form-control"
                                                                                    id="location_org"
                                                                                    name="location_org" required>
                                                                                <option value="">จากตำแหน่ง</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="location_to"
                                                                                   class="control-label">ไปตำแหน่ง</label>
                                                                            <select class="form-control"
                                                                                    id="location_to" name="location_to"
                                                                                    required>
                                                                                <option value="">ไปตำแหน่ง</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <input type="hidden" name="id" id="id"/>
                                                            <input type="hidden" name="action" id="action" value=""/>
                                                            <input type="hidden" name="create_by" id="create_by" value="<?php echo $create_by; ?>"/>
                                                            <input type="hidden" name="doc_user_id" id="doc_user_id" value="<?php echo $doc_user_id; ?>"/>
                                                            <button type="submit" name="save" id="save"
                                                                    class="btn btn-primary"><i class="fa fa-check"></i>
                                                                Save
                                                            </button>
                                                            <button type="button" class="btn btn-danger"
                                                                    data-dismiss="modal">Close <i
                                                                        class="fa fa-window-close"></i></button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <?php
    include('includes/Modal-Logout.php');
    include('includes/Footer.php');
    ?>


    <!-- Scroll to top -->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>


    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>

    <!-- Page level plugins -->

    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script src="vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
    <script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
    <link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>


    <style>

        .icon-input-btn {
            display: inline-block;
            position: relative;
        }

        .icon-input-btn input[type="submit"] {
            padding-left: 2em;
        }

        .icon-input-btn .fa {
            display: inline-block;
            position: absolute;
            left: 0.65em;
            top: 30%;
        }
    </style>

    <style>
        /* กำหนดความสูงของ select ให้เท่ากับ input text */
        .form-control {
            height: calc(1.5em + 0.75rem + 2px); /* ใช้ขนาดเดียวกันกับ input */
        }

        /* สำหรับ Select2 เพื่อให้ช่อง select มีขนาดสูงเท่ากับ input */
        .select2-container .select2-selection--single {
            height: calc(1.5em + 0.75rem + 2px) !important;
            padding: 0.375rem 0.75rem; /* ใช้ padding เดียวกันกับ input */
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: calc(1.5em + 0.75rem + 2px) !important; /* จัดแนวข้อความกลาง */
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: calc(1.5em + 0.75rem + 2px) !important; /* ปรับขนาดลูกศรให้สูงเท่ากับ select */
        }
    </style>

    <script>
        $(document).ready(function () {
            $(".icon-input-btn").each(function () {
                let btnFont = $(this).find(".btn").css("font-size");
                let btnColor = $(this).find(".btn").css("color");
                $(this).find(".fa").css({'font-size': btnFont, 'color': btnColor});
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            let today = new Date();
            let doc_date = getDay2Digits(today) + "-" + getMonth2Digits(today) + "-" + today.getFullYear();
            $('#doc_date').val(doc_date);
        });
    </script>

    <script>
        $(document).ready(function () {
            $('#doc_date').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });
        });
    </script>

    <script>
        document.getElementById('qty').addEventListener('input', function (e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>

    <script>
        $(document).ready(function () {
            $("#backBtn").click(function () {
                window.location.href = "Dashboard_stock";
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            let formData = {action: "GET_MOVEMENT", sub_action: "GET_MASTER"};
            let dataRecords = $('#TableRecordList').DataTable({
                'lengthMenu': [[5, 10, 20, 50, 100], [5, 10, 20, 50, 100]],
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
                'autoWidth': true,
                'searching': true,
                <?php  if ($_SESSION['deviceType'] !== 'computer') {
                    echo "'scrollX': true,";
                }?>
                'serverMethod': 'post',
                'ajax': {
                    'url': 'model/manage_movement_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'doc_date'},
                    {data: 'product_id'},
                    {data: 'product_name'},
                    {data: 'qty'},
                    {data: 'wh_org'},
                    {data: 'wh_week_id'},
                    {data: 'location_org'},
                    {data: 'location_to'},
                    {data: 'update'},
                ]
            });

            <!-- *** FOR SUBMIT FORM *** -->
            $("#recordModal").on('submit', '#recordForm', function (event) {
                event.preventDefault();
                $('#save').attr('disabled', 'disabled');
                let formData = $(this).serialize();
                //alert(formData);
                $.ajax({
                    url: 'model/manage_movement_process.php',
                    method: "POST",
                    data: formData,
                    success: function (data) {
                        alertify.success(data);
                        $('#recordForm')[0].reset();
                        $('#recordModal').modal('hide');
                        $('#save').attr('disabled', false);
                        dataRecords.ajax.reload();
                    }
                })
            });
            <!-- *** FOR SUBMIT FORM *** -->
        });

    </script>

    <script>
        $(document).ready(function () {
            $("#btnAdd").click(function () {
                $('#recordModal').modal('show');
                $('#id').val("");
                $('#product_id').val(null).trigger('change');
                $('#qty').val("");
                $('#wh_org').val(null).trigger('change');
                $('#wh_week_id').val(null).trigger('change');
                $('#location_org').val(null).trigger('change');
                $('#location_to').val(null).trigger('change');
                $('.modal-title').html("<i class='fa fa-plus'></i> ADD Record");
                $('#action').val('ADD');
                $('#save').val('Save');
            });
        });
    </script>

    <script>

        $("#TableRecordList").on('click', '.update', function () {
            let id = $(this).attr("id");
            //alert(id);
            let formData = {action: "GET_DATA", id: id};
            $.ajax({
                type: "POST",
                url: 'model/manage_movement_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let doc_date = response[i].doc_date;
                        let product_id = response[i].product_id;
                        let product_name = response[i].product_name;
                        let qty = response[i].qty;
                        let wh_org = response[i].wh_org;
                        let wh_week_id = response[i].wh_week_id;
                        let location_org = response[i].location_org;
                        let location_to = response[i].location_to;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#doc_date').val(doc_date);
                        $('#product_id').val(product_id).trigger('change');
                        $('#product_name').val(product_name);
                        $('#qty').val(qty);
                        $('#wh_org').val(wh_org).trigger('change');
                        $('#wh_week_id').val(wh_week_id).trigger('change');
                        $('#location_org').val(location_org).trigger('change');
                        $('#location_to').val(location_to).trigger('change');
                        $('.modal-title').html("<i class='fa fa-plus'></i> Edit Record");
                        $('#action').val('UPDATE');
                        $('#save').val('Save');
                    }
                },
                error: function (response) {
                    alertify.error("error : " + response);
                }
            });
        });

    </script>

    <script>

        $("#TableRecordList").on('click', '.delete', function () {
            let id = $(this).attr("id");
            let formData = {action: "GET_DATA", id: id};
            $.ajax({
                type: "POST",
                url: 'model/manage_movement_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let doc_date = response[i].doc_date;
                        let product_id = response[i].product_id;
                        let product_name = response[i].product_name;
                        let qty = response[i].qty;
                        let wh_org = response[i].wh_org;
                        let wh_week_id = response[i].wh_week_id;
                        let location_org = response[i].location_org;
                        let location_to = response[i].location_to;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#doc_date').val(doc_date);
                        $('#product_id').val(product_id).trigger('change');
                        $('#product_name').val(product_name);
                        $('#qty').val(qty);
                        $('#wh_org').val(wh_org).trigger('change');
                        $('#wh_week_id').val(wh_week_id).trigger('change');
                        $('#location_org').val(location_org).trigger('change');
                        $('#location_to').val(location_to).trigger('change');
                        $('.modal-title').html("<i class='fa fa-minus'></i> Delete Record");
                        $('#action').val('DELETE');
                        $('#save').val('Confirm Delete');
                    }
                },
                error: function (response) {
                    alertify.error("error : " + response);
                }
            });
        });

    </script>

    <script>
        $(document).ready(function () {
            // AJAX เพื่อดึงข้อมูลจากฐานข้อมูล
            $.ajax({
                url: 'model/get_products.php', // หน้า PHP ที่จะดึงข้อมูล
                method: 'GET',
                dataType: 'json',
                success: function (data) {
                    let select = $('#product_id');
                    $.each(data, function (index, product) {
                        select.append($('<option>', {
                            value: product.product_id,
                            text: product.product_id, // เปลี่ยนเป็นชื่อของข้อมูลที่คุณต้องการแสดง
                            'data-name': product.product_name // เก็บข้อมูลชื่อใน attribute เพื่อใช้ภายหลัง
                        }));
                    });

                    // แปลง select เป็น select2 หลังจากข้อมูลถูกเพิ่ม
                    $('#product_id').select2({
                        placeholder: "เลือกรหัสสินค้า",
                        allowClear: true,
                        width: '100%' // กำหนดขนาดให้เต็ม 100% เพื่อให้ตรงกับ element อื่น
                    });
                },
                error: function (xhr, status, error) {
                    console.error('เกิดข้อผิดพลาดในการดึงข้อมูล:', error);
                }
            });

            // เมื่อมีการเปลี่ยนแปลงค่าใน select
            $('#product_id').on('change', function () {
                let selectedOption = $(this).find('option:selected');
                let productName = selectedOption.data('name'); // ดึงข้อมูล product_name จาก attribute
                $('#product_name').val(productName); // กำหนดค่าให้กับ input ของ product_name
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            // AJAX เพื่อดึงข้อมูลจากฐานข้อมูล
            $.ajax({
                url: 'model/get_warehouse.php', // หน้า PHP ที่จะดึงข้อมูล
                method: 'GET',
                dataType: 'json',
                success: function (data) {
                    let select = $('#wh_org');
                    $.each(data, function (index, warehouse) {
                        select.append($('<option>', {
                            value: warehouse.warehouse_id,
                            text: warehouse.warehouse_id, // เปลี่ยนเป็นชื่อของข้อมูลที่คุณต้องการแสดง
                            'data-name': warehouse.warehouse_id // เก็บข้อมูลชื่อใน attribute เพื่อใช้ภายหลัง
                        }));
                    });

                    // แปลง select เป็น select2 หลังจากข้อมูลถูกเพิ่ม
                    $('#wh_org').select2({
                        placeholder: "เลือกคลังปี",
                        allowClear: true,
                        width: '100%' // กำหนดขนาดให้เต็ม 100% เพื่อให้ตรงกับ element อื่น
                    });
                },
                error: function (xhr, status, error) {
                    console.error('เกิดข้อผิดพลาดในการดึงข้อมูล:', error);
                }
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            // AJAX เพื่อดึงข้อมูลจากฐานข้อมูล
            $.ajax({
                url: 'model/get_wh_location.php', // หน้า PHP ที่จะดึงข้อมูล
                method: 'GET',
                dataType: 'json',
                success: function (data) {
                    let select = $('#location_org');
                    $.each(data, function (index, wh_location) {
                        select.append($('<option>', {
                            value: wh_location.location_id,
                            text: wh_location.location_id, // เปลี่ยนเป็นชื่อของข้อมูลที่คุณต้องการแสดง
                            'data-name': wh_location.location_id // เก็บข้อมูลชื่อใน attribute เพื่อใช้ภายหลัง
                        }));
                    });

                    // แปลง select เป็น select2 หลังจากข้อมูลถูกเพิ่ม
                    $('#location_org').select2({
                        placeholder: "เลือกตำแหน่ง",
                        allowClear: true,
                        width: '100%' // กำหนดขนาดให้เต็ม 100% เพื่อให้ตรงกับ element อื่น
                    });
                },
                error: function (xhr, status, error) {
                    console.error('เกิดข้อผิดพลาดในการดึงข้อมูล:', error);
                }
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            // AJAX เพื่อดึงข้อมูลจากฐานข้อมูล
            $.ajax({
                url: 'model/get_wh_location.php', // หน้า PHP ที่จะดึงข้อมูล
                method: 'GET',
                dataType: 'json',
                success: function (data) {
                    let select = $('#location_to');
                    $.each(data, function (index, wh_location) {
                        select.append($('<option>', {
                            value: wh_location.location_id,
                            text: wh_location.location_id, // เปลี่ยนเป็นชื่อของข้อมูลที่คุณต้องการแสดง
                            'data-name': wh_location.location_id // เก็บข้อมูลชื่อใน attribute เพื่อใช้ภายหลัง
                        }));
                    });

                    // แปลง select เป็น select2 หลังจากข้อมูลถูกเพิ่ม
                    $('#location_to').select2({
                        placeholder: "เลือกตำแหน่ง",
                        allowClear: true,
                        width: '100%' // กำหนดขนาดให้เต็ม 100% เพื่อให้ตรงกับ element อื่น
                    });
                },
                error: function (xhr, status, error) {
                    console.error('เกิดข้อผิดพลาดในการดึงข้อมูล:', error);
                }
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            // AJAX เพื่อดึงข้อมูลจากฐานข้อมูล
            $.ajax({
                url: 'model/get_wh_week.php', // หน้า PHP ที่จะดึงข้อมูล
                method: 'GET',
                dataType: 'json',
                success: function (data) {
                    let select = $('#wh_week_id');
                    $.each(data, function (index, wh_week) {
                        select.append($('<option>', {
                            value: wh_week.wh_week_id,
                            text: wh_week.wh_week_id, // เปลี่ยนเป็นชื่อของข้อมูลที่คุณต้องการแสดง
                            'data-name': wh_week.wh_week_id // เก็บข้อมูลชื่อใน attribute เพื่อใช้ภายหลัง
                        }));
                    });

                    // แปลง select เป็น select2 หลังจากข้อมูลถูกเพิ่ม
                    $('#wh_week_id').select2({
                        placeholder: "เลือก week",
                        allowClear: true,
                        width: '100%' // กำหนดขนาดให้เต็ม 100% เพื่อให้ตรงกับ element อื่น
                    });
                },
                error: function (xhr, status, error) {
                    console.error('เกิดข้อผิดพลาดในการดึงข้อมูล:', error);
                }
            });
        });
    </script>

    </body>
    </html>

<?php } ?>