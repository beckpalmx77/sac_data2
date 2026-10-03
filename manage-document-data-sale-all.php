<?php

include('includes/Header.php');
$curr_date = date("d-m-Y");

if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    ?>

    <!DOCTYPE html>

    <html lang="th">
    <body>

    <style>
        body, h1, h2, h3, h4, h5, h6 {
            font-family: 'Prompt', sans-serif !important;
        }
    </style>
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
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h4 mb-0 text-gray-800"><?php echo urldecode($_GET['s']) ?></h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page']?>">Home</a></li>
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
                                        <!--div class="col-md-12 col-md-offset-2"
                                             style="display: flex; align-items: center; gap: 10px;">
                                            <label for="name_t" class="control-label mb-0"><b>เลือกตามวันที่</b></label>
                                            <input type="text" class="form-control" id="doc_date_start" name="doc_date_start"
                                                   readonly="true" placeholder=""
                                                   style="width: calc(0.6em * 10 + 1.25rem);"
                                                   value="<?php echo $curr_date; ?>">
                                            <label for="name_t" class="control-label mb-0"><b>-</b></label>

                                            <input type="text" class="form-control" id="doc_date_to" name="doc_date_to"
                                                   readonly="true" placeholder=""
                                                   style="width: calc(0.6em * 10 + 1.25rem);"
                                                   value="<?php echo $curr_date; ?>">
                                        </div>
                                        <br-->
                                        <div class="col-md-12 col-md-offset-2">
                                            <table id='TableRecordList' class='display dataTable'>
                                                <thead>
                                                <tr>
                                                    <th>เลขที่เอกสาร</th>
                                                    <th>วันที่เอกสาร</th>
                                                    <th>รหัสสินค้า</th>
                                                    <th>ชื่อลูกค้า</th>
                                                    <th>จำนวน</th>
                                                    <th>จำนวนของแถม</th>
                                                    <th>จำนวนเงิน</th>
                                                    <th>Sale</th>
                                                    <th>Take</th>
                                                </tr>
                                                </thead>
                                                <tfoot>
                                                <tr>
                                                    <th>เลขที่เอกสาร</th>
                                                    <th>วันที่เอกสาร</th>
                                                    <th>รหัสสินค้า</th>
                                                    <th>ชื่อลูกค้า</th>
                                                    <th>จำนวน</th>
                                                    <th>จำนวนของแถม</th>
                                                    <th>จำนวนเงิน</th>
                                                    <th>Sale</th>
                                                    <th>Take</th>
                                                </tr>
                                                </tfoot>
                                            </table>

                                            <div id="result"></div>

                                        </div>

                                </div>
                            <!--/div-->
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


    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>


    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>

    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

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
            let today = new Date();
            let doc_date = getDay2Digits(today) + "-" + getMonth2Digits(today) + "-" + today.getFullYear();
            $('#doc_date_start').val(doc_date);
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
            let today = new Date();
            let doc_date = getDay2Digits(today) + "-" + getMonth2Digits(today) + "-" + today.getFullYear();
            $('#doc_date_to').val(doc_date);
            //document.getElementById('doc_date').value = doc_date;
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

            let cnt_date = $('#cnt_date').val();
            let formData = {action: "GET_DATA_SALE_ALL", sub_action: "GET_MASTER" , cnt_date: cnt_date};
            let dataRecords = $('#TableRecordList').DataTable({
                'lengthMenu': [[10, 20, 50, 100, 500, 1000], [10, 20, 50, 100, 500, 1000]],
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
                'searching': true,
                'searchDelay': 500,
                'order': [[0, 'desc']],
                'ajax': {
                    'url': 'model/manage_document_data-sale-all_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'DI_REF', title: 'เลขที่เอกสาร'},
                    {data: 'DI_DATE', title: 'วันที่เอกสาร'},
                    {data: 'SKU_CODE', title: 'รหัสสินค้า'},
                    {data: 'AR_NAME', title: 'ชื่อลูกค้า'},
                    {data: 'TRD_QTY', title: 'จำนวน', className: 'text-right'},
                    {data: 'TRD_Q_FREE', title: 'จำนวนของแถม', className: 'text-right'},
                    {
                        data: 'TRD_AMOUNT_PRICE',
                        title: 'จำนวนเงิน',
                        className: 'text-right',
                        render: function (data, type, row) {
                            if (type === 'display' || type === 'filter') {
                                var number = parseFloat(data);
                                if (isNaN(number)) {
                                    return data;
                                }
                                return new Intl.NumberFormat('en-US', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                }).format(number);
                            }
                            return data;
                        }
                    },
                    {data: 'SALE_NAME', title: 'Sale'},
                    {data: 'TAKE_NAME', title: 'Take'}
                ]
            });

            // Debounce search input to prevent hammering MySQL on every keystroke
            let searchTimeout = null;
            $('#TableRecordList_filter input').unbind().bind('input', function(e) {
                let self = this;
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    dataRecords.search(self.value).draw();
                }, 500);
            });
        });
    </script>

    </body>
    </html>

<?php } ?>