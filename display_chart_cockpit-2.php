<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index");
} else {

    include("config/connect_db.php");


    $doc_date = "";
    $sql_month = " SELECT * FROM ims_month ";
    $stmt_month = $conn->prepare($sql_month);
    $stmt_month->execute();
    $MonthRecords = $stmt_month->fetchAll();

    $sql_year = " SELECT DISTINCT(DI_YEAR) AS DI_YEAR
 FROM ims_product_sale_cockpit WHERE DI_YEAR >= 2019
 order by DI_YEAR desc ";
    $stmt_year = $conn->prepare($sql_year);
    $stmt_year->execute();
    $YearRecords = $stmt_year->fetchAll();

    $sql_branch = " SELECT * FROM ims_branch ";
    $stmt_branch = $conn->prepare($sql_branch);
    $stmt_branch->execute();
    $BranchRecords = $stmt_branch->fetchAll();


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

                                                        <form id="myform" name="myform" action="engine/chart_data_pie_daily.php" method="post">

                                                                 <div class="row">
                                                                    <div class="col-sm-12">

                                                                                <label for="doc_date"
                                                                                       class="control-label">วันที่ :</label>
                                                                                <input type="text" class="form-control"
                                                                                       id="doc_date"
                                                                                       name="doc_date"
                                                                                       required="required"
                                                                                       readonly="true"
                                                                                       placeholder="วันที่">
                                                                                <label for="branch">เลือกสาขา :</label>
                                                                                <select name="branch" id="branch" class="form-control" required>
                                                                                    <?php foreach ($BranchRecords as $row) { ?>
                                                                                        <option value="<?php echo $row["branch"]; ?>">
                                                                                            <?php echo $row["branch_name"]; ?>
                                                                                        </option>
                                                                                    <?php } ?>
                                                                                </select>
                                                                                <br>
                                                                                <div class="row">
                                                                                    <div class="col-sm-12">
                                                                                        <button type="button" id="BtnSale" name="BtnSale" class="btn btn-primary mb-3">แสดง
                                                                                            Chart ยอดขาย
                                                                                            รายวัน
                                                                                        </button>
                                                                                    </div>
                                                                        </div>
                                                        </form>
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

    <script>

        $("#BtnSale").click(function () {
            let doc_date = $("#doc_date").val();
            if (doc_date !== '') {
                document.forms['myform'].action = 'chart_cockpit_total_pie';
                document.forms['myform'].target = '_blank';
                document.forms['myform'].submit();
                return true;
            } else {
                alert("กรุณาเลือกวันที่");
            }
        });

    </script>

    <script src="js/util.js"></script>

    <script>
        $(document).ready(function () {
            let today = new Date();
            let doc_date = getDay2Digits(today) + "-" + getMonth2Digits(today) + "-" + today.getFullYear();
            $('#doc_date').val(doc_date);
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

    </body>

    </html>

<?php } ?>