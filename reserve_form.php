<?php
include('includes/Header.php');
include('config/lang.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {

    ?>

    <!DOCTYPE html>
    <html lang="th">

    <script src="js/data_format.js"></script>

    <body id="page-top">
    <div id="wrapper">
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <!-- Container Fluid-->
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><img src="img/logo/company_logo.png" width="30%" hight="30%"/>
                            หลักฐานการจอง
                        </h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a>
                            </li>
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
                                                            <input type="text" class="form-control"
                                                                   id="doc_no" name="doc_no"
                                                                   readonly="true"
                                                                   placeholder="เลขที่เอกสาร">
                                                        </div>

                                                        <div class="col-sm-2">
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


                                                    </div>

                                                    <div class="form-group row">
                                                        <div class="col-sm-1"></div>
                                                        <div class="col-sm-3">
                                                            <label for="doc_no"
                                                                   class="control-label">หนังสือสัญญาฉบับนี้ทำขึ้นระหว่าง
                                                            </label>
                                                        </div>

                                                        <select name="prefix" id="prefix">
                                                            <option value="นาย">นาย</option>
                                                            <option value="นาง">นาง</option>
                                                            <option value="นางสาว">นางสาว</option>
                                                        </select>

                                                        <div class="col-sm-2">
                                                            <input type="text" class="form-control"
                                                                   id="f_name"
                                                                   name="f_name"
                                                                   required="required"
                                                                   placeholder="ชื่อ">
                                                        </div>
                                                        <div class="col-sm-2">
                                                            <input type="text" class="form-control"
                                                                   id="l_name"
                                                                   name="l_name"
                                                                   required="required"
                                                                   placeholder="นามสกุล">
                                                        </div>
                                                        <div class="col-sm-3">
                                                            <input type="text" class="form-control"
                                                                   id="citizen_id"
                                                                   name="citizen_id"
                                                                   required="required"
                                                                   placeholder="เลขที่บัตรประชาชน">
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <div class="col-sm-1">
                                                        อายุ
                                                        </div>
                                                        <div class="col-sm-1">
                                                            <input type="text" class="form-control"
                                                                   id="age"
                                                                   name="age"
                                                                   required="required"
                                                                   placeholder="อายุ">
                                                        </div>
                                                        อาชีพ
                                                        <div class="col-sm-2">
                                                            <input type="text" class="form-control"
                                                                   id="job"
                                                                   name="job"
                                                                   required="required"
                                                                   placeholder="อาชีพ">
                                                        </div>
                                                        อยู่บ้านเลขที่
                                                        <div class="col-sm-3">
                                                            <input type="text" class="form-control"
                                                                   id="address_1"
                                                                   name="address_1"
                                                                   required="required"
                                                                   placeholder="บ้านเลขที่">
                                                        </div>
                                                        โทรศัพท์
                                                        <div class="col-sm-3">
                                                            <input type="text" class="form-control"
                                                                   id="phone"
                                                                   name="phone"
                                                                   required="required"
                                                                   placeholder="โทรศัพท์">
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <div class="col-sm-12">
                                                            <?php echo $contract_1 ?>
                                                        </div>
                                                    </div>
                                                    <div class="form-group row">
                                                        <div class="col-sm-1"></div>
                                                        <div class="col-sm-5">
                                                            ข้อ 1.
                                                            ผู้จะซื้อและผู้จะขายตกลงซื้อขายที่ดินพร้อมสิ่งปลูกสร้าง
                                                            แปลงที่
                                                        </div>
                                                        <div class="col-sm-1">
                                                            <input type="text" class="form-control"
                                                                   id="lot"
                                                                   name="lot"
                                                                   required="required"
                                                                   placeholder="แปลงที่">
                                                        </div>
                                                        บนโฉนดที่ดินเลขที่
                                                        <div class="col-sm-3">
                                                            <input type="text" class="form-control"
                                                                   id="land_no"
                                                                   name="land_no"
                                                                   required="required"
                                                                   placeholder="โฉนดที่ดินเลขที่">
                                                        </div>
                                                    </div>

                                                    <div class="form-group row">
                                                        <div class="col-sm-1">
                                                            เนื้อที่
                                                        </div>
                                                        <div class="col-sm-1">
                                                            <input type="text" class="form-control"
                                                                   id="lan_qty1"
                                                                   name="lan_qty1"
                                                                   required="required"
                                                                   placeholder="เนื้อที่">
                                                        </div>
                                                        ตร.ว. ตามผังสังเขป พร้อมบ้านเดี่ยวแบบ
                                                        <div class="col-sm-3">
                                                            <input type="text" class="form-control"
                                                                   id="home_plan"
                                                                   name="home_plan"
                                                                   required="required"
                                                                   placeholder="แบบบ้าน">
                                                        </div>
                                                        ขนาด
                                                        <div class="col-sm-1">
                                                            <input type="text" class="form-control"
                                                                   id="lan_qty2"
                                                                   name="lan_qty2"
                                                                   required="required"
                                                                   placeholder="ขนาด">
                                                        </div>
                                                        ตรม. ที่โครงการเดอะมิวส์ ท่าอ่าง ต.ท่าอ่าง

                                                    </div>

                                                    <div class="form-group row">
                                                        <div class="col-sm-1">
                                                            เนื้อที่
                                                        </div>
                                                        <div class="col-sm-1">
                                                            <input type="text" class="form-control"
                                                                   id="lan_qty1"
                                                                   name="lan_qty1"
                                                                   required="required"
                                                                   placeholder="เนื้อที่">
                                                        </div>
                                                        ตร.ว. ตามผังสังเขป พร้อมบ้านเดี่ยวแบบ
                                                        <div class="col-sm-3">
                                                            <input type="text" class="form-control"
                                                                   id="home_plan"
                                                                   name="home_plan"
                                                                   required="required"
                                                                   placeholder="แบบบ้าน">
                                                        </div>
                                                        ขนาด
                                                        <div class="col-sm-1">
                                                            <input type="text" class="form-control"
                                                                   id="lan_qty2"
                                                                   name="lan_qty2"
                                                                   required="required"
                                                                   placeholder="ขนาด">
                                                        </div>
                                                        ตรม. ที่โครงการเดอะมิวส์ ท่าอ่าง ต.ท่าอ่าง

                                                    </div>


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
                                                                       id="doc_no_detail"
                                                                       name="doc_no_detail" value="">
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
                                                                               id="name_t"
                                                                               name="name_t"
                                                                               required="required"
                                                                               readonly="true"
                                                                               placeholder="ชื่อสินค้า/วัสดุ">
                                                                    </div>

                                                                    <div class="col-sm-2">
                                                                        <label for="quantity"
                                                                               class="control-label">เลือก</label>

                                                                        <a data-toggle="modal"
                                                                           href="#SearchProductModal"
                                                                           class="btn btn-primary">
                                                                            Click <i class="fa fa-search"
                                                                                     aria-hidden="true"></i>
                                                                        </a>
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row">
                                                                    <div class="col-sm-5">
                                                                        <label for="quantity"
                                                                               class="control-label">จำนวน</label>
                                                                        <input type="number" class="form-control"
                                                                               id="quantity"
                                                                               name="quantity"
                                                                               required="required"
                                                                               placeholder="จำนวน">
                                                                    </div>
                                                                    <input type="hidden" class="form-control"
                                                                           id="unit_id"
                                                                           name="unit_id">
                                                                    <div class="col-sm-5">
                                                                        <label for="unit_name"
                                                                               class="control-label">หน่วยนับ</label>
                                                                        <input type="text" class="form-control"
                                                                               id="unit_name"
                                                                               name="unit_name"
                                                                               required="required"
                                                                               readonly="true"
                                                                               placeholder="หน่วยนับ">
                                                                    </div>

                                                                </div>
                                                                <div class="form-group row">
                                                                    <div class="col-sm-5">
                                                                        <label for="price"
                                                                               class="control-label">ราคา/หน่วย</label>
                                                                        <input type="number" class="form-control"
                                                                               id="price"
                                                                               name="price"
                                                                               required="required"
                                                                               placeholder="ราคา/หน่วย">
                                                                    </div>
                                                                    <div class="col-sm-5">
                                                                        <label for="total_price"
                                                                               class="control-label">ราคารวม</label>
                                                                        <input type="number" class="form-control"
                                                                               id="total_price"
                                                                               name="total_price"
                                                                               required="required"
                                                                               placeholder="ราคารวม">
                                                                    </div>
                                                                </div>

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
                                                                        class="fa fa-window-close"></i>
                                                            </button>
                                                        </div>
                                                    </form>

                                                </div>
                                            </div>
                                        </div>


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

            if (queryString["doc_no"] != null && queryString["f_name"] != null) {

                $('#doc_no').val(queryString["doc_no"]);
                $('#doc_date').val(queryString["doc_date"]);
                $('#customer_id').val(queryString["customer_id"]);
                $('#f_name').val(queryString["f_name"]);

                Load_Data_Detail(queryString["doc_no"], "v_order_detail");
            }
        });
    </script>

    <script>
        function Load_Data_Detail(doc_no, table_name) {

            let formData = {
                action: "GET_ORDER_DETAIL",
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
                if ($('#doc_date').val() == '' || $('#f_name').val() == '') {
                    alertify.error("กรุณาป้อนวันที่ / ชื่อลูกค้า ");
                } else {
                    $('#recordModal').modal('show');
                    $('#KeyAddDetail').val($('#KeyAddData').val());
                    $('#doc_no_detail').val($('#doc_no').val());
                    $('#doc_date_detail').val($('#doc_date').val());
                    $('#product_id').val("");
                    $('#name_t').val("");
                    $('#quantity').val("");
                    $('#price').val("");
                    $('#total_price').val("");
                    $('#unit_id').val("");
                    $('#unit_name').val("");
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
                doc_no = $('#KeyAddData').val();
                table_name = "v_order_detail_temp";
            } else {
                doc_no = $('#doc_no').val();
                table_name = "v_order_detail";
            }

            let formData = {action: "GET_DATA", id: rec_id, doc_no: doc_no, table_name: table_name};
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
                        $('#doc_no_detail').val(doc_no);
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
                doc_no = $('#KeyAddData').val();
                table_name = "v_order_detail_temp";
            } else {
                doc_no = $('#doc_no').val();
                table_name = "v_order_detail";
            }

            let formData = {action: "GET_DATA", id: rec_id, doc_no: doc_no, table_name: table_name};
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
                        $('#doc_no_detail').val(doc_no);
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
            let doc_no_detail = $('#doc_no_detail').val();
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
                        Load_Data_Detail(doc_no_detail, "v_order_detail");
                    }
                }
            })

        });

    </script>


    </body>

    </html>

<?php } ?>


