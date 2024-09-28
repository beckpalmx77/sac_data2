<?php
include('includes/Header.php');
include('config/connect_db.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {

    // Query to get unique months
    $sql_months = "SELECT DISTINCT DI_MONTH, DI_MONTH_NAME FROM ims_data_sale_sac_all ORDER BY CAST(DI_MONTH as unsigned)";
    $stmt_months = $conn->prepare($sql_months);
    $stmt_months->execute();
    $months = $stmt_months->fetchAll(PDO::FETCH_ASSOC);

// Query to get unique years
    $sql_years = "SELECT DISTINCT DI_YEAR FROM ims_data_sale_sac_all ORDER BY CAST(DI_YEAR as unsigned) DESC";
    $stmt_years = $conn->prepare($sql_years);
    $stmt_years->execute();
    $years = $stmt_years->fetchAll(PDO::FETCH_ASSOC);

// Query to get unique SKU categories
    $sql_sku = "SELECT DISTINCT SKU_CAT FROM ims_data_sale_sac_all ORDER BY SKU_CAT";
    $stmt_sku = $conn->prepare($sql_sku);
    $stmt_sku->execute();
    $sku_categories = $stmt_sku->fetchAll(PDO::FETCH_ASSOC);

    // Query to get unique Sale Name
    $sql_sale = "SELECT DISTINCT SALE_NAME FROM ims_data_sale_sac_all ORDER BY SALE_NAME";
    $stmt_sale = $conn->prepare($sql_sale);
    $stmt_sale->execute();
    $sale_name = $stmt_sale->fetchAll(PDO::FETCH_ASSOC);

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
                                            <div class="col-md-12">
                                                <div class="panel">
                                                    <div class="panel-body d-flex align-items-center">
                                                        <div class="form-group mr-3">
                                                            <label for="monthSelectStart">เริ่ม</label>
                                                            <select id="monthSelectStart" class="form-control">
                                                                <option value="">-- เลือกเดือน --</option>
                                                                <?php foreach ($months as $month): ?>
                                                                    <option value="<?php echo $month['DI_MONTH']; ?>">
                                                                        <?php echo $month['DI_MONTH_NAME']; ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>

                                                        <div class="form-group mr-3">
                                                            <label for="monthSelectTo">ถึง</label>
                                                            <select id="monthSelectTo" class="form-control">
                                                                <option value="">-- เลือกเดือน --</option>
                                                                <?php foreach ($months as $month): ?>
                                                                    <option value="<?php echo $month['DI_MONTH']; ?>">
                                                                        <?php echo $month['DI_MONTH_NAME']; ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>

                                                        <div class="form-group mr-3">
                                                            <label for="yearSelect">เลือกปี</label>
                                                            <select id="yearSelect" class="form-control">
                                                                <option value="">-- เลือกปี --</option>
                                                                <?php foreach ($years as $year): ?>
                                                                    <option value="<?php echo $year['DI_YEAR']; ?>">
                                                                        <?php echo $year['DI_YEAR']; ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>

                                                        <div class="form-group mr-3">
                                                            <label for="saleSelect">เลือกชื่อ Sale</label>
                                                            <select id="saleSelect" class="form-control">
                                                                <option value="">-- เลือก Sale --</option>
                                                                <?php foreach ($sale_name as $sale): ?>
                                                                    <option value="<?php echo $sale['SALE_NAME']; ?>">
                                                                        <?php echo $sale['SALE_NAME']; ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>

                                                        <div class="form-group mr-3">
                                                            <label for="skuCatSelect">เลือก SKU CAT</label>
                                                            <select id="skuCatSelect" class="form-control">
                                                                <option value="">-- เลือก SKU --</option>
                                                                <?php foreach ($sku_categories as $sku): ?>
                                                                    <option value="<?php echo $sku['SKU_CAT']; ?>">
                                                                        <?php echo $sku['SKU_CAT']; ?>
                                                                    </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                        </div>


                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4 col-md-offset-2">
                                                <div class="panel">
                                                    <div class="panel-body">
                                                        <button id="fetchData" class="btn btn-primary">แสดงกราฟ</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>


                                        <div class="row">
                                            <div class="col-md-4 col-md-offset-2">
                                                <div class="panel">
                                                    <div class="panel-body">
                                                        <div id="chart-container">
                                                            <canvas id="mycanvas" width="400" height="400"></canvas>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
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
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
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
    <link href="vendor/date-picker-1.9/css/bootstrap-datepicker.css" rel="stylesheet"/>

    <script src="js/MyFrameWork/framework_util.js"></script>

    <script src="js/util.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script>
        $(document).ready(function () {
            $('#fetchData').click(function () {
                const month_start = $('#monthSelectStart').val();
                const month_to = $('#monthSelectTo').val();
                const year = $('#yearSelect').val();
                const skuCat = $('#skuCatSelect').val();
                let sale_name = $('#saleSelect').val();
                let str_sale_name = "";
                if (sale_name !== '') {
                    str_sale_name = "ชื่อ sale " + sale_name;
                } else {
                    str_sale_name = "";
                }


                let label_name = skuCat + " เดือน " + month_start + " ถึง " + month_to + " ปี " + year + str_sale_name;

                if (!month_start || !month_to || !year || !skuCat ||) {
                    alert("กรุณาเลือกเดือน, ปี, และ SKU Category");
                    return;
                }

                $.ajax({
                    url: "engine/chart_sale_sac_001.php",
                    method: "GET",
                    data: {
                        month_start: month_start,
                        month_to: month_to,
                        year: year,
                        skuCat: skuCat,
                        sale_name: sale_name
                    },
                    dataType: "json",
                    success: function (data) {
                        console.log(data);
                        let DI_MONTH_NAME = [];
                        let SUM_TRD_QTY = [];

                        for (let i in data) {
                            DI_MONTH_NAME.push(data[i].DI_MONTH_NAME);
                            SUM_TRD_QTY.push(data[i].SUM_TRD_QTY);
                        }

                        let chartdata = {
                            labels: DI_MONTH_NAME,
                            datasets: [{
                                label: label_name,
                                backgroundColor: 'rgba(46,182,165,0.75)',
                                borderColor: 'rgba(76,195,199,0.75)',
                                hoverBackgroundColor: 'rgb(60,181,193)',
                                hoverBorderColor: 'rgb(45,193,188)',
                                data: SUM_TRD_QTY
                            }]
                        };

                        let ctx = $("#mycanvas");
                        new Chart(ctx, {
                            type: 'bar',
                            data: chartdata
                        });
                    },
                    error: function (data) {
                        console.error("Error:", data);
                    }
                });
            });
        });
    </script>


    </body>

    </html>

<?php } ?>