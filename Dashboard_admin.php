<?php
include('includes/Header.php');
if (strlen($_SESSION['alogin']) == "") {
    header("Location: index.php");
} else {

    include("config/connect_db.php");
    require_once 'vendor/mobiledetect/mobiledetectlib/Mobile_Detect.php';
    $detect = new Mobile_Detect;

    $sql_curr_month = " SELECT * FROM ims_month where month = '" . date("n") . "'";
    $stmt_curr_month = $conn->prepare($sql_curr_month);
    $stmt_curr_month->execute();
    $MonthCurr = $stmt_curr_month->fetchAll();
    foreach ($MonthCurr as $row_curr) {
        $month_name = $row_curr["month_name"];
    }

    $sale_point = 1500000;
    $sale_point_text = '1,500,000 บาท';

    ?>

    <!DOCTYPE html>
    <html lang="th">
    <!--script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script-->
    <script
            src="https://code.jquery.com/jquery-3.6.0.js"
            integrity="sha256-H+K7U5CnXl1h5ywQfKtSj8PCmoN9aaq30gDh27Xc0jk="
            crossorigin="anonymous"></script>

    <script type="text/javascript" src="https://cdn.datatables.net/1.11.3/js/jquery.dataTables.min.js"></script>

    <style>
        p.number {
            text-align-last: right;
        }
    </style>

    <body id="page-top" onload="showGraph_Cockpit_Daily();showGraph_Cockpit_Monthly();showGraph_Tires_Brand();">
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
                    <div class="row">
                        <div class="col-md-6">
                            <div class="card">
                                <div class="card-header">
                                    สถิติ ยอดขายรายวัน Cockpit แต่ละสาขา วันที่
                                    <?php echo date("d/m/Y"); ?>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title">ปี <?php echo date("Y"); ?></h5>
                                    <canvas id="myChartDaily" width="200" height="200"></canvas>
                                </div>
                                <div class="card-body">
                                    <table id="example" class="display table table-striped table-bordered"
                                           cellspacing="0" width="100%">
                                        <thead>
                                        <tr>
                                            <th>สาขา</th>
                                            <th>ยอดขาย</th>
                                        </tr>
                                        </thead>
                                        <tfoot>
                                        <tr>
                                            <th>สาขา</th>
                                            <th>ยอดขาย</th>
                                        </tr>
                                        </tfoot>
                                        <tbody>
                                        <?php
                                        $date = date("d/m/Y");
                                        $total = 0;
                                        $sql_daily = "SELECT BRANCH,sum(CAST(TRD_G_KEYIN AS DECIMAL(10,2))) as  TRD_G_KEYIN
                                                      FROM ims_product_sale_cockpit 
                                                      WHERE DI_DATE = '" . $date . "'
                                                      GROUP BY  BRANCH
                                                      ORDER BY BRANCH";

                                        $statement_daily = $conn->query($sql_daily);
                                        $results_daily = $statement_daily->fetchAll(PDO::FETCH_ASSOC);

                                        foreach ($results_daily

                                        as $row_daily) { ?>

                                        <tr>
                                            <td><?php echo htmlentities($row_daily['BRANCH']); ?></td>
                                            <td>
                                                <p class="number"><?php echo htmlentities(number_format($row_daily['TRD_G_KEYIN'], 2)); ?></p>
                                            </td>
                                            <?php $total = $total + $row_daily['TRD_G_KEYIN']; ?>
                                            <?php } ?>

                                        </tbody>
                                        <?php echo "ยอดขายรวมทุกสาขา วันที่ " . $date . " = " . number_format($total, 2) . " บาท " ?>
                                    </table>
                                </div>


                                <div class="card-header">
                                    สถิติ ยอดขายรายวัน Cockpit แต่ละสาขา เดือน
                                    <?php echo $month_name . " " . date("Y"); ?>
                                    เป้าหมายยอดขายสาขาละ = <?php echo $sale_point_text ?>
                                </div>
                                <div class="card-body">
                                    <h5 class="card-title">ปี <?php echo date("Y"); ?></h5>
                                    <canvas id="myChartMonthly" width="200" height="200"></canvas>
                                </div>
                                <div class="card-body">
                                    <table id="example" class="display table table-striped table-bordered"
                                           cellspacing="0" width="100%">
                                        <thead>
                                        <tr>
                                            <th>สาขา</th>
                                            <th>ยอดขาย</th>
                                        </tr>
                                        </thead>
                                        <tfoot>
                                        <tr>
                                            <th>สาขา</th>
                                            <th>ยอดขาย</th>
                                        </tr>
                                        </tfoot>
                                        <tbody>
                                        <?php
                                        $date = date("d/m/Y");
                                        $total = 0;
                                        $sql_daily = "SELECT BRANCH,sum(CAST(TRD_G_KEYIN AS DECIMAL(10,2))) as  TRD_G_KEYIN
                                                      FROM ims_product_sale_cockpit 
                                                      WHERE DI_MONTH = '" . date("n") . "'
                                                      AND DI_YEAR = '" . date("Y") . "'
                                                      GROUP BY  BRANCH
                                                      ORDER BY BRANCH";

                                        $statement_daily = $conn->query($sql_daily);
                                        $results_daily = $statement_daily->fetchAll(PDO::FETCH_ASSOC);

                                        foreach ($results_daily

                                        as $row_daily) { ?>

                                        <tr>
                                            <td><?php echo htmlentities($row_daily['BRANCH']); ?></td>
                                            <td>
                                                <?php $precent_sale = ($row_daily['TRD_G_KEYIN'] / $sale_point) * 100;
                                                $total_remain = $sale_point - $row_daily['TRD_G_KEYIN'];
                                                $precent_total_remain = ($total_remain / $sale_point) * 100;
                                                $data = "style='width: " . $precent_sale . "%'";
                                                ?>
                                                <p class="number"><?php echo htmlentities(number_format($row_daily['TRD_G_KEYIN'], 2)); ?></p>

                                                <div class="progress">
                                                    <div class="progress-bar progress-bar-striped progress-bar-animated"
                                                         role="progressbar" <?php echo $data ?>
                                                         aria-valuenow="<?php echo $precent_sale ?>" aria-valuemin="0"
                                                         aria-valuemax="100"><?php echo htmlentities(number_format($precent_sale, 2)) . "%" ?>
                                                    </div>
                                                </div>

                                                <p class="number">
                                                    คิดเป็น <?php echo htmlentities(number_format($precent_sale, 2)) . " % จากเป้ายอดขาย"; ?></p>

                                                <p class="number">
                                                    เป้ายอดขายที่ต้องทำเพิ่ม
                                                    คือ <?php echo htmlentities(number_format($total_remain, 2))
                                                        . " หรือ " . htmlentities(number_format($precent_total_remain, 2)) . " %"; ?> </p>

                                            </td>
                                            <?php $total = $total + $row_daily['TRD_G_KEYIN']; ?>
                                            <?php } ?>

                                        </tbody>
                                        <?php echo "ยอดขายรวมทุกสาขา เดือน " . $month_name . " " . date("Y") . " = " . number_format($total, 2) . " บาท " ?>
                                        เป้าหมายยอดขายสาขาละ = <?php echo $sale_point_text ?>
                                    </table>
                                </div>

                            </div>
                        </div>

                        <?php include('display_chart_tires_brand_admin.php'); ?>

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
    <script src="js/chart.js"></script>

    <link href='vendor/calendar/main.css' rel='stylesheet'/>
    <script src='vendor/calendar/main.js'></script>
    <script src='vendor/calendar/locales/th.js'></script>

    <script>
        myVar = setInterval(function () {
            window.location.reload(true);
        }, 100000);
    </script>


    <script>

        $(document).ready(function () {
            /*

            GET_DATA("ims_order_master", "1");
            GET_DATA("ims_product", "2");
            GET_DATA("ims_customer_ar", "3");
            GET_DATA("ims_supplier", "4");

            setInterval(function () {
                GET_DATA("ims_order_master", "1");
                GET_DATA("ims_product", "2");
                GET_DATA("ims_customer_ar", "3");
                GET_DATA("ims_supplier", "4");
            }, 3000);

             */
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

    <script>

        function showGraph_Tires_Brand() {
            {

                let barColors = [
                    "#0a4dd3",
                    "#17c024",
                    "#f3661a",
                    "#f81b61",
                    "#0c3f10",
                    "#1da5f2",
                    "#0e0b71",
                    "#e9e207",
                    "#07e9d8",
                    "#b91d47",
                    "#af43f5",
                    "#00aba9",
                    "#fcae13",
                    "#1d7804",
                    "#1a8cec",
                    "#50e310",
                    "#fa6ae4"
                ];

                $.post("engine/chart_data_pie_tires_brand.php", {doc_date: "1", branch: "2"}, function (data) {
                    console.log(data);
                    let label = [];
                    let label_name = [];
                    let total = [];
                    for (let i in data) {
                        label.push(data[i].BRN_CODE);
                        label_name.push(data[i].BRN_NAME);
                        total.push(parseFloat(data[i].TRD_G_KEYIN).toFixed(2));
                        //alert(label);
                    }

                    new Chart("myChart2", {
                        type: "doughnut",
                        data: {
                            labels: label_name,
                            datasets: [{
                                backgroundColor: barColors,
                                data: total
                            }]
                        },
                        options: {
                            title: {
                                display: true,
                                text: "-"
                            }
                        }
                    });

                })


            }
        }

    </script>

    <script>
        function showGraph_Cockpit_Daily() {
            {

                //let data_date = $("#data_date").val();

                let d = new Date();
                let day = d.getDay();
                let backgroundColor = '';
                let borderColor = '';
                let hoverBackgroundColor = '';
                let hoverBorderColor = '';

                switch (day) {
                    case 0:
                        backgroundColor = '#ff1f40';
                        borderColor = '#46d5f1';
                        hoverBackgroundColor = '#d0052e';
                        hoverBorderColor = '#a2a1a3';
                        break;
                    case 1:
                        backgroundColor = '#e9e207';
                        borderColor = '#46d5f1';
                        hoverBackgroundColor = '#d0ca05';
                        hoverBorderColor = '#a2a1a3';
                        break;
                    case 2:
                        backgroundColor = '#fc53f3';
                        borderColor = '#46d5f1';
                        hoverBackgroundColor = '#e933ff';
                        hoverBorderColor = '#a2a1a3';
                        break;
                    case 3:
                        backgroundColor = '#41f31c';
                        borderColor = '#46d5f1';
                        hoverBackgroundColor = '#28d904';
                        hoverBorderColor = '#a2a1a3';
                        break;
                    case 4:
                        backgroundColor = '#f3941f';
                        borderColor = '#46d5f1';
                        hoverBackgroundColor = '#ef8502';
                        hoverBorderColor = '#a2a1a3';
                        break;
                    case 5:
                        backgroundColor = '#24c9f1';
                        borderColor = '#46d5f1';
                        hoverBackgroundColor = '#04a4cb';
                        hoverBorderColor = '#a2a1a3';
                        break;
                    case 6:
                        backgroundColor = '#8341fd';
                        borderColor = '#46d5f1';
                        hoverBackgroundColor = '#6110fa';
                        hoverBorderColor = '#a2a1a3';
                        break;
                }

                $.post("engine/chart_data_cockpit_daily.php", {date: "2"}, function (data) {
                    console.log(data);
                    let branch = [];
                    let total = [];
                    for (let i in data) {
                        branch.push(data[i].BRANCH);
                        total.push(parseFloat(data[i].TRD_G_KEYIN).toFixed(2));
                    }

                    let chartdata = {
                        labels: branch,
                        datasets: [{
                            label: 'ยอดขายรายวัน รวม VAT (Daily)',
                            backgroundColor: backgroundColor,
                            borderColor: borderColor,
                            hoverBackgroundColor: hoverBackgroundColor,
                            hoverBorderColor: hoverBorderColor,
                            data: total
                        }]
                    };
                    let graphTarget = $('#myChartDaily');
                    let barGraph = new Chart(graphTarget, {
                        type: 'bar',
                        data: chartdata
                    })
                })
            }
        }

    </script>

    <script>
        function showGraph_Cockpit_Monthly() {
            {

                //let data_date = $("#data_date").val();

                let backgroundColor = '#285bfa';
                let borderColor = '#46d5f1';

                let hoverBackgroundColor = '#062ee8';
                let hoverBorderColor = '#a2a1a3';

                $.post("engine/chart_data_cockpit_monthly.php", {date: "2"}, function (data) {
                    console.log(data);
                    let branch = [];
                    let total = [];
                    for (let i in data) {
                        branch.push(data[i].BRANCH);
                        total.push(parseFloat(data[i].TRD_G_KEYIN).toFixed(2));
                    }

                    let chartdata = {
                        labels: branch,
                        datasets: [{
                            label: 'ยอดขายรายเดือน รวม VAT (Daily)',
                            backgroundColor: backgroundColor,
                            borderColor: borderColor,
                            hoverBackgroundColor: hoverBackgroundColor,
                            hoverBorderColor: hoverBorderColor,
                            data: total
                        }]
                    };
                    let graphTarget = $('#myChartMonthly');
                    let barGraph = new Chart(graphTarget, {
                        type: 'bar',
                        data: chartdata
                    })
                })
            }
        }

    </script>

    <script>

        $("#BtnSale").click(function () {
            document.forms['myform'].action = 'chart_cockpit_total_product_bar';
            document.forms['myform'].target = '_blank';
            document.forms['myform'].submit();
            return true;
        });

    </script>

    </body>

    </html>

<?php } ?>

