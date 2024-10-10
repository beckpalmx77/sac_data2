<?php include("config/connect_db.php"); ?>
<?php
if ($_POST['SALE_NAME'] === '-') {
    $sale_name = "*";
} else {
    $sale_name = $_POST['SALE_NAME'];
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
    <script src="js/jquery-3.6.0.js"></script>
    <!--script src="js/chartjs-2.9.0.js"></script-->
    <script src="js/chartjs-4.4.4.js"></script>
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="fontawesome/css/font-awesome.css">
    <title>สงวนออโต้คาร์</title>
    <style>
        body {
            width: 800px;
            margin: 3rem auto;
        }

        .chart-container {
            width: 100%;
            height: auto;
        }
    </style>
</head>

<body onload="DisplayGraph_Monthly();">
<input type="hidden" id="year" value="<?php echo $_POST['year']; ?>">
<input type="hidden" id="SALE_NAME" value="<?php echo $_POST['SALE_NAME']; ?>">
<input type="hidden" id="SKU_CAT" value="<?php echo $_POST['SKU_CAT']; ?>">
<div class="card">
    <div class="card-header bg-success text-white">
        <i class="fa fa-bar-chart" aria-hidden="true"></i> กราฟแสดงยอดขายยาง
        ปี <?php echo $_POST["year"] . " SALE [ " . $sale_name . " ]"; ?>
    </div>
    <div class="card-body">
        <?php for ($i = 1; $i <= 28; $i++): ?>
            <div class="chart-container">
                <canvas id="graphCanvas_Monthly<?php echo $i; ?>"></canvas>
            </div>
        <?php endfor; ?>
    </div>
</div>

<script>
    function DisplayGraph_Monthly() {
        const year = $("#year").val(), SALE_NAME = $("#SALE_NAME").val(), SKU_CAT = $("#SKU_CAT").val();
        $.post("engine/get_data_by_brand_list.php", {year, SKU_CAT, SALE_NAME}, function (data) {
            data.forEach((item, index) => showGraph_Monthly(index + 1, item.BRAND, SALE_NAME, SKU_CAT));
        });
    }

    const colors = [
        ['#f10d96', '#a16db6', '#cb037c', '#f372f3'],
        ['#5733f8', '#8358f8', '#4d0ff5', '#8452f8'],
        ['#fadb15', '#f8e358', '#c7af04', '#eec42e'],
        ['#14cd28', '#3ff573', '#14930a', '#33b41d'],
        ['#f65439', '#f87858', '#f84a2e', '#fc9053']
    ];

</script>

<script>
    function showGraph_Monthly(graph_number, BRAND, SALE_NAME, SKU_CAT) {
        const [bgColor, borderColor, hoverBgColor, hoverBorderColor] = colors[(graph_number - 1) % colors.length];
        const graphTarget = $(`#graphCanvas_Monthly${graph_number}`);

        $.post("engine/chart_data_sac_by_brand_monthly.php", {
            month: $("#month").val(),
            year: $("#year").val(),
            BRAND,
            SALE_NAME,
            SKU_CAT
        }, function (data) {
            const monthLabels = data.map(item => `${item.DI_MONTH_NAME} จำนวน ${item.TRD_QTY} `);
            const totals = data.map(item => parseFloat(item.TRD_AMOUNT_PRICE)); // จัดการให้ตัวเลขเป็นตัวเลขจริง

            new Chart(graphTarget, {
                type: 'bar',
                data: {
                    labels: monthLabels,
                    datasets: [{
                        label: `ยอดขายรายเดือน ${BRAND}`,
                        backgroundColor: bgColor,
                        borderColor: borderColor,
                        hoverBackgroundColor: hoverBgColor,
                        hoverBorderColor: hoverBorderColor,
                        data: totals
                    }]
                },
                options: {
                    scales: {
                        y: {
                            ticks: {
                                callback: function (value) {
                                    return value.toLocaleString('th-TH', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function (tooltipItem) {
                                    return tooltipItem.dataset.label + ': ' + tooltipItem.raw.toLocaleString('th-TH', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                }
                            }
                        }
                    }
                }
            });
        });
    }

</script>

<!--script>
    function showGraph_Monthly(graph_number, BRAND,SALE_NAME,SKU_CAT) {
        const [bgColor, borderColor, hoverBgColor, hoverBorderColor] = colors[(graph_number - 1) % colors.length];
        const graphTarget = $(`#graphCanvas_Monthly${graph_number}`);

        $.post("engine/chart_data_sac_by_brand_monthly.php", {
            month: $("#month").val(),
            year: $("#year").val(),
            BRAND,
            SALE_NAME,
            SKU_CAT
        }, function (data) {
            const monthLabels = data.map(item => `${item.DI_MONTH_NAME} จำนวน ${item.TRD_QTY} เส้น`);
            const totals = data.map(item => item.TRD_AMOUNT_PRICE);

            new Chart(graphTarget, {
                type: 'bar',
                data: {
                    labels: monthLabels,
                    datasets: [{
                        label: `ยอดขายรายเดือน ${BRAND}`,
                        backgroundColor: bgColor,
                        borderColor,
                        hoverBackgroundColor: hoverBgColor,
                        hoverBorderColor,
                        data: totals
                    }]
                }
            });
        });
    }
</script-->

</body>
</html>
