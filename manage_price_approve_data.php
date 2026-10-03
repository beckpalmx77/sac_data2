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
                                                        <div class="col-sm-2">
                                                            <label for="doc_no_detail"
                                                                   class="control-label">เลขที่เอกสาร</label>
                                                            <input type="text" class="form-control"
                                                                   id="doc_no_detail" name="doc_no_detail"
                                                                   readonly="true"
                                                                   placeholder="เลขที่เอกสาร">
                                                        </div>

                                                        <div class="col-sm-2">
                                                            <label for="doc_date"
                                                                   class="control-label">วันที่</label>
                                                            <input type="text" class="form-control"
                                                                   id="doc_date"
                                                                   name="doc_date"
                                                                   required="required"
                                                                   readonly="true"
                                                                   placeholder="วันที่">
                                                            <div class="input-group-addon">
                                                                <span class="glyphicon glyphicon-th"></span>
                                                            </div>
                                                        </div>

                                                        <input type="hidden" class="form-control"
                                                               id="customer_id"
                                                               name="customer_id">
                                                        <div class="col-sm-6">
                                                            <label for="customer_name"
                                                                   class="control-label">ชื่อลูกค้า</label>
                                                            <input type="text" class="form-control"
                                                                   id="customer_name"
                                                                   name="customer_name"
                                                                   required="required"
                                                                   placeholder="ชื่อลูกค้า">
                                                        </div>
                                                    </div>


                                                    <table cellpadding="0" cellspacing="0" border="0"
                                                           class="display"
                                                           id="TableOrderDetailList"
                                                           width="100%">
                                                        <thead>
                                                        <tr>
                                                            <th>รายการที่</th>
                                                            <th>รหัสสินค้า</th>
                                                            <th>ชื่อสินค้า</th>
                                                            <th>ราคาขายปกติ</th>
                                                            <th>ราคาที่ต้องการขาย</th>
                                                            <th>Action</th>
                                                        </tr>
                                                        </thead>
                                                    </table>

                                                    <br>
                                                    <br>

                                                    <?php if ($_SESSION['permission_price'] === "REQUESTER" || $_SESSION['permission_price'] === "ADMIN") { ?>
                                                        <div class="form-group">
                                                            <label for="request_status"
                                                                   class="control-label">ขออนุมัติราคาขาย</label>
                                                            <select id="request_status" name="request_status"
                                                                    class="form-control"
                                                                    data-live-search="true"
                                                                    title="Please select">
                                                                <option>N ขายราคาปกติ</option>
                                                                <option>Y ขออนุมัติราคาขาย</option>
                                                            </select>
                                                        </div>
                                                    <?php } else { ?> <input type="hidden" name="request_status"
                                                                             id="request_status"/> <?php } ?>

                                                    <?php if ($_SESSION['permission_price'] === "APPROVER" || $_SESSION['permission_price'] === "ADMIN") { ?>
                                                        <div class="form-group">
                                                            <label for="approve_status"
                                                                   class="control-label">อนุมัติราคาขาย</label>
                                                            <select id="approve_status" name="approve_status"
                                                                    class="form-control"
                                                                    data-live-search="true"
                                                                    title="Please select">
                                                                <option>N ขายราคาปกติ</option>
                                                                <option>Y อนุมัติ</option>
                                                            </select>
                                                        </div>
                                                    <?php } else { ?> <input type="hidden" name="approve_status"
                                                                             id="approve_status"/> <?php } ?>

                                                    <?php if ($_SESSION['permission_price'] === "EDITOR" || $_SESSION['permission_price'] === "ADMIN") { ?>
                                                    <div class="form-group">
                                                        <label for="edit_price_status"
                                                               class="control-label">แก้ไขราคาขาย</label>
                                                        <select id="edit_price_status" name="edit_price_status"
                                                                class="form-control"
                                                                data-live-search="true"
                                                                title="Please select">
                                                            <option>N ขายราคาปกติ</option>
                                                            <option>Y แก้ไขราคาแล้ว</option>
                                                        </select>
                                                    </div>
                                                    <?php } else { ?> <input type="hidden" name="edit_price_status"
                                                                             id="edit_price_status"/> <?php } ?>


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
                                                            class="fa fa-times"></i>
                                                </button>
                                            </div>
                                        </form>

                                        <div class="modal fade" id="recordModal">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Modal title</h4>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-hidden="true">×
                                                        </button>
                                                    </div>
                                                    <form method="post" id="recordForm">

                                                        <div class="form-group row">
                                                            <div class="col-sm-5">
                                                                <input type="hidden" class="form-control"
                                                                       id="KeyAddDetail"
                                                                       name="KeyAddDetail" value="">
                                                            </div>
                                                            <div class="col-sm-5">
                                                                <input type="hidden" class="form-control"
                                                                       id="doc_no_detail_line"
                                                                       name="doc_no_detail_line" value="">
                                                            </div>
                                                            <div class="col-sm-5">
                                                                <input type="hidden" class="form-control"
                                                                       id="doc_date_detail"
                                                                       name="doc_date_detail" value="">
                                                            </div>
                                                        </div>

                                                        <div class="modal-body">
                                                            <div class="modal-body">

                                                                <div class="form-group row">
                                                                    <div class="col-sm-5">
                                                                        <label for="line_no"
                                                                               class="control-label">รายการที่</label>
                                                                        <input type="line_no"
                                                                               class="form-control"
                                                                               id="line_no" name="line_no"
                                                                               required="required"
                                                                               readonly="true"
                                                                               placeholder="รายการที่">
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row">
                                                                    <div class="col-sm-5">
                                                                        <label for="product_id"
                                                                               class="control-label">รหัสสินค้า/วัสดุ</label>
                                                                        <input type="product_id"
                                                                               class="form-control"
                                                                               id="product_id" name="product_id"
                                                                               required="required"
                                                                               readonly="true"
                                                                               placeholder="รหัสสินค้า/วัสดุ">
                                                                    </div>

                                                                    <div class="col-sm-5">
                                                                        <label for="name_t"
                                                                               class="control-label">ชื่อสินค้า/วัสดุ</label>
                                                                        <input type="text" class="form-control"
                                                                               id="product_name"
                                                                               name="product_name"
                                                                               required="required"
                                                                               readonly="true"
                                                                               placeholder="ชื่อสินค้า/วัสดุ">
                                                                    </div>

                                                                </div>

                                                                <div class="form-group row">
                                                                    <div class="col-sm-5">
                                                                        <label for="price_normal"
                                                                               class="control-label">ราคาขายปกติ</label>
                                                                        <input type="number" class="form-control"
                                                                               id="price_normal"
                                                                               name="price_normal"
                                                                               placeholder="ราคาขายปกติ">
                                                                    </div>
                                                                    <div class="col-sm-5">
                                                                        <label for="price_special"
                                                                               class="control-label">ราคาที่ต้องการขาย</label>
                                                                        <input type="number" class="form-control"
                                                                               id="price_special"
                                                                               name="price_special"
                                                                               required="required"
                                                                               placeholder="ราคาที่ต้องการขาย">
                                                                    </div>
                                                                </div>

                                                                <?php if ($_SESSION['permission_price'] === "REQUESTER" || $_SESSION['permission_price'] === "ADMIN") { ?>
                                                                    <div class="form-group">
                                                                        <label for="request_edit_price_status"
                                                                               class="control-label">ขออนุมัติราคาขาย</label>
                                                                        <select id="request_edit_price_status" name="request_edit_price_status"
                                                                                class="form-control"
                                                                                data-live-search="true"
                                                                                title="Please select">
                                                                            <option>N</option>
                                                                            <option>Y</option>
                                                                        </select>
                                                                    </div>
                                                                <?php } else { ?> <input type="hidden" name="request_edit_price_status"
                                                                                         id="request_edit_price_status"/> <?php } ?>


                                                            </div>
                                                        </div>

                                                        <div class="modal-footer">
                                                            <input type="hidden" name="id" id="id"/>
                                                            <input type="hidden" name="detail_id" id="detail_id"/>
                                                            <input type="hidden" name="action_detail"
                                                                   id="action_detail" value=""/>
                                                            <span class="icon-input-btn">
                                                                <i class="fa fa-check"></i>
                                                            <input type="submit" name="save" id="save"
                                                                   class="btn btn-primary" value="Save"/>
                                                            </span>
                                                            <button type="button" class="btn btn-danger"
                                                                    data-dismiss="modal">Close <i
                                                                        class="fa fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </form>

                                                </div>
                                            </div>
                                        </div>


                                        <div class="modal fade" id="SearchProductModal">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Modal title</h4>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-hidden="true">×
                                                        </button>
                                                    </div>
                                                    <div class="container"></div>
                                                    <div class="modal-body">
                                                        <div class="modal-body">
                                                            <table cellpadding="0" cellspacing="0" border="0"
                                                                   class="display"
                                                                   id="TableProductList"
                                                                   width="100%">
                                                                <thead>
                                                                <tr>
                                                                    <th>รหัส</th>
                                                                    <th>สินค้า</th>
                                                                    <th>รหัสหน่วยนับ</th>
                                                                    <th>หน่วยนับ</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                                </thead>
                                                                <tfoot>
                                                                <tr>
                                                                    <th>รหัส</th>
                                                                    <th>สินค้า</th>
                                                                    <th>รหัสหน่วยนับ</th>
                                                                    <th>หน่วยนับ</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                                </tfoot>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal fade" id="SearchCusModal">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Modal title</h4>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-hidden="true">×
                                                        </button>
                                                    </div>

                                                    <div class="container"></div>
                                                    <div class="modal-body">

                                                        <div class="modal-body">

                                                            <table cellpadding="0" cellspacing="0" border="0"
                                                                   class="display"
                                                                   id="TableCustomerList"
                                                                   width="100%">
                                                                <thead>
                                                                <tr>
                                                                    <th>รหัสลูกค้า</th>
                                                                    <th>ชื่อลูกค้า</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                                </thead>
                                                                <tfoot>
                                                                <tr>
                                                                    <th>รหัสลูกค้า</th>
                                                                    <th>ชื่อลูกค้า</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                                </tfoot>
                                                            </table>
                                                        </div>
                                                    </div>
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
        //alert("OK Detail");
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

            if (queryString["doc_no"] != null && queryString["customer_name"] != null) {

                $('#doc_no_detail').val(queryString["doc_no"]);
                $('#doc_date').val(queryString["doc_date"]);
                $('#customer_name').val(queryString["customer_name"]);
                $('#request_status').val(queryString["request_status"]);
                $('#approve_status').val(queryString["approve_status"]);
                $('#edit_price_status').val(queryString["edit_price_status"]);


                Load_Data_Detail(queryString["doc_no"], "v_ims_price_approve_detail");
            }
        });
    </script>

    <script>
        function Load_Data_Detail(doc_no, table_name) {

            let formData = {
                action: "GET_PRICE_DETAIL",
                sub_action: "GET_MASTER",
                doc_no: doc_no,
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
                    'url': 'model/manage_price_approve_detail_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'line_no'},
                    {data: 'product_id'},
                    {data: 'product_name'},
                    {data: 'price_normal', className: "text-right"},
                    {data: 'price_special', className: "text-right"},
                    {data: 'update'}
                ]
            });
        }
    </script>

    <script>

        $("#TableOrderDetailList").on('click', '.update', function () {

            let rec_id = $(this).attr("id");

            doc_no = $('#doc_no').val();
            table_name = "v_ims_price_approve_detail";

            let formData = {action: "GET_DATA", id: rec_id, doc_no: doc_no, table_name: table_name};
            $.ajax({
                type: "POST",
                url: 'model/manage_price_approve_detail_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let line_no = response[i].line_no;
                        let doc_no = response[i].doc_no;
                        let doc_date = response[i].doc_date;
                        let product_id = response[i].product_id;
                        let product_name = response[i].product_name;
                        let price_normal = response[i].price_normal;
                        let price_special = response[i].price_special;
                        let request_edit_price_status = response[i].request_edit_price_status;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#line_no').val(line_no);
                        $('#detail_id').val(rec_id);
                        $('#doc_no_detail_line').val(doc_no);
                        $('#doc_date_detail').val(doc_date);
                        $('#product_id').val(product_id);
                        $('#product_name').val(product_name);
                        $('#price_normal').val(price_normal);
                        $('#price_special').val(price_special);
                        $('#request_edit_price_status').val(request_edit_price_status);
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
        $(document).ready(function () {
            $("#btnSave").click(function () {

                if ($('#doc_date').val() == '' || $('#customer_name').val() == '') {
                    alertify.error("กรุณาป้อนวันที่ / ชื่อลูกค้า ");
                } else {
                    let formData = $('#MainrecordForm').serialize();
                    $.ajax({
                        url: 'model/manage_price_approve_process.php',
                        method: "POST",
                        data: formData,
                        success: function (data) {

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
                url: 'model/manage_price_approve_detail_process.php',
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
            let doc_no_detail = $('#doc_no_detail').val();
            let formData = $(this).serialize();
            $.ajax({
                url: 'model/manage_price_approve_detail_process.php',
                method: "POST",
                data: formData,
                success: function (data) {
                    //alertify.success(data);
                    $('#recordForm')[0].reset();
                    $('#recordModal').modal('hide');

                    $('#TableOrderDetailList').DataTable().clear().destroy();
                    Load_Data_Detail(doc_no_detail, "v_ims_price_approve_detail");
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


