<?php

include('includes/Header.php');

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
                                        <form id="export_data" method="post"
                                              action="export_process/export_process_data_wh_stock_balance.php"
                                              enctype="multipart/form-data">
                                            <div class="col-md-12 col-md-offset-2"
                                                 style="display: flex; align-items: center; gap: 10px;">
                                                <button type="button" name="btnRefresh" id="btnRefresh"
                                                        class="btn btn-success btn-xs" onclick="ReloadDataTable();">
                                                    Refresh <i class="fa fa-refresh"></i>
                                                </button>
                                                <button type="button" name="btnExport" id="btnExport"
                                                        class="btn btn-success btn-xs" onclick="ExportData();">
                                                    Export <i class="fa fa-file-excel-o"></i>
                                                </button>
                                            </div>
                                        </form>

                                        <br>
                                        <div class="col-md-12 col-md-offset-2">
                                            <table id='TableRecordList' class='display dataTable'>
                                                <thead>
                                                <tr>
                                                    <th>รหัสสินค้า</th>
                                                    <th>คลังปี</th>
                                                    <th>สัปดาห์</th>
                                                    <th>ตำแหน่ง</th>
                                                    <th>จำนวน</th>
                                                </tr>
                                                </thead>
                                                <tfoot>
                                                <tr>
                                                    <th>รหัสสินค้า</th>
                                                    <th>คลังปี</th>
                                                    <th>สัปดาห์</th>
                                                    <th>ตำแหน่ง</th>
                                                    <th>จำนวน</th>
                                                </tr>
                                                </tfoot>
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
            let today = new Date();
            let doc_date = getDay2Digits(today) + "-" + getMonth2Digits(today) + "-" + today.getFullYear();
            $('#doc_date').val(doc_date);
            //document.getElementById('doc_date').value = doc_date;
        });
    </script>

    <script>
        $(document).ready(function () {
            $('#doc_date_start').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            $('#doc_date_to').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });
        });
    </script>

    <script>
        document.getElementById('qty').addEventListener('input', function (e) {
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    </script>

    <script>
        $(document).ready(function () {
            let formData = {action: "GET_STOCK_BALANCE", sub_action: "GET_MASTER"};
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
                    'url': 'model/manage_stock_balance_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'product_id'},
                    {data: 'wh'},
                    {data: 'wh_week_id'},
                    {data: 'location'},
                    {data: 'qty'},
                ]
            });

            // ตั้งค่าให้รีเฟรช DataTable ทุกๆ 10 วินาที
            setInterval(function () {
                dataRecords.ajax.reload(null, false); // false เพื่อรักษาหน้าปัจจุบัน
            }, 10000); // 10000 มิลลิวินาที (10 วินาที)

        });
    </script>

    <script>
        function ReloadDataTable() {
            $('#TableRecordList').DataTable().ajax.reload();
        }
    </script>

    <script>
        // ฟังก์ชันสำหรับทำการ submit ฟอร์ม
        function ExportData() {
            // ดึงฟอร์มจาก ID ที่กำหนด
            const form = document.getElementById("export_data");

            // ตรวจสอบว่า input ที่จำเป็นถูกกรอกครบหรือไม่
            if (form.checkValidity()) {
                // ทำการ submit ฟอร์ม
                form.submit();
            } else {
                alert("Please fill out the required fields.");
            }
        }
    </script>

    </body>
    </html>

<?php } ?>