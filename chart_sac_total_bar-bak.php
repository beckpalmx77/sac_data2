<?php

include("config/connect_db.php");

// ฟังก์ชันในการคำนวณจำนวนวันในเดือนที่เลือก
function getDaysInMonth($month, $year) {
    return cal_days_in_month(CAL_GREGORIAN, $month, $year);
}

$month_name = "";

$sql_month = " SELECT * FROM ims_month where month = '" . $_POST["month"] . "'";
$stmt_month = $conn->prepare($sql_month);
$stmt_month->execute();
$MonthRecords = $stmt_month->fetchAll();
foreach ($MonthRecords as $row) {
    $month_name = $row["month_name"];
}

$year = $_POST["year"];
$month = $_POST["month"];
$sale_name = $_POST["SALE_NAME"];

// ตรวจสอบจำนวนวันในเดือนที่เลือก
$daysInMonth = getDaysInMonth($month, $year);

$sql = "SELECT DI_MONTH,DI_MONTH_NAME,DI_DAY,
        SUM(CAST(TRD_QTY AS DECIMAL(10, 2))) AS TRD_QTY,
        SUM(CAST(TRD_AMOUNT_PRICE AS DECIMAL(10, 2))) AS TRD_AMOUNT_PRICE
    FROM ims_data_sale_sac_all
    WHERE DI_YEAR = :DI_YEAR AND DI_MONTH = :DI_MONTH AND SALE_NAME = :SALE_NAME
    GROUP BY DI_MONTH,DI_DAY 
    ORDER BY DI_MONTH,CAST(DI_DAY AS UNSIGNED)";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':DI_YEAR', $year);
$stmt->bindParam(':DI_MONTH', $month);
$stmt->bindParam(':SALE_NAME', $sale_name);
$stmt->execute();

// เก็บข้อมูลในอาร์เรย์
$data = [];
$amountData = []; // สร้างอาร์เรย์สำหรับเก็บยอดเงิน
$months = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $month = $row['DI_MONTH'];
    $month_name = $row['DI_MONTH_NAME'];
    $day = (int)$row['DI_DAY'];
    $qty = (float)$row['TRD_QTY'];
    $amount = (float)$row['TRD_AMOUNT_PRICE'];

    // สร้าง array ของแต่ละเดือน
    if (!isset($data[$month])) {
        $data[$month] = array_fill(1, $daysInMonth, 0); // เติมค่า 0 สำหรับวันที่มีในเดือนที่เลือก
        $amountData[$month] = array_fill(1, $daysInMonth, 0); // สร้างอาร์เรย์สำหรับยอดเงิน
    }

    // เติมข้อมูลใน array ของเดือน
    $data[$month][$day] = $amount;
    $amountData[$month][$day] = $amount; // เติมข้อมูลยอดเงิน

    // เก็บรายชื่อเดือน
    if (!in_array($month_name, $months)) {
        $months[] = $month_name;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta date="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
    <script src="js/jquery-3.6.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!--script src="js/chartjs-4.4.4.js"></script-->
    <!--script src=" https://cdn.jsdelivr.net/npm/chart.js@4.4.4/dist/chart.umd.min.js "></script-->
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

        <!-- แสดงข้อมูลเป็นตาราง -->
        <div class="table-responsive">
            <h5 class="mb-3">ข้อมูลยอดขายรายวัน <?php echo $month_name . ' ปี ' . $year . " Sale " . $sale_name ; ?></h5>
            <table class="table table-bordered table-striped">
                <thead>
                <tr class="table-primary">
                    <th>เดือน</th>
                    <?php for ($day = 1; $day <= $daysInMonth; $day++) { ?>
                        <th><?= $day ?></th>
                    <?php } ?>
                    <th>ยอดรวม</th> <!-- คอลัมน์สำหรับยอดรวมแต่ละจังหวัด -->
                </tr>
                </thead>
                <tbody>
                <?php
                $dailySums = array_fill(1, $daysInMonth, 0); // อาร์เรย์สำหรับเก็บยอดรวมรายวัน
                $totalSum = 0; // ตัวแปรสำหรับยอดรวมทั้งหมด

                foreach ($data as $month => $days) {
                    $sum = 0; // ตัวแปรสำหรับเก็บยอดรวม
                    echo "<tr>";
                    echo "<td>{$month_name}</td>";
                    for ($day = 1; $day <= $daysInMonth; $day++) {
                        $value = isset($days[$day]) ? $days[$day] : 0; // แทนที่ค่าว่างด้วย 0
                        echo "<td>{$value}</td>"; // แสดงข้อมูลวัน
                        $sum += (float)$value; // คำนวณยอดรวมของจังหวัด
                        $dailySums[$day] += (float)$value; // คำนวณยอดรวมของทุกจังหวัดในแต่ละวัน
                    }
                    echo "<td>{$sum}</td>"; // แสดงยอดรวมของจังหวัด
                    $totalSum += $sum; // คำนวณยอดรวมทั้งหมด
                    echo "</tr>";
                }
                ?>
                <tr class="table-success">
                    <td><strong>ยอดรวม</strong></td>
                    <?php
                    // แสดงยอดรวมของแต่ละวัน
                    foreach ($dailySums as $dailySum) {
                        echo "<td>{$dailySum}</td>";
                    }
                    ?>
                    <td><strong><?= $totalSum ?></strong></td> <!-- แสดงยอดรวมทั้งหมด -->
                </tr>
                </tbody>
            </table>
        </div>

        <div id="chart-container">
            <canvas id="graphCanvas_Monthly"></canvas>
        </div>
    </div>
</div>

<script>
    function showGraph_Daily() {
        {

            let month = $("#month").val();
            let year = $("#year").val();
            let SALE_NAME = $("#SALE_NAME").val();

            let backgroundColor = '#0a4dd3';
            let borderColor = '#46d5f1';

            let hoverBackgroundColor = '#a2a1a3';
            let hoverBorderColor = '#a2a1a3';

            $.post("engine/chart_data_sac_daily.php", {
                month: month,
                year: year,
                SALE_NAME: SALE_NAME
            }, function (data) {
                console.log(data);
                let date = [];
                let total = [];

                for (let i in data) {
                    date.push(data[i].DI_DAY);
                    total.push(parseFloat(data[i].TRD_AMOUNT_PRICE)); // เก็บเป็นตัวเลข
                }

                let chartdata = {
                    labels: date,
                    datasets: [{
                        label: 'ยอดขายรายวัน (บาท) รวม VAT (Daily)',
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
                    data: chartdata,
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
    }

</script>

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

            $.post("engine/chart_data_sac_monthly.php", {
                month: month,
                year: year,
                SALE_NAME: SALE_NAME
            }, function (data) {
                console.log(data);
                let month = [];
                let total = [];

                for (let i in data) {
                    month.push(data[i].DI_MONTH_NAME);
                    total.push(parseFloat(data[i].TRD_AMOUNT_PRICE)); // เก็บเป็นตัวเลข
                }

                let chartdata = {
                    labels: month,
                    datasets: [{
                        label: 'ยอดขายรายเดือน (บาท) รวม VAT (Monthly)',
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
                    data: chartdata,
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
    }

</script>


</body>
</html>
