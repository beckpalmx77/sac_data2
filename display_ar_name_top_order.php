<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index");
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

    //$myfile = fopen("param.txt", "w") or die("Unable to open file!");
    //fwrite($myfile, "month_num = " . $month_num . "| month_name" . $month_name . " | " . $sql_curr_month);
    //fclose($myfile);

    $sql_month = " SELECT * FROM ims_month ";
    $stmt_month = $conn->prepare($sql_month);
    $stmt_month->execute();
    $MonthRecords = $stmt_month->fetchAll();

    $sql_year = " SELECT DISTINCT(DI_YEAR) AS DI_YEAR
    FROM ims_data_sale_sac_all WHERE 1
    order by DI_YEAR desc ";
    $stmt_year = $conn->prepare($sql_year);
    $stmt_year->execute();
    $YearRecords = $stmt_year->fetchAll();

    $sql_sale_man = " SELECT DISTINCT(SALE_NAME) AS SALE_NAME
    FROM ims_data_sale_sac_all WHERE 1 
    order by SALE_NAME ";
    $stmt_sale_man = $conn->prepare($sql_sale_man);
    $stmt_sale_man->execute();
    $SaleManRecords = $stmt_sale_man->fetchAll();

    $sql_sku_cat = " SELECT DISTINCT(SKU_CAT) AS SKU_CAT
    FROM ims_data_sale_sac_all WHERE 1 
    order by SKU_CAT ";
    $stmt_sku_cat = $conn->prepare($sql_sku_cat);
    $stmt_sku_cat->execute();
    $skuRecords = $stmt_sku_cat->fetchAll();



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
                                        <div class="row">
                                            <div class="col-md-12 col-md-offset-2">
                                                <div class="panel">
                                                    <div class="panel-body">

                                                        <form id="myform" name="myform"
                                                              action="engine/chart_data_daily.php" method="post">

                                                            <div class="row">
                                                                <div class="col-sm-12">

                                                                    <label for="month">เลือกเดือน :</label>
                                                                    <select name="month" id="month" class="form-control"
                                                                            required>
                                                                        <option value="-"
                                                                                selected>-</option>
                                                                        <?php foreach ($MonthRecords as $row) { ?>
                                                                            <option value="<?php echo $row["month"]; ?>">
                                                                                <?php echo $row["month_name"]; ?>
                                                                            </option>
                                                                        <?php } ?>
                                                                    </select>
                                                                    <label for="year">เลือกปี :</label>
                                                                    <select name="year" id="year" class="form-control"
                                                                            required>
                                                                        <option value="-"
                                                                                selected>-</option>
                                                                        <?php foreach ($YearRecords as $row) { ?>
                                                                            <option value="<?php echo $row["DI_YEAR"]; ?>">
                                                                                <?php echo $row["DI_YEAR"]; ?>
                                                                            </option>
                                                                        <?php } ?>
                                                                    </select>
                                                                    <label for="SALE_NAME">เลือก SALE :</label>
                                                                    <select name="SALE_NAME" id="SALE_NAME"
                                                                            class="form-control" required>
                                                                        <option value="-">-</option>
                                                                        <?php foreach ($SaleManRecords as $row) { ?>
                                                                            <option value="<?php echo $row["SALE_NAME"]; ?>">
                                                                                <?php echo $row["SALE_NAME"]; ?>
                                                                            </option>
                                                                        <?php } ?>
                                                                    </select>

                                                                    <br>
                                                                    <div class="row">
                                                                        <div class="col-sm-12">
                                                                            <button type="button" id="BtnSale"
                                                                                    name="BtnSale"
                                                                                    class="btn btn-primary mb-3">แสดงยอดขาย
                                                                            </button>
                                                                        </div>
                                                                    </div>

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
            document.forms['myform'].action = 'show_data_ar_top_order';
            document.forms['myform'].target = '_blank';
            document.forms['myform'].submit();
            return true;
        });

    </script>

    </body>

    </html>

<?php } ?>