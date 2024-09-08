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
                                                    <th>วันที่</th>
                                                    <th>รหัสสินค้า</th>
                                                    <th>รายละเอียด</th>
                                                    <th>จำนวน</th>
                                                    <th>คลังปี</th>
                                                    <th>จากตำแหน่ง</th>
                                                    <th>ไปตำแหน่ง</th>
                                                    <th>Action</th>

                                                </tr>
                                                </thead>
                                                <tfoot>
                                                <tr>
                                                    <th>วันที่</th>
                                                    <th>รหัสสินค้า</th>
                                                    <th>รายละเอียด</th>
                                                    <th>จำนวน</th>
                                                    <th>คลังปี</th>
                                                    <th>จากตำแหน่ง</th>
                                                    <th>ไปตำแหน่ง</th>
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
                                                            <div class="container-fluid">
                                                                <!-- ใช้ container-fluid เพื่อให้เต็มความกว้างของ modal -->
                                                                <!-- กลุ่มฟอร์มที่ 1 -->
                                                                <div class="row">
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="doc_date" class="control-label">วันที่</label>
                                                                            <div class="input-group">
                                                                                <input type="text" class="form-control"
                                                                                       id="doc_date" name="doc_date"
                                                                                       readonly="true"
                                                                                       value="<?php echo $curr_date; ?>">
                                                                                <div class="input-group-append">
                                                                                    <span class="input-group-text"><i
                                                                                                class="glyphicon glyphicon-th"></i></span>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <!-- กลุ่มฟอร์มที่ 2 -->
                                                                <div class="row">
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="product_id"
                                                                                   class="control-label">รหัสสินค้า</label>
                                                                            <select class="form-control" id="product_id"
                                                                                    name="product_id" required>
                                                                                <option value="">เลือกรหัสสินค้า
                                                                                </option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="product_name"
                                                                                   class="control-label">รายละเอียด</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="product_name" name="product_name"
                                                                                   readonly placeholder="รายละเอียด"
                                                                                   required>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="qty"
                                                                                   class="control-label">จำนวน</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="qty" name="qty" placeholder=""
                                                                                   required>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                <!-- กลุ่มฟอร์มที่ 3 -->
                                                                <div class="row">
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="wh_org" class="control-label">คลังปี</label>
                                                                            <select class="form-control" id="wh_org"
                                                                                    name="wh_org" required>
                                                                                <option value="">เลือกคลังปี</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="location_org"
                                                                                   class="control-label">จากตำแหน่ง</label>
                                                                            <select class="form-control"
                                                                                    id="location_org"
                                                                                    name="location_org" required>
                                                                                <option value="">จากตำแหน่ง</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-md-4">
                                                                        <div class="form-group">
                                                                            <label for="location_to"
                                                                                   class="control-label">ไปตำแหน่ง</label>
                                                                            <select class="form-control"
                                                                                    id="location_to" name="location_to"
                                                                                    required>
                                                                                <option value="">ไปตำแหน่ง</option>
                                                                            </select>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                            </div>
                                                        </div>
                                                        <div class="modal-footer">
                                                            <input type="hidden" name="id" id="id"/>
                                                            <input type="hidden" name="action" id="action" value=""/>
                                                            <button type="submit" name="save" id="save"
                                                                    class="btn btn-primary"><i class="fa fa-check"></i>
                                                                Save
                                                            </button>
                                                            <button type="button" class="btn btn-danger"
                                                                    data-dismiss="modal">Close <i
                                                                        class="fa fa-window-close"></i></button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="modal fade" id="SearchModal">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h4 class="modal-title">Modal title</h4>
                                                        <button type="button" class="close" data-dismiss="modal"
                                                                aria-hidden="true">×
                                                        </button>
                                                    </div>

                                                    <div class="container"></div>
                                                    <div class="modal-body">

                                                        <div class="modal-body">

                                                            <table cellpadding="0" cellspacing="0" border="0"
                                                                   class="display"
                                                                   id="TableUnitList"
                                                                   width="100%">
                                                                <thead>
                                                                <tr>
                                                                    <th>รหัส</th>
                                                                    <th>หน่วยนับ</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                                </thead>
                                                                <tfoot>
                                                                <tr>
                                                                    <th>รหัส</th>
                                                                    <th>หน่วยนับ</th>
                                                                    <th>Action</th>
                                                                </tr>
                                                                </tfoot>
                                                            </table>
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
            // Set styles for buttons with icons
            $(".icon-input-btn").each(function () {
                let btn = $(this).find(".btn"),
                    fa = $(this).find(".fa");
                fa.css({'font-size': btn.css("font-size"), 'color': btn.css("color")});
            });

            // Set default date
            let today = new Date(),
                doc_date = `${getDay2Digits(today)}-${getMonth2Digits(today)}-${today.getFullYear()}`;
            $('#doc_date').val(doc_date);

            // Initialize date picker
            $('#doc_date').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });

            // Initialize DataTable
            let formData = {action: "GET_MOVEMENT", sub_action: "GET_MASTER"};
            let dataRecords = $('#TableRecordList').DataTable({
                lengthMenu: [[5, 10, 20, 50, 100], [5, 10, 20, 50, 100]],
                language: {
                    search: 'ค้นหา', lengthMenu: 'แสดง _MENU_ รายการ',
                    info: 'หน้าที่ _PAGE_ จาก _PAGES_',
                    infoEmpty: 'ไม่มีข้อมูล',
                    zeroRecords: "ไม่มีข้อมูลตามเงื่อนไข",
                    infoFiltered: '(กรองข้อมูลจากทั้งหมด _MAX_ รายการ)',
                    paginate: {previous: 'ก่อนหน้า', last: 'สุดท้าย', next: 'ต่อไป'}
                },
                processing: true,
                serverSide: true,
                autoWidth: true,
                searching: true,
                <?php  if ($_SESSION['deviceType'] !== 'computer') { echo "'scrollX': true,"; }?>
                serverMethod: 'post',
                ajax: {url: 'model/manage_movement_process.php', data: formData},
                columns: [
                    {data: 'doc_date'}, {data: 'product_id'}, {data: 'product_name'},
                    {data: 'qty'}, {data: 'wh_org'}, {data: 'location_org'},
                    {data: 'location_to'}, {data: 'update'}
                ]
            });

            // Handle form submission
            $("#recordModal").on('submit', '#recordForm', function (event) {
                event.preventDefault();
                $('#save').prop('disabled', true);
                $.post('model/manage_movement_process.php', $(this).serialize(), function (data) {
                    alertify.success(data);
                    $('#recordForm')[0].reset();
                    $('#recordModal').modal('hide');
                    $('#save').prop('disabled', false);
                    dataRecords.ajax.reload();
                });
            });

            // Handle add button click
            $("#btnAdd").click(function () {
                $('#recordModal').modal('show').find('input, select').val('');
                $('.modal-title').html("<i class='fa fa-plus'></i> ADD Record");
                $('#action').val('ADD');
                $('#save').val('Save');
            });

            // Handle update and delete actions
            $("#TableRecordList").on('click', '.update, .delete', function () {
                let id = $(this).attr("id"),
                    action = $(this).hasClass('update') ? 'UPDATE' : 'DELETE',
                    title = action === 'UPDATE' ? 'Edit' : 'Delete';

                $.post('model/manage_movement_process.php', {action: "GET_DATA", id: id}, function (response) {
                    $.each(response, function (i, item) {
                        $('#recordModal').modal('show').find('input, select').each(function () {
                            $(this).val(item[$(this).attr('id')]).trigger('change');
                        });
                        $('.modal-title').html(`<i class='fa fa-${action.toLowerCase() === 'update' ? 'plus' : 'minus'}'></i> ${title} Record`);
                        $('#action').val(action);
                        $('#save').val(action === 'UPDATE' ? 'Save' : 'Confirm Delete');
                    });
                }, 'json').fail(function (response) {
                    alertify.error("error : " + response);
                });
            });

            // Helper function for AJAX-based select options
            function populateSelect(url, selector, placeholder) {
                $.getJSON(url, function (data) {
                    let select = $(selector);
                    $.each(data, function (index, item) {
                        select.append($('<option>', {value: item.id, text: item.id, 'data-name': item.name}));
                    });
                    select.select2({placeholder: placeholder, allowClear: true, width: '100%'});
                }).fail(function (xhr, status, error) {
                    console.error('Error loading data:', error);
                });
            }

            // Initialize selects with AJAX data
            populateSelect('model/get_products.php', '#product_id', "เลือกรหัสสินค้า");
            populateSelect('model/get_warehouse.php', '#wh_org', "เลือกคลังปี");
            populateSelect('model/get_wh_location.php', '#location_org', "เลือกตำแหน่ง");
            populateSelect('model/get_wh_location.php', '#location_to', "เลือกตำแหน่ง");

            // Update product name on selection
            $('#product_id').on('change', function () {
                $('#product_name').val($(this).find('option:selected').data('name'));
            });
        });
    </script>


    </body>
    </html>

<?php } ?>