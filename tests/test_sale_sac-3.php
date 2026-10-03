<?php
$servername = "localhost";
$username = "myadmin";
$password = "myadmin";
$dbname = "sac_data2";
$port = 3307;

try {
    // สร้างการเชื่อมต่อ PDO
    $conn = new PDO("mysql:host=$servername;port=$port;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // คำสั่ง SQL
    $sql = "SELECT TRD_PROVINCE, DI_DAY,
        SUM(CAST(TRD_QTY AS DECIMAL(10, 2))) AS TRD_QTY
    FROM ims_data_sale_sac_all
    WHERE DI_YEAR = '2024' AND SKU_CAT = 'ยางเล็ก' AND DI_MONTH = '9' AND SALE_NAME = 'จิรกร (เตี้ยม)' 
    GROUP BY TRD_PROVINCE, DI_DAY 
    ORDER BY TRD_PROVINCE, DI_DAY";

    $stmt = $conn->prepare($sql);
    $stmt->execute();

    // เก็บข้อมูลในอาร์เรย์
    $data = [];
    $provinces = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $province = $row['TRD_PROVINCE'];
        $day = (int)$row['DI_DAY'];
        $qty = (float)$row['TRD_QTY'];

        // สร้าง array ของแต่ละจังหวัด
        if (!isset($data[$province])) {
            $data[$province] = array_fill(1, 30, 0); // เติมค่า 0 สำหรับวัน 1 ถึง 30
        }

        // เติมข้อมูลใน array ของจังหวัด
        $data[$province][$day] = $qty;

        // เก็บรายชื่อจังหวัด
        if (!in_array($province, $provinces)) {
            $provinces[] = $province;
        }
    }

    // ปิดการเชื่อมต่อ
    $conn = null;

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Graph</title>

    <!-- เพิ่ม Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

    <!-- เพิ่ม Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>

<div class="container my-5">
    <h2 class="text-center">ยอดขายรายจังหวัดในเดือนกันยายน 2024</h2>

    <!-- กราฟ Chart.js -->
    <div class="card mt-4">
        <div class="card-body">
            <canvas id="salesChart"></canvas>
        </div>
    </div>

    <!-- แสดงข้อมูลเป็นตาราง -->
    <div class="table-responsive mt-4">
        <table class="table table-bordered table-striped">
            <thead>
            <tr class="table-primary">
                <th>จังหวัด</th>
                <?php for ($day = 1; $day <= 30; $day++) { ?>
                    <th><?= $day ?></th>
                <?php } ?>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($data as $province => $days) { ?>
                <tr>
                    <td><?= $province ?></td>
                    <?php for ($day = 1; $day <= 30; $day++) { ?>
                        <td><?= isset($days[$day]) ? $days[$day] : '' ?></td>
                    <?php } ?>
                </tr>
            <?php } ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    // กำหนดข้อมูลที่ดึงมาจาก PHP
    const provinces = <?php echo json_encode($provinces); ?>;
    const salesData = <?php echo json_encode($data); ?>;

    // จัดรูปแบบข้อมูลสำหรับกราฟ
    const labels = Array.from({length: 30}, (_, i) => i + 1); // วันที่ 1 ถึง 30
    const datasets = provinces.map(province => ({
        label: province,
        data: labels.map(day => salesData[province][day] || 0), // แสดงเฉพาะวันที่มีข้อมูล
        backgroundColor: `rgba(${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, 0.5)`,
        borderColor: `rgba(${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, ${Math.floor(Math.random() * 255)}, 1)`,
        borderWidth: 1
    }));

    // สร้างกราฟ Bar ด้วย Chart.js
    const ctx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels, // วันที่ 1-30
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
                    text: 'ยอดขายรายจังหวัดในเดือนกันยายน 2024'
                }
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
