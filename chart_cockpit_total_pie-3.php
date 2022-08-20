<?php

include("config/connect_db.php");

//$doc_date = substr($_POST['doc_date'], 6, 4) . "/" . substr($_POST['doc_date'], 3, 2) . "/" . substr($_POST['doc_date'], 0, 2);
$month = $_POST['month'];
$year = $_POST['year'];

$sql_curr_month = " SELECT * FROM ims_month where month = '" . $month . "'";

$stmt_curr_month = $conn->prepare($sql_curr_month);
$stmt_curr_month->execute();
$MonthCurr = $stmt_curr_month->fetchAll();
foreach ($MonthCurr as $row_curr) {
    $month_name = $row_curr["month_name"];
}

$sql_branch = " SELECT * FROM ims_branch where branch = '" . $_POST["branch"] . "'";
$stmt_branch = $conn->prepare($sql_branch);
$stmt_branch->execute();
$BranchRecords = $stmt_branch->fetchAll();
foreach ($BranchRecords as $rows) {
    $branch_name = $rows["branch_name"];
}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta date="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
    <script src="js/jquery-3.6.0.js"></script>
    <script src="js/chartjs-2.9.0.js"></script>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="fontawesome/css/font-awesome.css">
    <title>สงวนออโต้คาร์</title>
    <style>

        body {
            width: 800px;
            margin: 3rem auto;
        }

        #chart-container {
            width: 100%;
            height: auto;
        }
    </style>

    <style>
        p.number {
            text-align-last: right;
        }
    </style>
</head>

<body onload="showGraph_Monthly();showGraph_Tires_Brand_Monthly();showGraph_Part_Monthly();">
<div class="card">
    <div class="card-header bg-success text-white">
        <i class="fa fa-bar-chart" aria-hidden="true"></i> กราฟแสดงยอดขาย เดือน <?php echo $month_name . " " . $year; ?>
        <?php echo $branch_name; ?>
    </div>
    <input type="hidden" name="month" id="month" value="<?php echo $month; ?>">
    <input type="hidden" name="year" id="year" value="<?php echo $year; ?>">
    <input type="hidden" name="branch" id="branch" value="<?php echo $_POST["branch"]; ?>">
    <input type="hidden" name="branch_name" id="branch_name" class="form-control" value="<?php echo $branch_name; ?>">
    <div class="card-body">
        <div id="chart-container">
            <canvas id="graphCanvas_Monthly"></canvas>
        </div>

    </div>
</div>


<?php
include("display_data_cockpit_detail_grp_monthly.php");
?>


<div class="card">
    <div class="card-header bg-success text-white">
        <i class="fa fa-bar-chart" aria-hidden="true"></i> กราฟแสดงยอดขายยางแต่ละยี่ห้อ
        เดือน <?php echo $month_name . " " . $year; ?>
        <?php echo $branch_name; ?>
    </div>
    <input type="hidden" name="month" id="month" value="<?php echo $month; ?>">
    <input type="hidden" name="year" id="year" value="<?php echo $year; ?>">
    <input type="hidden" name="branch" id="branch" value="<?php echo $_POST["branch"]; ?>">
    <input type="hidden" name="branch_name" id="branch_name" class="form-control" value="<?php echo $branch_name; ?>">
    <div class="card-body">
        <div id="chart-container">
            <canvas id="graphCanvas_Brand_Monthly"></canvas>
        </div>
    </div>

    <div class="card-body">
        <table id="example" class="display table table-striped table-bordered"
               cellspacing="0" width="100%">
            <thead>
            <tr>
                <th>ยี่ห้อ</th>
                <th>จำนวน (เส้น)</th>
                <th>ยอดขาย</th>
            </tr>
            </thead>
            <tfoot>
            <tr>
                <th>ยี่ห้อ</th>
                <th>จำนวน (เส้น)</th>
                <th>ยอดขาย</th>
            </tr>
            </tfoot>
            <tbody>
            <?php
            $total = 0;
            $total_sale = 0;
            $sql_brand = " SELECT BRN_CODE,BRN_NAME,SKU_CAT,ICCAT_NAME,sum(CAST(TRD_QTY AS DECIMAL(10,2))) as  TRD_QTY,sum(CAST(TRD_G_KEYIN AS DECIMAL(10,2))) as TRD_G_KEYIN 
 FROM ims_product_sale_cockpit
 WHERE SKU_CAT IN ('2SAC01','2SAC02','2SAC03','2SAC02','2SAC04','2SAC05','2SAC06','2SAC07','2SAC08','2SAC09','2SAC10','2SAC11','2SAC12','2SAC13','2SAC14','2SAC15')
 AND DI_YEAR = '" . $year . "'
 AND DI_MONTH = '" . $month . "'
 AND BRANCH = '" . $branch . "'
 GROUP BY BRN_CODE,BRN_NAME,SKU_CAT,ICCAT_NAME
 ORDER BY SKU_CAT ";

            $statement_brand = $conn->query($sql_brand);
            $results_brand = $statement_brand->fetchAll(PDO::FETCH_ASSOC);

            foreach ($results_brand

            as $row_brand) { ?>

            <tr>
                <td><?php echo htmlentities($row_brand['BRN_NAME']); ?></td>
                <td><?php echo htmlentities(number_format($row_brand['TRD_QTY'], 2)); ?></td>
                <?php $total = $total + $row_brand['TRD_QTY']; ?>
                <td><p class="number"><?php echo htmlentities(number_format($row_brand['TRD_G_KEYIN'], 2)); ?></p></td>
                <?php $total_sale = $total_sale + $row_brand['TRD_G_KEYIN']; ?>
                <?php } ?>

            </tbody>
            <b><?php echo "รวม : ยางทั้งหมด  = " . number_format($total, 2) . " เส้น จำนวนเงินรวม = " . number_format($total_sale, 2) . " บาท " ?></b>
        </table>
    </div>

</div>


<div class="card">
    <div class="card-header bg-success text-white">
        <i class="fa fa-bar-chart" aria-hidden="true"></i> กราฟแสดงยอดขายอะไหล่
        เดือน <?php echo $month_name . " " . $year; ?>
        <?php echo $branch_name; ?>
    </div>
    <input type="hidden" name="month" id="month" value="<?php echo $month; ?>">
    <input type="hidden" name="year" id="year" value="<?php echo $year; ?>">
    <input type="hidden" name="branch" id="branch" value="<?php echo $_POST["branch"]; ?>">
    <input type="hidden" name="branch_name" id="branch_name" class="form-control" value="<?php echo $branch_name; ?>">
    <div class="card-body">
        <div id="chart-container">
            <canvas id="graphCanvas_Part_Monthly"></canvas>
        </div>
    </div>

    <div class="card-body">
        <table id="example" class="display table table-striped table-bordered"
               cellspacing="0" width="100%">
            <thead>
            <tr>
                <th>อะไหล่</th>
                <th>ยอดขาย</th>
            </tr>
            </thead>
            <tfoot>
            <tr>
                <th>อะไหล่</th>
                <th>ยอดขาย</th>
            </tr>
            </tfoot>
            <tbody>
            <?php
            $total = 0;
            $total_sale = 0;
            $sql_brand = " SELECT SKU_CAT,ICCAT_NAME,sum(CAST(TRD_QTY AS DECIMAL(10,2))) as  TRD_QTY,sum(CAST(TRD_G_KEYIN AS DECIMAL(10,2))) as TRD_G_KEYIN 
 FROM ims_product_sale_cockpit
 WHERE PGROUP = 'P2'
 AND DI_YEAR = '" . $year . "'
 AND DI_MONTH = '" . $month . "'
 AND BRANCH = '" . $branch . "'
 GROUP BY SKU_CAT,ICCAT_NAME
 ORDER BY SKU_CAT ";

            $statement_brand = $conn->query($sql_brand);
            $results_brand = $statement_brand->fetchAll(PDO::FETCH_ASSOC);

            foreach ($results_brand

            as $row_brand) { ?>

            <tr>
                <td><?php echo htmlentities($row_brand['ICCAT_NAME']); ?></td>
                <!--td><?php echo htmlentities(number_format($row_brand['TRD_QTY'], 2)); ?></td-->
                <!--?php $total = $total + $row_brand['TRD_QTY']; ?-->
                <td><p class="number"><?php echo htmlentities(number_format($row_brand['TRD_G_KEYIN'], 2)); ?></p></td>
                <?php $total_sale = $total_sale + $row_brand['TRD_G_KEYIN']; ?>
                <?php } ?>

            </tbody>
            <b><?php echo "รวม : อะไหล่ทั้งหมด จำนวนเงินรวม = " . number_format($total_sale, 2) . " บาท " ?></b>
        </table>
    </div>

</div>

<!--?php
include("display_data_cockpit_detail.php");
?-->


<script>

    function showGraph_Monthly() {
        {

            let month = $("#month").val();
            let year = $("#year").val();
            let branch = $("#branch").val();

            let backgroundColor = '#0a4dd3';
            let borderColor = '#46d5f1';

            let hoverBackgroundColor = '#a2a1a3';
            let hoverBorderColor = '#a2a1a3';

            let barColors = [
                "#0a4dd3",
                "#c21bf8",
                "#f3661a",
                "#b91d47",
                "#00aba9",
                "#f81b61",
                "#fcae13"

            ];

            $.post("engine/chart_data_pie_monthly.php", {month: month, year: year, branch: branch}, function (data) {
                console.log(data);
                let label = [];
                let total = [];
                for (let i in data) {
                    label.push(data[i].pgroup_name);
                    total.push(data[i].TRD_G_KEYIN);
                    //alert(label);
                }

                new Chart("graphCanvas_Monthly", {
                    type: "pie",
                    data: {
                        labels: label,
                        datasets: [{
                            backgroundColor: barColors,
                            data: total
                        }]
                    },
                    options: {
                        title: {
                            display: true,
                            text: ""
                        }
                    }
                });

            })


        }
    }

</script>

<script>

    function showGraph_Tires_Brand_Monthly() {
        {

            let month = $("#month").val();
            let year = $("#year").val();
            let branch = $("#branch").val();

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

            $.post("engine/chart_data_pie_brand_monthly.php", {
                month: month,
                year: year,
                branch: branch
            }, function (data) {
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

                new Chart("graphCanvas_Brand_Monthly", {
                    type: "pie",
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
                            text: ""
                        }
                    }
                });

            })


        }
    }

</script>

<script>

    function showGraph_Part_Monthly() {
        {

            let month = $("#month").val();
            let year = $("#year").val();
            let branch = $("#branch").val();

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

            $.post("engine/chart_data_pie_part_monthly.php", {
                month: month,
                year: year,
                branch: branch
            }, function (data) {
                console.log(data);
                let label_name = [];
                let total = [];
                for (let i in data) {
                    label_name.push(data[i].ICCAT_NAME);
                    total.push(parseFloat(data[i].TRD_G_KEYIN).toFixed(2));
                    //alert(label);
                }

                new Chart("graphCanvas_Part_Monthly", {
                    type: "pie",
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
                            text: ""
                        }
                    }
                });

            })


        }
    }

</script>


</body>
</html>
