<?php

include("config/connect_db.php");

// ฟังก์ชันในการคำนวณจำนวนวันในเดือนที่เลือก
function getDaysInMonth($month, $year) {
    return cal_days_in_month(CAL_GREGORIAN, $month, $year);
}

$year = $_POST["year"] ?? '';
$month = $_POST["month"] ?? '';
$sale_name = $_POST["SALE_NAME"] ?? '';
$sku_cat = $_POST["SKU_CAT"] ?? '';

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

$sql = "SELECT TRD_PROVINCE, DI_DAY,
        SUM(CAST(TRD_QTY AS DECIMAL(10, 2))) AS TRD_QTY,
        SUM(CAST(TRD_AMOUNT_PRICE AS DECIMAL(10, 2))) AS TRD_AMOUNT_PRICE
    FROM ims_data_sale_sac_all
    WHERE DI_YEAR = :DI_YEAR AND DI_MONTH = :DI_MONTH AND SKU_CAT = :SKU_CAT AND SALE_NAME = :SALE_NAME
    GROUP BY TRD_PROVINCE, DI_DAY 
    ORDER BY TRD_PROVINCE, CAST(DI_DAY AS UNSIGNED)";

/*
$txt = $sql;
$myfile = fopen("sale1-param.txt", "w") or die("Unable to open file!");
fwrite($myfile, $txt);
fclose($myfile);
*/

$stmt = $conn->prepare($sql);
$stmt->bindParam(':DI_YEAR', $year);
$stmt->bindParam(':DI_MONTH', $month);
$stmt->bindParam(':SKU_CAT', $sku_cat); // สลับกับ SALE_NAME
$stmt->bindParam(':SALE_NAME', $sale_name);
$stmt->execute();

// เก็บข้อมูลในอาร์เรย์
$data = [];
$provinces = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $province = $row['TRD_PROVINCE'];
    $day = (int)$row['DI_DAY'];
    $qty = (float)$row['TRD_QTY'];
    $amount = (float)$row['TRD_AMOUNT_PRICE'];

    // สร้าง array ของแต่ละจังหวัด
    if (!isset($data[$province])) {
        $data[$province] = array_fill(1, $daysInMonth, 0); // เติมค่า 0 สำหรับวันที่มีในเดือนที่เลือก
    }

    // เติมข้อมูลใน array ของจังหวัด
    $data[$province][$day] = $qty;

    // เก็บรายชื่อจังหวัด
    if (!in_array($province, $provinces)) {
        $provinces[] = $province;
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
            width: 1398px;
            margin: 3rem auto;
        }
        #chart-container {
            width: 100%;
            height: auto;
        }
    </style>

    <style>

        .table-responsive {
            max-height: 400px; /* กำหนดความสูงสูงสุด */
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
        <?php echo "ประเภท " . $sku_cat . " ชื่อ Sale " . $sale_name . " เดือน " . $month_name . " ปี " . $year; ?>
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
        <div class="table-responsive mt-12">
            <h5 class="mb-3">ข้อมูลยอดขายรายวันตามจังหวัดในเดือน <?php echo $month_name . ' ปี ' . $year; ?></h5>
            <table class="table table-bordered table-striped">
                <thead>
                <tr class="table-primary">
                    <th>จังหวัด</th>
                    <?php for ($day = 1; $day <= $daysInMonth; $day++) { ?>
                        <th><?= $day ?></th>
                    <?php } ?>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($data as $province => $days) { ?>
                    <tr>
                        <td><?= $province ?></td>
                        <?php for ($day = 1; $day <= $daysInMonth; $day++) { ?>
                            <td><?= isset($days[$day]) ? $days[$day] : '' ?></td>
                        <?php } ?>
                    </tr>
                <?php } ?>
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
    const provinces = <?php echo json_encode($provinces); ?>;
    const salesData = <?php echo json_encode($data); ?>;
    const daysInMonth = <?php echo $daysInMonth; ?>;
    const sku_cat = '<?php echo $sku_cat; ?>';
    const saleName = '<?php echo $sale_name; ?>';
    const monthName = '<?php echo $month_name; ?>';
    const year = '<?php echo $year; ?>';
    const titleText = `ยอดขาย ${sku_cat} รายจังหวัดในเดือน ${monthName} ปี ${year} สำหรับ ${saleName}`;

    // จัดรูปแบบข้อมูลสำหรับกราฟ
    const labels = Array.from({length: daysInMonth}, (_, i) => i + 1); // จำนวนวันที่สอดคล้องกับเดือนที่เลือก
    const datasets = provinces.map(province => ({
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
                    }
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
