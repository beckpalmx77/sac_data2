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
                                            <table id='TableRecordList' class='display dataTable'>

                                                <thead>
                                                <tr>
                                                    <th>วันที่ต้องการ</th>
                                                    <th>ยี่ห้อ</th>
                                                    <th>ลายดอก</th>
                                                    <th>รหัสสินค้า</th>
                                                    <th>รายละเอียด</th>
                                                    <th>ร้านค้า</th>
                                                    <th>Sale/Take</th>
                                                    <th>STOCK</th>
                                                    <th>จำนวนที่ต้องการ</th>
                                                    <th>ประมาณการวันที่ของเข้า</th>
                                                    <th>วันที่ของเข้า</th>
                                                    <th>Action</th>
                                                </tr>
                                                </thead>
                                                <tfoot>
                                                <tr>
                                                    <th>วันที่ต้องการ</th>
                                                    <th>ยี่ห้อ</th>
                                                    <th>ลายดอก</th>
                                                    <th>รหัสสินค้า</th>
                                                    <th>รายละเอียด</th>
                                                    <th>ร้านค้า</th>
                                                    <th>Sale/Take</th>
                                                    <th>STOCK</th>
                                                    <th>จำนวนที่ต้องการ</th>
                                                    <th>ประมาณการวันที่ของเข้า</th>
                                                    <th>วันที่ของเข้า</th>
                                                    <th>Action</th>
                                                </tr>
                                                </tfoot>
                                            </table>

                                            <div id="result"></div>

                                        </div>

                                        <!--/div-->
                                        <!-- /.row -->

                                    </section>


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
                                                                <label for="date_request"
                                                                       class="control-label">วันที่ต้องการ :</label>
                                                                <input type="text" class="form-control"
                                                                       id="date_request"
                                                                       name="date_request"
                                                                       required="required"
                                                                       readonly="true"
                                                                       placeholder="วันที่ต้องการ">
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="estimate_date"
                                                                       class="control-label">ประมาณการวันที่ของเข้า :</label>
                                                                <input type="text" class="form-control"
                                                                       id="estimate_date"
                                                                       name="estimate_date"
                                                                       required="required"
                                                                       readonly="true"
                                                                       placeholder="ประมาณการวันที่ของเข้า">
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="date_in"
                                                                       class="control-label">ของมาวันที่ :</label>
                                                                <input type="text" class="form-control"
                                                                       id="date_in"
                                                                       name="date_in"
                                                                       required="required"
                                                                       readonly="true"
                                                                       placeholder="ของมาวันที่">
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="brand" class="control-label">ยี่ห้อ</label>
                                                                <input type="text" class="form-control"
                                                                       id="brand" name="brand"
                                                                       placeholder="">
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="class" class="control-label">ลายดอกยาง</label>
                                                                <input type="text" class="form-control"
                                                                       id="class" name="class"
                                                                       placeholder="">
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="detail" class="control-label">รายละเอียด</label>
                                                                <input type="text" class="form-control"
                                                                       id="detail" name="detail"
                                                                       readonly="true"
                                                                       placeholder="">
                                                            </div>

                                                            <div class="form-group">
                                                                <label for="qty_need" class="control-label">จำนวนที่ต้องการ</label>
                                                                <input type="text" class="form-control"
                                                                       id="qty_need" name="qty_need"
                                                                       readonly="true"
                                                                       placeholder="">
                                                            </div>

                                                                <label for="complete_flag">สถานะ :</label>
                                                                <input type="hidden" name="complete_flag" id="complete_flag"
                                                                   required
                                                                   class="form-control">

                                                            <select id='Selcomplete_flag' class='form-control'>
                                                                <option value='N'>- เลือกสถานะ -</option>
                                                            </select>
                                                            <br>

                                                            <input type="text" name="status_detail" id="status_detail"
                                                                   readonly = "true"
                                                                   class="form-control">

                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <input type="hidden" name="id" id="id"/>
                                                        <input type="hidden" name="action" id="action" value=""/>
                                                        <span class="icon-input-btn">
                                                        <i class="fa fa-check"></i>
                                                        <input type="submit" name="save" id="save" class="btn btn-primary" value="Save"/>
                                                        </span>
                                                        <button type="button" class="btn btn-danger"
                                                                data-dismiss="modal">Close <i
                                                                    class="fa fa-window-close"></i>
                                                        </button>
                                                    </div>
                                                </form>

                                            </div>
                                        </div>
                                    </div>


                                </div>

                            </div>

                        </div>

                    </div>
                    <!--Row-->

                    <!-- Row -->

                </div>

            </div>

        </div>


        <!---Container Fluid-->

    </div>

    <?php
    include('includes/Modal-Logout.php');
    include('includes/Footer.php');
    ?>

    </div>
    </div>

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

    <script src="vendor/datatables/v11/bootbox.min.js"></script>
    <script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

    <!-- Bootstrap Datepicker -->
    <script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
    <!-- Bootstrap Touchspin -->
    <script src="vendor/bootstrap-touchspin/js/jquery.bootstrap-touchspin.js"></script>
    <!-- ClockPicker -->
    <script src="vendor/clock-picker/clockpicker.js"></script>


    <script src="vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
    <script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
    <!--link href="vendor/date-picker-1.9/css/date_picker_style.css" rel="stylesheet"/-->
    <link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>

    <!-- Select2 -->
    <script src="vendor/select2/dist/js/select2.min.js"></script>

    <!-- select2 css -->
    <link href='js/select2/dist/css/select2.min.css' rel='stylesheet' type='text/css'>

    <!-- select2 script -->
    <script src='js/select2/dist/js/select2.min.js'></script>

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
            $('#date_request').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            $('#date_in').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });
        });
    </script>

    <script>
        $(document).ready(function () {
            $('#estimate_date').datepicker({
                format: "dd-mm-yyyy",
                todayHighlight: true,
                language: "th",
                autoclose: true
            });
        });
    </script>



    <script>
        $(document).ready(function () {
            /* 'scrollX': true, */
            let formData = {action: "GET_TIRES_REQUEST", sub_action: "GET_MASTER"};
            let dataRecords = $('#TableRecordList').DataTable({
                'processing': true,
                'serverSide': true,
                'serverMethod': 'post',
                'ajax': {
                    'url': 'model/manage_data_tires_process.php',
                    'data': formData
                },
                'columns': [
                    {data: 'date_request'},
                    {data: 'tires_brand'},
                    {data: 'tires_class'},
                    {data: 'tires_code'},
                    {data: 'tires_detail'},
                    {data: 'customer_name'},
                    {data: 'sale_name'},
                    {data: 'remark'},
                    {data: 'qty_need' , className: "text-right"},
                    {data: 'estimate_date'},
                    {data: 'date_in'},
                    {data: 'update'}
                ]
            });

            <!-- *** FOR SUBMIT FORM *** -->
            $("#recordModal").on('submit', '#recordForm', function (event) {
                event.preventDefault();
                //alert($('#estimate_date').val());
                $('#action').val("UPDATE");
                $('#complete_flag').val($(Selcomplete_flag).val());
                $('#save').attr('disabled', 'disabled');
                let formData = $(this).serialize();
                //alert(formData);

                $.ajax({
                    url: 'model/manage_data_tires_process.php',
                    method: "POST",
                    data: formData,
                    success: function (data) {
                        alertify.success(data);
                        $('#recordForm')[0].reset();
                        $('#recordModal').modal('hide');
                        $('#save').attr('disabled', false);
                        dataRecords.ajax.reload();
                    }
                })

            });
            <!-- *** FOR SUBMIT FORM *** -->

        });
    </script>

    <script>

        $("#TableRecordList").on('click', '.update', function () {
            let id = $(this).attr("id");
            let formData = {action: "GET_DATA", id: id};
            $.ajax({
                type: "POST",
                url: 'model/manage_data_tires_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let date_request = response[i].date_request;
                        let estimate_date = response[i].estimate_date;
                        let date_in = response[i].date_in;
                        let tires_brand = response[i].tires_brand;
                        let tires_class = response[i].tires_class;
                        let detail = response[i].detail;
                        let qty_need = response[i].qty_need;
                        let complete_flag = response[i].complete_flag;
                        let status_detail = response[i].status_detail;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#date_request').val(date_request);
                        $('#estimate_date').val(estimate_date);
                        $('#date_in').val(date_in);
                        $('#brand').val(tires_brand);
                        $('#class').val(tires_class);
                        $('#detail').val(detail);
                        $('#qty_need').val(qty_need);
                        $('#complete_flag').val(complete_flag);

                        if (complete_flag==='Y') {
                            $('#save').attr('disabled', 'disabled');
                        } else {
                            $('#save').attr('disabled', false);
                        }

                        $('#status_detail').val(status_detail);
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
                url: 'model/manage_account_process.php',
                dataType: "json",
                data: formData,
                success: function (response) {
                    let len = response.length;
                    for (let i = 0; i < len; i++) {
                        let id = response[i].id;
                        let email = response[i].email;
                        let first_name = response[i].first_name;
                        let last_name = response[i].last_name;
                        let account_type = response[i].account_type;
                        let status = response[i].status;

                        $('#recordModal').modal('show');
                        $('#id').val(id);
                        $('#email').val(email);
                        $('#first_name').val(first_name);
                        $('#last_name').val(last_name);
                        $('#account_type').val(account_type);
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
            $('[data-toggle="tooltip"]').tooltip();
        });
    </script>

    <script>
        $(document).ready(function () {

            $("#Selcomplete_flag").select2({
                ajax: {
                    url: "model/status_ajaxfile.php",
                    type: "post",
                    dataType: 'json',
                    delay: 100,
                    data: function (params) {
                        return {
                            searchTerm: params.term // search term
                        };
                    },
                    processResults: function (response) {
                        return {
                            results: response
                        };
                    },
                    cache: true
                }
            });
        });

    </script>


    </body>

    </html>

<?php } ?>