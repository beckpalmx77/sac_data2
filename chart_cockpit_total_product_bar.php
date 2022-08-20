<?php

include("config/connect_db.php");

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

<body onload="DisplayGraph_Monthly();">
<div class="card">
    <div class="card-header bg-success text-white">
        <i class="fa fa-bar-chart" aria-hidden="true"></i> กราฟแสดงยอดขายยาง Cockpit
        <?php echo " ปี " . $_POST["year"]; ?>
    </div>

    <input type="hidden" name="year" id="year" class="form-control" value="<?php echo $_POST["year"]; ?>">

    <div class="card-body">

        <div id="chart-container">
            <canvas id="graphCanvas_Monthly1"></canvas>
        </div>

        <div id="chart-container">
            <canvas id="graphCanvas_Monthly2"></canvas>
        </div>

        <div id="chart-container">
            <canvas id="graphCanvas_Monthly3"></canvas>
        </div>

        <div id="chart-container">
            <canvas id="graphCanvas_Monthly4"></canvas>
        </div>

        <div id="chart-container">
            <canvas id="graphCanvas_Monthly5"></canvas>
        </div>

        <div id="chart-container">
            <canvas id="graphCanvas_Monthly6"></canvas>
        </div>

        <div id="chart-container">
            <canvas id="graphCanvas_Monthly7"></canvas>
        </div>

        <div id="chart-container">
            <canvas id="graphCanvas_Monthly8"></canvas>
        </div>

        <div id="chart-container">
            <canvas id="graphCanvas_Monthly9"></canvas>
        </div>

        <div id="chart-container">
            <canvas id="graphCanvas_Monthly10"></canvas>
        </div>

        <div id="chart-container">
            <canvas id="graphCanvas_Monthly11"></canvas>
        </div>

        <div id="chart-container">
            <canvas id="graphCanvas_Monthly12"></canvas>
        </div>

        <div id="chart-container">
            <canvas id="graphCanvas_Monthly13"></canvas>
        </div>

        <div id="chart-container">
            <canvas id="graphCanvas_Monthly14"></canvas>
        </div>

        <div id="chart-container">
            <canvas id="graphCanvas_Monthly15"></canvas>
        </div>

        <!--div id="chart-container">
            <canvas id="graphCanvas_Monthly16"></canvas>
        </div>

        <div id="chart-container">
            <canvas id="graphCanvas_Monthly17"></canvas>
        </div>

        <div id="chart-container">
            <canvas id="graphCanvas_Monthly18"></canvas>
        </div>

        <div id="chart-container">
            <canvas id="graphCanvas_Monthly18"></canvas>
        </div-->



    </div>
</div>

<script>
    function DisplayGraph_Monthly() {

        let p_group = "P1";
        let year = $("#year").val();

        $.post("engine/get_data_list.php", {year: year , p_group: p_group}, function (data) {
            console.log(data);
            let brn_name = [];
            let loop = 1;
            for (let i in data) {
                brn_name.push(data[i].BRN_NAME);
                showGraph_Monthly(loop, data[i].BRN_NAME);
                loop++;
            }
        });
    }
</script>

<script>
    function showGraph_Monthly(graph_number, brn_name) {

        let month = $("#month").val();
        let year = $("#year").val();

        let backgroundColor = '';
        let borderColor = '';
        let hoverBackgroundColor = '';
        let hoverBorderColor = '';

        let graphTarget = "";

        switch (graph_number) {
            case 1:
                backgroundColor = '#f10d96';
                borderColor = '#a16db6';
                hoverBackgroundColor = '#cb037c';
                hoverBorderColor = '#f372f3';
                graphTarget = $('#graphCanvas_Monthly1');
                break;
            case 2:
                backgroundColor = '#5733f8';
                borderColor = '#8358f8';
                hoverBackgroundColor = '#4d0ff5';
                hoverBorderColor = '#8452f8';
                graphTarget = $('#graphCanvas_Monthly2');
                break;
            case 3:
                backgroundColor = '#fadb15';
                borderColor = '#f8e358';
                hoverBackgroundColor = '#c7af04';
                hoverBorderColor = '#eec42e';
                graphTarget = $('#graphCanvas_Monthly3');
                break;
            case 4:
                backgroundColor = '#14cd28';
                borderColor = '#3ff573';
                hoverBackgroundColor = '#14930a';
                hoverBorderColor = '#33b41d';
                graphTarget = $('#graphCanvas_Monthly4');
                break;
            case 5:
                backgroundColor = '#f65439';
                borderColor = '#f87858';
                hoverBackgroundColor = '#f84a2e';
                hoverBorderColor = '#fc9053';
                graphTarget = $('#graphCanvas_Monthly5');
                break;
            case 6:
                backgroundColor = '#5733f8';
                borderColor = '#8358f8';
                hoverBackgroundColor = '#4d0ff5';
                hoverBorderColor = '#8452f8';
                graphTarget = $('#graphCanvas_Monthly6');
                break;
            case 7:
                backgroundColor = '#f10d96';
                borderColor = '#a16db6';
                hoverBackgroundColor = '#cb037c';
                hoverBorderColor = '#f372f3';
                graphTarget = $('#graphCanvas_Monthly7');
                break;
            case 8:
                backgroundColor = '#14cd28';
                borderColor = '#3ff573';
                hoverBackgroundColor = '#14930a';
                hoverBorderColor = '#33b41d';
                graphTarget = $('#graphCanvas_Monthly8');
                break;
            case 9:
                backgroundColor = '#fadb15';
                borderColor = '#f8e358';
                hoverBackgroundColor = '#c7af04';
                hoverBorderColor = '#eec42e';
                graphTarget = $('#graphCanvas_Monthly9');
                break;
            case 10:
                backgroundColor = '#c03cf8';
                borderColor = '#c371f6';
                hoverBackgroundColor = '#9220e7';
                hoverBorderColor = '#bf6bee';
                graphTarget = $('#graphCanvas_Monthly10');
                break;

            case 11:
                backgroundColor = '#5733f8';
                borderColor = '#8358f8';
                hoverBackgroundColor = '#4d0ff5';
                hoverBorderColor = '#8452f8';
                graphTarget = $('#graphCanvas_Monthly11');
                break;
            case 12:
                backgroundColor = '#f10d96';
                borderColor = '#a16db6';
                hoverBackgroundColor = '#cb037c';
                hoverBorderColor = '#f372f3';
                graphTarget = $('#graphCanvas_Monthly12');
                break;
            case 13:
                backgroundColor = '#f65439';
                borderColor = '#f87858';
                hoverBackgroundColor = '#f84a2e';
                hoverBorderColor = '#fc9053';
                graphTarget = $('#graphCanvas_Monthly13');
                break;
            case 14:
                backgroundColor = '#fadb15';
                borderColor = '#f8e358';
                hoverBackgroundColor = '#c7af04';
                hoverBorderColor = '#eec42e';
                graphTarget = $('#graphCanvas_Monthly14');
                break;
            case 15:
                backgroundColor = '#14cd28';
                borderColor = '#3ff573';
                hoverBackgroundColor = '#14930a';
                hoverBorderColor = '#33b41d';
                graphTarget = $('#graphCanvas_Monthly15');
                break;
            case 16:
                backgroundColor = '#5733f8';
                borderColor = '#8358f8';
                hoverBackgroundColor = '#4d0ff5';
                hoverBorderColor = '#8452f8';
                graphTarget = $('#graphCanvas_Monthly16');
                break;
            case 17:
                backgroundColor = '#f10d96';
                borderColor = '#a16db6';
                hoverBackgroundColor = '#cb037c';
                hoverBorderColor = '#f372f3';
                graphTarget = $('#graphCanvas_Monthly17');
                break;
            default:
                backgroundColor = '#f65439';
                borderColor = '#f87858';
                hoverBackgroundColor = '#f84a2e';
                hoverBorderColor = '#fc9053';
                graphTarget = $('#graphCanvas_Monthly18');
                break;

        }

        $.post("engine/chart_data_bar_brand_monthly.php", {
            month: month,
            year: year,
            brn_name: brn_name
        }, function (data) {
            console.log(data);
            let month = [];
            let total = [];
            for (let i in data) {
                month.push(data[i].DI_MONTH_NAME + " จำนวน " + data[i].TRD_QTY + " เส้น ");
                total.push(data[i].TRD_G_KEYIN);
            }

            let chartdata = {
                labels: month,
                datasets: [{
                    label: 'ยอดขายรายเดือน ' + brn_name,
                    backgroundColor: backgroundColor,
                    borderColor: borderColor,
                    hoverBackgroundColor: hoverBackgroundColor,
                    hoverBorderColor: hoverBorderColor,
                    data: total
                }]
            };

            let barGraph = new Chart(graphTarget, {
                type: 'bar',
                data: chartdata
            })
        })
    }

</script>

</body>
</html>