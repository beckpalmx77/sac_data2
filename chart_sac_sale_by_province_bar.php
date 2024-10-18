<?php include("config/connect_db.php"); ?>
<?php

$Cond_Query = "";

$year = $_POST['year'];
$SKU_CAT = $_POST['SKU_CAT'];
$TRD_PROVINCE = $_POST['TRD_PROVINCE'];


$sql_count = "SELECT COUNT(DISTINCT TRD_PROVINCE) as province
              FROM ims_data_sale_sac_all
              WHERE SKU_CAT = :sku_cat 
              AND DI_YEAR = :year";

// เขียนค่าลงในไฟล์สำหรับตรวจสอบ
/*
$myfile = fopen("a-param-brn.txt", "w") or die("Unable to open file!");
fwrite($myfile, "Year = " . $year . " | SKU_CAT = " . $SKU_CAT . " | SALE_NAME = " . $sale_name . " | Cond_Query = " . $Cond_Query . " | SQL = " . $sql_count);
fclose($myfile);
*/

// เตรียมการคิวรีและผูกค่า
$stmt_count = $conn->prepare($sql_count);
$stmt_count->bindParam(':sku_cat', $SKU_CAT);
$stmt_count->bindParam(':year', $year);
$stmt_count->execute();

// ดึงผลลัพธ์
$row_count = $stmt_count->fetch(PDO::FETCH_ASSOC);

// แสดงจำนวนนจังหวัด

if ($row_count) {
    $province_count = $row_count["province"];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
    <script src="js/jquery-3.6.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!--script src="js/chartjs-4.4.4.js"></script-->
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
<input type="hidden" id="PROVINCE" value="<?php echo $_POST['PROVINCE']; ?>">
<input type="hidden" id="SKU_CAT" value="<?php echo $_POST['SKU_CAT']; ?>">
<div class="card">
    <div class="card-header bg-success text-white">
        <i class="fa fa-bar-chart" aria-hidden="true"></i> กราฟแสดงยอดขายยาง
        ปี <?php echo $_POST["year"] . " ประเภท [ " . $SKU_CAT . " ]"; ?>
    </div>
    <div class="card-body">
        <?php for ($i = 1; $i <= $province_count; $i++): ?>
            <div class="chart-container">
                <canvas id="graphCanvas_Monthly<?php echo $i; ?>"></canvas>
            </div>
        <?php endfor; ?>
    </div>
</div>

<script>
    function DisplayGraph_Monthly() {
        const year = $("#year").val(), TRD_PROVINCE = $("#TRD_PROVINCE").val(), SKU_CAT = $("#SKU_CAT").val();
        $.post("engine/get_data_by_province_list.php", {year, SKU_CAT, TRD_PROVINCE}, function (data) {
            data.forEach((item, index) => showGraph_Monthly(index + 1, item.BRAND, TRD_PROVINCE, SKU_CAT));
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
    function showGraph_Monthly(graph_number, PROVINCE, SKU_CAT) {
        const [bgColor, borderColor, hoverBgColor, hoverBorderColor] = colors[(graph_number - 1) % colors.length];
        const graphTarget = $(`#graphCanvas_Monthly${graph_number}`);

        $.post("engine/chart_data_sale_by_province_process.php", {
            month: $("#month").val(),
            year: $("#year").val(),
            PROVINCE,
            SKU_CAT
        }, function (data) {
            const monthLabels = data.map(item => `${item.DI_MONTH_NAME} จำนวน ${item.TRD_QTY} `);
            const totals = data.map(item => parseFloat(item.TRD_AMOUNT_PRICE)); // จัดการให้ตัวเลขเป็นตัวเลขจริง

            new Chart(graphTarget, {
                type: 'bar',
                data: {
                    labels: monthLabels,
                    datasets: [{
                        label: `ยอดขายรายเดือน ${PROVINCE}`,
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

</body>
</html>
