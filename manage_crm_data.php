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
                                                            <label for="doc_id"
                                                                   class="control-label">เลขที่เอกสาร</label>
                                                            <input type="text" class="form-control"
                                                                   id="doc_id" name="doc_id"
                                                                   readonly="true"
                                                                   placeholder="">
                                                        </div>

                                                        <div class="col-sm-2">
                                                            <label for="doc_date"
                                                                   class="control-label">วันที่</label>
                                                            <input type="text" class="form-control"
                                                                   id="doc_date"
                                                                   name="doc_date"
                                                                   required="required"
                                                                   readonly="true"
                                                                   value=""
                                                                   placeholder="">
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
                                                        <div class="col-sm-2">
                                                            <label for="CusModal"
                                                                   class="control-label"> เลือกชื่อลูกค้า </label>
                                                            <a data-toggle="modal" href="#SearchCusModal"
                                                               class="btn btn-primary">
                                                                Click <i class="fa fa-search"
                                                                         aria-hidden="true"></i>
                                                            </a>

                                                        </div>
                                                    </div>

                                                    <button type='button' name='btnAdd' id='btnAdd'
                                                            class='btn btn-primary btn-xs'>Add เพิ่มรายการคำถาม-คำตอบ
                                                        <i class="fa fa-plus"></i>
                                                    </button>
                                                    <button type='button' name='btnRefresh' id='btnRefresh'
                                                            class='btn btn-success btn-xs'
                                                            onclick="RefreshDataTable();">Refresh
                                                        <i class="fa fa-refresh"></i>
                                                    </button>

                                                    <table cellpadding="0" cellspacing="0" border="0"
                                                           class="display"
                                                           id="TableCRMDetailList"
                                                           width="100%">
                                                        <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>คำถาม</th>
                                                            <th>คำตอบ</th>
                                                            <th>Action</th>
                                                        </tr>
                                                        </thead>
                                                    </table>

                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <input type="hidden" name="id" id="id"/>
                                                <input type="hidden" name="save_status" id="save_status"/>
                                                <input type="hidden" name="action" id="action" value=""/>
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
                                                                   id="TableCusCRMList"
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
                                                                       id="doc_id_detail"
                                                                       name="doc_id_detail" value="">
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
                                                                    <div>
                                                                        <!--label for="faq_id"
                                                                               class="control-label">รหัสคำถาม</label-->
                                                                        <input type="hidden"
                                                                               class="form-control"
                                                                               id="faq_id" name="faq_id"
                                                                               readonly="true"
                                                                               placeholder="รหัสคำถาม">
                                                                    </div>
                                                                    <div class="col-sm-12">
                                                                        <label for="faq_desc"
                                                                               class="control-label">คำถาม</label>
                                                                        <input type="text" class="form-control"
                                                                               id="faq_desc"
                                                                               name="faq_desc"
                                                                               readonly="true"
                                                                               placeholder="คำถาม">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <div class="col-sm-12">
                                                                        <label for="faq_anwser" class="control-label">คำตอบ</label>
                                                                        <textarea class="form-control" id="faq_anwser"
                                                                                  name="faq_anwser"
                                                                                  required="required"
                                                                                  rows="4"></textarea>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="modal-footer">
                                                                <input type="hidden" name="id" id="id"/>
                                                                <input type="hidden" name="detail_id"
                                                                       id="detail_id"/>
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

    <
    <script src="js/modal/show_cust_crm_modal.js"></script>

    <!-- Bootstrap Touchspin -->
    <script src="vendor/bootstrap-touchspin/js/jquery.bootstrap-touchspin.js"></script>
    <!-- ClockPicker -->

    <!-- RuangAdmin Javascript -->
    <script src="js/myadmin.min.js"></script>
    <script src="js/util.js"></script>
    <script src="js/Calculate.js"></script>

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

            if (queryString["doc_id"] != null && queryString["customer_name"] != null) {

                $('#doc_id').val(queryString["doc_id"]);
                $('#doc_date').val(queryString["doc_date"]);
                $('#customer_id').val(queryString["customer_id"]);
                $('#customer_name').val(queryString["customer_name"]);

                Load_Data_Detail(queryString["doc_id"], "v_ims_customer_crm_quest_detail");
            }
        });
    </script>

    <script>
        function Load_Data_Detail(doc_id, table_name) {

            let formData = {
                action: "GET_CRM_DETAIL",
                sub_action: "GET_MASTER",
                doc_id: doc_id,
                table_name: table_name
            };
            let dataRecords = $('#TableCRMDetailList').DataTable({
                'processing': true,
                'serverSide': true,
                'serverMethod': 'post',
                "paging": false,
                "ordering": false,
                'info': false,
                "searching": false,
                'autoWidth': true,
                <?php  if ($_SESSION['deviceType'] !== 'computer') {
                    echo "'scrollX': true,";
                }?>
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
                'ajax': {
                    'url': 'model/manage_crm_detail_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'line_no'},
                    {data: 'faq_desc'},
                    {data: 'faq_anwser'},
                    {data: 'update'}
                ]
            });
        }
    </script>

    <script>
        $(document).ready(function () {
            $("#btnAdd").click(function () {
                if ($('#doc_date').val() === '' || $('#customer_name').val() === '') {
                    alertify.error("กรุณาป้อนวันที่ / ชื่อลูกค้า ");
                } else {
                    let formData = $('#MainrecordForm').serialize();
                    $.ajax({
                        url: 'model/manage_crm_process.php',
                        method: "POST",
                        data: formData,
                        success: function (data) {
                            if ($('#KeyAddData').val() !== '') {
                                let KeyAddData = $('#KeyAddData').val();
                                Save_Detail(KeyAddData);
                                ReloadDataTable(KeyAddData);
                                $('#action').val("UPDATE");
                            }
                            alertify.success(data);
                            $('#save_status').val("save");
                        }
                    })
                }
            });
        });
    </script>

    <script>

        $("#TableCRMDetailList").on('click', '.update', function () {

            let id = $(this).attr("id");
            let doc_id = $('#doc_id').val();
            let table_name = "v_ims_customer_crm_quest_detail";

            //alert(id + " | " + doc_id + " | " + table_name);

            let formData = {action: "GET_DATA", id: id, doc_id: doc_id, table_name: table_name};

            $.ajax({
                type: "POST",
                url: 'model/manage_crm_detail_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let doc_id = response[i].doc_id;
                        let doc_date = response[i].doc_date;
                        let faq_id = response[i].faq_id;
                        let faq_desc = response[i].faq_desc;
                        let faq_anwser = response[i].faq_anwser;

                        //alert(id + " | " + faq_id + " | " + faq_desc + " | " + faq_anwser);

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#detail_id').val(id);
                        $('#doc_id_detail').val(doc_id);
                        $('#doc_date_detail').val(doc_date);
                        $('#faq_id').val(faq_id);
                        $('#faq_desc').val(faq_desc);
                        $('#faq_anwser').val(faq_anwser);
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
        function Save_Detail(KeyAddData) {

            let formData = {action: "SAVE_DETAIL", KeyAddData: KeyAddData};
            $.ajax({
                url: 'model/manage_crm_detail_process.php',
                method: "POST",
                data: formData,
                success: function (data) {
                    alertify.success(data);
                }
            })

        }
    </script>

    <script>
        $(document).ready(function () {
            $('#recordForm').on('submit', function (event) {
                let doc_id = $('#doc_id_detail').val();
                let table_name = "v_ims_customer_crm_quest_detail";
                let data = $(this).serialize();
                //alert(doc_id + " | " + table_name + " | " + data);
                event.preventDefault(); // ป้องกันการส่งฟอร์มแบบปกติ
                $.ajax({
                    url: 'model/manage_crm_detail_process.php',
                    method: "POST",
                    data: data,
                    success: function (data) {
                        alertify.success(data);
                        RefreshDataTable();
                        $('#recordModal').modal('hide'); // ปิด modal หลังบันทึกสำเร็จ
                    }
                })
            });
        });
    </script>

    <script>
        function ReloadDataTable() {
            let KeyAddData = $('#KeyAddData').val();
            let formData = {action: "GET_DATA_KEY", KeyAddData: KeyAddData};
            $.ajax({
                type: "POST",
                url: 'model/manage_crm_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        //let id = response[i].id;
                        let doc_id = response[i].doc_id;
                        let doc_date = response[i].doc_date;
                        //let customer_id = response[i].customer_id;
                        let customer_name = response[i].customer_name;
                        //$('#id').val(id);
                        $('#doc_id').val(doc_id);
                        $('#doc_date').val(doc_date);
                        //$('#customer_id').val(customer_id);
                        $('#customer_name').val(customer_name);

                        Load_Data_Detail($('#doc_id').val(), "v_ims_customer_crm_quest_detail");

                    }
                },
                error: function (response) {
                    alertify.error("error : " + response);
                }
            });

        }
    </script>

    <script>
        function RefreshDataTable() {
            // ตรวจสอบว่า #KeyAddData เป็นค่าว่าง หรือ #doc_id ไม่ใช่ค่าว่าง
            if ($('#KeyAddData').val() === '' || $('#doc_id').val() !== '') {
                $('#TableCRMDetailList').DataTable().ajax.reload(); // รีโหลด DataTable
            }
        }
    </script>

    <script>
        $(document).ready(function () {
            // ตรวจสอบค่า action เมื่อโหลดหน้าเว็บ
            if ($('#action').val() === 'UPDATE') {
                // ปิดการใช้งานปุ่มเมื่อค่าเป็น 'UPDATE'
                $('#btnAdd').prop('disabled', true);
                $('#btnRefresh').prop('disabled', false);
            } else {
                $('#btnAdd').prop('disabled', false);
                $('#btnRefresh').prop('disabled', true);
            }

            // กรณีต้องการให้มีการตรวจสอบใหม่ทุกครั้งที่ค่า action เปลี่ยน
            $('#action').on('change', function () {
                if ($(this).val() === 'UPDATE') {
                    $('#btnAdd').prop('disabled', true);
                } else {
                    $('#btnAdd').prop('disabled', false);
                }
            });
        });
    </script>


    </body>

    </html>

<?php } ?>