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


        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- Container Fluid-->
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><span id="title"></span></h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a>
                            </li>
                            <li class="breadcrumb-item"><span id="main_menu"></li>
                            <li class="breadcrumb-item active"
                                aria-current="page"><span id="sub_menu"></li>
                        </ol>
                    </div>

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-12">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                </div>
                                <div class="card-body">
                                    <section class="container-fluid">

                                        <form method="post" id="MainrecordForm">
                                            <input type="hidden" class="form-control" id="KeyAddData" name="KeyAddData"
                                                   value="">
                                            <div class="modal-body">
                                                <div class="form-group row">
                                                    <input type="hidden" id="seq_record" value="">
                                                    <input type="hidden" id="doc_user_id" value="">
                                                    <input type="hidden" id="create_by" value="">
                                                    <div class="col-sm-4">
                                                        <label for="doc_id"
                                                               class="control-label">เลขที่เอกสาร</label>
                                                        <input type="text" class="form-control"
                                                               id="doc_id" name="doc_id"
                                                               readonly="true"
                                                               placeholder="เลขที่เอกสาร">
                                                    </div>

                                                    <div class="col-sm-2">
                                                        <label for="doc_date"
                                                               class="control-label">วันที่</label>
                                                        <input type="text" class="form-control"
                                                               id="doc_date"
                                                               name="doc_date"
                                                               readonly="true"
                                                               placeholder="วันที่">
                                                        <div class="input-group-addon">
                                                            <span class="glyphicon glyphicon-th"></span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <div class="col-sm-2">
                                                        <label for="product_id"
                                                               class="control-label">รหัสสินค้า</label>
                                                        <input type="text" class="form-control"
                                                               id="product_id"
                                                               name="product_id" readonly="true">
                                                    </div>
                                                    <div class="col-sm-4">
                                                        <label for="product_name"
                                                               class="control-label">ชื่อสินค้า</label>
                                                        <input type="text" class="form-control"
                                                               id="product_name"
                                                               name="product_name"
                                                               placeholder="" readonly="true">
                                                    </div>

                                                    <div class="col-sm-1">
                                                        <label for="qty"
                                                               class="control-label">จำนวน</label>
                                                        <input type="number" class="form-control"
                                                               id="qty" name="qty"
                                                               readonly="true"
                                                               placeholder="">
                                                    </div>

                                                    <div class="col-sm-1">
                                                        <label for="wh_org"
                                                               class="control-label">จาก</label>
                                                        <input type="text" class="form-control"
                                                               id="wh_org"
                                                               name="wh_org"
                                                               readonly="true"
                                                               placeholder="">
                                                        <div class="input-group-addon">
                                                            <span class="glyphicon glyphicon-th"></span>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-2">
                                                        <label for="wh_to"
                                                               class="control-label">ไป</label>
                                                        <input type="text" class="form-control"
                                                               id="wh_to"
                                                               name="wh_to"
                                                               readonly="true"
                                                               placeholder="">
                                                        <div class="input-group-addon">
                                                            <span class="glyphicon glyphicon-th"></span>
                                                        </div>
                                                    </div>

                                                    <div class="col-sm-4">
                                                        <label for="remark"
                                                               class="control-label">หมายเหตุ</label>
                                                        <input type="text" class="form-control"
                                                               id="remark"
                                                               name="remark"
                                                               readonly="true"
                                                               placeholder="">
                                                        <div class="input-group-addon">
                                                            <span class="glyphicon glyphicon-th"></span>
                                                        </div>
                                                    </div>
                                                    <div class="col-sm-2">
                                                        <label for="totalQty"
                                                               class="control-label">ผลรวมจำนวน</label>
                                                        <input type="text" class="form-control"
                                                               id="totalQty" name="totalQty"
                                                               readonly="true"
                                                               placeholder="">
                                                    </div>

                                                </div>

                                                <button type='button' name='btnAdd' id='btnAdd'
                                                        class='btn btn-primary btn-xs'>Add เพิ่มรายการรับเข้าคลังจริง
                                                    <i class="fa fa-plus"></i>
                                                </button>

                                                <table cellpadding="0" cellspacing="0" border="0"
                                                       class="display"
                                                       id="TableOrderDetailList"
                                                       width="100%">
                                                    <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>รหัสสินค้า</th>
                                                        <th>รายละเอียด</th>
                                                        <th>ประเภทรายการ</th>
                                                        <th>จำนวน</th>
                                                        <th>คลังปี</th>
                                                        <th>สัปดาห์</th>
                                                        <th>ตำแหน่ง</th>
                                                        <th>Action</th>
                                                        <th>Action</th>
                                                    </tr>
                                                    </thead>
                                                </table>

                                            </div>

                                            <div class="modal-footer">
                                                <input type="hidden" name="id" id="id"/>
                                                <input type="hidden" name="save_status" id="save_status"/>
                                                <input type="hidden" name="action" id="action"
                                                       value=""/>
                                                <!--button type="button" class="btn btn-primary"
                                                        id="btnSave">Save <i
                                                            class="fa fa-check"></i>
                                                </button-->
                                                <button type="button" class="btn btn-danger"
                                                        id="btnClose">Close <i
                                                            class="fa fa-window-close"></i>
                                                </button>
                                            </div>
                                        </form>

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

                                                                <div class="col-sm-5">
                                                                    <input type="hidden" class="form-control"
                                                                           id="KeyAddDetail"
                                                                           name="KeyAddDetail" value="">
                                                                </div>
                                                                <div class="col-sm-5">
                                                                    <input type="hidden" class="form-control"
                                                                           id="doc_id_detail"
                                                                           name="doc_id_detail" value="">
                                                                </div>
                                                                <div class="col-sm-5">
                                                                    <input type="hidden" class="form-control"
                                                                           id="doc_date_detail"
                                                                           name="doc_date_detail" value="">
                                                                </div>
                                                                <input type="hidden" id="qty_master" value="">
                                                                <!-- กลุ่มฟอร์มที่ 2 -->
                                                                <div class="row">
                                                                    <div class="col-sm-4">
                                                                        <label for="product_id_detail"
                                                                               class="control-label">รหัสสินค้า</label>
                                                                        <input type="text" class="form-control"
                                                                               id="product_id_detail"
                                                                               name="product_id_detail" readonly="true">
                                                                    </div>
                                                                    <div class="col-md-8">
                                                                        <div class="form-group">
                                                                            <label for="product_name_detail"
                                                                                   class="control-label">รายละเอียด</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="product_name_detail"
                                                                                   name="product_name_detail"
                                                                                   readonly placeholder="รายละเอียด"
                                                                                   readonly="true">
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <!-- กลุ่มฟอร์มที่ 3 -->
                                                                <div class="row">
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="wh_to_detail"
                                                                                   class="control-label">คลังปี</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="wh_to_detail" name="wh_to_detail"
                                                                                   placeholder=""
                                                                                   readonly="true">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="wh_week_id_detail"
                                                                                   class="control-label">สัปดาห์</label>
                                                                            <select class="form-control"
                                                                                    id="wh_week_id_detail"
                                                                                    name="wh_week_id_detail" required>
                                                                                <option value="">สัปดาห์</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="location_detail"
                                                                                   class="control-label">ตำแหน่ง</label>
                                                                            <select class="form-control"
                                                                                    id="location_detail"
                                                                                    name="location_detail" required>
                                                                                <option value="">ตำแหน่ง</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="qty_detail"
                                                                                   class="control-label">จำนวน</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="qty_detail" name="qty_detail"
                                                                                   placeholder=""
                                                                                   onblur="validateQty()"
                                                                                   required>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <input type="hidden" name="detail_id" id="detail_id"/>
                                                            <input type="hidden" name="action_detail" id="action_detail"
                                                                   value=""/>
                                                            <input type="hidden" name="create_by_detail"
                                                                   id="create_by_detail"
                                                                   value=""/>
                                                            <input type="hidden" name="seq_record_detail"
                                                                   id="seq_record_detail"
                                                                   value=""/>
                                                            <input type="hidden" name="doc_user_id_detail"
                                                                   id="doc_user_id_detail"
                                                                   value=""/>
                                                            <button type="submit" name="save" id="save"
                                                                    class="btn btn-primary"><i class="fa fa-check"></i>Save
                                                            </button>
                                                            <button type="button" class="btn btn-danger"
                                                                    data-dismiss="modal">Close <i
                                                                        class="fa fa-window-close"></i></button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <div id="result"></div>

                                    </section>


                                </div>

                            </div>

                        </div>

                    </div>
                    <!--Row-->

                    <!-- Row -->

                </div>

                <!---Container Fluid-->

            </div>

            <?php
            include('includes/Modal-Logout.php');
            include('includes/Footer.php');
            ?>

        </div>
    </div>

    <!-- Scroll to top -->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>

    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <!-- Select2 -->
    <script src="vendor/select2/dist/js/select2.min.js"></script>


    <!-- Bootstrap Touchspin -->
    <script src="vendor/bootstrap-touchspin/js/jquery.bootstrap-touchspin.js"></script>
    <!-- ClockPicker -->

    <!-- RuangAdmin Javascript -->
    <script src="js/myadmin.min.js"></script>
    <script src="js/util.js"></script>
    <script src="js/Calculate.js"></script>

    <script src="js/modal/show_customer_modal.js"></script>
    <script src="js/modal/show_product_modal.js"></script>
    <script src="js/modal/show_unit_modal.js"></script>
    <!-- Javascript for this page -->

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
            $(".icon-input-btn").each(function () {
                let btnFont = $(this).find(".btn").css("font-size");
                let btnColor = $(this).find(".btn").css("color");
                $(this).find(".fa").css({'font-size': btnFont, 'color': btnColor});
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            $("#btnClose").click(function () {
                if ($('#save_status').val() !== '') {
                    window.opener = self;
                    window.close();
                } else {
                    alertify.error("กรุณากด save อีกครั้ง");
                }
            });
        });
    </script>

    <script type="text/javascript">
        let queryString = new Array();
        $(function () {
            if (queryString.length == 0) {
                if (window.location.search.split('?').length > 1) {
                    let params = window.location.search.split('?')[1].split('&');
                    for (let i = 0; i < params.length; i++) {
                        let key = params[i].split('=')[0];
                        let value = decodeURIComponent(params[i].split('=')[1]);
                        queryString[key] = value;
                    }
                }
            }

            let data = "<b>" + queryString["title"] + "</b>";
            $("#title").html(data);
            $("#main_menu").html(queryString["main_menu"]);
            $("#sub_menu").html(queryString["sub_menu"]);
            $('#action').val(queryString["action"]);

            $('#save_status').val("before");

            if (queryString["action"] === 'ADD') {
                let KeyData = generate_token(15);
                $('#KeyAddData').val(KeyData + ":" + Date.now());
                $('#save_status').val("add");
            }

            if (queryString["doc_id"] != null && queryString["product_id"] != null && queryString["seq_record"] != null) {
                $('#id').val(queryString["id"]);
                $('#doc_id').val(queryString["doc_id"]);
                $('#doc_date').val(queryString["doc_date"]);
                $('#line_no').val(queryString["line_no"]);
                $('#seq_record').val(queryString["seq_record"]);
                $('#product_id').val(queryString["product_id"]);
                $('#product_name').val(queryString["product_name"]);
                $('#qty').val(queryString["qty"]);
                $('#wh_org').val(queryString["wh_org"]);
                $('#wh_to').val(queryString["wh_to"]);
                $('#status').val(queryString["status"]);
                $('#remark').val(queryString["remark"]);
                $('#doc_user_id').val(queryString["doc_user_id"]);
                $('#create_by').val(queryString["create_by"]);

                Load_Data_Detail(queryString["doc_id"], "v_wh_stock_transaction");
                DisplaySumQty();

            }
        });
    </script>

    <script>
        function Load_Data_Detail(doc_id, table_name) {

            let formData = {
                action: "GET_STOCK_DETAIL",
                sub_action: "GET_MASTER",
                doc_id: doc_id,
                table_name: table_name
            };
            let dataRecords = $('#TableOrderDetailList').DataTable({
                "paging": false,
                "ordering": false,
                'info': false,
                "searching": false,
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
                'serverMethod': 'post',
                'ajax': {
                    'url': 'model/manage_wh_stock_detail_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'line_no'},
                    {data: 'product_id'},
                    {data: 'product_name'},
                    {data: 'record_type'},
                    {data: 'qty', className: "text-right"},
                    {data: 'wh'},
                    {data: 'wh_week_id'},
                    {data: 'location'},
                    {data: 'update'},
                    {data: 'delete'}
                ]
            });
        }
    </script>

    <script>
        $(document).ready(function () {
            $("#btnAdd").click(function () {
                if ($('#doc_date').val() == '') {
                    alertify.error("กรุณาป้อนวันที่");
                } else {
                    let doc_id_detail = $('#doc_id').val();
                    let doc_date_detail = $('#doc_date').val();
                    let product_id_detail = $('#product_id').val();
                    let product_name_detail = $('#product_name').val();
                    let wh_to_detail = $('#wh_to').val();
                    let seq_record_detail = $('#seq_record').val();
                    let doc_user_id_detail = $('#doc_user_id').val();
                    let create_by_detail = $('#create_by').val();
                    let qty_master = $('#qty').val();
                    $('#recordModal').modal('show');
                    $('#doc_id_detail').val(doc_id_detail);
                    $('#doc_date_detail').val(doc_date_detail);
                    $('#product_id_detail').val(product_id_detail);
                    $('#product_name_detail').val(product_name_detail);
                    $('#qty_detail').val('');
                    $('#wh_to_detail').val(wh_to_detail);
                    //$('#wh_week_id_detail').val('');
                    //$('#location_detail').val('');
                    $('#wh_week_id_detail').val(null).trigger('change');
                    $('#location_detail').val(null).trigger('change');
                    $('#seq_record_detail').val(seq_record_detail);
                    $('#doc_user_id_detail').val(doc_user_id_detail);
                    $('#create_by_detail').val(create_by_detail);
                    $('#qty_master').val(qty_master);
                    $('.modal-title').html("<i class='fa fa-plus'></i> ADD Record");
                    $('#action_detail').val('ADD');
                    $('#save').val('Save');
                }
            });
        });
    </script>

    <script>

        $("#TableOrderDetailList").on('click', '.update', function () {

            let rec_id = $(this).attr("id");
            let table_name = "v_wh_stock_transaction";
            let formData = {action: "GET_DATA", id: rec_id, table_name: table_name};
            $.ajax({
                type: "POST",
                url: 'model/manage_wh_stock_detail_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let doc_id_detail = response[i].doc_id;
                        let doc_date_detail = response[i].doc_date;
                        let product_id_detail = response[i].product_id;
                        let product_name_detail = response[i].product_name;
                        let qty_detail = response[i].qty;
                        let wh_to_detail = response[i].wh;
                        let wh_week_id_detail = response[i].wh_week_id;
                        let location_detail = response[i].location;
                        let qty_master = $('#qty').val();
                        let master_id_detail = $('#id').val();
                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#detail_id').val(rec_id);
                        $('#doc_id_detail').val(doc_id_detail);
                        $('#doc_date_detail').val(doc_date_detail);
                        $('#product_id_detail').val(product_id_detail);
                        $('#product_name_detail').val(product_name_detail);
                        $('#qty_detail').val(qty_detail);
                        $('#wh_to_detail').val(wh_to_detail);
                        $('#wh_week_id_detail').val(wh_week_id_detail).trigger('change');
                        $('#location_detail').val(location_detail).trigger('change');
                        $('#qty_master').val(qty_master);
                        $('#master_id_detail').val(master_id_detail);
                        $('.modal-title').html("<i class='fa fa-plus'></i> Edit Record");
                        $('#action_detail').val('UPDATE');
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

        $("#TableOrderDetailList").on('click', '.delete', function () {

            let rec_id = $(this).attr("id");
            let table_name = "v_wh_stock_transaction";
            let formData = {action: "GET_DATA", id: rec_id, table_name: table_name};
            $.ajax({
                type: "POST",
                url: 'model/manage_wh_stock_detail_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let doc_id_detail = response[i].doc_id;
                        let doc_date_detail = response[i].doc_date;
                        let product_id_detail = response[i].product_id;
                        let product_name_detail = response[i].product_name;
                        let qty_detail = response[i].qty;
                        let wh_to_detail = response[i].wh;
                        let wh_week_id_detail = response[i].wh_week_id;
                        let location_detail = response[i].location;
                        let qty_master = $('#qty').val();
                        let master_id_detail = $('#id').val();
                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#detail_id').val(rec_id);
                        $('#doc_id_detail').val(doc_id_detail);
                        $('#doc_date_detail').val(doc_date_detail);
                        $('#product_id_detail').val(product_id_detail);
                        $('#product_name_detail').val(product_name_detail);
                        $('#qty_detail').val(qty_detail);
                        $('#wh_to_detail').val(wh_to_detail);
                        $('#wh_week_id_detail').val(wh_week_id_detail).trigger('change');
                        $('#location_detail').val(location_detail).trigger('change');
                        $('#qty_master').val(qty_master);
                        $('#master_id_detail').val(master_id_detail);
                        $('.modal-title').html("<i class='fa fa-plus'></i> Delete Record");
                        $('#action_detail').val('DELETE');
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
        $(document).ready(function () {
            // AJAX เพื่อดึงข้อมูลจากฐานข้อมูล
            $.ajax({
                url: 'model/get_wh_week.php', // หน้า PHP ที่จะดึงข้อมูล
                method: 'GET',
                dataType: 'json',
                success: function (data) {
                    let select = $('#wh_week_id_detail');
                    $.each(data, function (index, wh_week) {
                        select.append($('<option>', {
                            value: wh_week.wh_week_id,
                            text: wh_week.wh_week_id, // เปลี่ยนเป็นชื่อของข้อมูลที่คุณต้องการแสดง
                            'data-name': wh_week.wh_week_id // เก็บข้อมูลชื่อใน attribute เพื่อใช้ภายหลัง
                        }));
                    });

                    // แปลง select เป็น select2 หลังจากข้อมูลถูกเพิ่ม
                    $('#wh_week_id_detail').select2({
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
                url: 'model/get_wh_location.php', // หน้า PHP ที่จะดึงข้อมูล
                method: 'GET',
                dataType: 'json',
                success: function (data) {
                    let select = $('#location_detail');
                    $.each(data, function (index, wh_location) {
                        select.append($('<option>', {
                            value: wh_location.location_id,
                            text: wh_location.location_id, // เปลี่ยนเป็นชื่อของข้อมูลที่คุณต้องการแสดง
                            'data-name': wh_location.location_id // เก็บข้อมูลชื่อใน attribute เพื่อใช้ภายหลัง
                        }));
                    });

                    // แปลง select เป็น select2 หลังจากข้อมูลถูกเพิ่ม
                    $('#location_detail').select2({
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
        function ReloadDataTable() {
            $('#TableOrderDetailList').DataTable().ajax.reload();
        }
    </script>

    <script>
        function validateQty(callback) {
            let doc_id = $('#doc_id_detail').val();  // รับค่า doc_id จาก input
            let qty_master = parseFloat($('#qty_master').val());  // รับค่า qty_master จาก input และแปลงเป็น float
            let qty_detail_input = parseFloat($('#qty_detail').val());  // รับค่า qty_detail จาก input form และแปลงเป็น float

            // ตรวจสอบว่าค่าที่ผู้ใช้ป้อนเป็นตัวเลขหรือไม่
            if (isNaN(qty_detail_input)) {
                qty_detail_input = 0;  // ถ้าไม่เป็นตัวเลขให้ค่าเป็น 0
            }

            // ใช้ AJAX เพื่อตรวจสอบข้อมูลจากฐานข้อมูล
            $.ajax({
                url: 'model/manage_wh_stock_detail_process.php',  // ไฟล์ PHP ที่ใช้ดึงข้อมูล
                type: 'POST',
                data: {action: "CAL_SUM_DETAIL", doc_id: doc_id},  // ส่ง doc_id ไปให้ PHP
                success: function (response) {
                    let total_qty_detail = parseFloat(response);  // รับผลรวม qty_detail จาก table Detail

                    if (isNaN(total_qty_detail)) {
                        total_qty_detail = 0;  // ถ้าไม่ได้ค่าใดๆ ให้เริ่มต้นเป็น 0
                    }

                    // คำนวณผลรวมระหว่าง qty_detail ที่มีอยู่ในตารางและ qty ที่ป้อนใหม่
                    let total_qty = total_qty_detail + qty_detail_input;

                    // ตรวจสอบว่าผลรวมเกินค่า qty_master หรือไม่
                    if (total_qty > qty_master) {
                        alertify.alert("จำนวนที่ป้อน รวมทุกรายการแล้วไม่สามารถเกิน " + qty_master + " ได้");
                        callback(false);  // ส่งค่า false กลับเมื่อไม่ผ่านการตรวจสอบ
                    } else {
                        callback(true);  // ส่งค่า true กลับเมื่อผ่านการตรวจสอบ
                    }
                }
            });
        }

        $("#recordModal").on('submit', '#recordForm', function (event) {
            event.preventDefault();  // หยุดการ submit form ชั่วคราว

            validateQty(function (isValid) {
                if (isValid) {
                    // ถ้าผ่านการ validate ให้ทำการ submit form
                    let formData = $('#recordForm').serialize();

                    $.ajax({
                        url: 'model/manage_wh_stock_detail_process.php',
                        method: "POST",
                        data: formData,
                        success: function (data) {
                            alertify.success(data);
                            $('#recordForm')[0].reset();
                            $('#recordModal').modal('hide');
                            $('#save').attr('disabled', false);
                            $('#TableOrderDetailList').DataTable().ajax.reload();
                            DisplaySumQty();
                        }
                    });
                } else {
                    // ถ้าการ validate ไม่ผ่าน จะไม่ทำการ submit
                    alertify.error("การตรวจสอบจำนวนไม่ผ่าน");
                }
            });
        });
    </script>

    <script>
        function DisplaySumQty() {
            let doc_id = $('#doc_id').val();  // รับค่า doc_id จาก input
            // ใช้ AJAX เพื่อตรวจสอบข้อมูลจากฐานข้อมูล
            $.ajax({
                url: 'model/manage_wh_stock_detail_process.php',  // ไฟล์ PHP ที่ใช้ดึงข้อมูล
                type: 'POST',
                data: {action: "CAL_SUM_DETAIL", doc_id: doc_id},  // ส่ง doc_id ไปให้ PHP
                success: function (response) {
                    let total_qty_detail = parseFloat(response);  // รับผลรวม qty_detail จาก table Detail
                    $('#totalQty').val(total_qty_detail);
                }
            });
        }
    </script>

    <script>
        $(document).ready(function () {
            // ตรวจสอบเมื่อมีการพิมพ์ในช่อง input
            $('#qty_detail').on('input', function () {
                let inputVal = $(this).val(); // ดึงค่าจากช่อง input

                // ตรวจสอบว่าเป็นตัวเลขหรือไม่
                if (!/^\d+$/.test(inputVal)) {
                    $(this).val(''); // ถ้าไม่ใช่ตัวเลข clear ค่าออก
                }
            });
        });
    </script>


    </body>

    </html>

<?php } ?>


