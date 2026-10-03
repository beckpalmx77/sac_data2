<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {

    ?>

    <!DOCTYPE html>
    <html lang="th">

    <style>

        .feedback {
            background-color: #31B0D5;
            color: white;
            padding: 10px 20px;
            border-radius: 4px;
            border-color: #46b8da;
        }


        #menu_fix_button {
            position: fixed;
            bottom: 4px;
            right: 80px;
        }

    </style>

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
                                                        <div class="col-sm-4">
                                                            <label for="customer_id"
                                                                   class="control-label">รหัสลูกค้า</label>
                                                            <input type="text" class="form-control"
                                                                   id="customer_id"
                                                                   name="customer_id"
                                                                   required="required"
                                                                   placeholder="รหัสลูกค้า">
                                                        </div>
                                                        <div class="col-sm-4">
                                                            <label for="f_name"
                                                                   class="control-label">ชื่อลูกค้า</label>
                                                            <input type="text" class="form-control"
                                                                   id="f_name"
                                                                   name="f_name"
                                                                   required="required"
                                                                   placeholder="ชื่อลูกค้า">
                                                        </div>
                                                        <div class="col-sm-2">
                                                            <label for="CusModal"
                                                                   class="control-label"> เลือกชื่อลูกค้า </label>
                                                            <a data-toggle="modal" href="#SearchCusCrmModal"
                                                               class="btn btn-primary">
                                                                Click <i class="fa fa-search"
                                                                         aria-hidden="true"></i>
                                                            </a>

                                                        </div>
                                                    </div>

                                                    <div class="col-md-12 col-md-offset-2">
                                                        <table id='TableRecordList' class='display dataTable'>
                                                            <thead>
                                                            <tr>
                                                                <th>รหัสคำถาม</th>
                                                                <th>คำถาม</th>
                                                                <th>คำตอบ</th>
                                                                <th>Action</th>
                                                            </tr>
                                                            </thead>
                                                            <tfoot>
                                                            <tr>
                                                                <th>รหัสคำถาม</th>
                                                                <th>คำถาม</th>
                                                                <th>คำตอบ</th>
                                                                <th>Action</th>
                                                            </tr>
                                                            </tfoot>
                                                        </table>

                                                    </div>
                                                </div>

                                                <?php include("includes/stick_menu.php"); ?>

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
                                                                       id="customer_detail_id"
                                                                       name="customer_detail_id" value="">
                                                            </div>
                                                        </div>

                                                        <div class="modal-body">
                                                            <div class="modal-body">

                                                                <div class="form-group row">
                                                                    <div class="col-sm-5">
                                                                        <label for="faq_id"
                                                                               class="control-label">รหัสคำถาม</label>
                                                                        <input type="faq_id"
                                                                               class="form-control"
                                                                               id="faq_id" name="faq_id"
                                                                               required="required"
                                                                               readonly="true"
                                                                               placeholder="รหัสคำถาม">
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row">
                                                                    <div class="col-sm-12">
                                                                        <label for="faq_desc"
                                                                               class="control-label">คำถาม</label>
                                                                        <input type="text" class="form-control"
                                                                               id="faq_desc"
                                                                               name="faq_desc"
                                                                               required="required"
                                                                               readonly="true"
                                                                               placeholder="คำถาม">
                                                                    </div>
                                                                </div>

                                                                <div class="col-sm-12">
                                                                    <label for="AnwserModal"
                                                                           class="control-label"> เลือกคำตอบ </label>
                                                                    <a data-toggle="modal" href="#SearchAnwserModal"
                                                                       class="btn btn-primary">
                                                                        Click <i class="fa fa-search"
                                                                                 aria-hidden="true"></i>
                                                                    </a>

                                                                </div>

                                                                <div class="form-group row">
                                                                    <div class="col-sm-12">
                                                                        <label for="faq_anwser"
                                                                               class="control-label">คำตอบ</label>
                                                                        <input type="hidden" id="faq_anwser_id" name="faq_anwser_id">
                                                                        <input type="text" class="form-control"
                                                                               id="faq_anwser"
                                                                               name="faq_anwser"
                                                                               required="required"
                                                                               placeholder="คำตอบ">
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
                                                                        class="fa fa-times"></i>
                                                            </button>
                                                        </div>
                                                    </form>

                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal fade" id="SearchAnwserModal">
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
                                                                   id="TableAnwserList"
                                                                   width="100%">
                                                                <thead>
                                                                <tr>
                                                                    <th>รหัสคำตอบ</th>
                                                                    <th>คำตอบ</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                                </thead>
                                                                <tfoot>
                                                                <tr>
                                                                    <th>รหัสคำตอบ</th>
                                                                    <th>คำตอบ</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                                </tfoot>
                                                            </table>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="modal fade" id="SearchCusCrmModal">
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
    <!-- Javascript for this page -->

    <script src="js/modal/show_customer_crm_modal.js"></script>
    <script src="js/modal/show_anwser_modal.js"></script>

    <script src="js/modal/show_product_modal.js"></script>
    <script src="js/modal/show_unit_modal.js"></script>

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
            $('#id').val(queryString["id"]);

            $('#save_status').val("before");

            let formData = {action: "GET_DATA", id: queryString["id"]};

            $.ajax({
                type: "POST",
                url: 'model/manage_customer_crm_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let customer_id = response[i].customer_id;
                        let customer_name = response[i].customer_name;
                        $('#id').val(id);
                        $('#customer_detail_id').val(customer_id);
                        $('#customer_id').val(customer_id);
                        $('#f_name').val(customer_name);
                        Load_Answer();
                    }
                },
                error: function (response) {
                    alertify.error("error : " + response);
                }
            });
        });
    </script>

    <script>
        function Save_Answer() {

            let customer_id = $('#customer_id').val();

            let formData = {action: "SEARCH_DATA", customer_id: customer_id};
            $.ajax({
                type: "POST",
                url: 'model/manage_customer_crm_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {

                    Load_Answer();

                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                    }
                },
                error: function (response) {
                    alertify.error("error : " + response);
                }
            });

        }
    </script>

    <script>
        function Load_Answer() {
            let customer_id = $('#customer_id').val();
            let formData = {action: "GET_DATA_CRM", sub_action: "GET_MASTER", customer_id: customer_id};
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
                'serverMethod': 'post',
                'ajax': {
                    'url': 'model/manage_customer_crm_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'faq_id'},
                    {data: 'faq_desc'},
                    {data: 'faq_anwser'},
                    {data: 'update'}
                ]
            });

        }
    </script>

    <script>

        $("#TableRecordList").on('click', '.update', function () {
            let id = $(this).attr("id");
            //alert(id);
            let formData = {action: "GET_DATA", id: id};
            $.ajax({
                type: "POST",
                url: 'model/manage_customer_crm_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let customer_id = response[i].customer_id;
                        let faq_id = response[i].faq_id;
                        let faq_desc = response[i].faq_desc;
                        let faq_anwser = response[i].faq_anwser;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#customer_detail_id').val(customer_id);
                        $('#faq_id').val(faq_id);
                        $('#faq_desc').val(faq_desc);
                        $('#faq_anwser').val(faq_anwser);
                        $('.modal-title').html("<i class='fa fa-plus'></i> Edit Record");
                        $('#action').val('UPDATE');
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
            $("#recordModal").on('submit', '#recordForm', function (event) {
                event.preventDefault();
                $('#save').attr('disabled', 'disabled');
                let formData = $(this).serialize();
                $.ajax({
                    url: 'model/manage_customer_crm_process.php',
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

    </body>

    </html>

<?php } ?>



