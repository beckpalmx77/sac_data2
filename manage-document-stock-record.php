<?php
include('includes/Header.php');

$year = date("Y");
$month = date("m");
$start_date = "01-" . $month . "-" . $year;
$curr_date = date("d-m-Y");

$curr_date = date("d-m-Y");
if (strlen($_SESSION['alogin']) == "") {
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
                        <h1 class="h4 mb-0 text-gray-800"><?php echo urldecode($_GET['s']) ?></h1>
                        <input type="hidden" id="main_menu" value="<?php echo urldecode($_GET['m']) ?>">
                        <input type="hidden" id="sub_menu" value="<?php echo urldecode($_GET['s']) ?>">
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
                                        <form id="export_data" method="post"
                                              action="export_process/export_process_display_data_wh_stock_balance.php"
                                              enctype="multipart/form-data">
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
                                                       value="<?php echo $start_date; ?>">
                                                <label for="name_t" class="control-label mb-0"><b>-</b></label>
                                                <input type="text" class="form-control" id="doc_date_to"
                                                       name="doc_date_to" readonly="true"
                                                       style="width: calc(0.6em * 10 + 1.25rem);"
                                                       value="<?php echo $curr_date; ?>">
                                                <label for="create_by" class="control-label mb-0"><b>เลือกผู้บันทึกข้อมูล</b></label>
                                                <select id="create_by" name="create_by" class="form-control"
                                                        style="width: 100px;">
                                                    <option value="">-</option>
                                                </select>
                                                
                                                <!--label for="create_by" class="control-label mb-0"><b>สถานะ</b></label>
                                                <select id="status" name="status" class="form-control"
                                                        style="width: 100px;">
                                                    <option value="-">-</option>
                                                    <option value="N">N</option>
                                                    <option value="Y">Y</option>
                                                </select-->
                                                <input type="hidden" id="status" name="status" value="-">

                                                <button type="button" name="btnFilter" id="btnFilter"
                                                        class="btn btn-primary btn-xs">FilterData <i
                                                            class="fa fa-filter"></i></button>
                                                <!--button type="button" name="btnExport" id="btnExport"
                                                        class="btn btn-success btn-xs" onclick="ExportData();">Export <i
                                                            class="fa fa-file-excel-o"></i></button-->
                                            </div>
                                        </form>
                                        <div class="col-md-12 col-md-offset-2">
                                            <table id='TableRecordList' class='display dataTable'>
                                                <thead>
                                                <tr>
                                                    <th>เลขที่เอกสาร</th>
                                                    <th>วันที่</th>
                                                    <th>รหัสสินค้า</th>
                                                    <th>จำนวน</th>
                                                    <th>จาก</th>
                                                    <th>ไป</th>
                                                    <th>ผู้บันทึกรายการ</th>
                                                    <th>หมายเหตุ</th>
                                                    <th>สถานะ</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                            </table>
                                            <div id="result"></div>
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

    <script src="js/modal/show_cust_crm_modal.js"></script>

    <!-- Page level plugins -->

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
            $('#doc_date').datepicker({
                format: "yyyy-mm-dd",
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

        $("#doc_id").blur(function () {
            let method = $('#action').val();
            if (method === "ADD") {
                let doc_id = $('#doc_id').val();
                let customer_id = $('#doc_id').val();
                let formData = {action: "SEARCH", doc_id: doc_id, customer_id: customer_id};
                $.ajax({
                    url: 'model/manage_doc_stock_process.php',
                    method: "POST",
                    data: formData,
                    success: function (data) {
                        if (data == 2) {
                            alert("Duplicate มีข้อมูลนี้แล้วในระบบ กรุณาตรวจสอบ");
                        }
                    }
                })
            }
        });

    </script>

    <script>
        $(document).ready(function () {
            // Datepicker configuration
            $('#doc_date_start, #doc_date_to').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });

            // DataTable configuration
            let dataRecords = $('#TableRecordList').DataTable({
                'lengthMenu': [[10, 20, 50, 100], [5, 10, 20, 50, 100]],
                'language': {
                    search: 'ค้นหา',
                    lengthMenu: 'แสดง _MENU_ รายการ',
                    info: 'หน้าที่ _PAGE_ จาก _PAGES_',
                    infoEmpty: 'ไม่มีข้อมูล',
                    zeroRecords: "ไม่มีข้อมูลตามเงื่อนไข",
                    infoFiltered: '(กรองข้อมูลจากทั้งหมด _MAX_ รายการ)',
                    paginate: {previous: 'ก่อนหน้า', last: 'สุดท้าย', next: 'ต่อไป'}
                },
                'processing': true,
                'serverSide': true,
                'autoWidth': true,
                'searching': false,
                'sortable': true,
                <?php if ($_SESSION['deviceType'] !== 'computer') {
                    echo "'scrollX': true,";
                } ?>
                'serverMethod': 'post',
                'ajax': {
                    'url': 'model/manage_doc_stock_process.php',
                    'data': function (d) {
                        // ดึงค่า create_by จาก select box
                        d.doc_date_start = $('#doc_date_start').val();
                        d.doc_date_to = $('#doc_date_to').val();
                        d.create_by = $('#create_by').val();
                        d.status = $('#status').val();
                        d.action = "GET_WH_STOCK";
                        d.sub_action = "GET_MASTER";
                    }
                },
                'columns': [
                    {data: 'doc_id'},
                    {data: 'doc_date'},
                    {data: 'product_id'},
                    {data: 'qty'},
                    {data: 'wh_org'},
                    {data: 'wh_to'},
                    {data: 'create_by'},  // ตรวจสอบว่า column นี้มีในฐานข้อมูลและส่งค่ากลับมาถูกต้อง
                    {data: 'remark'},
                    {data: 'status'},
                    {data: 'update'}
                ]
            });

            // เมื่อกดปุ่มกรอง จะทำการ reload ข้อมูลใน DataTable
            $('#btnFilter').click(function () {
                // ตรวจสอบค่า create_by ก่อนการ reload ข้อมูล
                let doc_date_start = $('#doc_date_start').val();
                let doc_date_to = $('#doc_date_to').val();
                let create_by = $('#create_by').val();
                let status = $('#status').val();
                console.log("Selected create_by: ", create_by);  // ใช้ตรวจสอบว่าได้ค่า create_by หรือไม่
                console.log("Selected status: ", status);  // ใช้ตรวจสอบว่าได้ค่า status หรือไม่
                dataRecords.ajax.reload();  // Reload ข้อมูลใหม่
            });

            // Reload ข้อมูลทุก ๆ 10 วินาที
            //setInterval(function () {
            //dataRecords.ajax.reload(null, false);  // false เพื่อไม่ให้หน้ารีเซ็ต
            //}, 10000);
        });

    </script>

    <script>
        $("#TableRecordList").on('click', '.update', function () {
            let id = $(this).attr("id");
            let main_menu = document.getElementById("main_menu").value;
            let sub_menu = document.getElementById("sub_menu").value;
            let formData = {action: "GET_DATA", id: id};
            $.ajax({
                type: "POST",
                url: 'model/manage_doc_stock_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let seq_record = response[i].seq_record;
                        let doc_id = response[i].doc_id;
                        let doc_date = response[i].doc_date;
                        let line_no = response[i].line_no;
                        let product_id = response[i].product_id;
                        let product_name = response[i].product_name;
                        let qty = response[i].qty;
                        let wh_org = response[i].wh_org;
                        let wh_to = response[i].wh_to;
                        let remark = response[i].remark;
                        let status = response[i].status;
                        let doc_user_id = response[i].doc_user_id;
                        let create_by = response[i].create_by;
                        let url = "manage-wh-stock-data?title=รายการย้ายสินค้า-กำหนดตำแหน่ง (Warehose Location)"
                            + '&id=' + id
                            + '&main_menu=' + main_menu
                            + '&sub_menu=' + sub_menu
                            + '&seq_record=' + seq_record
                            + '&doc_id=' + doc_id
                            + '&doc_date=' + doc_date
                            + '&line_no=' + line_no
                            + '&product_id=' + product_id
                            + '&product_name=' + product_name
                            + '&qty=' + qty
                            + '&wh_org=' + wh_org
                            + '&wh_to=' + wh_to
                            + '&remark=' + remark
                            + '&status=' + status
                            + '&doc_user_id=' + doc_user_id
                            + '&create_by=' + create_by
                            + '&action=UPDATE';

                        let popup = window.open(url, "PopupWindow", "");
                        // ตรวจสอบการปิดหน้าต่าง POPUP
                        let checkPopupClosed = setInterval(function () {
                            if (popup.closed) {
                                clearInterval(checkPopupClosed);
                                // รีโหลด DataTable เมื่อหน้าต่าง POPUP ถูกปิด
                                //ReloadDataTable();
                                $('#TableRecordList').DataTable().ajax.reload(null, false); // false เพื่อไม่ให้กลับไปหน้าแรก
                            }
                        }, 1000);
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
                url: 'model/get_record_stock_create_by_data.php', // หน้า PHP ที่จะดึงข้อมูล
                method: 'GET',
                dataType: 'json',
                success: function (data) {
                    console.log(data);  // ตรวจสอบข้อมูลที่ได้รับจาก PHP

                    let select = $('#create_by');

                    if (Array.isArray(data) && data.length > 0) {
                        $.each(data, function (index, create_user) {
                            select.append($('<option>', {
                                value: create_user.create_by,
                                text: create_user.create_by, // เปลี่ยนเป็นชื่อของข้อมูลที่คุณต้องการแสดง
                                'data-name': create_user.create_by // เก็บข้อมูลชื่อใน attribute เพื่อใช้ภายหลัง
                            }));
                        });

                        // แปลง select เป็น select2 หลังจากข้อมูลถูกเพิ่ม
                        $('#create_by').select2({
                            placeholder: "-",
                            allowClear: true,
                            width: '100%' // กำหนดขนาดให้เต็ม 100% เพื่อให้ตรงกับ element อื่น
                        });
                    } else {
                        console.error('ไม่มีข้อมูลหรือข้อมูลไม่ถูกต้อง');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('เกิดข้อผิดพลาดในการดึงข้อมูล:', error);
                }
            });
        });
    </script>

    <script>
        function ReloadDataTable() {
            $('#TableRecordList').DataTable().ajax.reload();
        }
    </script>

    <script>
        // Export Data function
        function ExportData() {
            const form = document.getElementById("export_data");
            if (form.checkValidity()) {
                form.submit();
            } else {
                alert("Please fill out the required fields.");
            }
        }
    </script>

    </body>
    </html>

<?php } ?>


