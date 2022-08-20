<?php

include("config/connect_db.php");

//$doc_date = substr($_POST['doc_date'], 6, 4) . "/" . substr($_POST['doc_date'], 3, 2) . "/" . substr($_POST['doc_date'], 0, 2);
$doc_date = $_POST['doc_date'];
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

<body onload="showGraph_Daily();graphCanvas_Brand_Daily()">
<div class="card">
    <div class="card-header bg-success text-white">
        <i class="fa fa-bar-chart" aria-hidden="true"></i> กราฟแสดงยอดขาย วันที่ <?php echo $_POST["doc_date"]; ?>
        <?php echo $branch_name; ?>
    </div>
    <input type="hidden" name="doc_date" id="doc_date" value="<?php echo $doc_date; ?>">
    <input type="hidden" name="branch" id="branch" value="<?php echo $_POST["branch"]; ?>">
    <input type="hidden" name="branch_name" id="branch_name" class="form-control" value="<?php echo $branch_name; ?>">
    <div class="card-body">
        <div id="chart-container">
            <canvas id="graphCanvas_Daily"></canvas>
        </div>
    </div>

</div>

<div class="card">
    <div class="card-header bg-success text-white">
        <i class="fa fa-bar-chart" aria-hidden="true"></i> กราฟแสดงยอดขายยางตามยี่ห้อ วันที่ <?php echo $_POST["doc_date"]; ?>
        <?php echo $branch_name; ?>
    </div>
    <input type="hidden" name="doc_date" id="doc_date" value="<?php echo $doc_date; ?>">
    <input type="hidden" name="branch" id="branch" value="<?php echo $_POST["branch"]; ?>">
    <input type="hidden" name="branch_name" id="branch_name" class="form-control" value="<?php echo $branch_name; ?>">
    <div class="card-body">
        <div id="chart-container">
            <canvas id="graphCanvas_Brand_Daily"></canvas>
        </div>
    </div>

</div>

<?php
include("display_data_cockpit_detail_grp.php");
include("display_data_cockpit_detail.php");
?>


<script>

    function showGraph_Daily() {
        {

            let doc_date = $("#doc_date").val();
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

            $.post("engine/chart_data_pie_daily.php", {doc_date: doc_date ,branch: branch }, function (data) {
                console.log(data);
                let label = [];
                let total = [];
                for (let i in data) {
                    label.push(data[i].pgroup_name);
                    total.push(data[i].TRD_G_KEYIN);
                    //alert(label);
                }

                new Chart("graphCanvas_Daily", {
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

    function graphCanvas_Brand_Daily() {
        {

            let doc_date = $("#doc_date").val();
            let branch = $("#branch").val();

            let backgroundColor = '#0a4dd3';
            let borderColor = '#46d5f1';

            let hoverBackgroundColor = '#a2a1a3';
            let hoverBorderColor = '#a2a1a3';

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

            $.post("engine/chart_data_pie_brand_daily.php", {doc_date: doc_date ,branch: branch }, function (data) {
                console.log(data);
                let label = [];
                let total = [];
                for (let i in data) {
                    label.push(data[i].BRN_NAME);
                    total.push(data[i].TRD_G_KEYIN);
                    //alert(label);
                }

                new Chart("graphCanvas_Brand_Daily", {
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



</body>
</html>
