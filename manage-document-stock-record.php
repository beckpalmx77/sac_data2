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
            let formData = {action: "GET_WH_STOCK", sub_action: "GET_MASTER"};
            let dataRecords = $('#TableRecordList').DataTable({
                'columnDefs': [{"orderSequence": ["desc", "asc"]}],
                'lengthMenu': [[10, 20, 50, 100], [10, 20, 50, 100]],
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
                    'url': 'model/manage_doc_stock_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'doc_id'},
                    {data: 'doc_date'},
                    {data: 'product_id'},
                    {data: 'qty'},
                    {data: 'wh_org'},
                    {data: 'wh_to'},
                    {data: 'create_by'},
                    {data: 'remark'},
                    {data: 'status'},
                    {data: 'update'}
                ]
            });

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
                                ReloadDataTable();
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
        function ReloadDataTable() {
            $('#TableRecordList').DataTable().ajax.reload();
        }
    </script>

    </body>
    </html>

<?php } ?>

