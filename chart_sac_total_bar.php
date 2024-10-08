<?php

include("config/connect_db.php");

$month_name = "";

$sql_month = " SELECT * FROM ims_month where month = '" . $_POST["month"] . "'";
$stmt_month = $conn->prepare($sql_month);
$stmt_month->execute();
$MonthRecords = $stmt_month->fetchAll();
foreach ($MonthRecords as $row) {
    $month_name = $row["month_name"];
}

$sale_name = $_POST["SALE_NAME"];

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
            width: 620px;
            margin: 3rem auto;
        }

        #chart-container {
            width: 100%;
            height: auto;
        }
    </style>
</head>

<body onload="showGraph_Daily();showGraph_Monthly();">
<div class="card">
    <div class="card-header bg-success text-white">
        <i class="fa fa-bar-chart" aria-hidden="true"></i> กราฟแสดงยอดขาย รายวัน - รายเดือน
        <?php echo $sale_name . " เดือน " . $month_name . " ปี " . $_POST["year"]; ?>
    </div>
    <input type="hidden" name="month" id="month" value="<?php echo $_POST["month"]; ?>">
    <!--input type="text" name="month_name" id="month_name" class="form-control" value="<?php echo $month_name; ?>"-->
    <input type="hidden" name="year" id="year" class="form-control" value="<?php echo $_POST["year"]; ?>">

    <input type="hidden" name="SALE" id="SALE" value="<?php echo $_POST["SALE_NAME"]; ?>">
    <input type="hidden" name="SALE_NAME" id="SALE_NAME" class="form-control" value="<?php echo $sale_name; ?>">

    <div class="card-body">

        <div id="chart-container">
            <canvas id="graphCanvas_Daily"></canvas>
        </div>

        <div id="chart-container">
            <canvas id="graphCanvas_Monthly"></canvas>
        </div>
    </div>
</div>


<script>
    function showGraph_Monthly() {
        {

            let month = $("#month").val();
            let year = $("#year").val();
            let SALE_NAME = $("#SALE_NAME").val();

            let backgroundColor = '#bd58fa';
            let borderColor = '#46d5f1';

            let hoverBackgroundColor = '#a2a1a3';
            let hoverBorderColor = '#a2a1a3';

            $.post("engine/chart_data_sac_monthly.php", {month: month, year: year, SALE_NAME: SALE_NAME}, function (data) {
                console.log(data);
                let month = [];
                let total = [];
                for (let i in data) {
                    month.push(data[i].DI_MONTH_NAME);
                    total.push(data[i].TRD_AMOUNT_PRICE);
                }

                let chartdata = {
                    labels: month,
                    datasets: [{
                        label: 'ยอดขายรายเดือน รวม VAT (Monthly)',
                        backgroundColor: backgroundColor,
                        borderColor: borderColor,
                        hoverBackgroundColor: hoverBackgroundColor,
                        hoverBorderColor: hoverBorderColor,
                        data: total
                    }]
                };
                let graphTarget = $('#graphCanvas_Monthly');
                let barGraph = new Chart(graphTarget, {
                    type: 'bar',
                    data: chartdata
                })
            })
        }
    }

    function showGraph_Daily() {
        {

            let month = $("#month").val();
            let year = $("#year").val();
            let SALE_NAME = $("#SALE_NAME").val();

            let backgroundColor = '#0a4dd3';
            let borderColor = '#46d5f1';

            let hoverBackgroundColor = '#a2a1a3';
            let hoverBorderColor = '#a2a1a3';

            $.post("engine/chart_data_sac_daily.php", {month: month, year: year, SALE_NAME: SALE_NAME}, function (data) {
                console.log(data);
                let date = [];
                let total = [];
                for (let i in data) {
                    date.push(data[i].DI_DAY);
                    total.push(data[i].TRD_AMOUNT_PRICE);
                }

                let chartdata = {
                    labels: date,
                    datasets: [{
                        label: 'ยอดขายรายวัน รวม VAT (Daily)',
                        backgroundColor: backgroundColor,
                        borderColor: borderColor,
                        hoverBackgroundColor: hoverBackgroundColor,
                        hoverBorderColor: hoverBorderColor,
                        data: total
                    }]
                };
                let graphTarget = $('#graphCanvas_Daily');
                let barGraph = new Chart(graphTarget, {
                    type: 'bar',
                    data: chartdata
                })
            })
        }
    }

</script>
</body>
</html>
