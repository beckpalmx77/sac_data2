<?php

include("config/connect_db.php");

// ฟังก์ชันในการคำนวณจำนวนวันในเดือนที่เลือก
function getDaysInMonth($month, $year) {
    return cal_days_in_month(CAL_GREGORIAN, $month, $year);
}

$year = $_POST["year"] ?? '';
$month = $_POST["month"] ?? '';
$sale_name = $_POST["SALE_NAME"] ?? '%';

// ตรวจสอบจำนวนวันในเดือนที่เลือก
$daysInMonth = getDaysInMonth($month, $year);

$month_name = "";
$sql_month = "SELECT * FROM ims_month WHERE month = :month";
$stmt_month = $conn->prepare($sql_month);
$stmt_month->bindParam(':month', $month);
$stmt_month->execute();
$MonthRecords = $stmt_month->fetchAll();

foreach ($MonthRecords as $row) {
    $month_name = $row["month_name"];
}

$sql = "SELECT BRAND, DI_DAY,
        SUM(CAST(TRD_QTY AS DECIMAL(10, 2))) AS TRD_QTY,
        SUM(CAST(TRD_AMOUNT_PRICE AS DECIMAL(10, 2))) AS TRD_AMOUNT_PRICE
    FROM v_ims_data_sale_sac_all_ml_tire
    WHERE DI_YEAR = :DI_YEAR AND DI_MONTH = :DI_MONTH AND SALE_NAME LIKE :SALE_NAME
    GROUP BY BRAND, DI_DAY 
    ORDER BY BRAND, CAST(DI_DAY AS UNSIGNED)";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':DI_YEAR', $year);
$stmt->bindParam(':DI_MONTH', $month);
$stmt->bindParam(':SALE_NAME', $sale_name);
$stmt->execute();

// เก็บข้อมูลในอาร์เรย์
$data = [];
$amountData = []; // สร้างอาร์เรย์สำหรับเก็บยอดเงิน
$brands = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $brand = $row['BRAND'];
    $day = (int)$row['DI_DAY'];
    $qty = (float)$row['TRD_QTY'];
    $amount = (float)$row['TRD_AMOUNT_PRICE'];

    // สร้าง array ของแต่ละยี่ห้อ
    if (!isset($data[$brand])) {
        $data[$brand] = array_fill(1, $daysInMonth, 0); // เติมค่า 0 สำหรับวันที่มีในเดือนที่เลือก
        $amountData[$brand] = array_fill(1, $daysInMonth, 0); // สร้างอาร์เรย์สำหรับยอดเงิน
    }

    // เติมข้อมูลใน array ของยี่ห้อ
    $data[$brand][$day] = $qty;
    $amountData[$brand][$day] = $amount; // เติมข้อมูลยอดเงิน

    // เก็บรายชื่อยี่ห้อ
    if (!in_array($brand, $brands)) {
        $brands[] = $brand;
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="fontawesome/css/font-awesome.css">
    <title>สงวนออโต้คาร์</title>
    <style>
        body {
            width: 1500px;
            margin: 3rem auto;
        }
        #chart-container {
            width: 100%;
            height: auto;
        }
    </style>

    <style>
        .table-responsive {
            max-height: 600px; /* กำหนดความสูงสูงสุด */
            overflow-y: auto; /* แสดง scroll bar ถ้าต้องการ */
        }

        .table-responsive::-webkit-scrollbar {
            width: 8px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background-color: darkgrey;
            border-radius: 10px;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
    </style>

</head>

<body>
<div class="card">
    <div class="card-header bg-success text-white">
        <i class="fa fa-bar-chart" aria-hidden="true"></i> กราฟแสดงยอดขาย
        <?php echo "ประเภท ยางกลาง-ใหญ่ " . " ชื่อ Sale " . $sale_name . " เดือน " . $month_name . " ปี " . $year; ?>
    </div>
    <input type="hidden" name="month" id="month" value="<?php echo $month; ?>">
    <input type="hidden" name="month_name" id="month_name" value="<?php echo $month_name; ?>">
    <input type="hidden" name="year" id="year" value="<?php echo $year; ?>">
    <input type="hidden" name="SALE" id="SALE" value="<?php echo $sale_name; ?>">
    <input type="hidden" name="SALE_NAME" id="SALE_NAME" value="<?php echo $sale_name; ?>">

    <div class="card-body">
        <div id="chart-container">
            <canvas id="salesChart"></canvas>
        </div>

        <!-- แสดงข้อมูลเป็นตาราง -->
        <div class="table-responsive">
            <h5 class="mb-3">ข้อมูลยอดขายรายวัน ยางกลาง-ใหญ่ ตามยี่ห้อ  <?php echo $month_name . ' ปี ' . $year . " Sale " . $sale_name ; ?></h5>
            <table class="table table-bordered table-striped">
                <thead>
                <tr class="table-primary">
                    <th>ยี่ห้อ</th>
                    <?php for ($day = 1; $day <= $daysInMonth; $day++) { ?>
                        <th><?= $day ?></th>
                    <?php } ?>
                    <th>ยอดรวม</th> <!-- คอลัมน์สำหรับยอดรวมแต่ละยี่ห้อ -->
                </tr>
                </thead>
                <tbody>
                <?php
                $dailySums = array_fill(1, $daysInMonth, 0); // อาร์เรย์สำหรับเก็บยอดรวมรายวัน
                $totalSum = 0; // ตัวแปรสำหรับยอดรวมทั้งหมด

                foreach ($data as $brand => $days) {
                    $sum = 0; // ตัวแปรสำหรับเก็บยอดรวมของยี่ห้อ
                    echo "<tr>";
                    echo "<td>{$brand}</td>";
                    for ($day = 1; $day <= $daysInMonth; $day++) {
                        $value = isset($days[$day]) ? $days[$day] : 0; // แทนที่ค่าว่างด้วย 0
                        echo "<td>{$value}</td>"; // แสดงข้อมูลวัน
                        $sum += (float)$value; // คำนวณยอดรวมของยี่ห้อ
                        $dailySums[$day] += (float)$value; // คำนวณยอดรวมของทุกยี่ห้อในแต่ละวัน
                    }
                    echo "<td>{$sum}</td>"; // แสดงยอดรวมของยี่ห้อ
                    $totalSum += $sum; // คำนวณยอดรวมทั้งหมด
                    echo "</tr>";
                }
                ?>
                <tr class="table-success">
                    <td><strong>ยอดรวมทุกยี่ห้อ</strong></td>
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



    </div>
</div>
<script>
    // ฟังก์ชันในการสุ่มสี
    function getRandomColor() {
        const letters = '0123456789ABCDEF';
        let color = '#';
        for (let i = 0; i < 6; i++) {
            color += letters[Math.floor(Math.random() * 16)];
        }
        return color;
    }

    // กำหนดข้อมูลที่ดึงมาจาก PHP
    const brands = <?php echo json_encode($brands); ?>;
    const salesData = <?php echo json_encode($data); ?>;
    const daysInMonth = <?php echo $daysInMonth; ?>;
    const saleName = '<?php echo $sale_name; ?>';
    const monthName = '<?php echo $month_name; ?>';
    const year = '<?php echo $year; ?>';
    const titleText = `ยอดขาย ยางกลาง-ใหญ่ รายยี่ห้อในเดือน ${monthName} ปี ${year} สำหรับ ${saleName}`;

    // จัดรูปแบบข้อมูลสำหรับกราฟ
    const labels = Array.from({length: daysInMonth}, (_, i) => i + 1); // จำนวนวันที่สอดคล้องกับเดือนที่เลือก
    const datasets = brands.map(province => ({
        label: province,
        data: labels.map(day => salesData[province][day] || 0), // แสดงเฉพาะวันที่มีข้อมูล
        backgroundColor: getRandomColor(), // ใช้ฟังก์ชันสุ่มสี
        borderColor: getRandomColor(), // ใช้ฟังก์ชันสุ่มสี
        borderWidth: 1
    }));

    // สร้างกราฟ Bar ด้วย Chart.js
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels, // จำนวนวันในเดือนที่เลือก
            datasets: datasets
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: titleText
                },
            },
            scales: {
                x: {
                    title: {
                        display: true,
                        text: 'วันที่'
                    },
                    barPercentage: 100, // ปรับขนาดแท่งให้กว้างขึ้น
                    categoryPercentage: 100 // ปรับขนาดของกลุ่มแท่ง
                },
                y: {
                    title: {
                        display: true,
                        text: 'ยอดขาย (จำนวน)'
                    },
                    beginAtZero: true
                }
            }
        }
    });
</script>


</body>
</html>
