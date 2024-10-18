<?php
$servername = "localhost";
$username = "myadmin";
$password = "myadmin";
$dbname = "sac_data2";
$port = 3307;

// เดือนและปีที่ต้องการตรวจสอบ (รับจากฟอร์มเลือกเดือน)
$selectedYear = isset($_GET['year']) ? $_GET['year'] : '2024';
$selectedMonth = isset($_GET['month']) ? $_GET['month'] : '9'; // ค่าเริ่มต้นคือเดือนกันยายน

$selectedSkuCat = "ยางเล็ก";
$selectedSaleName = "จิรกร (เตี้ยม)";

// ฟังก์ชันในการคำนวณจำนวนวันในเดือนที่เลือก
function getDaysInMonth($month, $year) {
    return cal_days_in_month(CAL_GREGORIAN, $month, $year);
}

// ตรวจสอบจำนวนวันในเดือนที่เลือก
$daysInMonth = getDaysInMonth($selectedMonth, $selectedYear);

try {
    // สร้างการเชื่อมต่อ PDO
    $conn = new PDO("mysql:host=$servername;port=$port;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // คำสั่ง SQL สำหรับดึงข้อมูลตามเดือนที่เลือก
    $sql = "SELECT TRD_PROVINCE, DI_DAY,
        SUM(CAST(TRD_QTY AS DECIMAL(10, 2))) AS TRD_QTY,
        SUM(CAST(TRD_AMOUNT_PRICE AS DECIMAL(10, 2))) AS TRD_AMOUNT_PRICE
    FROM ims_data_sale_sac_all
    WHERE DI_YEAR = :year AND SKU_CAT = :sku_cat  AND DI_MONTH = :month AND SALE_NAME = :sale_name
    GROUP BY TRD_PROVINCE, DI_DAY 
    ORDER BY TRD_PROVINCE, DI_DAY";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':year', $selectedYear);
    $stmt->bindParam(':month', $selectedMonth);
    $stmt->bindParam(':sku_cat', $selectedSkuCat);
    $stmt->bindParam(':sale_name', $selectedSaleName);
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
            $data[$province] = array_fill(1, $daysInMonth, 0); // เติมค่า 0 สำหรับวันที่มีในเดือนที่เลือก
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
    <h2 class="text-center">ยอดขายรายจังหวัดในเดือนที่เลือก</h2>

    <!-- ฟอร์มเลือกเดือน -->
    <form method="GET" class="mb-4">
        <div class="row">
            <div class="col-md-4">
                <label for="year" class="form-label">ปี</label>
                <select id="year" name="year" class="form-select">
                    <?php for ($i = 2020; $i <= 2025; $i++) { ?>
                        <option value="<?= $i ?>" <?= $i == $selectedYear ? 'selected' : '' ?>><?= $i ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-4">
                <label for="month" class="form-label">เดือน</label>
                <select id="month" name="month" class="form-select">
                    <?php for ($m = 1; $m <= 12; $m++) { ?>
                        <option value="<?= $m ?>" <?= $m == $selectedMonth ? 'selected' : '' ?>>
                            <?= date("F", mktime(0, 0, 0, $m, 10)) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>
            <div class="col-md-4 d-flex align-items-end">
                <button type="submit" class="btn btn-primary">แสดงผล</button>
            </div>
        </div>
    </form>

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

<script>
    // กำหนดข้อมูลที่ดึงมาจาก PHP
    const provinces = <?php echo json_encode($provinces); ?>;
    const salesData = <?php echo json_encode($data); ?>;
    const daysInMonth = <?php echo $daysInMonth; ?>;

    // จัดรูปแบบข้อมูลสำหรับกราฟ
    const labels = Array.from({length: daysInMonth}, (_, i) => i + 1); // จำนวนวันที่สอดคล้องกับเดือนที่เลือก
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
                    text: 'ยอดขายรายจังหวัดในเดือนที่เลือก'
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
