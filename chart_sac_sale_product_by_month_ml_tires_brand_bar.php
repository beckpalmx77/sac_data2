<?php
include('includes/Header.php');
include 'config/connect_db.php';

$year = $_POST["year"] ?? '';
$sale_name = $_POST["SALE_NAME"] ?? '%';

// รายการชื่อเดือนทั้งหมด
$allMonths = [
    '1' => 'January', '2' => 'February', '3' => 'March', '4' => 'April',
    '5' => 'May', '6' => 'June', '7' => 'July', '8' => 'August',
    '9' => 'September', '10' => 'October', '11' => 'November', '12' => 'December'
];

// Query SQL
$sql = "
    SELECT BRAND, DI_MONTH, DI_MONTH_NAME,
        SUM(CAST(TRD_QTY AS DECIMAL(10, 2))) AS TRD_QTY,
        SUM(CAST(TRD_AMOUNT_PRICE AS DECIMAL(10, 2))) AS TRD_AMOUNT_PRICE
    FROM v_ims_data_sale_sac_all_ml_tire
    WHERE DI_YEAR = :year AND SALE_NAME = :sale_name
    GROUP BY BRAND, DI_MONTH 
    ORDER BY BRAND, CAST(DI_MONTH AS UNSIGNED)";
$stmt = $conn->prepare($sql);
$stmt->execute(['year' => $year, 'sale_name' => $sale_name]);

// จัดกลุ่มข้อมูลตามยี่ห้อ
$data = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $brand = $row['BRAND'];
    if (!isset($data[$brand])) {
        $data[$brand] = array_fill_keys(array_keys($allMonths), 0); // เติมค่าเริ่มต้นทุกเดือนเป็น 0
    }
    $data[$brand][$row['DI_MONTH']] = $row['TRD_QTY']; // เติมข้อมูลเดือนที่มีค่า
}

// จัดรูปแบบข้อมูลสำหรับกราฟ
$chartData = [];
foreach ($data as $brand => $quantities) {
    $chartData[$brand] = [
        'month_names' => array_values($allMonths), // ชื่อเดือนเรียงตามลำดับ
        'quantities' => array_map(function ($month) use ($quantities) {
            return $quantities[$month] ?? 0; // ใช้ 0 สำหรับเดือนที่ไม่มีข้อมูล
        }, array_keys($allMonths))
    ];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Charts</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels"></script>
</head>
<body>
<div class="container my-5">
    <h1 class="text-center mb-4">แสดงยอดขายยางขนาดกลาง-ใหญ่แต่ละยี่ห้อตามเดือน ปี <?= htmlspecialchars($year) ?></h1>
    <h3 class="text-center mb-4">ชื่อ  Sale : <?= htmlspecialchars($sale_name) ?></h3>

    <!-- แสดงกราฟแยกตามยี่ห้อ -->
    <?php foreach ($chartData as $brand => $chart): ?>
        <div class="mb-5">
            <h3 class="text-center"><?= htmlspecialchars($brand) ?></h3>
            <canvas id="chart-<?= htmlspecialchars($brand) ?>" width="400" height="200"></canvas>
        </div>
    <?php endforeach; ?>
</div>

<script>
    // ข้อมูลทั้งหมดจาก PHP
    const chartData = <?= json_encode($chartData) ?>;

    // ฟังก์ชันสุ่มสี
    function getRandomColor() {
        const letters = '0123456789ABCDEF';
        let color = '#';
        for (let i = 0; i < 6; i++) {
            color += letters[Math.floor(Math.random() * 16)];
        }
        return color;
    }

    // วนลูปสร้างกราฟสำหรับแต่ละยี่ห้อ
    Object.keys(chartData).forEach(brand => {
        const ctx = document.getElementById(`chart-${brand}`).getContext('2d');
        const monthNames = chartData[brand].month_names;
        const quantities = chartData[brand].quantities;
        const randomColor = getRandomColor();

        // ตรวจสอบข้อมูลใน console
        console.log(`Brand: ${brand}`, { months: monthNames, quantities: quantities });

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: monthNames,
                datasets: [{
                    label: `จำนวน (เส้น) ตามยี่ห้อ (${brand})`,
                    data: quantities,
                    backgroundColor: randomColor + '80', // สีพื้น (โปร่งใส 50%)
                    borderColor: randomColor,          // สีขอบ
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    tooltip: {
                        enabled: true
                    },
                    datalabels: {
                        display: true,
                        color: 'black',
                        anchor: 'end',
                        align: 'start',
                        font: {
                            size: 12
                        },
                        formatter: function (value) {
                            // แสดงค่าว่างแทน 0
                            return value === 0 ? '' : value.toLocaleString(); // แปลงตัวเลขให้เป็นรูปแบบอ่านง่าย
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            },
            plugins: [ChartDataLabels]
        });
    });
</script>

<?php include('includes/Footer.php'); ?></php>

</body>
</html>
