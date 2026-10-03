<?php

include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    $curr_date = date("d-m-Y");
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
                        <h1 class="h4 mb-0 text-gray-800"><?php echo isset($_GET['s']) ? urldecode($_GET['s']) : 'รายการรับ cheque (ผ่านเช็ค)' ?></h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a>
                            </li>
                            <li class="breadcrumb-item"><?php echo isset($_GET['m']) ? urldecode($_GET['m']) : 'Cheque' ?></li>
                            <li class="breadcrumb-item active"
                                aria-current="page"><?php echo isset($_GET['s']) ? urldecode($_GET['s']) : 'รายการรับ cheque' ?></li>
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
                                              action="export_process/payment_pass_cheque_data.php"
                                              enctype="multipart/form-data">
                                            <div class="col-md-12 col-md-offset-2"
                                                 style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                                <label for="name_t"
                                                       class="control-label"><b>ข้อมูล <?php echo isset($_GET['s']) ? urldecode($_GET['s']) : 'รายการรับ cheque' ?></b></label>

                                                <button type="button" name="btnRefresh" id="btnRefresh"
                                                        class="btn btn-success btn-xs" onclick="ReloadDataTable();">
                                                    Refresh <i class="fa fa-refresh"></i>
                                                </button>

                                                <label for="doc_date_start" class="control-label mb-0"><b>วันที่&nbsp;</b></label>

                                                <input type="text" class="form-control" id="doc_date_start"
                                                       name="doc_date_start"
                                                       readonly="true" placeholder="เริ่มต้น"
                                                       style="width: calc(0.6em * 10 + 1.25rem);"
                                                       value="">
                                                <label for="doc_date_to" class="control-label mb-0"><b>-</b></label>
                                                <input type="text" class="form-control" id="doc_date_to"
                                                       name="doc_date_to"
                                                       readonly="true" placeholder="สิ้นสุด"
                                                       style="width: calc(0.6em * 10 + 1.25rem);"
                                                       value="">

                                                <button type="button" name="btnFilter" id="btnFilter"
                                                        class="btn btn-primary btn-xs">
                                                    Filter <i class="fa fa-filter"></i>
                                                </button>

                                                <button type="button" name="btnExport" id="btnExport"
                                                        class="btn btn-success btn-xs" onclick="ExportData();">
                                                    Export <i class="fa fa-file-excel-o"></i>
                                                </button>
                                            </div>
                                        </form>

                                        <br>
                                        <div class="col-md-12 col-md-offset-2">
                                            <table id='TableRecordList' class='display dataTable' style="width:100%">
                                                <thead>
                                                <tr>
                                                    <th>วันที่เช็ค</th>
                                                    <th>เลขที่เอกสาร</th>
                                                    <th>รหัสธนาคาร</th>
                                                    <th>ชื่อบัญชีธนาคาร</th>
                                                    <th>เลขที่เช็ค</th>
                                                    <th>สั่งจ่ายโดย</th>
                                                    <th>ยอดเงิน</th>
                                                    <th>ค่าธรรมเนียม</th>
                                                    <th>ยอดสุทธิ</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                                <tfoot>
                                                <tr>
                                                    <th>วันที่เช็ค</th>
                                                    <th>เลขที่เอกสาร</th>
                                                    <th>รหัสธนาคาร</th>
                                                    <th>ชื่อบัญชีธนาคาร</th>
                                                    <th>เลขที่เช็ค</th>
                                                    <th>สั่งจ่ายโดย</th>
                                                    <th>ยอดเงิน</th>
                                                    <th>ค่าธรรมเนียม</th>
                                                    <th>ยอดสุทธิ</th>
                                                    <th>Action</th>
                                                </tr>
                                                </tfoot>
                                            </table>

                                            <div id="result"></div>

                                        </div>

                                        <!-- Modal แสดงรายละเอียดข้อมูลเช็ค -->
                                        <div class="modal fade" id="recordModal">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title"><i class="fa fa-info-circle"></i> รายละเอียดข้อมูลเช็ค</h4>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <form method="post" id="recordForm">
                                                        <div class="modal-body">
                                                            <div class="container-fluid">
                                                                <!-- กลุ่มฟอร์มที่ 1 -->
                                                                <div class="row">
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="DI_DATE" class="control-label">วันที่เอกสาร</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="DI_DATE" name="DI_DATE" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="CQIN_CHEQUE_DD" class="control-label">วันที่เช็ค</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="CQIN_CHEQUE_DD" name="CQIN_CHEQUE_DD" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="DI_REF" class="control-label">เลขที่เอกสาร</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="DI_REF" name="DI_REF" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="DI_ACTIVE" class="control-label">สถานะเอกสาร</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="DI_ACTIVE" name="DI_ACTIVE" readonly>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <!-- กลุ่มฟอร์มที่ 2 -->
                                                                <div class="row">
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="BANK_INITIAL" class="control-label">รหัสธนาคาร</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="BANK_INITIAL" name="BANK_INITIAL" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="BANK_T_NAME" class="control-label">ชื่อธนาคาร</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="BANK_T_NAME" name="BANK_T_NAME" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="BNKAC_CODE" class="control-label">รหัสบัญชีธนาคาร</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="BNKAC_CODE" name="BNKAC_CODE" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="BNKAC_NAME" class="control-label">ชื่อบัญชีธนาคาร</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="BNKAC_NAME" name="BNKAC_NAME" readonly>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <!-- กลุ่มฟอร์มที่ 3 -->
                                                                <div class="row">
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="CQIN_CHEQUE_NO" class="control-label">เลขที่เช็ค</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="CQIN_CHEQUE_NO" name="CQIN_CHEQUE_NO" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="CQIN_OWNER" class="control-label">สั่งจ่ายโดย</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="CQIN_OWNER" name="CQIN_OWNER" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <div class="form-group">
                                                                            <label for="CQIN_AMT" class="control-label">ยอดเงินเช็ค</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="CQIN_AMT" name="CQIN_AMT" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <div class="form-group">
                                                                            <label for="CQIN_FEE_A" class="control-label">ค่าธรรมเนียม</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="CQIN_FEE_A" name="CQIN_FEE_A" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-2">
                                                                        <div class="form-group">
                                                                            <label for="CQIN_NET" class="control-label">ยอดสุทธิ</label>
                                                                            <input type="text" class="form-control font-weight-bold"
                                                                                   id="CQIN_NET" name="CQIN_NET" readonly>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <!-- กลุ่มฟอร์มที่ 4 -->
                                                                <div class="row">
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="BR_CODE" class="control-label">รหัสสาขา</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="BR_CODE" name="BR_CODE" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="DEPT_CODE" class="control-label">รหัสแผนก</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="DEPT_CODE" name="DEPT_CODE" readonly>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="PRJ_CODE" class="control-label">รหัสโครงการ</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="PRJ_CODE" name="PRJ_CODE" readonly>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary"
                                                                    data-dismiss="modal">Close <i
                                                                        class="fa fa-times"></i></button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
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

        .form-control {
            height: calc(1.5em + 0.75rem + 2px);
        }

        .select2-container .select2-selection--single {
            height: calc(1.5em + 0.75rem + 2px) !important;
            padding: 0.375rem 0.75rem;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: calc(1.5em + 0.75rem + 2px) !important;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: calc(1.5em + 0.75rem + 2px) !important;
        }

        #TableRecordList th, #TableRecordList td {
            white-space: nowrap;
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
        let dataRecords;
        $(document).ready(function () {
            dataRecords = $('#TableRecordList').DataTable({
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
                'order': [[0, 'desc']],
                'processing': true,
                'serverSide': true,
                'autoWidth': true,
                'searching': true,
                'scrollX': true,
                'serverMethod': 'post',
                'ajax': {
                    'url': 'model/manage_pass_cheque_data_process.php',
                    'data': function (d) {
                        d.action = "GET_PASS_CHEQUE_DATA";
                        d.doc_date_start = $('#doc_date_start').val();
                        d.doc_date_to = $('#doc_date_to').val();
                    }
                },
                'columns': [
                    {data: 'CQIN_CHEQUE_DD'},
                    {data: 'DI_REF'},
                    {data: 'BANK_INITIAL'},
                    {data: 'BNKAC_NAME'},
                    {data: 'CQIN_CHEQUE_NO'},
                    {data: 'CQIN_OWNER'},
                    {data: 'CQIN_AMT', className: 'text-right'},
                    {data: 'CQIN_FEE_A', className: 'text-right'},
                    {data: 'CQIN_NET', className: 'text-right font-weight-bold'},
                    {data: 'update', className: 'text-center'}
                ]
            });

            $('#btnFilter').click(function () {
                dataRecords.ajax.reload();
            });
        });
    </script>

    <script>
        $("#TableRecordList").on('click', '.info', function () {
            let id = $(this).attr("id");
            let formData = {action: "GET_DATA", id: id};
            $.ajax({
                type: "POST",
                url: 'model/manage_pass_cheque_data_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    if (response && response.length > 0) {
                        let data = response[0];
                        $('#DI_DATE').val(data.DI_DATE);
                        $('#CQIN_CHEQUE_DD').val(data.CQIN_CHEQUE_DD);
                        $('#DI_REF').val(data.DI_REF);
                        $('#DI_ACTIVE').val(data.DI_ACTIVE);
                        $('#BANK_INITIAL').val(data.BANK_INITIAL);
                        $('#BANK_T_NAME').val(data.BANK_T_NAME);
                        $('#BNKAC_CODE').val(data.BNKAC_CODE);
                        $('#BNKAC_NAME').val(data.BNKAC_NAME);
                        $('#CQIN_CHEQUE_NO').val(data.CQIN_CHEQUE_NO);
                        $('#CQIN_OWNER').val(data.CQIN_OWNER);
                        $('#CQIN_AMT').val(data.CQIN_AMT);
                        $('#CQIN_FEE_A').val(data.CQIN_FEE_A);
                        $('#CQIN_NET').val(data.CQIN_NET);
                        $('#BR_CODE').val(data.BR_CODE);
                        $('#DEPT_CODE').val(data.DEPT_CODE);
                        $('#PRJ_CODE').val(data.PRJ_CODE);

                        $('#recordModal').modal('show');
                    }
                },
                error: function (response) {
                    if (typeof alertify !== 'undefined') {
                        alertify.error("เกิดข้อผิดพลาดในการดึงข้อมูล");
                    } else {
                        alert("เกิดข้อผิดพลาดในการดึงข้อมูล");
                    }
                }
            });
        });
    </script>

    <script>
        function ReloadDataTable() {
            $('#doc_date_start').val('');
            $('#doc_date_to').val('');
            if (typeof dataRecords !== 'undefined') {
                dataRecords.ajax.reload(null, false);
            } else {
                $('#TableRecordList').DataTable().ajax.reload(null, false);
            }
        }
    </script>

    <script>
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
