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
                        <div class="col-lg-12">
                            <div class="card mb-12">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                </div>
                                <div class="card-body">
                                    <section class="container-fluid">
                                        <form id="export_data" method="POST" action="export_process/export_process_data_wh_movement_out.php" enctype="multipart/form-data">
                                            <div class="col-md-12 col-md-offset-2"
                                                 style="display: flex; align-items: center; gap: 10px;">
                                                <button type="button" name="btnRefresh" id="btnRefresh"
                                                        class="btn btn-success btn-xs" onclick="ReloadDataTable();">
                                                    Refresh <i class="fa fa-refresh"></i>
                                                </button>
                                                <label for="name_t" class="control-label mb-0"><b>วัน</b></label>
                                                <input type="text" class="form-control" id="doc_date_start"
                                                       name="doc_date_start" readonly="true"
                                                       style="width: calc(0.6em * 10 + 1.25rem);"
                                                       value="<?php echo $curr_date; ?>">
                                                <label for="name_t" class="control-label mb-0"><b>-</b></label>
                                                <input type="text" class="form-control" id="doc_date_to"
                                                       name="doc_date_to" readonly="true"
                                                       style="width: calc(0.6em * 10 + 1.25rem);"
                                                       value="<?php echo $curr_date; ?>">
                                                <label for="car_no_main"
                                                       class="control-label mb-0"><b>รถคันที่</b></label>
                                                <select id="car_no_main" name="car_no_main" class="form-control"
                                                        style="width: 100px;">
                                                    <option value="-">-</option>
                                                    <?php for ($car_no = 1; $car_no <= 12; $car_no++) { ?>
                                                        <option value="<?php echo $car_no ?>"><?php echo $car_no ?></option>
                                                    <?php } ?>
                                                </select>

                                                <!--button type="button" name="btnFilter" id="btnFilter"
                                                        class="btn btn-primary btn-xs">FilterData <i
                                                            class="fa fa-filter"></i></button-->
                                                <button type="submit" name="btnExport" id="btnExport"
                                                        class="btn btn-success btn-xs" onclick="">Export <i
                                                            class="fa fa-file-excel-o"></i></button>
                                                <button type="button" name="btnPrint" id="btnPrint"
                                                        class="btn btn-primary btn-xs" onclick="PrintData();">Print <i
                                                            class="fa fa-print"></i></button>
                                            </div>

                                            <input type="hidden" id="search_value" name="search_value" value="">

                                        </form>

                                        <div id="output_area"></div>
                                        <br>
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
                                                    <th>ตำแหน่ง</th>
                                                    <th>เลขที่เอกสาร</th>
                                                    <th>รถคันที่</th>
                                                    <th>เทค</th>
                                                    <th>supplier/ลูกค้า</th>
                                                    <th>คงเหลือ</th>
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
                                                    <th>ตำแหน่ง</th>
                                                    <th>เลขที่เอกสาร</th>
                                                    <th>รถคันที่</th>
                                                    <th>เทค</th>
                                                    <th>supplier/ลูกค้า</th>
                                                    <th>คงเหลือ</th>
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
                                                                <input type="hidden" class="form-control"
                                                                       id="doc_id" name="doc_id"
                                                                       readonly="true"
                                                                       value="">
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
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="product_name"
                                                                                   class="control-label">รายละเอียด</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="product_name" name="product_name"
                                                                                   readonly placeholder="รายละเอียด"
                                                                                   required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-2">
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
                                                                </div>

                                                                <!-- กลุ่มฟอร์มที่ 4 -->
                                                                <div class="row">
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="car_no" class="control-label">รถคันที่</label>
                                                                            <select class="form-control" id="car_no"
                                                                                    name="car_no" required>
                                                                                <option value="">รถคันที่</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="location_to"
                                                                                   class="control-label">ไป</label>
                                                                            <select class="form-control"
                                                                                    id="location_to"
                                                                                    name="location_to" required>
                                                                                <option value="">ไป</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="remark"
                                                                                   class="control-label">หมายเหตุ</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="remark" name="remark"
                                                                                   placeholder=""
                                                                                   required>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <input type="hidden" name="id" id="id"/>
                                                            <input type="hidden" name="line_no_master"
                                                                   id="line_no_master"/>
                                                            <input type="hidden" name="action" id="action" value=""/>
                                                            <input type="hidden" name="create_by" id="create_by"
                                                                   value="<?php echo $create_by; ?>"/>
                                                            <input type="hidden" name="doc_user_id" id="doc_user_id"
                                                                   value="<?php echo $doc_user_id; ?>"/>
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
            //document.getElementById('doc_date').value = doc_date;
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
        $(document).ready(function () {
            let today = new Date();
            let doc_date = getDay2Digits(today) + "-" + getMonth2Digits(today) + "-" + today.getFullYear();
            $('#doc_date_start').val(doc_date);
            //document.getElementById('doc_date').value = doc_date;
        });
    </script>

    <script>
        $(document).ready(function () {
            $('#doc_date_start').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            let today = new Date();
            let doc_date = getDay2Digits(today) + "-" + getMonth2Digits(today) + "-" + today.getFullYear();
            $('#doc_date_to').val(doc_date);
            //document.getElementById('doc_date').value = doc_date;
        });
    </script>

    <script>
        $(document).ready(function () {
            $('#doc_date_to').datepicker({
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
            let doc_date_start = $('#doc_date_start').val();
            let doc_date_to = $('#doc_date_to').val();
            let formData = {
                action: "GET_MOVEMENT_OUT",
                sub_action: "GET_MASTER",
                doc_date_start: doc_date_start,
                doc_date_to: doc_date_to
            };
            let dataRecords = $('#TableRecordList').DataTable({
                'lengthMenu': [[5, 10, 20, 50, 100], [5, 10, 20, 50, 100]],
                'language': {
                    search: 'ค้นหาตามเลขที่เอกสาร', lengthMenu: 'แสดง _MENU_ รายการ',
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
                    'url': 'model/manage_movement_out_process.php',
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
                    {data: 'doc_id'},
                    {data: 'car_no'},
                    {data: 'sale_take'},
                    {data: 'customer_name'},
                    {data: 'total_qty'},
                    {data: 'update'},
                ]
            });

            $('#btnFilter').click(function () {
                dataRecords.ajax.reload();
            });

            setInterval(function () {
                dataRecords.ajax.reload(null, false); // รีเฟรชตารางทุกๆ 5 นาที
            }, 300000);
        });

        // ฟังก์ชันรีเฟรช DataTable
        function ReloadDataTable() {
            $('#TableRecordList').DataTable().ajax.reload(null, false);
        }

        // ฟังก์ชันพิมพ์ข้อมูล
        function PrintData() {
            let doc_date_start = $('#doc_date_start').val();
            let doc_date_to = $('#doc_date_to').val();

            window.open('print_preview.php?doc_date_start=' + doc_date_start + '&doc_date_to=' + doc_date_to, '_blank');
        }
    </script>

    <script>
        $(document).ready(function () {
            <!-- *** FOR SUBMIT FORM *** -->
            $("#recordModal").on('submit', '#recordForm', function (event) {
                event.preventDefault();
                $('#save').attr('disabled', 'disabled');
                let formData = $(this).serialize();
                //alert(formData);
                $.ajax({
                    url: 'model/manage_movement_out_process.php',
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
                $('#doc_id').val("");
                $('#product_id').val(null).trigger('change');
                $('#qty').val("");
                $('#remark').val("");
                $('#wh_org').val(null).trigger('change');
                $('#wh_week_id').val(null).trigger('change');
                $('#location_org').val(null).trigger('change');
                $('#location_to').val(null).trigger('change');
                $('#car_no').val(null).trigger('change');
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
                url: 'model/manage_movement_out_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let doc_id = response[i].doc_id;
                        let doc_date = response[i].doc_date;
                        let product_id = response[i].product_id;
                        let product_name = response[i].product_name;
                        let qty = response[i].qty;
                        let car_no = response[i].car_no;
                        let wh_org = response[i].wh_org;
                        let wh_week_id = response[i].wh_week_id;
                        let location_org = response[i].location_org;
                        let location_to = response[i].location_to;
                        let line_no_master = response[i].line_no;
                        let remark = response[i].remark;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#doc_id').val(doc_id);
                        $('#doc_date').val(doc_date);
                        $('#product_id').val(product_id).trigger('change');
                        $('#product_name').val(product_name);
                        $('#qty').val(qty);
                        $('#line_no_master').val(line_no_master);
                        $('#wh_org').val(wh_org).trigger('change');
                        $('#wh_week_id').val(wh_week_id).trigger('change');
                        $('#location_org').val(location_org).trigger('change');
                        $('#location_to').val(location_to).trigger('change');
                        $('#car_no').val(car_no).trigger('change');
                        $('#remark').val(remark);
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
                url: 'model/manage_movement_out_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let doc_id = response[i].doc_id;
                        let doc_date = response[i].doc_date;
                        let product_id = response[i].product_id;
                        let product_name = response[i].product_name;
                        let qty = response[i].qty;
                        let car_no = response[i].car_no;
                        let wh_org = response[i].wh_org;
                        let wh_week_id = response[i].wh_week_id;
                        let location_org = response[i].location_org;
                        let location_to = response[i].location_to;
                        let line_no_master = response[i].line_no;
                        let remark = response[i].remark;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#doc_id').val(doc_id);
                        $('#doc_date').val(doc_date);
                        $('#product_id').val(product_id).trigger('change');
                        $('#product_name').val(product_name);
                        $('#qty').val(qty);
                        $('#line_no_master').val(line_no_master);
                        $('#wh_org').val(wh_org).trigger('change');
                        $('#wh_week_id').val(wh_week_id).trigger('change');
                        $('#location_org').val(location_org).trigger('change');
                        $('#location_to').val(location_to).trigger('change');
                        $('#car_no').val(car_no).trigger('change');
                        $('#remark').val(remark);
                        $('.modal-title').html("<i class='fa fa-plus'></i> Delete Record");
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
                url: 'model/get_wh_location_out.php', // หน้า PHP ที่จะดึงข้อมูล
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
                        placeholder: "เลือกปลายทาง",
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

    <script>
        $(document).ready(function () {
            // AJAX เพื่อดึงข้อมูลจากฐานข้อมูล
            $.ajax({
                url: 'model/get_wh_car_no.php', // หน้า PHP ที่จะดึงข้อมูล
                method: 'GET',
                dataType: 'json',
                success: function (data) {
                    let select = $('#car_no');
                    $.each(data, function (index, wh_car_no) {
                        select.append($('<option>', {
                            value: wh_car_no.car_no,
                            text: wh_car_no.car_no, // เปลี่ยนเป็นชื่อของข้อมูลที่คุณต้องการแสดง
                            'data-name': wh_car_no.car_no // เก็บข้อมูลชื่อใน attribute เพื่อใช้ภายหลัง
                        }));
                    });

                    // แปลง select เป็น select2 หลังจากข้อมูลถูกเพิ่ม
                    $('#car_no').select2({
                        placeholder: "เลือกรถคันที่",
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

    <!--script>
        $(document).ready(function () {
            // AJAX เพื่อดึงข้อมูลจากฐานข้อมูล
            $.ajax({
                url: 'model/get_wh_cars.php', // หน้า PHP ที่จะดึงข้อมูล
                method: 'GET',
                dataType: 'json',
                success: function (data) {
                    let select = $('#cars');
                    $.each(data, function (index, wh_car_no) {
                        select.append($('<option>', {
                            value: wh_car_no.car_no,
                            text: wh_car_no.car_no, // เปลี่ยนเป็นชื่อของข้อมูลที่คุณต้องการแสดง
                            'data-name': wh_car_no.car_no // เก็บข้อมูลชื่อใน attribute เพื่อใช้ภายหลัง
                        }));
                    });

                    // แปลง select เป็น select2 หลังจากข้อมูลถูกเพิ่ม
                    $('#cars').select2({
                        placeholder: "เลือกรถคันที่",
                        allowClear: true,
                        width: '100%' // กำหนดขนาดให้เต็ม 100% เพื่อให้ตรงกับ element อื่น
                    });
                },
                error: function (xhr, status, error) {
                    console.error('เกิดข้อผิดพลาดในการดึงข้อมูล:', error);
                }
            });
        });
    </script-->


    <script>
        function ReloadDataTable() {
            $('#TableRecordList').DataTable().ajax.reload();
        }
    </script>

    <script>
        // ฟังก์ชันสำหรับทำการ submit ฟอร์ม
        function ExportData_BAK(event) {
            // ป้องกันการรีเฟรชหน้าเมื่อกดปุ่มส่งฟอร์ม
            event.preventDefault();

            // ดึงฟอร์มจาก ID ที่กำหนด
            const form = document.getElementById("export_data");

            // ดึงค่าจากฟิลด์ต่างๆ
            let searchValue = $('#TableRecordList_filter input').val();
            let doc_date_start = $('#doc_date_start').val();
            let doc_date_to = $('#doc_date_to').val();
            let car_no_main = $('#car_no_main').val();

            // ตรวจสอบค่าที่ได้มา
            alert("กำลังส่งออกข้อมูล: " + searchValue);

            // ตั้งค่า hidden input เพื่อเก็บค่าที่ดึงมา
            document.getElementById("search_value").value = searchValue;
            document.getElementById("doc_date_start_value").value = doc_date_start;
            document.getElementById("doc_date_to_value").value = doc_date_to;
            document.getElementById("car_no_main_value").value = car_no_main;

            // ตั้งค่า action ให้ไปที่ PHP ที่จะทำการส่งออกเป็น CSV
            form.action = "export_process/export_process_data_wh_movement_out.php"; // PHP ที่จะสร้างไฟล์ CSV
            form.method = "POST"; // ใช้ POST เพื่อส่งข้อมูล
            form.target = "_blank"; // เปิดการดาวน์โหลดไฟล์ในแท็บใหม่

            // ทำการส่งฟอร์ม
            form.submit();
        }

    </script>

    <script>
        function PrintData() {
            let searchValue = $('#TableRecordList_filter input').val();
            //alert(searchValue);
            let doc_date_start = $('#doc_date_start').val();
            let doc_date_to = $('#doc_date_to').val();
            let car_no_main = $('#car_no_main').val();
            window.open('print_process/print_wh_out_process.php?doc_date_start=' + doc_date_start + '&doc_date_to=' + doc_date_to + '&car_no_main=' + car_no_main + '&searchValue=' + searchValue, '_blank');
        }
    </script>

    <script>
        $(document).ready(function () {
            setInterval(function () {
                let search_value = $('#TableRecordList_filter input').val();
                //alert(search_value);
            }, 1000);
        });
    </script>


    </body>
    </html>

<?php } ?>