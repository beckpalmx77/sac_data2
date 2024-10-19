<?php

include("config/connect_db.php");

// ฟังก์ชันในการคำนวณจำนวนวันในเดือนที่เลือก
function getDaysInMonth($month, $year) {
    return cal_days_in_month(CAL_GREGORIAN, $month, $year);
}

// ตรวจสอบค่าว่างก่อนใช้งาน $_POST
$year = isset($_POST["year"]) ? $_POST["year"] : date("Y");
$month = isset($_POST["month"]) ? $_POST["month"] : date("m");
$sale_name = isset($_POST["SALE_NAME"]) ? $_POST["SALE_NAME"] : '';

$month_name = "";

// ตรวจสอบว่ามีการเลือกเดือนหรือไม่
if (!empty($month)) {
    $sql_month = "SELECT * FROM ims_month WHERE month = :month";
    $stmt_month = $conn->prepare($sql_month);
    $stmt_month->bindParam(':month', $month);
    $stmt_month->execute();
    $MonthRecords = $stmt_month->fetchAll();
    foreach ($MonthRecords as $row) {
        $month_name = $row["month_name"];
    }
}

// ตรวจสอบจำนวนวันในเดือนที่เลือก
$daysInMonth = getDaysInMonth($month, $year);

$sql = "SELECT DI_MONTH_NAME, DI_DAY,
            SUM(CAST(TRD_QTY AS DECIMAL(10, 2))) AS TRD_QTY,
            SUM(CAST(TRD_AMOUNT_PRICE AS DECIMAL(10, 2))) AS TRD_AMOUNT_PRICE
        FROM ims_data_sale_sac_all
        WHERE DI_YEAR = :DI_YEAR AND SALE_NAME = :SALE_NAME
        GROUP BY DI_MONTH_NAME, DI_DAY
        ORDER BY CAST(DI_MONTH AS UNSIGNED),CAST(DI_DAY AS UNSIGNED)";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':DI_YEAR', $year);
$stmt->bindParam(':SALE_NAME', $sale_name);
$stmt->execute();

// เก็บข้อมูลในอาร์เรย์
$data = [];
$amountData = [];
$months = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $month_name = $row['DI_MONTH_NAME'];
    $day = (int)$row['DI_DAY'];
    $qty = (float)$row['TRD_QTY'];
    $amount = (float)$row['TRD_AMOUNT_PRICE'];

    // สร้าง array ของแต่ละเดือน
    if (!isset($data[$month_name])) {
        $data[$month_name] = array_fill(1, $daysInMonth, 0);
        $amountData[$month_name] = array_fill(1, $daysInMonth, 0);
    }

    // เติมข้อมูลใน array ของเดือน
    $data[$month_name][$day] = $amount;
    $amountData[$month_name][$day] = $amount;

    if (!in_array($month_name, $months)) {
        $months[] = $month_name;
    }
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
    <link href="bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <script src="bootstrap/js/bootstrap.bundle.min.js"></script>
    <link rel="stylesheet" href="fontawesome/css/font-awesome.css">
    <title>สงวนออโต้คาร์</title>
    <style>
        body {
            width: 1400px;
            margin: 3rem auto;
        }
        #chart-container {
            width: 100%;
            height: auto;
        }
        .table td {
            text-align: right; /* จัดตัวเลขชิดขวา */
        }
        .table td:nth-child(1) {
            width: 150px; /* กำหนดความกว้างของคอลัมน์ชื่อเดือน */
            white-space: nowrap; /* ไม่ให้ชื่อเดือนแสดงในหลายบรรทัด */
            overflow: hidden; /* ซ่อนข้อความที่เกิน */
            text-overflow: ellipsis; /* แสดง ... ถ้าข้อความยาวเกิน */
        }
    </style>
</head>

<body>
<div class="card">
    <div class="card-header bg-success text-white">
        <i class="fa fa-bar-chart" aria-hidden="true"></i> กราฟแสดงยอดขาย รายวัน - รายเดือน
        <?php echo $sale_name . " ปี " . $year; ?>
    </div>
    <input type="hidden" name="month" id="month" value="<?php echo $month; ?>">
    <input type="hidden" name="year" id="year" value="<?php echo $year; ?>">
    <input type="hidden" name="SALE_NAME" id="SALE_NAME" value="<?php echo $sale_name; ?>">

    <div class="card-body">
        <!--div id="chart-container">
            <canvas id="graphCanvas_Daily"></canvas>
        </div>

        <!-- แสดงข้อมูลเป็นตาราง -->
        <div class="table-responsive">
            <h5 class="mb-3">ข้อมูลยอดขายรายวัน <?php echo  " ปี " . $year . " Sale " . $sale_name; ?></h5>
            <table class="table table-bordered table-striped">
                <thead>
                <tr class="table-primary">
                    <th style="text-align: left;">เดือน</th> <!-- จัดชิดซ้ายสำหรับหัวข้อเดือน -->
                    <?php for ($day = 1; $day <= $daysInMonth; $day++) { ?>
                        <th><?= $day ?></th>
                    <?php } ?>
                    <th>ยอดรวม</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $dailySums = array_fill(1, $daysInMonth, 0);
                $totalSum = 0;

                foreach ($data as $month_name => $days) {
                    $sum = 0;
                    echo "<tr>";
                    echo "<td style='text-align: left;'>{$month_name}</td>"; // ชื่อเดือนชิดซ้าย
                    for ($day = 1; $day <= $daysInMonth; $day++) {
                        $value = isset($days[$day]) ? number_format($days[$day], 2) : number_format(0, 2); // จัดรูปแบบตัวเลข
                        echo "<td>{$value}</td>";
                        $sum += (float)$value;
                        $dailySums[$day] += (float)$value;
                    }
                    echo "<td>" . number_format($sum, 2) . "</td>"; // แสดงยอดรวมด้วยการจัดรูปแบบ
                    $totalSum += $sum;
                    echo "</tr>";
                }
                ?>
                <tr class="table-success">
                    <td><strong>ยอดรวม</strong></td>
                    <?php foreach ($dailySums as $dailySum) {
                        echo "<td>" . number_format($dailySum, 2) . "</td>"; // จัดรูปแบบตัวเลขยอดรวมแต่ละวัน
                    } ?>
                    <td><strong><?= number_format($totalSum, 2) ?></strong></td> <!-- จัดรูปแบบตัวเลขยอดรวมทั้งหมด -->
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
        let month = $("#month").val();
        let year = $("#year").val();
        let SALE_NAME = $("#SALE_NAME").val();

        $.post("engine/chart_data_sac_daily.php", { month: month, year: year, SALE_NAME: SALE_NAME }, function (data) {
            let date = [];
            let total = [];

            for (let i in data) {
                date.push(data[i].DI_DAY);
                total.push(parseFloat(data[i].TRD_AMOUNT_PRICE));
            }

            let chartdata = {
                labels: date,
                datasets: [{
                    label: 'ยอดขายรายวัน รวม VAT (Daily)',
                    backgroundColor: '#0a4dd3',
                    borderColor: '#46d5f1',
                    hoverBackgroundColor: '#a2a1a3',
                    hoverBorderColor: '#a2a1a3',
                    data: total
                }]
            };

            let graphTarget = $('#graphCanvas_Daily');
            new Chart(graphTarget, {
                type: 'bar',
                data: chartdata,
                options: {
                    scales: {
                        y: {
                            ticks: {
                                callback: function (value) {
                                    return value.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function (tooltipItem) {
                                    return tooltipItem.raw.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                }
                            }
                        }
                    }
                }
            });
        });
    }

    function showGraph_Monthly() {
        let year = $("#year").val();
        let SALE_NAME = $("#SALE_NAME").val();

        $.post("engine/chart_data_sac_monthly.php", { year: year, SALE_NAME: SALE_NAME }, function (data) {
            let month = [];
            let total = [];

            for (let i in data) {
                month.push(data[i].DI_MONTH_NAME);
                total.push(parseFloat(data[i].TRD_AMOUNT_PRICE));
            }

            let chartdata = {
                labels: month,
                datasets: [{
                    label: 'ยอดขายรายเดือน รวม VAT (Monthly)',
                    backgroundColor: '#0a4dd3',
                    borderColor: '#46d5f1',
                    hoverBackgroundColor: '#a2a1a3',
                    hoverBorderColor: '#a2a1a3',
                    data: total
                }]
            };

            let graphTarget = $('#graphCanvas_Monthly');
            new Chart(graphTarget, {
                type: 'bar',
                data: chartdata,
                options: {
                    scales: {
                        y: {
                            ticks: {
                                callback: function (value) {
                                    return value.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                }
                            }
                        }
                    },
                    plugins: {
                        tooltip: {
                            callbacks: {
                                label: function (tooltipItem) {
                                    return tooltipItem.raw.toLocaleString('th-TH', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                }
                            }
                        }
                    }
                }
            });
        });
    }

    $(document).ready(function () {
        //showGraph_Daily();
        //showGraph_Monthly();
    });
</script>
</body>
</html>
