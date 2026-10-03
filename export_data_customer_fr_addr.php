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
                                        <div class="row">
                                            <div class="col-md-12 col-md-offset-2">
                                                <div class="panel">
                                                    <div class="panel-body">

                                                        <form id="from_data" method="post"
                                                              action="export_process/export_data_customer_addr.php"
                                                              enctype="multipart/form-data">

                                                            <div class="modal-body">

                                                                <div class="modal-body">
                                                                    <div class="form-group row">

                                                                        <div class="col-sm-3">
                                                                            <label for="ar_code"
                                                                                   class="control-label">รหัสลูกค้า</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="ar_code"
                                                                                   name="ar_code"
                                                                                   placeholder="ค้นหารหัสลูกค้า">
                                                                        </div>

                                                                        <div class="col-sm-3">
                                                                            <label for="ar_name"
                                                                                   class="control-label">ชื่อลูกค้า</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="ar_name"
                                                                                   name="ar_name"
                                                                                   placeholder="พิมพ์ชื่อลูกค้า...">
                                                                        </div>

                                                                        <div class="col-sm-3">
                                                                            <label for="province"
                                                                                   class="control-label">จังหวัด</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="province"
                                                                                   name="province"
                                                                                   placeholder="พิมพ์ชื่อจังหวัด...">
                                                                        </div>

                                                                        <div class="col-sm-3">
                                                                            <label for="phone"
                                                                                   class="control-label">เบอร์โทรศัพท์</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="phone"
                                                                                   name="phone"
                                                                                   placeholder="พิมพ์เบอร์โทรศัพท์...">
                                                                        </div>

                                                                    </div>

                                                                    <div class="form-group row">

                                                                        <div class="col-sm-3">
                                                                            <label for="slmn_code"
                                                                                   class="control-label">รหัสพนักงานขาย</label>
                                                                            <input type="text" class="form-control"
                                                                                   id="slmn_code"
                                                                                   name="slmn_code"
                                                                                   placeholder="พิมพ์ชื่อพนักงานขาย...">
                                                                        </div>

                                                                    </div>
                                                                </div>

                                                            </div>

                                                            <div class="modal-footer">
                                                                <input type="hidden" name="id" id="id"/>
                                                                <input type="hidden" name="save_status"
                                                                       id="save_status"/>
                                                                <input type="hidden" name="action" id="action"
                                                                       value=""/>
                                                                <button type="submit" class="btn btn-success"
                                                                        id="btnExport"> Export <i
                                                                            class="fa fa-check"></i>
                                                                </button>
                                                            </div>


                                                        </form>


                                                        <div id="result"></div>

                                                    </div>
                                                </div>
                                            </div>
                                            <!-- /.col-md-8 col-md-offset-2 -->
                                        </div>
                                        <!-- /.row -->

                                    </section>


                                </div>

                            </div>

                        </div>

                    </div>
                    <!--Row-->

                    <!-- Row -->

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

    <script src="vendor/select2/dist/js/select2.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">

    <!-- select2 css -->
    <link href='js/select2/dist/css/select2.min.css' rel='stylesheet' type='text/css'>

    <!-- select2 script -->
    <script src='js/select2/dist/js/select2.min.js'></script>

    <!-- Bootstrap Datepicker -->
    <script src="vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js"></script>
    <!-- Bootstrap Touchspin -->
    <script src="vendor/bootstrap-touchspin/js/jquery.bootstrap-touchspin.js"></script>
    <!-- ClockPicker -->
    <script src="vendor/clock-picker/clockpicker.js"></script>
    <!-- RuangAdmin Javascript -->
    <script src="js/myadmin.min.js"></script>
    <!-- Javascript for this page -->

    <script src="vendor/date-picker-1.9/js/bootstrap-datepicker.js"></script>
    <script src="vendor/date-picker-1.9/locales/bootstrap-datepicker.th.min.js"></script>
    <!--link href="vendor/date-picker-1.9/css/date_picker_style.css" rel="stylesheet"/-->
    <link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>

    <script src="js/MyFrameWork/framework_util.js"></script>

    <script src="js/util.js"></script>

    <script>
        $(document).ready(function () {
            $("#ar_name").autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: "model/get_customer_ADDRBOOK.php",
                        dataType: "json",
                        data: {term: request.term},
                        success: function (data) {
                            response(data.results);
                        }
                    });
                },
                minLength: 1
            });

            $("#province").autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: "model/get_province.php",
                        dataType: "json",
                        data: {term: request.term},
                        success: function (data) {
                            response(data.results);
                        }
                    });
                },
                minLength: 1
            });

            $("#slmn_code").autocomplete({
                source: function (request, response) {
                    $.ajax({
                        url: "model/get_salesman.php",
                        dataType: "json",
                        data: {term: request.term},
                        success: function (data) {
                            response(data.results);
                        }
                    });
                },
                minLength: 1,
                select: function (event, ui) {
                    var code = ui.item.value.split(' - ')[0];
                    $(this).val(code);
                    return false;
                }
            });
        });
    </script>

    </html>

<?php } ?>