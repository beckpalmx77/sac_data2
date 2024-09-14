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
                                                <div class="modal-body">
                                                    <div class="form-group row">
                                                        <div class="col-sm-1">
                                                            <label for="doc_id"
                                                                   class="control-label">เลขที่เอกสาร</label>
                                                            <input type="text" class="form-control"
                                                                   id="doc_id" name="doc_id"
                                                                   readonly="true"
                                                                   placeholder="เลขที่เอกสาร">
                                                        </div>

                                                        <div class="col-sm-1">
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
                                                        <div class="col-sm-2">
                                                            <label for="product_id"
                                                                   class="control-label">รหัสสินค้า</label>
                                                            <input type="text" class="form-control"
                                                                   id="product_id"
                                                                   name="product_id">
                                                        </div>
                                                        <div class="col-sm-4">
                                                            <label for="product_name"
                                                                   class="control-label">ชื่อสินค้า</label>
                                                            <input type="text" class="form-control"
                                                                   id="product_name"
                                                                   name="product_name"
                                                                   placeholder="">
                                                        </div>

                                                        <div class="col-sm-1">
                                                            <label for="qty"
                                                                   class="control-label">จำนวน</label>
                                                            <input type="text" class="form-control"
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

                                                        <div class="col-sm-1">
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

                                                    </div>

                                                    <button type='button' name='btnAdd' id='btnAdd'
                                                            class='btn btn-primary btn-xs'>Add เพิ่มรายการสินค้า
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
                                                        </tr>
                                                        </thead>
                                                    </table>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <input type="hidden" name="id" id="id"/>
                                                <input type="hidden" name="save_status" id="save_status"/>
                                                <input type="hidden" name="action" id="action"
                                                       value=""/>
                                                <button type="button" class="btn btn-primary"
                                                        id="btnSave">Save <i
                                                            class="fa fa-check"></i>
                                                </button>
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
                                                                    <input type="text" class="form-control"
                                                                           id="KeyAddDetail"
                                                                           name="KeyAddDetail" value="">
                                                                </div>
                                                                <div class="col-sm-5">
                                                                    <input type="text" class="form-control"
                                                                           id="doc_id_detail"
                                                                           name="doc_id_detail" value="">
                                                                </div>
                                                                <div class="col-sm-5">
                                                                    <input type="text" class="form-control"
                                                                           id="doc_date_detail"
                                                                           name="doc_date_detail" value="">
                                                                </div>

                                                                <!-- ใช้ container-fluid เพื่อให้เต็มความกว้างของ modal -->
                                                                <!-- กลุ่มฟอร์มที่ 1 -->
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="doc_id"
                                                                                   class="control-label">เลขที่เอกสาร</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="doc_id" name="doc_id"
                                                                                   placeholder=""
                                                                                   required readonly="true">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="line_no"
                                                                                   class="control-label">รายการที่</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="line_no" name="line_no"
                                                                                   placeholder=""
                                                                                   required readonly="true">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                                <div class="row">
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="doc_date" class="control-label">วันที่</label>
                                                                            <div class="input-group">
                                                                                <input type="text" class="form-control"
                                                                                       id="doc_date" name="doc_date"
                                                                                       readonly="true"
                                                                                       value="">
                                                                                <div class="input-group-append">
                                                                                    <span class="input-group-text"><i
                                                                                                class="glyphicon glyphicon-th"></i></span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="record_type_id"
                                                                                   class="control-label">ประเภทรายการ</label>
                                                                            <select class="form-control"
                                                                                    id="record_type_id"
                                                                                    name="record_type_id" required>
                                                                                <option value="">ประเภทรายการ</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="record_type_desc"
                                                                                   class="control-label">รายละเอียด</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="record_type_desc"
                                                                                   name="record_type_desc"
                                                                                   readonly placeholder="รายละเอียด"
                                                                                   required>
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
                                                                            <label for="wh"
                                                                                   class="control-label">คลังปี</label>
                                                                            <select class="form-control" id="wh"
                                                                                    name="wh" required>
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
                                                                            <label for="location"
                                                                                   class="control-label">ตำแหน่ง</label>
                                                                            <select class="form-control"
                                                                                    id="location"
                                                                                    name="location" required>
                                                                                <option value="">ตำแหน่ง</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <input type="hidden" name="id" id="id"/>
                                                            <input type="hidden" name="detail_id" id="detail_id"/>
                                                            <input type="hidden" name="action_detail" id="action_detail"
                                                                   value=""/>
                                                            <input type="hidden" name="create_by" id="create_by"
                                                                   value=""/>
                                                            <input type="hidden" name="doc_user_id" id="doc_user_id"
                                                                   value="<?php echo $doc_user_id; ?>"/>
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

    <!--script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/5.5.2/bootbox.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.0/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.0/css/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.0.0/css/buttons.dataTables.min.css"/-->

    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

    <script src="vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
    <script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
    <!--link href="vendor/date-picker-1.9/css/date_picker_style.css" rel="stylesheet"/-->
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

                //Load_Data_Detail(queryString["doc_id"], "v_order_detail");
            }
        });
    </script>

    <script>
        function Load_Data_Detail(doc_id, table_name) {

            let formData = {
                action: "GET_ORDER_DETAIL",
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
                    'url': 'model/manage_order_detail_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'line_no'},
                    {data: 'product_name'},
                    {data: 'quantity', className: "text-right"},
                    {data: 'unit_name'},
                    {data: 'price', className: "text-right"},
                    {data: 'total_price', className: "text-right"},
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
                    let qty_detail = $('#qty').val();
                    let wh_org_detail = $('#wh_org').val();
                    let wh_to_detail = $('#wh_org').val();

                    $('#recordModal').modal('show');
                    $('#KeyAddDetail').val($('#KeyAddData').val());
                    $('#doc_id_detail').val(doc_id_detail);
                    $('#doc_date_detail').val(doc_date_detail);
                    $('#product_id_detail').val(product_id_detail);

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

            if ($('#KeyAddData').val() !== '') {
                doc_id = $('#KeyAddData').val();
                table_name = "v_order_detail_temp";
            } else {
                doc_id = $('#doc_id').val();
                table_name = "v_order_detail";
            }

            let formData = {action: "GET_DATA", id: rec_id, doc_id: doc_id, table_name: table_name};
            $.ajax({
                type: "POST",
                url: 'model/manage_order_detail_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let product_id = response[i].product_id;
                        let id = response[i].id;
                        let name_t = response[i].name_t;
                        let doc_date = response[i].doc_date;
                        let quantity = response[i].quantity;
                        let price = response[i].price;
                        let total_price = response[i].total_price;
                        let unit_id = response[i].unit_id;
                        let unit_name = response[i].unit_name;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#detail_id').val(rec_id);
                        $('#doc_id_detail').val(doc_id);
                        $('#doc_date_detail').val(doc_date);
                        $('#product_id').val(product_id);
                        $('#name_t').val(name_t);
                        $('#quantity').val(quantity);
                        $('#price').val(price);
                        $('#total_price').val(total_price);
                        $('#unit_id').val(unit_id);
                        $('#unit_name').val(unit_name);
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

            if ($('#KeyAddData').val() !== '') {
                doc_id = $('#KeyAddData').val();
                table_name = "v_order_detail_temp";
            } else {
                doc_id = $('#doc_id').val();
                table_name = "v_order_detail";
            }

            let formData = {action: "GET_DATA", id: rec_id, doc_id: doc_id, table_name: table_name};
            $.ajax({
                type: "POST",
                url: 'model/manage_order_detail_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let product_id = response[i].product_id;
                        let id = response[i].id;
                        let name_t = response[i].name_t;
                        let quantity = response[i].quantity;
                        let price = response[i].price;
                        let total_price = response[i].total_price;
                        let unit_id = response[i].unit_id;
                        let unit_name = response[i].unit_name;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#detail_id').val(rec_id);
                        $('#doc_id_detail').val(doc_id);
                        $('#product_id').val(product_id);
                        $('#name_t').val(name_t);
                        $('#quantity').val(quantity);
                        $('#price').val(price);
                        $('#total_price').val(total_price);
                        $('#unit_id').val(unit_id);
                        $('#unit_name').val(unit_name);
                        $('.modal-title').html("<i class='fa fa-plus'></i> Edit Record");
                        $('#action_detail').val('DELETE');
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
            $("#btnSave").click(function () {
                if ($('#doc_date').val() == '' || $('#f_name').val() == '') {
                    alertify.error("กรุณาป้อนวันที่ / ชื่อลูกค้า ");
                } else {
                    let formData = $('#MainrecordForm').serialize();
                    $.ajax({
                        url: 'model/manage_order_process.php',
                        method: "POST",
                        data: formData,
                        success: function (data) {

                            if ($('#KeyAddData').val() !== '') {
                                let KeyAddData = $('#KeyAddData').val();
                                Save_Detail(KeyAddData);
                            }
                            alertify.success(data);
                            window.opener.location.reload();
                            $('#save_status').val("save");
                        }
                    })

                }

            });
        });
    </script>

    <script>
        function Save_Detail(KeyAddData) {

            let formData = {action: "SAVE_DETAIL", KeyAddData: KeyAddData};
            $.ajax({
                url: 'model/manage_order_detail_process.php',
                method: "POST",
                data: formData,
                success: function (data) {
                    //alertify.success(data);
                }
            })

        }
    </script>

    <script>

        $("#recordModal").on('submit', '#recordForm', function (event) {
            event.preventDefault();
            let KeyAddData = $('#KeyAddData').val();
            if (KeyAddData !== '') {
                $('#KeyAddDetail').val(KeyAddData);
            }
            let doc_id_detail = $('#doc_id_detail').val();
            let formData = $(this).serialize();
            $.ajax({
                url: 'model/manage_order_detail_process.php',
                method: "POST",
                data: formData,
                success: function (data) {
                    //alertify.success(data);
                    $('#recordForm')[0].reset();
                    $('#recordModal').modal('hide');

                    $('#TableOrderDetailList').DataTable().clear().destroy();

                    if (KeyAddData !== '') {
                        Load_Data_Detail(KeyAddData, "v_order_detail_temp");
                    } else {
                        Load_Data_Detail(doc_id_detail, "v_order_detail");
                    }
                }
            })

        });

    </script>

    <script>

        $('#quantity,#price,#total_price').blur(function () {
            let total_price = new Calculate($('#quantity').val(), $('#price').val());
            $('#total_price').val(total_price.Multiple().toFixed(2));
        });

    </script>

    </body>

    </html>

<?php } ?>


