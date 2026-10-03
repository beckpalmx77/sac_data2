<?php

include("config/connect_db.php");

$year = date("Y");
$month = "1";
$sale_name = $_SESSION['first_name'];

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
        SUM(CAST(TRD_QTY AS DECIMAL(10, 2))) AS TRD_QTY
    FROM v_ims_data_sale_sac_all_sm_tire
    WHERE DI_YEAR = :DI_YEAR AND SALE_NAME LIKE :SALE_NAME
    GROUP BY BRAND, DI_MONTH
    ORDER BY BRAND, CAST(DI_MONTH AS UNSIGNED)";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':DI_YEAR', $year);
$stmt->bindParam(':SALE_NAME', $sale_name_with_wildcard);
$stmt->execute();

// เก็บข้อมูลในอาร์เรย์
$data = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $brand = $row['BRAND'];
    $month = (int)$row['DI_MONTH'];
    $qty = (float)$row['TRD_QTY'];

    // สร้าง array ของแต่ละยี่ห้อ
    if (!isset($data[$brand])) {
        $data[$brand] = array_fill(1, $monthsInMonth, ['target' => 0, 'actual' => 0]); // เติมค่า 0 สำหรับ Target และ Actual
    }

    // เติมข้อมูลใน array ของยี่ห้อ
    $data[$brand][$month]['actual'] = $qty; // ใช้ TRD_QTY เป็น Actual
    // คุณสามารถกำหนดค่า target สำหรับแต่ละแบรนด์ได้ที่นี่
    $data[$brand][$month]['target'] = 1000; // เปลี่ยนตามความต้องการจริงของคุณ
}

// เริ่มแสดงผล
?>

<div class="card">
    <div class="card-header bg-success text-white">
        <i class="fa fa-bar-chart" aria-hidden="true"></i> ข้อมูลยอดขาย
        <?php echo "ประเภท ยางเล็ก " . " ชื่อ Sale " . $sale_name . " ปี " . $year; ?>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                <tr class="table-primary">
                    <th>ยี่ห้อ</th>
                    <?php for ($month = 1; $month <= $monthsInMonth; $month++) {
                        $month_eng_name = getMonthNameEng($month);
                        echo "<th colspan='4'>{$month_eng_name}</th>"; // เพิ่มคอลัมน์ 4 คอลัมน์สำหรับแต่ละเดือน
                    } ?>
                </tr>
                <tr class="table-primary">
                    <th></th>
                    <?php for ($month = 1; $month <= $monthsInMonth; $month++) { ?>
                        <th>Target</th>
                        <th>Actual</th>
                        <th>Diff(เส้น)</th>
                        <th>Diff(%)</th>
                    <?php } ?>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($data as $brand => $months) {
                    echo "<tr>";
                    echo "<td>{$brand}</td>";
                    foreach ($months as $month => $values) {
                        $target = $values['target'];
                        $actual = $values['actual'];
                        $diff = $actual - $target;
                        $diffPercent = $target ? ($diff / $target) * 100 : 0;

                        echo "<td>{$target}</td>";
                        echo "<td>{$actual}</td>";
                        echo "<td>{$diff}</td>";
                        echo "<td>" . round($diffPercent, 2) . "%</td>";
                    }
                    echo "</tr>";
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
