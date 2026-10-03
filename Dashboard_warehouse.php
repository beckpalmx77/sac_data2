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
            font-size: 50px; /* ปรับขนาดตัวอักษรตามที่คุณต้องการ */
        }

        .medium-text {
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
    <style>
        @media print {
            #printArea {
                display: block; /* Show print area */
                width: 4in;
                height: 6in;
                /* width: 100%;
                height: 100%; */
                margin: 0;
                padding: 0;
                font-size: 16pt; /* Adjust font size for print */
                justify-content: center;
                align-items: center;
                text-align: center;
                color: black;
            }

            #printArea p {
                margin: 0.5in 0; /* Adjust spacing between paragraphs */
            }

            body * {
                visibility: hidden; /* Hide everything except the print area */
            }

            #printArea,
            #printArea * {
                visibility: visible;
            }

            #printArea {
                position: absolute;
                left: 0;
                top: 0;
            }

            #recordForm {
                display: none; /* Hide the form during print */
            }
        }
    </style>

    <div id="wrapper">
        <?php
        include('includes/Side-Bar.php');
        ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php
                include('includes/Top-Bar.php');
                ?>
                <div class="container-fluid" id="container-wrapper">
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-12">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                </div>
                                <div class="card-body">
                                    <section class="container-fluid">
                                        <div class="medium-text font-weight-bold text-uppercase mb-1 "
                                             style="color: #8F35F6;">
                                            รายการจองยางประจำวัน
                                            <button type='button' name='btnRefresh' id='btnRefresh'
                                                    class='btn btn-success btn-xs' onclick="ReloadDataTable();">Refresh
                                                <i class="fa fa-refresh"></i>
                                            </button>
                                        </div>

                                        <!-- Export Excel Card -->
                                        <div class="card mb-4 mt-3 border-left-primary">
                                            <div class="card-body">
                                                <form id="exportForm" method="post" action="export_process/export_data_reserve_product_process.php" target="_blank">
                                                    <div class="row align-items-end">
                                                        <div class="col-md-3 mb-2 text-left">
                                                            <label for="doc_date_start" class="font-weight-bold text-dark">จากวันที่</label>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control" id="doc_date_start" name="doc_date_start" required="required" readonly="true" placeholder="จากวันที่">
                                                                <div class="input-group-append">
                                                                    <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3 mb-2 text-left">
                                                            <label for="doc_date_to" class="font-weight-bold text-dark">ถึงวันที่</label>
                                                            <div class="input-group">
                                                                <input type="text" class="form-control" id="doc_date_to" name="doc_date_to" required="required" readonly="true" placeholder="ถึงวันที่">
                                                                <div class="input-group-append">
                                                                    <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-3 mb-2 text-left">
                                                            <label for="sku_code" class="font-weight-bold text-dark">ข้อมูลที่ต้องการ</label>
                                                            <input type="text" class="form-control" id="sku_code" name="sku_code" value="ยาง , กระทะล้อ , ล้อแม็กซ์" readonly="true">
                                                        </div>
                                                        <div class="col-md-3 mb-2">
                                                            <button type="submit" class="btn btn-success btn-block" id="btnExport">
                                                                <i class="fa fa-file-excel"></i> Export Excel
                                                            </button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>

                                        <div class="col-md-12 col-md-offset-2">
                                            <table id='TableRecordList' class='display dataTable'>
                                                <thead>
                                                <tr>
                                                    <th>วันที่</th>
                                                    <th>รหัสสินค้า</th>
                                                    <th>รายละเอียดสินค้า</th>
                                                    <th>จำนวน</th>
                                                    <th>คลัง/ปี</th>
                                                    <th>เลขที่เอกสาร</th>
                                                    <th>เทค/sale</th>
                                                    <th>ชื่อลูกค้า</th>
                                                </tr>
                                                </thead>
                                                <tfoot>
                                                <tr>
                                                    <th>วันที่</th>
                                                    <th>รหัสสินค้า</th>
                                                    <th>รายละเอียดสินค้า</th>
                                                    <th>จำนวน</th>
                                                    <th>คลัง/ปี</th>
                                                    <th>เลขที่เอกสาร</th>
                                                    <th>เทค/sale</th>
                                                    <th>ชื่อลูกค้า</th>
                                                </tr>
                                                </tfoot>
                                            </table>

                                            <div id="result"></div>

                                        </div>

                                </div>
                            </div>
                        </div>
                    </div>    <?php
    include('includes/Footer.php');
    ?>

                </div>
            </div>
        </div>
    </div>

    <?php
    include('includes/Modal-Logout.php');
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

    <script src="js/MyFrameWork/framework_util.js"></script>
    <script src="js/util.js"></script>

    <script>
        $(document).ready(function () {
            $(".icon-input-btn").each(function () {
                let btnFont = $(this).find(".btn").css("font-size");
                let btnColor = $(this).find(".btn").css("color");
                $(this).find(".fa").css({'font-size': btnFont, 'color': btnColor});
            });

            // Initialize dates
            let today = new Date();
            let doc_date = getDay2Digits(today) + "-" + getMonth2Digits(today) + "-" + today.getFullYear();
            $('#doc_date_start').val(doc_date);
            $('#doc_date_to').val(doc_date);

            // Initialize Datepickers
            $('#doc_date_start').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });
            $('#doc_date_to').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            setInterval(function () {
                ReloadDataTable();
            }, 100000);
        });
    </script>

    <script>
        $(document).ready(function () {
            
            let formData = {action: "GET_RESERVE_PRODUCT", sub_action: "GET_MASTER", screen_action: "DASHBOARD"};
            let dataRecords = $('#TableRecordList').DataTable({
                'lengthMenu': [[5, 10, 20, 50, 100], [5, 10, 20, 50, 100]],
                'pageLength': 5,
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
                    'url': 'model/manage_reserve_sac_process.php',
                    'data': formData,
                },
                'columns': [
                    {data: 'DI_DATE'},
                    {data: 'SKU_CODE'},
                    {data: 'SKU_NAME'},
                    {data: 'TRD_QTY'},
                    {data: 'WL_CODE'},
                    {data: 'DI_REF'},
                    {data: 'SLMN_NAME'},
                    {data: 'AR_NAME'}
                ]
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


