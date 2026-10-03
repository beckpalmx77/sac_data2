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
                <div class="container-fluid" id="container-wrapper">
                    <div class="row mb-3">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-uppercase mb-1">All Order
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><p class="text-success"
                                                                                                   id="Text1"></p></div>
                                            <div class="mt-2 mb-0 text-muted text-xs">
                                                <span class="text-success mr-2"><i
                                                            class="fa fa-arrow-up"></i></span>
                                                <span>Since last month</span>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-shopping-cart fa-2x text-success"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Earnings (Annual) Card Example -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-uppercase mb-1">Total Product
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><p class="text-success"
                                                                                                   id="Text2"></p></div>
                                            <div class="mt-2 mb-0 text-muted text-xs">
                                                <span class="text-success mr-2"><i
                                                            class="fas fa-arrow-up"></i> 12%</span>
                                                <span>Since last years</span>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-box fa-2x text-primary"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- New User Card Example -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-uppercase mb-1">Total Customer
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><p class="text-success"
                                                                                                   id="Text3"></p></div>
                                            <div class="mt-2 mb-0 text-muted text-xs">
                                                <span class="text-success mr-2"><i
                                                            class="fas fa-arrow-up"></i> 20.4%</span>
                                                <span>Since last month</span>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-users fa-2x text-info"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Pending Requests Card Example -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-uppercase mb-1">Total Supplier
                                            </div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><p class="text-success"
                                                                                                   id="Text4"></p></div>
                                            <div class="mt-2 mb-0 text-muted text-xs">
                                                <!--span class="text-danger mr-2"><i
                                                            class="fas fa-arrow-down"></i> 1.10%</span>
                                                    <span>Since yesterday</span-->
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-warehouse fa-2x text-warning"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <!--div class="row">
                        <div class="col-lg-12">
                            <div class="card mb-12">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                </div>
                                <div class="card-body">
                                    <section class="container-fluid">
                                        <div id='calendar'></div>
                                    </section>
                                </div>
                            </div>
                        </div>
                    </div-->
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
    <script src="vendor/chart.js/Chart.min.js"></script>
    <script src="js/chart/chart-area-demo.js"></script>

    <link href='vendor/calendar/main.css' rel='stylesheet'/>
    <script src='vendor/calendar/main.js'></script>
    <script src='vendor/calendar/locales/th.js'></script>

    <script>

        $(document).ready(function () {

            GET_DATA("ims_order_master", "1");
            GET_DATA("ims_product", "2");
            GET_DATA("ims_customer", "3");
            GET_DATA("ims_supplier", "4");

            setInterval(function () {
                GET_DATA("ims_order_master", "1");
                GET_DATA("ims_product", "2");
                GET_DATA("ims_customer", "3");
                GET_DATA("ims_supplier", "4");
            }, 3000);
        });

    </script>

    <script>

        function GET_DATA(table_name, idx) {
            let input_text = document.getElementById("Text" + idx);
            let action = "GET_COUNT_RECORDS";
            let formData = {action: action, table_name: table_name};
            $.ajax({
                type: "POST",
                url: 'model/manage_general_data.php',
                data: formData,
                success: function (response) {
                    input_text.innerHTML = response;
                },
                error: function (response) {
                    alertify.error("error : " + response);
                }
            });
        }

    </script>


    </body>

    </html>

<?php } ?>

