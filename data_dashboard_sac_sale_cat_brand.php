<?php

include("config/connect_db.php");

//$year = $_POST["year"] ?? '';
//$month = $_POST["month"] ?? '';
//$sale_name = $_POST["SALE_NAME"] ?? '%';

$year = date("Y");
$month = "1";
$sale_name = $_SESSION['first_name'];

// ตรวจสอบจำนวนวันในเดือนที่เลือก
//$monthsInMonth = getDaysInMonth($month, $year);
$monthsInMonth = 12;

$sale_name_with_wildcard = "%" . $sale_name . "%";

$month_name = "";
$sql_month = "SELECT * FROM ims_month WHERE month = :month";
$stmt_month = $conn->prepare($sql_month);
$stmt_month->bindParam(':month', $month);
$stmt_month->execute();
$MonthRecords = $stmt_month->fetchAll();

foreach ($MonthRecords as $row) {
    $month_name = $row["month_name"];
}

$sql = "SELECT BRAND, DI_MONTH,
        SUM(CAST(TRD_QTY AS DECIMAL(10, 2))) AS TRD_QTY,
        SUM(CAST(TRD_AMOUNT_PRICE AS DECIMAL(10, 2))) AS TRD_AMOUNT_PRICE
    FROM v_ims_data_sale_sac_all_sm_tire
    WHERE DI_YEAR = :DI_YEAR AND SALE_NAME LIKE :SALE_NAME
    GROUP BY BRAND,DI_MONTH
    ORDER BY BRAND, CAST(DI_MONTH AS UNSIGNED)";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':DI_YEAR', $year);
$stmt->bindParam(':SALE_NAME', $sale_name_with_wildcard);
$stmt->execute();

// เก็บข้อมูลในอาร์เรย์
$data = [];
$amountData = []; // สร้างอาร์เรย์สำหรับเก็บยอดเงิน
$brands = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $brand = $row['BRAND'];
    $month = (int)$row['DI_MONTH'];
    $qty = (float)$row['TRD_QTY'];
    $amount = (float)$row['TRD_AMOUNT_PRICE'];

    // สร้าง array ของแต่ละยี่ห้อ
    if (!isset($data[$brand])) {
        $data[$brand] = array_fill(1, $monthsInMonth, 0); // เติมค่า 0 สำหรับวันที่มีในเดือนที่เลือก
        $amountData[$brand] = array_fill(1, $monthsInMonth, 0); // สร้างอาร์เรย์สำหรับยอดเงิน
    }

    // เติมข้อมูลใน array ของยี่ห้อ
    $data[$brand][$month] = $qty;
    $amountData[$brand][$month] = $amount; // เติมข้อมูลยอดเงิน

    // เก็บรายชื่อยี่ห้อ
    if (!in_array($brand, $brands)) {
        $brands[] = $brand;
    }
}

?>

<div class="card">
    <div class="card-header bg-success text-white">
        <i class="fa fa-bar-chart" aria-hidden="true"></i> ข้อมูลยอดขาย
        <?php echo "ประเภท ยางเล็ก " . " ชื่อ Sale " . $sale_name . " ปี " . $year; ?>
    </div>
    <input type="hidden" name="month" id="month" value="<?php echo $month; ?>">
    <input type="hidden" name="month_name" id="month_name" value="<?php echo $month_name; ?>">
    <input type="hidden" name="year" id="year" value="<?php echo $year; ?>">
    <input type="hidden" name="SALE" id="SALE" value="<?php echo $sale_name; ?>">
    <input type="hidden" name="SALE_NAME" id="SALE_NAME" value="<?php echo $sale_name; ?>">

    <div class="card-body">
        <!--div id="chart-container">
            <canvas id="salesChart"></canvas>
        </div-->

        <!-- แสดงข้อมูลเป็นตาราง -->
        <div class="table-responsive">
            <h5 class="mb-3">ข้อมูลยอดขายรายวัน ยางเล็ก ตามยี่ห้อ  <?php echo $month_name . ' ปี ' . $year . " Sale " . $sale_name ; ?></h5>
            <table class="table table-bordered table-striped">
                <thead>
                <tr class="table-primary">
                    <th>ยี่ห้อ</th>
                    <?php for ($month = 1; $month <= $monthsInMonth; $month++) { ?>
                        <th><?= $month ?></th>
                    <?php } ?>
                    <th>ยอดรวม</th> <!-- คอลัมน์สำหรับยอดรวมแต่ละยี่ห้อ -->
                </tr>
                </thead>
                <tbody>
                <?php
                $dailySums = array_fill(1, $monthsInMonth, 0); // อาร์เรย์สำหรับเก็บยอดรวมรายวัน
                $totalSum = 0; // ตัวแปรสำหรับยอดรวมทั้งหมด

                foreach ($data as $brand => $months) {
                    $sum = 0; // ตัวแปรสำหรับเก็บยอดรวมของยี่ห้อ
                    echo "<tr>";
                    echo "<td>{$brand}</td>";
                    for ($month = 1; $month <= $monthsInMonth; $month++) {
                        $value = isset($months[$month]) ? $months[$month] : 0; // แทนที่ค่าว่างด้วย 0
                        echo "<td>{$value}</td>"; //
                        $sum += (float)$value; // คำนวณยอดรวมของยี่ห้อ
                        $dailySums[$month] += (float)$value; // คำนวณยอดรวมของทุกยี่ห้อในแต่ละวัน
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