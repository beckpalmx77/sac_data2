<?php
include('includes/Header.php');
include('config/connect_sqlserver.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {

    $sql_iccat = "SELECT ICCAT_CODE, ICCAT_NAME FROM ICCAT ORDER BY ICCAT_CODE";
    $query_iccat = $conn_sqlsvr->prepare($sql_iccat);
    $query_iccat->execute();
    $iccat_list = $query_iccat->fetchAll(PDO::FETCH_ASSOC);

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
                                            <div class="col-md-12">
                                                <div class="panel">
                                                    <div class="panel-body">

                                                        <form id="from_data" method="post"
                                                              action="export_process/export_data_product_prices.php"
                                                              enctype="multipart/form-data">

                                                            <div class="form-group row align-items-end">

                                                                <div class="form-group col-md-4">
                                                                    <label for="price_code"
                                                                           class="control-label">Select Price Code</label>
                                                                    <select id="price_code" name="price_code"
                                                                            class="form-control"
                                                                            data-live-search="true"
                                                                            title="Please select">
                                                                        <option>ALL</option>
                                                                        <option>SAC</option>
                                                                        <option>BTC</option>
                                                                        <option>COCKPIT</option>
                                                                    </select>
                                                                </div>

                                                                <div class="form-group col-md-2">
                                                                    <button type="submit" class="btn btn-success btn-block"
                                                                            id="btnExport"> Export <i
                                                                                class="fa fa-check"></i>
                                                                    </button>
                                                                </div>

                                                            </div>

                                                            <div class="form-group">
                                                                <label class="control-label">กลุ่มสินค้า (Product Group)</label>
                                                                <div class="form-check">
                                                                    <input class="form-check-input" type="checkbox" id="select_all" checked>
                                                                    <label class="form-check-label" for="select_all">เลือกทั้งหมด (Select All)</label>
                                                                </div>
                                                                <hr>
                                                                <div class="row">
                                                                    <?php
                                                                    $exclude_iccat = array('TATA-001', 'RR.เสื้อผ้า', 'TATA-006');
                                                                    foreach ($iccat_list as $row) {
                                                                        if (in_array($row['ICCAT_CODE'], $exclude_iccat)) continue;
                                                                        ?>
                                                                        <div class="col-md-3">
                                                                            <div class="form-check">
                                                                                <input class="form-check-input iccat-checkbox" type="checkbox" name="iccat_code[]" value="<?php echo $row['ICCAT_CODE']; ?>" id="iccat_<?php echo $row['ICCAT_CODE']; ?>" checked>
                                                                                <label class="form-check-label" for="iccat_<?php echo $row['ICCAT_CODE']; ?>">
                                                                                    <?php echo $row['ICCAT_CODE'] . " - " . $row['ICCAT_NAME']; ?>
                                                                                </label>
                                                                            </div>
                                                                        </div>
                                                                    <?php } ?>
                                                                </div>
                                                            </div>

                                                            <div class="modal-footer">
                                                                <input type="hidden" name="id" id="id"/>
                                                                <input type="hidden" name="save_status"
                                                                       id="save_status"/>
                                                                <input type="hidden" name="action" id="action"
                                                                       value=""/>
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
    <!-- Select2 -->
    <script src="vendor/select2/dist/js/select2.min.js"></script>
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
            let today = new Date();
            let doc_date = getDay2Digits(today) + "-" + getMonth2Digits(today) + "-" + today.getFullYear();
            $('#doc_date_start').val(doc_date);
            $('#doc_date_to').val(doc_date);

            $('#select_all').click(function () {
                $('.iccat-checkbox').prop('checked', this.checked);
            });

            $('.iccat-checkbox').click(function () {
                if ($('.iccat-checkbox:checked').length === $('.iccat-checkbox').length) {
                    $('#select_all').prop('checked', true);
                } else {
                    $('#select_all').prop('checked', false);
                }
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

    </body>

    </html>

<?php } ?>