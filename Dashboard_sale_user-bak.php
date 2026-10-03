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
    <script
            src="https://code.jquery.com/jquery-3.6.0.js"
            integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk="
            crossorigin="anonymous"></script>

    <script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>

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