<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {

    include("config/connect_db.php");

    $month_num = str_replace('0', '', date('m'));

    $sql_curr_month = " SELECT * FROM ims_month where month = '" . $month_num . "'";

    $stmt_curr_month = $conn->prepare($sql_curr_month);
    $stmt_curr_month->execute();
    $MonthCurr = $stmt_curr_month->fetchAll();
    foreach ($MonthCurr as $row_curr) {
        $month_name = $row_curr["month_name"];
    }

    $sql_month = " SELECT * FROM ims_month ";
    $stmt_month = $conn->prepare($sql_month);
    $stmt_month->execute();
    $MonthRecords = $stmt_month->fetchAll();

    $sql_year = " SELECT DISTINCT(DI_YEAR) AS DI_YEAR
    FROM ims_data_sale_sac_all WHERE DI_YEAR >= 2019
    order by DI_YEAR desc ";
    $stmt_year = $conn->prepare($sql_year);
    $stmt_year->execute();
    $YearRecords = $stmt_year->fetchAll();

    $sql_sku_cat = " SELECT DISTINCT(SKU_CAT) AS SKU_CAT
    FROM ims_data_sale_sac_all WHERE DI_YEAR >= 2019
    order by SKU_CAT ";
    $stmt_sku_cat = $conn->prepare($sql_sku_cat);
    $stmt_sku_cat->execute();
    $SkuCatRecords = $stmt_sku_cat->fetchAll();

    $sql_brand = " SELECT DISTINCT(BRAND) AS BRAND
    FROM ims_data_sale_sac_all WHERE DI_YEAR >= 2019
    order by BRAND ";
    $stmt_brand = $conn->prepare($sql_brand);
    $stmt_brand->execute();
    $BrandRecords = $stmt_brand->fetchAll();

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
                                                    <th>รหัสเป้าหมาย</th>
                                                    <th>ชื่อ TAKE</th>
                                                    <th>ประเภท</th>
                                                    <th>ยี่ห้อ</th>
                                                    <th>ปี</th>
                                                    <th>เดือน</th>
                                                    <th>ยอดเป้าหมาย</th>
                                                    <th>Status</th>
                                                    <th>Action</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                                <tfoot>
                                                <tr>
                                                    <th>รหัสเป้าหมาย</th>
                                                    <th>ชื่อ TAKE</th>
                                                    <th>ประเภท</th>
                                                    <th>ยี่ห้อ</th>
                                                    <th>ปี</th>
                                                    <th>เดือน</th>
                                                    <th>ยอดเป้าหมาย</th>
                                                    <th>Status</th>
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

                                                                <div class="form-group">
                                                                    <label for="target_id" class="control-label">รหัสเป้าหมาย</label>
                                                                    <input type="target_id" class="form-control"
                                                                           id="target_id" name="target_id"
                                                                           readonly="true"
                                                                           placeholder="สร้างอัตโนมัติ">
                                                                </div>

                                                                <!--div class="form-group">
                                                                    <label for="target_name"
                                                                           class="control-label">รายละเอียด</label>
                                                                    <input type="text" class="form-control"
                                                                           id="target_name"
                                                                           name="target_name"
                                                                           required="required"
                                                                           placeholder="">
                                                                </div-->

                                                                <div class="form-group">
                                                                    <label for="DI_YEAR">เลือกปี :</label>
                                                                    <select name="DI_YEAR" id="DI_YEAR" class="form-control"
                                                                            required>
                                                                        <?php foreach ($YearRecords as $row) { ?>
                                                                            <option value="<?php echo $row["DI_YEAR"]; ?>">
                                                                                <?php echo $row["DI_YEAR"]; ?>
                                                                            </option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </div>

                                                                <div class="form-group">
                                                                    <label for="DI_MONTH">เลือกเดือน :</label>
                                                                    <select name="DI_MONTH" id="DI_MONTH" class="form-control"
                                                                            required>
                                                                        <option value="<?php echo $month_num; ?>"
                                                                                selected><?php echo $month_name; ?></option>
                                                                        <?php foreach ($MonthRecords as $row) { ?>
                                                                            <option value="<?php echo $row["month"]; ?>">
                                                                                <?php echo $row["month_name"]; ?>
                                                                            </option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </div>

                                                                <div class="form-group">
                                                                        <label for="TAKE_NAME" class="control-label">ค้นหาชื่อ
                                                                            TAKE</label>
                                                                        <select id="TAKE_NAME" name="TAKE_NAME"
                                                                                class="form-control">
                                                                            <option value="-">ค้นหาชื่อ TAKE</option>
                                                                        </select>
                                                                </div>

                                                                <div class="form-group">
                                                                    <label for="SKU_CAT">เลือกประเภท :</label>
                                                                    <select name="SKU_CAT" id="SKU_CAT"
                                                                            class="form-control"
                                                                            required>
                                                                        <option value="-"
                                                                                selected>-
                                                                        </option>
                                                                        <?php foreach ($SkuCatRecords as $row) { ?>
                                                                            <option value="<?php echo $row["SKU_CAT"]; ?>">
                                                                                <?php echo $row["SKU_CAT"]; ?>
                                                                            </option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </div>

                                                                <div class="form-group">
                                                                    <label for="BRAND">เลือกยี่ห้อ :</label>
                                                                    <select name="BRAND" id="BRAND" class="form-control"
                                                                            required>
                                                                        <option value="-"
                                                                                selected>-
                                                                        </option>
                                                                        <?php foreach ($BrandRecords as $row) { ?>
                                                                            <option value="<?php echo $row["BRAND"]; ?>">
                                                                                <?php echo $row["BRAND"]; ?>
                                                                            </option>
                                                                        <?php } ?>
                                                                    </select>
                                                                </div>

                                                                <div class="form-group">
                                                                    <label for="target_qty"
                                                                           class="control-label">ยอดเป้าหมาย
                                                                        (จำนวน)</label>
                                                                    <input type="text" class="form-control"
                                                                           id="target_qty"
                                                                           name="target_qty"
                                                                           required="required"
                                                                           placeholder="">
                                                                </div>


                                                                <div class="form-group">
                                                                    <label for="status"
                                                                           class="control-label">Status</label>
                                                                    <select id="status" name="status"
                                                                            class="form-control" data-live-search="true"
                                                                            title="Please select">
                                                                        <option>Y</option>
                                                                        <option>N</option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <input type="hidden" name="id" id="id"/>
                                                            <input type="hidden" name="action" id="action" value=""/>
                                                            <span class="icon-input-btn">
                                                                <i class="fa fa-check"></i>
                                                            <input type="submit" name="save" id="save"
                                                                   class="btn btn-primary" value="Save"/>
                                                            </span>
                                                            <button type="button" class="btn btn-danger"
                                                                    data-dismiss="modal">Close <i
                                                                        class="fa fa-times"></i>
                                                            </button>
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

    <!--script src="https://cdnjs.cloudflare.com/ajax/libs/bootbox.js/5.5.2/bootbox.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.0/js/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.0/css/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.0.0/css/buttons.dataTables.min.css"/-->

    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>



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
        .select2-container {
            width: 100% !important; /* ปรับให้ขนาดเต็ม 100% ของพื้นที่ */
        }

        .select2-selection--single {
            height: 38px !important; /* ปรับความสูงให้ตรงกับ Text Input */
            padding: 0.375rem 0.75rem !important; /* เพิ่มระยะห่างภายในให้เหมือน Text Input */
            font-size: 1rem !important; /* ปรับขนาดตัวอักษรให้ตรงกับ Text Input */
            line-height: 1.5 !important; /* ปรับ line-height ให้สอดคล้อง */
        }

        .select2-selection__rendered {
            line-height: 38px !important; /* ปรับ line-height ของข้อความที่เลือกใน Select2 */
        }

        .select2-selection__arrow {
            height: 38px !important; /* ปรับลูกศรให้มีความสูงเท่ากับ Select2 */
        }
    </style>

    <style>
        /* Spin Loader CSS */
        #spinner {
            display: none; /* ซ่อน spinner ตอนแรก */
            position: fixed;
            z-index: 999;
            left: 50%;
            top: 50%;
            width: 40px;
            height: 40px;
            border: 4px solid rgba(0, 0, 0, 0.1);
            border-top: 4px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }
            100% {
                transform: rotate(360deg);
            }
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

        $("#target_id").blur(function () {
            let method = $('#action').val();
            if (method === "ADD") {
                let target_id = $('#target_id').val();
                let formData = {action: "SEARCH", target_id: target_id};
                $.ajax({
                    url: 'model/manage_take_target_process.php',
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
            let formData = {action: "GET_TAKE_TARGET", sub_action: "GET_MASTER"};
            let dataRecords = $('#TableRecordList').DataTable({
                'lengthMenu': [[5, 10, 20, 50, 100], [5, 10, 20, 50, 100]],
                'language': {
                    search: 'ค้นหา',
                    lengthMenu: 'แสดง _MENU_ รายการ',
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
                'ajax': {
                    'url': 'model/manage_take_target_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'target_id'},
                    {data: 'TAKE_NAME'},
                    {data: 'SKU_CAT'},
                    {data: 'BRAND'},
                    {data: 'DI_YEAR'},
                    {data: 'DI_MONTH'},
                    {data: 'target_qty'},
                    {data: 'status'},
                    {data: 'update'},
                    {data: 'delete'}
                ]
            });
        });

    </script>

    <script>
        $(document).ready(function () {
            // *** FOR SUBMIT FORM ***
            $("#recordModal").on('submit', '#recordForm', function (event) {
                event.preventDefault();
                if ($('#SKU_CAT').val() !== "-" && $('#BRAND').val() !== "-" && $('#DI_MONTH').val() !== "-" && $('#DI_YEAR').val() !== "-") {
                    $('#save').attr('disabled', 'disabled');
                    let formData = $(this).serialize();
                    $.ajax({
                        url: 'model/manage_take_target_process.php',
                        method: "POST",
                        data: formData,
                        success: function (data) {
                            alertify.success(data);
                            $('#recordForm')[0].reset();
                            $('#recordModal').modal('hide');
                            $('#save').attr('disabled', false);
                            $('#TableRecordList').DataTable().ajax.reload();
                        }
                    });
                } else {
                    alertify.error("กรุณาตรวจสอบข้อมูล");
                }
            });
        });

    </script>


    <script>
        $(document).ready(function () {
            $("#btnAdd").click(function () {
                $('#recordModal').modal('show');
                $('#id').val("");
                $('#target_id').val("");
                $('#TAKE_NAME').val("");
                $('#DI_YEAR').val("");
                $('#DI_MONTH').val("");
                $('#SKU_CAT').val("-");
                $('#BRAND').val("-");
                $('#target_qty').val("");
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
                url: 'model/manage_take_target_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let target_id = response[i].target_id;
                        let TAKE_NAME = response[i].TAKE_NAME;
                        let DI_YEAR = response[i].DI_YEAR;
                        let DI_MONTH = response[i].DI_MONTH;
                        let SKU_CAT = response[i].SKU_CAT;
                        let BRAND = response[i].BRAND;
                        let target_qty = response[i].target_qty;
                        let status = response[i].status;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#target_id').val(target_id);
                        $('#TAKE_NAME').val(TAKE_NAME);
                        $('#DI_YEAR').val(DI_YEAR);
                        $('#DI_MONTH').val(DI_MONTH);
                        $('#SKU_CAT').val(SKU_CAT);
                        $('#BRAND').val(BRAND);
                        $('#target_qty').val(target_qty);
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

        $("#TableRecordList").on('click', '.delete', function () {
            let id = $(this).attr("id");
            let formData = {action: "GET_DATA", id: id};
            $.ajax({
                type: "POST",
                url: 'model/manage_take_target_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let target_id = response[i].target_id;
                        let TAKE_NAME = response[i].TAKE_NAME;
                        let DI_YEAR = response[i].DI_YEAR;
                        let DI_MONTH = response[i].DI_MONTH;
                        let SKU_CAT = response[i].SKU_CAT;
                        let BRAND = response[i].BRAND;
                        let target_qty = response[i].target_qty;
                        let status = response[i].status;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#target_id').val(target_id);
                        $('#TAKE_NAME').val(TAKE_NAME);
                        $('#DI_YEAR').val(DI_YEAR);
                        $('#DI_MONTH').val(DI_MONTH);
                        $('#SKU_CAT').val(SKU_CAT);
                        $('#BRAND').val(BRAND);
                        $('#target_qty').val(target_qty);
                        $('#status').val(status);
                        $('.modal-title').html("<i class='fa fa-minus'></i> Delete Record");
                        $('#action').val('DELETE');
                        $('#save').val('Confirm Delete');
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
                url: 'model/get_take_name.php', // หน้า PHP ที่จะดึงข้อมูล
                method: 'GET',
                dataType: 'json',
                success: function (data) {
                    let select = $('#TAKE_NAME');
                    $.each(data, function (index, TAKE_NAME) {
                        select.append($('<option>', {
                            value: TAKE_NAME.TAKE_NAME,
                            text: TAKE_NAME.TAKE_NAME, // เปลี่ยนเป็นชื่อของข้อมูลที่คุณต้องการแสดง
                            'data-name': TAKE_NAME.TAKE_NAME // เก็บข้อมูลชื่อใน attribute เพื่อใช้ภายหลัง
                        }));
                    });

                    // แปลง select เป็น select2 หลังจากข้อมูลถูกเพิ่ม
                    $('#TAKE_NAME').select2({
                        placeholder: "เลือกชื่อ TAKE",
                        allowClear: true,
                        width: '100%' // กำหนดขนาดให้เต็ม 100% เพื่อให้ตรงกับ element อื่น
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