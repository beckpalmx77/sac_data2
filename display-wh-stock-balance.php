<?php
include('includes/Header.php');

$year = date("Y");
$start_date = "01-01-" . $year;
$curr_date = date("d-m-Y");

if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {
    ?>
    <!DOCTYPE html>
    <html lang="th">
    <body id="page-top">
    <div id="wrapper">
        <?php include('includes/Side-Bar.php'); ?>
        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php include('includes/Top-Bar.php'); ?>
                <div class="container-fluid" id="container-wrapper">
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800"><?php echo urldecode($_GET['s']) ?></h1>
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="<?php echo $_SESSION['dashboard_page'] ?>">Home</a></li>
                            <li class="breadcrumb-item"><?php echo urldecode($_GET['m']) ?></li>
                            <li class="breadcrumb-item active" aria-current="page"><?php echo urldecode($_GET['s']) ?></li>
                        </ol>
                    </div>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-12">
                                <div class="card-body">
                                    <section class="container-fluid">
                                        <form id="export_data" method="post" action="export_process/export_process_display_data_wh_stock_balance.php" enctype="multipart/form-data">
                                            <div class="col-md-12 col-md-offset-2" style="display: flex; align-items: center; gap: 10px;">
                                                <button type="button" name="btnRefresh" id="btnRefresh" class="btn btn-success btn-xs" onclick="ReloadDataTable();">
                                                    Refresh <i class="fa fa-refresh"></i>
                                                </button>
                                                <label for="name_t" class="control-label mb-0"><b>วัน</b></label>
                                                <input type="text" class="form-control" id="doc_date_start" name="doc_date_start" readonly="true" style="width: calc(0.6em * 10 + 1.25rem);" value="<?php echo $start_date; ?>">
                                                <label for="name_t" class="control-label mb-0"><b>-</b></label>
                                                <input type="text" class="form-control" id="doc_date_to" name="doc_date_to" readonly="true" style="width: calc(0.6em * 10 + 1.25rem);" value="<?php echo $curr_date; ?>">
                                                <select id="product_id" name="product_id" class="form-control" style="width: 150px;">
                                                    <option value="">ค้นหารหัสสินค้า</option>
                                                </select>
                                                <input type="text" id="product_name" name="product_name" class="form-control" placeholder="รายละเอียดสินค้า" readonly style="width: 300px;">
                                                <select id="wh" name="wh" class="form-control" style="width: 100px;">
                                                    <option value="">คลังปี</option>
                                                </select>
                                                <select id="wh_week_id" name="wh_week_id" class="form-control" style="width: 100px;">
                                                    <option value="">สัปดาห์</option>
                                                </select>

                                                <button type="button" name="btnFilter" id="btnFilter" class="btn btn-primary btn-xs">FilterData <i class="fa fa-filter"></i></button>
                                                <button type="button" name="btnExport" id="btnExport" class="btn btn-success btn-xs" onclick="ExportData();">Export <i class="fa fa-file-excel-o"></i></button>
                                            </div>
                                        </form>
                                        <br>
                                        <div class="col-md-12 col-md-offset-2">
                                            <table id='TableRecordList' class='display dataTable'>
                                                <thead>
                                                <tr>
                                                    <th>รหัสสินค้า</th>
                                                    <th>รายละเอียด</th>
                                                    <th>คลังปี</th>
                                                    <th>สัปดาห์</th>
                                                    <th>ตำแหน่ง</th>
                                                    <th>จำนวน</th>
                                                </tr>
                                                </thead>
                                                <tfoot>
                                                <tr>
                                                    <th>รหัสสินค้า</th>
                                                    <th>รายละเอียด</th>
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

    <!-- JavaScript -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="js/myadmin.min.js"></script>
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
        .icon-input-btn { display: inline-block; position: relative; }
        .icon-input-btn input[type="submit"] { padding-left: 2em; }
        .icon-input-btn .fa { display: inline-block; position: absolute; left: 0.65em; top: 30%; }
        .form-control { height: calc(1.5em + 0.75rem + 2px); }
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
    </style>

    <script>
        $(document).ready(function () {
            //$('#doc_date_start, #doc_date_to').datepicker({
            $('#doc_date_to').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });

            let dataRecords = $('#TableRecordList').DataTable({
                'lengthMenu': [[10, 20, 50, 100], [5, 10, 20, 50, 100]],
                'language': {
                    search: 'ค้นหา', lengthMenu: 'แสดง _MENU_ รายการ',
                    info: 'หน้าที่ _PAGE_ จาก _PAGES_',
                    infoEmpty: 'ไม่มีข้อมูล',
                    zeroRecords: "ไม่มีข้อมูลตามเงื่อนไข",
                    infoFiltered: '(กรองข้อมูลจากทั้งหมด _MAX_ รายการ)',
                    paginate: { previous: 'ก่อนหน้า', last: 'สุดท้าย', next: 'ต่อไป' }
                },
                'processing': true,
                'serverSide': true,
                'autoWidth': true,
                'searching': false,
                'sortable': true,
                <?php if ($_SESSION['deviceType'] !== 'computer') { echo "'scrollX': true,"; } ?>
                'serverMethod': 'post',
                'ajax': {
                    'url': 'model/manage_stock_balance_process.php',
                    'data': function (d) {
                        d.doc_date_start = $('#doc_date_start').val();
                        d.doc_date_to = $('#doc_date_to').val();
                        d.product_id = $('#product_id').val();
                        d.wh = $('#wh').val();
                        d.wh_week_id = $('#wh_week_id').val();
                        d.action = "GET_STOCK_BALANCE_DISPLAY";
                        d.sub_action = "GET_MASTER";
                    }
                },
                'columns': [
                    {data: 'product_id'},
                    {data: 'product_name'},
                    {data: 'wh'},
                    {data: 'wh_week_id'},
                    {data: 'location'},
                    {data: 'qty'}
                ]
            });

            $('#btnFilter').click(function() {
                dataRecords.ajax.reload();
            });

            setInterval(function () {
                dataRecords.ajax.reload(null, false);
            }, 10000);
        });

        function ReloadDataTable() {
            $('#TableRecordList').DataTable().ajax.reload();
        }

        function ExportData() {
            const form = document.getElementById("export_data");
            if (form.checkValidity()) {
                form.submit();
            } else {
                alert("Please fill out the required fields.");
            }
        }
    </script>

    <script>
        $(document).ready(function () {
            // AJAX เพื่อดึงข้อมูลจากฐานข้อมูล
            $.ajax({
                url: 'model/get_products.php', // หน้า PHP ที่จะดึงข้อมูล
                method: 'GET',
                dataType: 'json',
                success: function (data) {
                    let select = $('#product_id');
                    $.each(data, function (index, product) {
                        select.append($('<option>', {
                            value: product.product_id,
                            text: product.product_id, // เปลี่ยนเป็นชื่อของข้อมูลที่คุณต้องการแสดง
                            'data-name': product.product_name // เก็บข้อมูลชื่อใน attribute เพื่อใช้ภายหลัง
                        }));
                    });

                    // แปลง select เป็น select2 หลังจากข้อมูลถูกเพิ่ม
                    $('#product_id').select2({
                        placeholder: "เลือกรหัสสินค้า",
                        allowClear: true,
                        width: '80%' // กำหนดขนาดให้เต็ม 100% เพื่อให้ตรงกับ element อื่น
                    });
                },
                error: function (xhr, status, error) {
                    console.error('เกิดข้อผิดพลาดในการดึงข้อมูล:', error);
                }
            });

            // เมื่อมีการเปลี่ยนแปลงค่าใน select
            $('#product_id').on('change', function () {
                let selectedOption = $(this).find('option:selected');
                let productName = selectedOption.data('name'); // ดึงข้อมูล product_name จาก attribute
                $('#product_name').val(productName); // กำหนดค่าให้กับ input ของ product_name
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            // AJAX เพื่อดึงข้อมูลจากฐานข้อมูล
            $.ajax({
                url: 'model/get_warehouse.php', // หน้า PHP ที่จะดึงข้อมูล
                method: 'GET',
                dataType: 'json',
                success: function (data) {
                    let select = $('#wh');
                    $.each(data, function (index, warehouse) {
                        select.append($('<option>', {
                            value: warehouse.warehouse_id,
                            text: warehouse.warehouse_id, // เปลี่ยนเป็นชื่อของข้อมูลที่คุณต้องการแสดง
                            'data-name': warehouse.warehouse_id // เก็บข้อมูลชื่อใน attribute เพื่อใช้ภายหลัง
                        }));
                    });

                    // แปลง select เป็น select2 หลังจากข้อมูลถูกเพิ่ม
                    $('#wh').select2({
                        placeholder: "เลือกคลังปี",
                        allowClear: true,
                        width: '40%' // กำหนดขนาดให้เต็ม 100% เพื่อให้ตรงกับ element อื่น
                    });
                },
                error: function (xhr, status, error) {
                    console.error('เกิดข้อผิดพลาดในการดึงข้อมูล:', error);
                }
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            // AJAX เพื่อดึงข้อมูลจากฐานข้อมูล
            $.ajax({
                url: 'model/get_wh_week.php', // หน้า PHP ที่จะดึงข้อมูล
                method: 'GET',
                dataType: 'json',
                success: function (data) {
                    let select = $('#wh_week_id');
                    $.each(data, function (index, wh_week) {
                        select.append($('<option>', {
                            value: wh_week.wh_week_id,
                            text: wh_week.wh_week_id, // เปลี่ยนเป็นชื่อของข้อมูลที่คุณต้องการแสดง
                            'data-name': wh_week.wh_week_id // เก็บข้อมูลชื่อใน attribute เพื่อใช้ภายหลัง
                        }));
                    });

                    // แปลง select เป็น select2 หลังจากข้อมูลถูกเพิ่ม
                    $('#wh_week_id').select2({
                        placeholder: "เลือกสัปดาห์",
                        allowClear: true,
                        width: '30%' // กำหนดขนาดให้เต็ม 100% เพื่อให้ตรงกับ element อื่น
                    });
                },
                error: function (xhr, status, error) {
                    console.error('เกิดข้อผิดพลาดในการดึงข้อมูล:', error);
                }
            });
        });
    </script>


    </body>
    </html>

<?php } ?>
