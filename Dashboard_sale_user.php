<?php
include('includes/Header.php');
include('util/month_util_eng.php');
include('util/month_util_tha.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {

    ?>

    <!DOCTYPE html>
    <html lang="th">
    
    <!-- Load DataTables FixedColumns extension -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/fixedcolumns/4.0.1/css/fixedColumns.dataTables.min.css"/>
    <script type="text/javascript" src="https://cdn.datatables.net/fixedcolumns/4.0.1/js/dataTables.fixedColumns.min.js"></script>

    <!-- Load Plotly library -->
    <script src="https://cdn.plot.ly/plotly-latest.min.js"></script>

    <style>
        p.number {
            text-align-last: right;
        }
    </style>

    <body id="page-top" onload="">
    <div id="wrapper">
        <?php
        include('includes/Side-Bar.php');
        ?>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <?php
                include('includes/Top-Bar.php');
                ?>
                <div class="container-fluid" id="container-wrapper">
                    <?php include('data_dashboard_sac_sale_cat_sm_brand-1.php') ?>
                </div>
                <br>
                <!--div class="container-fluid" id="container-wrapper">
                    <?php include('data_dashboard_sac_sale_cat_sm_brand-2.php') ?>
                </div-->
                <div class="container-fluid" id="container-wrapper">
                    <?php include('data_dashboard_sac_sale_cat_ml_brand-1.php') ?>
                </div>
                <!--div class="container-fluid" id="container-wrapper">
                    <?php include('data_dashboard_sac_sale_cat_ml_brand-2.php') ?>
                </div-->
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
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <link href='vendor/calendar/main.css' rel='stylesheet'/>
    <script src='vendor/calendar/main.js'></script>
    <script src='vendor/calendar/locales/th.js'></script>


    </body>

    </html>

<?php } ?>