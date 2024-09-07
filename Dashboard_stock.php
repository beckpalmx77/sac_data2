<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "" || strlen($_SESSION['department_id']) == "") {
    header("Location: index.php");
} else {
    ?>

    <!DOCTYPE html>
    <html lang="th">
    <body id="page-top">

    <style>
        .large-text {
            font-size: 20px; /* ปรับขนาดตัวอักษรตามที่คุณต้องการ */
        }

        .medium-text {
            font-size: 10px; /* ปรับขนาดตัวอักษรตามที่คุณต้องการ */
        }

        .large-text {
            font-size: 20px; /* ปรับขนาดตัวอักษรตามที่คุณต้องการ */
        }
    </style>

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

    <div id="wrapper">
        <?php
        // include('includes/Side-Bar.php');
        ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('includes/Top-Bar-WH.php'); ?>
                <div class="container-fluid" id="container-wrapper">
                    <div class="row mb-3">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col mr-2">
                                            <div class="large-text font-weight-bold text-uppercase mb-1">
                                                จำนวนผู้เข้าร่วมงานทั้งหมด
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <!--i class="fa fa-address-book fa-3x text-primary"></i-->
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><p
                                                        class="text-primary large-text"
                                                        id="Text1"></p></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Earnings (Annual) Card Example -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="large-text font-weight-bold text-uppercase mb-1">Check In
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <!--i class="fa fa-address-book fa-3x text-success"></i-->
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><p
                                                        class="text-success large-text"
                                                        id="Text2"></p></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- New User Card Example -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="large-text font-weight-bold text-uppercase mb-1">Not Check In
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <!--i class="fa fa-address-book fa-3x text-danger"></i-->
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><p
                                                        class="text-danger large-text"
                                                        id="Text3"></p></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Pending Requests Card Example -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="large-text font-weight-bold text-uppercase mb-1">% Check In
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <!--i class="fa fa-address-book fa-3x text-info"></i-->
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><p
                                                        class="text-info large-text"
                                                        id="Text-Percent"></p></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="container-fluid" id="container-wrapper">

                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-12">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                </div>
                                <div class="card-body">
                                    <section class="container-fluid">
                                        <div class="large-text font-weight-bold text-uppercase mb-1 "
                                             style="color: #8F35F6;">
                                            ลงทะเบียนเข้างาน
                                            <button type='button' name='btnRefresh' id='btnRefresh'
                                                    class='btn btn-success btn-xs' onclick="ReloadDataTable();">Refresh
                                                <i class="fa fa-refresh"></i>
                                            </button>
                                        </div>
                                        <div class="col-md-12 col-md-offset-2">
                                            <table id='TableRecordList' class='display dataTable'>
                                                <thead>
                                                <tr>
                                                    <th>รหัสลูกค้า</th>
                                                    <th>ชื่อบริษัท/ร้านค้า</th>
                                                    <th>ชื่อผู้ติดต่อ</th>
                                                    <th>หมายเลขโทรศัพท์</th>
                                                    <th>จังหวัด</th>
                                                    <th>sale/ผู้ติดต่อ</th>
                                                    <th>โต๊ะ</th>
                                                    <th>Check In</th>
                                                    <th>Action</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                                <tfoot>
                                                <tr>
                                                    <th>รหัสลูกค้า</th>
                                                    <th>ชื่อบริษัท/ร้านค้า</th>
                                                    <th>ชื่อผู้ติดต่อ</th>
                                                    <th>หมายเลขโทรศัพท์</th>
                                                    <th>จังหวัด</th>
                                                    <th>sale/ผู้ติดต่อ</th>
                                                    <th>โต๊ะ</th>
                                                    <th>Check In</th>
                                                    <th>Action</th>
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

                                                                <div class="form-group row">
                                                                    <div class="col-sm-3">
                                                                        <label for="cust_id"
                                                                               class="control-label">รหัสบริษัท/ร้านค้า</label>
                                                                        <input type="text" class="form-control"
                                                                               id="cust_id"
                                                                               name="cust_id"
                                                                               readonly="true"
                                                                               placeholder="">
                                                                    </div>
                                                                    <div class="col-sm-5">
                                                                        <label for="ar_name"
                                                                               class="control-label">ชื่อบริษัท/ร้านค้า</label>
                                                                        <input type="text" class="form-control"
                                                                               id="ar_name"
                                                                               name="ar_name"
                                                                               readonly="true"
                                                                               placeholder="">
                                                                    </div>
                                                                    <div class="col-sm-2">
                                                                        <label for="table_number"
                                                                               class="control-label">หมายเลขโต๊ะ</label>
                                                                        <input type="text" class="form-control"
                                                                               id="table_number"
                                                                               name="table_number"
                                                                               placeholder="หมายเลขโต๊ะ">
                                                                    </div>
                                                                    <div class="col-sm-2">
                                                                        <label for="cust_level"
                                                                               class="control-label">GUEST</label>
                                                                        <input type="text" class="form-control"
                                                                               id="cust_level"
                                                                               name="cust_level"
                                                                               placeholder="">
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row">

                                                                    <div class="col-sm-4">
                                                                        <label for="phone"
                                                                               class="control-label">หมายเลขโทรศัพท์</label>
                                                                        <input type="text" class="form-control"
                                                                               id="phone"
                                                                               name="phone"
                                                                               required="required"
                                                                               placeholder="">
                                                                    </div>
                                                                    <input type="hidden" class="form-control"
                                                                           id="province_code"
                                                                           name="province_code">
                                                                    <div class="col-sm-4">
                                                                        <label for="province_name"
                                                                               class="control-label">จังหวัด</label>
                                                                        <input type="text" class="form-control"
                                                                               id="province_name"
                                                                               name="province_name"
                                                                               required="required"
                                                                               readonly="true"
                                                                               placeholder="จังหวัด">
                                                                    </div>
                                                                    <div class="col-sm-4">
                                                                        <label for="sale_contact_name"
                                                                               class="control-label">ชื่อเซลล์</label>
                                                                        <input type="text" class="form-control"
                                                                               id="sale_contact_name"
                                                                               name="sale_contact_name"
                                                                               required="required"
                                                                               readonly="true"
                                                                               placeholder="ชื่อเซลล์">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <div class="col-sm-4">
                                                                        <label for="cust_name_1"
                                                                               class="control-label">ชื่อผู้เข้าร่วมงาน
                                                                            1</label>
                                                                        <input type="text" class="form-control"
                                                                               id="cust_name_1"
                                                                               name="cust_name_1"
                                                                               placeholder="">
                                                                    </div>

                                                                    <div class="col-sm-4">
                                                                        <label for="cust_name_2"
                                                                               class="control-label">ชื่อผู้เข้าร่วมงาน
                                                                            2</label>
                                                                        <input type="text" class="form-control"
                                                                               id="cust_name_2"
                                                                               name="cust_name_2"
                                                                               placeholder="">
                                                                    </div>
                                                                    <div class="col-sm-4">
                                                                        <label for="cust_name_3"
                                                                               class="control-label">ชื่อผู้เข้าร่วมงาน
                                                                            3</label>
                                                                        <input type="text" class="form-control"
                                                                               id="cust_name_3"
                                                                               name="cust_name_3"
                                                                               placeholder="">
                                                                    </div>
                                                                </div>
                                                                <div class="form-group row">
                                                                    <div class="col-sm-4">
                                                                        <label for="cust_name_4"
                                                                               class="control-label">ชื่อผู้เข้าร่วมงาน
                                                                            4</label>
                                                                        <input type="text" class="form-control"
                                                                               id="cust_name_4"
                                                                               name="cust_name_4"
                                                                               placeholder="">
                                                                    </div>

                                                                    <div class="col-sm-4">
                                                                        <label for="cust_name_5"
                                                                               class="control-label">ชื่อผู้เข้าร่วมงาน
                                                                            5</label>
                                                                        <input type="text" class="form-control"
                                                                               id="cust_name_5"
                                                                               name="cust_name_5"
                                                                               placeholder="">
                                                                    </div>
                                                                    <div class="col-sm-4">
                                                                        <label for="cust_name_6"
                                                                               class="control-label">ชื่อผู้เข้าร่วมงาน
                                                                            6</label>
                                                                        <input type="text" class="form-control"
                                                                               id="cust_name_6"
                                                                               name="cust_name_6"
                                                                               placeholder="">
                                                                    </div>
                                                                </div>

                                                                <div class="form-group row">
                                                                    <div class="col-sm-4">
                                                                        <label for="register_qty"
                                                                               class="control-label">จำนวนลงทะเบียนร่วมงาน</label>
                                                                        <input type="text" class="form-control"
                                                                               id="register_qty"
                                                                               name="register_qty"
                                                                               placeholder="">
                                                                    </div>
                                                                    <div class="col-sm-4">
                                                                        <label for="attendance_qty"
                                                                               class="control-label">จำนวนผู้เข้าร่วมงาน</label>
                                                                        <input type="text" class="form-control"
                                                                               id="attendance_qty"
                                                                               name="attendance_qty"
                                                                               placeholder="">
                                                                    </div>
                                                                    <div class="col-sm-4">
                                                                        <input type="hidden" id="check_in_status"
                                                                               name="check_in_status">
                                                                        <label for="check_in_status_display"
                                                                               class="control-label">สถานะการ Check
                                                                            In</label>
                                                                        <input type="text" class="form-control"
                                                                               id="check_in_status_display"
                                                                               name="check_in_status_display"
                                                                               readonly="true"
                                                                               placeholder="">
                                                                    </div>
                                                                </div>


                                                            </div>
                                                        </div>

                                                        <div class="modal-footer">
                                                            <input type="hidden" name="id" id="id"/>
                                                            <input type="hidden" name="action" id="action" value=""/>
                                                            <button type="button" id="DetailButton" name="DetailButton"
                                                                    class="btn btn-success">Update <i
                                                                        class="fa fa-check"></i>
                                                            </button>
                                                            <button type="button" id="printButton" name="printButton"
                                                                    class="btn btn-info">Print <i
                                                                        class="fa fa-print"></i>
                                                            </button>

                                                            <span class="icon-input-btn">
                                                                <i class="fa fa-check"></i>
                                                            <input type="submit" name="save" id="save"
                                                                   class="btn btn-primary" value="Confirm"/>
                                                            </span>

                                                            <button type="button" class="btn btn-danger"
                                                                    data-dismiss="modal">Close <i
                                                                        class="fa fa-window-close"></i>
                                                            </button>
                                                        </div>
                                                    </form>


                                                    <div id="printArea" class="d-none mt-5">
                                                        <br>
                                                        <h4>SAC สงวนออโต้คาร์</h4>
                                                        <h4>10 YEARS ANNIVERSARY</h4>
                                                        <h4>CONNECT THE POWER 2024</h4>
                                                        <h4>21 กันยายน 2567</h4>
                                                        <p id="printName"></p>
                                                        <p id="printPhone"></p>
                                                        <p id="printTableNumber"></p>
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
    <script src="vendor/chart.js/Chart.min.js"></script>
    <script src="js/chart/chart-area-demo.js"></script>

    <link href='vendor/calendar/main.css' rel='stylesheet'/>
    <script src='vendor/calendar/main.js'></script>
    <script src='vendor/calendar/locales/th.js'></script>

    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

    <script>
        $(document).ready(function () {

            let height = screen.availHeight;

            //alert(height);

            $(".icon-input-btn").each(function () {
                let btnFont = $(this).find(".btn").css("font-size");
                let btnColor = $(this).find(".btn").css("color");
                $(this).find(".fa").css({'font-size': btnFont, 'color': btnColor});
            });
        });
    </script>


    <script>

        $(document).ready(function () {
            GET_DATA("evs_event_checkin", 1);
            GET_DATA("evs_event_checkin", 2);
            GET_DATA("evs_event_checkin", 3);

            setInterval(function () {
                GET_DATA("evs_event_checkin", 1);
                GET_DATA("evs_event_checkin", 2);
                GET_DATA("evs_event_checkin", 3);
            }, 3000);

            setInterval(function () {
                ReloadDataTable();
            }, 7000);

        });

    </script>

    <script>

        function GET_DATA(table_name, idx) {
            let input_text = document.getElementById("Text" + idx);
            let action = "GET_COUNT_RECORDS_COND";
            let cond = "";
            switch (idx) {
                case 1:
                    action = "GET_COUNT_RECORDS";
                    break;
                case 2:
                    cond = " Where check_in_status = 'Y'";
                    break;
                case 3:
                    cond = " Where check_in_status = 'N'";
                    break;
            }

            let formData = {action: action, table_name: table_name, cond: cond};
            $.ajax({
                type: "POST",
                url: 'model/manage_general_data.php',
                data: formData,
                success: function (response) {
                    input_text.innerHTML = response;
                },
                error: function (response) {
                    alertify.error("error : " + response);
                }
            });

            let value1 = document.getElementById("Text1").innerText;
            let value2 = document.getElementById("Text2").innerText;
            let number1 = parseFloat(value1);
            let number2 = parseFloat(value2);
            let result = (number2 * 100) / number1;
            result = isNaN(result) ? 0 : result.toFixed(2);
            document.getElementById("Text-Percent").innerText = result;
        }

    </script>

    <script>

        //select sum(register_qty) AS sum_register_qty from evs_event_checkin where check_in_status = 'Y'
        //select sum(attendance_qty) AS sum_attendance_qty from evs_event_checkin where check_in_status = 'Y'


    </script>

    <script>
        $(document).ready(function () {
            let formData = {action: "GET_CUSTOMER_CHECKIN", sub_action: "GET_MASTER", page_manage: "ADMIN"};
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
                    'url': 'model/manage_customer_event_checkin_process.php',
                    'data': formData,
                },
                'columns': [
                    {data: 'cust_id'},
                    {data: 'ar_name'},
                    {data: 'cust_name_1'},
                    {data: 'phone'},
                    {data: 'province_name'},
                    {data: 'sale_contact_name'},
                    {data: 'table_number'},
                    {data: 'check_in_status'},
                    {data: 'detail'},
                    {data: 'update'}
                ]
            });

            <!-- *** FOR SUBMIT FORM *** -->
            $("#recordModal").on('submit', '#recordForm', function (event) {
                if ($('#check_in_status').val() === 'N') {
                    event.preventDefault();
                    $('#save').attr('disabled', 'disabled');
                    let formData = $(this).serialize();
                    //alert(formData);
                    $.ajax({
                        url: 'model/manage_customer_event_checkin_process.php',
                        method: "POST",
                        data: formData,
                        success: function (data) {
                            alertify.success(data);
                            $('#recordForm')[0].reset();
                            $('#recordModal').modal('hide');
                            $('#save').attr('disabled', false);
                            dataRecords.ajax.reload(null, false); // Reload data without resetting pagination
                        },
                        error: function (xhr, status, error) {
                            alertify.error('Error: ' + error);
                        }
                    })
                } else {
                    alert("ยืนยันรายการนี้ไปเรียบร้อยแล้ว");
                }
            });
            <!-- *** FOR SUBMIT FORM *** -->
        });
    </script>

    <script>

        $("#TableRecordList").on('click', '.update', function () {
            let id = $(this).attr("id");
            //alert(id);
            let formData = {action: "GET_DATA", id: id};
            $.ajax({
                type: "POST",
                url: 'model/manage_customer_event_checkin_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let cust_id = response[i].cust_id;
                        let ar_name = response[i].ar_name;
                        let cust_name_1 = response[i].cust_name_1;
                        let cust_name_2 = response[i].cust_name_2;
                        let cust_name_3 = response[i].cust_name_3;
                        let cust_name_4 = response[i].cust_name_4;
                        let cust_name_5 = response[i].cust_name_5;
                        let cust_name_6 = response[i].cust_name_6;
                        let cust_level = response[i].cust_level;
                        let phone = response[i].phone;
                        let province_name = response[i].province_name;
                        let check_in_status = response[i].check_in_status;
                        let check_in_status_display = response[i].check_in_status === "Y" ? "Check In แล้ว" : "ยังไม่ได้ Check In";
                        let table_number = response[i].table_number;
                        let register_qty = response[i].register_qty;
                        let attendance_qty = response[i].attendance_qty;
                        let sale_contact_name = response[i].sale_contact_name;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#cust_id').val(cust_id);
                        $('#ar_name').val(ar_name);
                        $('#cust_name_1').val(cust_name_1);
                        $('#cust_name_2').val(cust_name_2);
                        $('#cust_name_3').val(cust_name_3);
                        $('#cust_name_4').val(cust_name_4);
                        $('#cust_name_5').val(cust_name_5);
                        $('#cust_name_6').val(cust_name_6);
                        $('#cust_level').css('color', 'red');
                        $("#cust_level").css("fontSize", "20px");
                        $('#cust_level').val(cust_level);
                        $('#phone').val(phone);
                        $('#province_name').val(province_name);
                        $('#check_in_status').val(check_in_status);
                        if (check_in_status === 'Y') {
                            //document.getElementById("check_in_status_display").style.color = 'green';
                            $('#check_in_status_display').css('color', 'green');
                        } else {
                            //document.getElementById("check_in_status_display").style.color = 'red';
                            $('#check_in_status_display').css('color', 'red');

                        }
                        ;

                        $('#check_in_status_display').val(check_in_status_display);
                        $('#table_number').css('color', 'blue');
                        $("#table_number").css("fontSize", "30px");
                        $('#table_number').val(table_number);
                        $('#register_qty').val(register_qty);
                        $('#attendance_qty').val(attendance_qty);
                        $('#sale_contact_name').val(sale_contact_name);
                        $('.modal-title').html("<i class='fa fa-plus'></i> Check In Record");
                        $('#action').val('CONFIRM');
                        $('#save').val('Confirm');
                    }
                },
                error: function (response) {
                    alertify.error("error : " + response);
                }
            });
        });

    </script>

    <script>

        $("#TableRecordList").on('click', '.detail', function () {
            let id = $(this).attr("id");
            //alert(id);
            let formData = {action: "GET_DATA", id: id};
            $.ajax({
                type: "POST",
                url: 'model/manage_customer_event_checkin_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let cust_id = response[i].cust_id;
                        let ar_name = response[i].ar_name;
                        let cust_name_1 = response[i].cust_name_1;
                        let cust_name_2 = response[i].cust_name_2;
                        let cust_name_3 = response[i].cust_name_3;
                        let cust_name_4 = response[i].cust_name_4;
                        let cust_name_5 = response[i].cust_name_5;
                        let cust_name_6 = response[i].cust_name_6;
                        let cust_level = response[i].cust_level;
                        let phone = response[i].phone;
                        let province_name = response[i].province_name;
                        let check_in_status = response[i].check_in_status;
                        let check_in_status_display = response[i].check_in_status === "Y" ? "Check In แล้ว" : "ยังไม่ได้ Check In";
                        let table_number = response[i].table_number;
                        let attendance_qty = response[i].attendance_qty;
                        let register_qty = response[i].register_qty;
                        let sale_contact_name = response[i].sale_contact_name;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#cust_id').val(cust_id);
                        $('#ar_name').val(ar_name);
                        $('#cust_name_1').val(cust_name_1);
                        $('#cust_name_2').val(cust_name_2);
                        $('#cust_name_3').val(cust_name_3);
                        $('#cust_name_4').val(cust_name_4);
                        $('#cust_name_5').val(cust_name_5);
                        $('#cust_name_6').val(cust_name_6);
                        $('#cust_level').css('color', 'red');
                        $("#cust_level").css("fontSize", "20px");
                        $('#cust_level').val(cust_level);
                        $('#phone').val(phone);
                        $('#province_name').val(province_name);
                        $('#check_in_status').val(check_in_status);
                        if (check_in_status === 'Y') {
                            //document.getElementById("check_in_status_display").style.color = 'green';
                            $('#check_in_status_display').css('color', 'green');
                        } else {
                            //document.getElementById("check_in_status_display").style.color = 'red';
                            $('#check_in_status_display').css('color', 'red');

                        }
                        ;

                        $('#check_in_status_display').val(check_in_status_display);
                        $('#table_number').css('color', 'blue');
                        $("#table_number").css("fontSize", "30px");
                        $('#table_number').val(table_number);
                        $('#attendance_qty').val(attendance_qty);
                        $('#register_qty').val(register_qty);
                        $('#sale_contact_name').val(sale_contact_name);
                        $('.modal-title').html("<i class='fa fa-plus'></i> Detail ");
                        $('#action').val('UPDATE_DETAIL');
                        $('#save').val('Confirm');
                    }
                },
                error: function (response) {
                    alertify.error("error : " + response);
                }
            });
        });

    </script>

    <script>
        $("#recordModal").on('click', '#DetailButton', function (event) {
            event.preventDefault();
            $('#save').attr('disabled', 'disabled');
            let formData = $('#recordForm').serialize();
            //alert(formData);
            $.ajax({
                url: 'model/manage_customer_event_checkin_process.php',
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
    </script>

    <script>
        $("#recordModal").on('click', '#printButton', function (event) {
            event.preventDefault();
            //let idx = $(this).attr("id");
            let id = $('#id').val();
            //alert(id);
            let formData = {action: "GET_DATA", id: id};
            $.ajax({
                type: "POST",
                url: 'model/manage_customer_event_checkin_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let ar_name = response[i].ar_name;
                        let phone = response[i].phone;
                        let table_number = response[i].table_number;
                        let url = "print_slip.php?title=สงวนออโต้คาร์(SAC)"
                            + '&ar_name=' + ar_name + '&phone=' + phone
                            + '&table_number=' + table_number
                            + '&action=PRINT';
                        window.open(url, "_blank");
                    }
                },
                error: function (response) {
                    alertify.error("error : " + response);
                }
            });
        });
    </script>

    <script>
        function ReloadDataTable() {
            //$('#TableRecordList').DataTable().ajax.reload();
        }
    </script>

    </body>

    </html>

<?php } ?>


