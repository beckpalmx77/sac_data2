<?php
session_start();
error_reporting(0);
$curr_date = date("d-m-Y");

include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['department_id']) == "") {
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
                        <h1 class="h3 mb-0 text-gray-800"><?php echo urldecode($_GET['s']) ?></h1>
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

                                        <div class="col-md-12 col-md-offset-2">
                                            <label for="name_t"
                                                   class="control-label"><b>เพิ่ม <?php echo urldecode($_GET['s']) ?></b></label>
                                            <button type='button' name='btnAdd' id='btnAdd'
                                                    class='btn btn-primary btn-xs'>Add
                                                <i class="fa fa-plus"></i>
                                            </button>
                                        </div>

                                        <div class="col-md-12 col-md-offset-2">
                                            <table id='TableRecordList' class='display dataTable'>
                                                <thead>
                                                <tr>
                                                    <th>เลขที่เอกสาร</th>
                                                    <th>วันที่</th>
                                                    <th>รหัสลูกค้า</th>
                                                    <th>ชื่อลูกค้า</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                                <tfoot>
                                                <tr>
                                                    <th>เลขที่เอกสาร</th>
                                                    <th>วันที่</th>
                                                    <th>รหัสลูกค้า</th>
                                                    <th>ชื่อลูกค้า</th>
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
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-hidden="true">×
                                                        </button>
                                                    </div>

                                                    <form method="post" id="recordForm">
                                                        <div class="modal-body">
                                                            <div class="modal-body">

                                                                <div class="form-group">
                                                                    <label for="customer_id" class="control-label">รหัสลูกค้า</label>
                                                                    <input type="customer_id" class="form-control"
                                                                           id="customer_id" name="customer_id"
                                                                           readonly="true"
                                                                           placeholder="สร้างอัตโนมัติ">
                                                                </div>

                                                                <div class="form-group">
                                                                    <label for="f_name" class="control-label">ชื่อลูกค้า</label>
                                                                    <input type="f_name" class="form-control"
                                                                           id="f_name" name="f_name"
                                                                           readonly="true"
                                                                           placeholder="">
                                                                </div>

                                                                <div class="form-group">
                                                                    <label for="ARCD_NAME" class="control-label">การชำระเงิน</label>
                                                                    <input type="ARCD_NAME" class="form-control"
                                                                           id="ARCD_NAME" name="ARCD_NAME"
                                                                           readonly="true"
                                                                           placeholder="">
                                                                </div>

                                                                <div class="form-group">
                                                                    <label for="credit" class="control-label">วงเงิน</label>
                                                                    <input type="credit" class="form-control"
                                                                           id="credit" name="credit"
                                                                           readonly="true"
                                                                           placeholder="">
                                                                </div>

                                                                <div class="form-group">
                                                                    <label for="phone" class="control-label">โทรฯ</label>
                                                                    <input type="phone" class="form-control"
                                                                           id="phone" name="phone"
                                                                           readonly="true"
                                                                           placeholder="">
                                                                </div>

                                                                <div class="form-group">
                                                                    <label for="contact_name" class="control-label">ชื่อผู้ติดต่อ</label>
                                                                    <input type="contact_name" class="form-control"
                                                                           id="contact_name" name="contact_name"
                                                                           readonly="true"
                                                                           placeholder="">
                                                                </div>

                                                                <div class="form-group">
                                                                    <label for="sale_name" class="control-label">ชื่อ Sale ที่รับผิดชอบ</label>
                                                                    <input type="sale_name" class="form-control"
                                                                           id="sale_name" name="sale_name"
                                                                           readonly="true"
                                                                           placeholder="">
                                                                </div>

                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <input type="hidden" name="id" id="id"/>
                                                            <input type="hidden" name="action" id="action" value=""/>
                                                            <button type="button" class="btn btn-danger"
                                                                    data-dismiss="modal">Close <i
                                                                        class="fa fa-window-close"></i>
                                                            </button>
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


    <script src="js/util/calculate_datetime.js"></script>

    <script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>

    <script src="vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
    <script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
    <!--link href="vendor/date-picker-1.9/css/date_picker_style.css" rel="stylesheet"/-->
    <link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>

    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

    <script src="js/popup.js"></script>

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
            $(".icon-input-btn").each(function () {
                let btnFont = $(this).find(".btn").css("font-size");
                let btnColor = $(this).find(".btn").css("color");
                $(this).find(".fa").css({'font-size': btnFont, 'color': btnColor});
            });
        });
    </script>

    <script>
        function encodeURL(url) {
            return encodeURIComponent(url);
        }

        function decodeURL(url) {
            return encodeURIComponent(url);
        }

    </script>

    <script>
        function readURL(input) {
            if (input.files && input.files[0]) {
                let reader = new FileReader();
                reader.onload = function (e) {
                    $('#ImgFile')
                        .attr('src', e.target.result);
                };

                reader.readAsDataURL(input.files[0]);

            }
        }
    </script>

    <script>
        $(document).ready(function () {
            let formData = {action: "GET_QUEST_DOC", sub_action: "GET_MASTER", page_manage: "USER",};
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
                'autoWidth': true,
                'searching': true,
                <?php  if ($_SESSION['deviceType'] !== 'computer') {
                    echo "'scrollX': true,";
                }?>
                'ajax': {
                    'url': 'model/manage_customer_crm_process-a.php',
                    'data': formData
                },
                'columns': [
                    {data: 'doc_id'},
                    {data: 'doc_date'},
                    {data: 'customer_id'},
                    {data: 'customer_name'},
                    {data: 'update'}
                ]
            });

        });

    </script>

    <script>
        $(document).ready(function () {

            $("#recordModal").on('submit', '#recordForm', function (event) {
                event.preventDefault();
                //$('#save').attr('disabled', 'disabled');

                if (chkTime($('#time_leave_start').val()) && chkTime($('#time_leave_to').val())) {

                    if ($('#date_leave_start').val() !== '' && $('#date_leave_to').val() !== '' && ($('#leave_day').val() !== '' || $('#leave_day').val() !== '0')) {

                        let date_leave_1 = $('#doc_date').val().substr(3, 2) + "/" + $('#doc_date').val().substr(0, 2) + "/" + $('#doc_date').val().substr(6, 10);
                        let date_leave_2 = $('#date_leave_start').val().substr(3, 2) + "/" + $('#date_leave_start').val().substr(0, 2) + "/" + $('#date_leave_start').val().substr(6, 10);

                        let check_day = CalDay(date_leave_1, date_leave_2); // Check Date
                        let l_before = $('#leave_before').val();


                        $('#filename').val($('#ImgFile').val());

                        let formData = $(this).serialize();

                        // alert(formData);

                        $.ajax({
                            url: 'model/manage_leave_document_process.php',
                            method: "POST",
                            data: formData,
                            success: function (data) {

                                if (data.includes("Over")) {
                                    alertify.error(data);
                                } else {
                                    alertify.success(data);
                                }

                                $('#recordForm')[0].reset();
                                $('#recordModal').modal('hide');
                                $('#save').attr('disabled', false);
                                dataRecords.ajax.reload();

                            }
                        })

                    } else {
                        alertify.error("กรุณาป้อนวันที่ต้องการลา !!!");
                    }
                } else {
                    alertify.error("กรุณาป้อนวันที่ - เวลา ให้ถูกต้อง !!!");
                }

            });

        });

    </script>

    <script>
        $(document).ready(function () {

            $("#btnAdd").click(function () {

                //alert(<?php echo $_SESSION['work_time_start']?>);
                let today = new Date();
                let day = String(today.getDate()).padStart(2, '0');
                let month = String(today.getMonth() + 1).padStart(2, '0'); // January is 0!
                let year = today.getFullYear();
                let formattedDate = day + '-' + month + '-' + year;

                $('#recordModal').modal('show');
                $('#id').val("");
                $('#doc_id').val("");
                $('#doc_date').val(formattedDate);
                $('#leave_type_id').val("");
                $('#leave_type_detail').val("");
                $('#date_leave_start').val("");
                $('#date_leave_to').val("");
                $('#leave_day').val("1");
                $('#remark').val("");
                $('#status').val("N");
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
                url: 'model/manage_leave_document_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let doc_id = response[i].doc_id;
                        let doc_date = response[i].doc_date;
                        let emp_id = response[i].emp_id;
                        let full_name = response[i].full_name;
                        let f_name = response[i].f_name;
                        let l_name = response[i].l_name;
                        let leave_type_id = response[i].leave_type_id;
                        let leave_type_detail = response[i].leave_type_detail;
                        let date_leave_start = response[i].date_leave_start;
                        let date_leave_to = response[i].date_leave_to;
                        let time_leave_start = response[i].time_leave_start;
                        let time_leave_to = response[i].time_leave_to;
                        let leave_before = response[i].leave_before;
                        let leave_day = response[i].leave_day;
                        let remark = response[i].remark;
                        let status = response[i].status;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#doc_id').val(doc_id);
                        $('#doc_date').val(doc_date);
                        $('#emp_id').val(emp_id);
                        $('#full_name').val(full_name);
                        $('#f_name').val(f_name);
                        $('#l_name').val(l_name);
                        $('#leave_type_id').val(leave_type_id);
                        $('#leave_type_detail').val(leave_type_detail);
                        $('#date_leave_start').val(date_leave_start);
                        $('#date_leave_to').val(date_leave_to);
                        $('#time_leave_start').val(time_leave_start);
                        $('#time_leave_to').val(time_leave_to);
                        $('#leave_before').val(leave_before);
                        $('#leave_day').val(leave_day);
                        $('#remark').val(remark);
                        $('#status').val(status);
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

        $("#TableRecordList").on('click', '.image', function () {
            let id = $(this).attr("id");
            let formData = {action: "GET_DATA", id: id};
            $.ajax({
                type: "POST",
                url: 'model/manage_leave_document_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let doc_id = response[i].doc_id;
                        let doc_date = response[i].doc_date;
                        let emp_id = response[i].emp_id;
                        let full_name = response[i].full_name;
                        let leave_type_id = response[i].leave_type_id;
                        let leave_type_detail = response[i].leave_type_detail;
                        let date_leave_start = response[i].date_leave_start;
                        let date_leave_to = response[i].date_leave_to;
                        let time_leave_start = response[i].time_leave_start;
                        let time_leave_to = response[i].time_leave_to;
                        let leave_before = response[i].leave_before;
                        let leave_day = response[i].leave_day;
                        let picture = response[i].picture;
                        let remark = response[i].remark;
                        let status = response[i].status;

                        let main_menu = "บันทึกข้อมูลหลัก";
                        let sub_menu = "เอกสารการลางาน (พนักงาน)";

                        let originalURL = "upload_leave_data.php?title=เอกสารการลา (Document)"
                            + '&main_menu=' + main_menu + '&sub_menu=' + sub_menu
                            + '&id=' + id
                            + '&doc_id=' + doc_id + '&doc_date=' + doc_date
                            + '&emp_id=' + emp_id + '&full_name=' + full_name
                            + '&leave_type_id=' + leave_type_id
                            + '&leave_type_detail=' + leave_type_detail
                            + '&date_leave_start=' + date_leave_start
                            + '&date_leave_to=' + date_leave_to
                            + '&time_leave_start=' + time_leave_start
                            + '&time_leave_to=' + time_leave_to
                            + '&leave_before=' + leave_before
                            + '&leave_day=' + leave_day
                            + '&picture=' + picture
                            + '&remark=' + remark
                            + '&status=' + status
                            + '&action=UPDATE';

                        OpenPopupCenter(originalURL, "", "");

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