<?php

include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    $curr_date = date("d-m-Y");
    $create_by = $_SESSION['user_id'];
    $doc_user_id = $_SESSION['doc_user_id'];
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
                                              action="export_process/export_process_data_product_sale_sac.php"
                                              enctype="multipart/form-data">
                                            <div class="col-md-12 col-md-offset-2"
                                                 style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                                <label for="name_t"
                                                       class="control-label"><b>ข้อมูล <?php echo urldecode($_GET['s']) ?></b></label>

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

                                                <button type="button" name="btnAnalyze" id="btnAnalyze"
                                                        class="btn btn-dark btn-xs" onclick="ShowExplainAnalyze();" title="วิเคราะห์ประสิทธิภาพ Query ด้วย EXPLAIN ANALYZE">
                                                    Explain Analyze <i class="fa fa-tachometer"></i>
                                                </button>

                                                <span id="query_perf_badge" class="badge badge-info" 
                                                      style="padding: 5px 10px; font-size: 0.8rem; font-weight: 600; display: none;">
                                                    <i class="fa fa-bolt"></i> <span id="perf_time">0</span> ms
                                                </span>
                                            </div>
                                        </form>

                                        <br>
                                        <div class="col-md-12 col-md-offset-2">
                                            <table id='TableRecordList' class='display dataTable' style="width:100%">
                                                <thead>
                                                <tr>
                                                    <th>วันที่</th>
                                                    <th>เวลา</th>
                                                    <th>เลขที่เอกสาร</th>
                                                    <th>รหัสลูกค้า</th>
                                                    <th>ชื่อลูกค้า</th>
                                                    <th>พนักงานขาย</th>
                                                    <th>รหัสสินค้า</th>
                                                    <th>รายละเอียดสินค้า</th>
                                                    <th>จำนวน</th>
                                                    <th>ราคา/หน่วย</th>
                                                    <th>จำนวนเงิน</th>
                                                    <th>มูลค่าสินค้า</th>
                                                    <th>ภาษีมูลค่าเพิ่ม</th>
                                                    <th>สาขา</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                                <tfoot>
                                                <tr>
                                                    <th>วันที่</th>
                                                    <th>เวลา</th>
                                                    <th>เลขที่เอกสาร</th>
                                                    <th>รหัสลูกค้า</th>
                                                    <th>ชื่อลูกค้า</th>
                                                    <th>พนักงานขาย</th>
                                                    <th>รหัสสินค้า</th>
                                                    <th>รายละเอียดสินค้า</th>
                                                    <th>จำนวน</th>
                                                    <th>ราคา/หน่วย</th>
                                                    <th>จำนวนเงิน</th>
                                                    <th>มูลค่าสินค้า</th>
                                                    <th>ภาษีมูลค่าเพิ่ม</th>
                                                    <th>สาขา</th>
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
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                    </div>
                                                    <form method="post" id="recordForm">
                                                        <div class="modal-body">
                                                            <div class="container-fluid">
                                                                <!-- กลุ่มฟอร์มที่ 1 -->
                                                                <div class="row">
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="DI_DATE" class="control-label">วันที่</label>
                                                                            <div class="input-group">
                                                                                <input type="text" class="form-control"
                                                                                       id="DI_DATE" name="DI_DATE"
                                                                                       readonly="true"
                                                                                       value="<?php echo $curr_date; ?>">
                                                                                <div class="input-group-append">
                                                                                    <span class="input-group-text"><i
                                                                                                class="glyphicon glyphicon-th"></i></span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="DI_TIME_CHK" class="control-label">เวลา</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="DI_TIME_CHK" name="DI_TIME_CHK" placeholder="เวลา" readonly="true">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="DI_REF"
                                                                                   class="control-label">เลขที่เอกสาร</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="DI_REF" name="DI_REF" placeholder="เลขที่เอกสาร"
                                                                                   required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="BRANCH"
                                                                                   class="control-label">สาขา</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="BRANCH" name="BRANCH" placeholder="สาขา">
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <!-- กลุ่มฟอร์มที่ 2 -->
                                                                <div class="row">
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="AR_CODE"
                                                                                   class="control-label">รหัสลูกค้า</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="AR_CODE" name="AR_CODE" placeholder="รหัสลูกค้า">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="AR_NAME"
                                                                                   class="control-label">ชื่อลูกค้า</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="AR_NAME" name="AR_NAME" placeholder="ชื่อลูกค้า">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="SLMN_NAME"
                                                                                   class="control-label">พนักงานขาย</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="SLMN_NAME" name="SLMN_NAME" placeholder="พนักงานขาย">
                                                                            <input type="hidden" id="SLMN_CODE" name="SLMN_CODE">
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <!-- กลุ่มฟอร์มที่ 3 -->
                                                                <div class="row">
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="SKU_CODE"
                                                                                   class="control-label">รหัสสินค้า</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="SKU_CODE" name="SKU_CODE" placeholder="รหัสสินค้า"
                                                                                   required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-5">
                                                                        <div class="form-group">
                                                                            <label for="SKU_NAME"
                                                                                   class="control-label">รายละเอียดสินค้า</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="SKU_NAME" name="SKU_NAME" placeholder="รายละเอียดสินค้า">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-3">
                                                                        <div class="form-group">
                                                                            <label for="SKU_CAT"
                                                                                   class="control-label">หมวดสินค้า</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="SKU_CAT" name="SKU_CAT" placeholder="หมวดสินค้า">
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <!-- กลุ่มฟอร์มที่ 4 -->
                                                                <div class="row">
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="TRD_QTY"
                                                                                   class="control-label">จำนวน</label>
                                                                            <input type="number" step="0.01" class="form-control"
                                                                                   id="TRD_QTY" name="TRD_QTY" placeholder="0.00"
                                                                                   required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="TRD_U_PRC"
                                                                                   class="control-label">ราคา/หน่วย</label>
                                                                            <input type="number" step="0.01" class="form-control"
                                                                                   id="TRD_U_PRC" name="TRD_U_PRC" placeholder="0.00">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="TRD_G_KEYIN"
                                                                                   class="control-label">จำนวนเงิน</label>
                                                                            <input type="number" step="0.01" class="form-control"
                                                                                   id="TRD_G_KEYIN" name="TRD_G_KEYIN" placeholder="0.00">
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <!-- กลุ่มฟอร์มที่ 5 -->
                                                                <div class="row">
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="TRD_B_SELL"
                                                                                   class="control-label">มูลค่าสินค้า</label>
                                                                            <input type="number" step="0.01" class="form-control"
                                                                                   id="TRD_B_SELL" name="TRD_B_SELL" placeholder="0.00">
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-6">
                                                                        <div class="form-group">
                                                                            <label for="TRD_B_VAT"
                                                                                   class="control-label">ภาษีมูลค่าเพิ่ม</label>
                                                                            <input type="number" step="0.01" class="form-control"
                                                                                   id="TRD_B_VAT" name="TRD_B_VAT" placeholder="0.00">
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <input type="hidden" name="id" id="id"/>
                                                            <input type="hidden" name="action" id="action" value=""/>
                                                            <input type="hidden" name="create_by" id="create_by" value="<?php echo $create_by; ?>"/>
                                                            <button type="submit" name="save" id="save"
                                                                    class="btn btn-primary"><i class="fa fa-check"></i>
                                                                Save
                                                            </button>
                                                            <button type="button" class="btn btn-danger"
                                                                    data-dismiss="modal">Close <i
                                                                        class="fa fa-times"></i></button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Modal Explain Analyze Profiler -->
                                        <div class="modal fade" id="explainModal" tabindex="-1" role="dialog" aria-hidden="true">
                                            <div class="modal-dialog modal-xl">
                                                <div class="modal-content">
                                                    <div class="modal-header bg-dark text-white">
                                                        <h5 class="modal-title"><i class="fa fa-tachometer"></i> MySQL EXPLAIN ANALYZE - Query Profiler</h5>
                                                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="alert alert-info py-2" id="explain_summary">
                                                            <i class="fa fa-info-circle"></i> <b>เวลาประมวลผลจริง (Actual Execution Time):</b> <span id="explain_exec_time" class="badge badge-success" style="font-size: 0.9rem;">-</span> ms
                                                        </div>
                                                        <h6><b>คำสั่ง SQL ที่วิเคราะห์:</b></h6>
                                                        <pre id="explain_sql" class="bg-dark text-warning p-3 rounded" style="font-size: 0.82rem; max-height: 130px; overflow-y: auto; white-space: pre-wrap;"></pre>
                                                        
                                                        <h6 class="mt-3"><b>ผลการวิเคราะห์ Execution Plan (Tree Structure):</b></h6>
                                                        <pre id="explain_tree" class="bg-light text-dark p-3 border rounded font-monospace" style="font-size: 0.85rem; font-family: Consolas, monospace; white-space: pre-wrap; max-height: 380px; overflow-y: auto;"></pre>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">Close</button>
                                                    </div>
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
            let today = new Date();
            let doc_date = getDay2Digits(today) + "-" + getMonth2Digits(today) + "-" + today.getFullYear();
            $('#DI_DATE').val(doc_date);
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

            $('#DI_DATE').datepicker({
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
                'order': [],
                'processing': true,
                'serverSide': true,
                'autoWidth': true,
                'searching': true,
                'scrollX': true,
                'serverMethod': 'post',
                'ajax': {
                    'url': 'model/manage_product_sale_sac_process.php',
                    'data': function (d) {
                        d.action = "GET_DATA_SALE_SAC";
                        d.doc_date_start = $('#doc_date_start').val();
                        d.doc_date_to = $('#doc_date_to').val();
                    }
                },
                'columns': [
                    {data: 'DI_DATE'},
                    {data: 'DI_TIME_CHK'},
                    {data: 'DI_REF'},
                    {data: 'AR_CODE'},
                    {data: 'AR_NAME'},
                    {data: 'SLMN_NAME'},
                    {data: 'SKU_CODE'},
                    {data: 'SKU_NAME'},
                    {data: 'TRD_QTY'},
                    {data: 'TRD_U_PRC'},
                    {data: 'TRD_G_KEYIN'},
                    {data: 'TRD_B_SELL'},
                    {data: 'TRD_B_VAT'},
                    {data: 'BRANCH'},
                    {data: 'update'},
                ],
                'columnDefs': [
                    {
                        'targets': [8, 9, 10, 11, 12],
                        'className': 'text-right'
                    },
                    {
                        'targets': [14],
                        'className': 'text-center',
                        'orderable': false,
                        'searchable': false
                    }
                ]
            });

            // Update execution time badge whenever DataTable loads data
            $('#TableRecordList').on('xhr.dt', function (e, settings, json, xhr) {
                if (json && json.exec_time_ms !== undefined) {
                    $('#perf_time').text(json.exec_time_ms);
                    $('#query_perf_badge').fadeIn();
                }
            });

            $('#btnFilter').click(function () {
                dataRecords.ajax.reload();
            });
        });

        function ShowExplainAnalyze() {
            $('#explain_exec_time').html('<i class="fa fa-spinner fa-spin"></i> กำลังวิเคราะห์...');
            $('#explain_sql').text('Loading SQL...');
            $('#explain_tree').text('Analyzing query performance on server...');
            $('#explainModal').modal('show');

            let searchVal = '';
            if (typeof dataRecords !== 'undefined') {
                searchVal = dataRecords.search();
            }

            $.ajax({
                type: "POST",
                url: 'model/manage_product_sale_sac_process.php',
                dataType: "json",
                data: {
                    action: "EXPLAIN_ANALYZE",
                    doc_date_start: $('#doc_date_start').val(),
                    doc_date_to: $('#doc_date_to').val(),
                    search_value: searchVal
                },
                success: function (response) {
                    if (response.status === 'success') {
                        $('#explain_exec_time').text(response.exec_time_ms);
                        $('#explain_sql').text(response.sql);
                        $('#explain_tree').text(response.explain_result);
                    } else {
                        $('#explain_tree').text('Error: ' + response.message);
                    }
                },
                error: function (xhr, status, error) {
                    $('#explain_tree').text('AJAX Error: ' + error);
                }
            });
        }
    </script>

    <script>
        $(document).ready(function () {
            $("#recordModal").on('submit', '#recordForm', function (event) {
                event.preventDefault();
                $('#save').attr('disabled', 'disabled');
                let formData = $(this).serialize();
                $.ajax({
                    url: 'model/manage_product_sale_sac_process.php',
                    method: "POST",
                    data: formData,
                    success: function (data) {
                        if (typeof alertify !== 'undefined') {
                            alertify.success(data);
                        } else {
                            alert(data);
                        }
                        $('#recordForm')[0].reset();
                        $('#recordModal').modal('hide');
                        $('#save').attr('disabled', false);
                        dataRecords.ajax.reload();
                    },
                    error: function (xhr, status, error) {
                        if (typeof alertify !== 'undefined') {
                            alertify.error("Error: " + error);
                        } else {
                            alert("Error: " + error);
                        }
                        $('#save').attr('disabled', false);
                    }
                })
            });
        });
    </script>

    <script>
        $("#TableRecordList").on('click', '.info', function () {
            let id = $(this).attr("id");
            let formData = {action: "GET_DATA", id: id};
            $.ajax({
                type: "POST",
                url: 'model/manage_product_sale_sac_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let DI_DATE = response[i].DI_DATE;
                        let DI_TIME_CHK = response[i].DI_TIME_CHK;
                        let DI_REF = response[i].DI_REF;
                        let AR_CODE = response[i].AR_CODE;
                        let AR_NAME = response[i].AR_NAME;
                        let SLMN_CODE = response[i].SLMN_CODE;
                        let SLMN_NAME = response[i].SLMN_NAME;
                        let SKU_CODE = response[i].SKU_CODE;
                        let SKU_NAME = response[i].SKU_NAME;
                        let SKU_CAT = response[i].SKU_CAT;
                        let TRD_QTY = response[i].TRD_QTY;
                        let TRD_U_PRC = response[i].TRD_U_PRC;
                        let TRD_G_KEYIN = response[i].TRD_G_KEYIN;
                        let TRD_B_SELL = response[i].TRD_B_SELL;
                        let TRD_B_VAT = response[i].TRD_B_VAT;
                        let BRANCH = response[i].BRANCH;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#DI_DATE').val(DI_DATE);
                        $('#DI_TIME_CHK').val(DI_TIME_CHK);
                        $('#DI_REF').val(DI_REF);
                        $('#AR_CODE').val(AR_CODE);
                        $('#AR_NAME').val(AR_NAME);
                        $('#SLMN_CODE').val(SLMN_CODE);
                        $('#SLMN_NAME').val(SLMN_NAME);
                        $('#SKU_CODE').val(SKU_CODE);
                        $('#SKU_NAME').val(SKU_NAME);
                        $('#SKU_CAT').val(SKU_CAT);
                        $('#TRD_QTY').val(TRD_QTY);
                        $('#TRD_U_PRC').val(TRD_U_PRC);
                        $('#TRD_G_KEYIN').val(TRD_G_KEYIN);
                        $('#TRD_B_SELL').val(TRD_B_SELL);
                        $('#TRD_B_VAT').val(TRD_B_VAT);
                        $('#BRANCH').val(BRANCH);

                        // Make form inputs readonly and hide save button for Info mode
                        $('#recordForm input').prop('readonly', true);
                        $('#save').hide();

                        $('.modal-title').html("<i class='fa fa-info-circle'></i> Record Info");
                        $('#action').val('INFO');
                    }
                },
                error: function (response) {
                    if (typeof alertify !== 'undefined') {
                        alertify.error("error : " + response);
                    } else {
                        alert("error : " + response);
                    }
                }
            });
        });
    </script>

    <script>
        $("#TableRecordList").on('click', '.delete', function () {
            let id = $(this).attr("id");
            let formData = {action: "GET_DATA", id: id};
            $.ajax({
                type: "POST",
                url: 'model/manage_product_sale_sac_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let DI_DATE = response[i].DI_DATE;
                        let DI_TIME_CHK = response[i].DI_TIME_CHK;
                        let DI_REF = response[i].DI_REF;
                        let AR_CODE = response[i].AR_CODE;
                        let AR_NAME = response[i].AR_NAME;
                        let SLMN_CODE = response[i].SLMN_CODE;
                        let SLMN_NAME = response[i].SLMN_NAME;
                        let SKU_CODE = response[i].SKU_CODE;
                        let SKU_NAME = response[i].SKU_NAME;
                        let SKU_CAT = response[i].SKU_CAT;
                        let TRD_QTY = response[i].TRD_QTY;
                        let TRD_U_PRC = response[i].TRD_U_PRC;
                        let TRD_G_KEYIN = response[i].TRD_G_KEYIN;
                        let TRD_B_SELL = response[i].TRD_B_SELL;
                        let TRD_B_VAT = response[i].TRD_B_VAT;
                        let BRANCH = response[i].BRANCH;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#DI_DATE').val(DI_DATE);
                        $('#DI_TIME_CHK').val(DI_TIME_CHK);
                        $('#DI_REF').val(DI_REF);
                        $('#AR_CODE').val(AR_CODE);
                        $('#AR_NAME').val(AR_NAME);
                        $('#SLMN_CODE').val(SLMN_CODE);
                        $('#SLMN_NAME').val(SLMN_NAME);
                        $('#SKU_CODE').val(SKU_CODE);
                        $('#SKU_NAME').val(SKU_NAME);
                        $('#SKU_CAT').val(SKU_CAT);
                        $('#TRD_QTY').val(TRD_QTY);
                        $('#TRD_U_PRC').val(TRD_U_PRC);
                        $('#TRD_G_KEYIN').val(TRD_G_KEYIN);
                        $('#TRD_B_SELL').val(TRD_B_SELL);
                        $('#TRD_B_VAT').val(TRD_B_VAT);
                        $('#BRANCH').val(BRANCH);

                        $('#recordForm input').prop('readonly', true);
                        $('#save').show();

                        $('.modal-title').html("<i class='fa fa-minus'></i> Delete Record");
                        $('#action').val('DELETE');
                        $('#save').val('Confirm Delete');
                        $('#save').text('Confirm Delete');
                    }
                },
                error: function (response) {
                    if (typeof alertify !== 'undefined') {
                        alertify.error("error : " + response);
                    } else {
                        alert("error : " + response);
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
