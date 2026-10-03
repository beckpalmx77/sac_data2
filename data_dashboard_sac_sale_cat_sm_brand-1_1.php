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
        SUM(CAST(TRD_QTY AS DECIMAL(10, 2))) AS TRD_QTY,
        SUM(CAST(TRD_AMOUNT_PRICE AS DECIMAL(10, 2))) AS TRD_AMOUNT_PRICE
    FROM v_ims_data_sale_sac_all_sm_tire
    WHERE DI_YEAR = :DI_YEAR AND SALE_NAME LIKE :SALE_NAME
    GROUP BY BRAND, DI_MONTH
    ORDER BY BRAND, CAST(DI_MONTH AS UNSIGNED)";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':DI_YEAR', $year);
$stmt->bindParam(':SALE_NAME', $sale_name_with_wildcard);
$stmt->execute();

$data = [];
$amountData = [];
$brands = [];
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $brand = $row['BRAND'];
    $month = (int)$row['DI_MONTH'];
    $qty = (float)$row['TRD_QTY'];
    $amount = (float)$row['TRD_AMOUNT_PRICE'];

    if (!isset($data[$brand])) {
        $data[$brand] = array_fill(1, $monthsInMonth, 0);
        $amountData[$brand] = array_fill(1, $monthsInMonth, 0);
    }

    $data[$brand][$month] = $qty;
    $amountData[$brand][$month] = $amount;

    if (!in_array($brand, $brands)) {
        $brands[] = $brand;
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ยอดขาย</title>
    <!-- เพิ่มการโหลด DataTables CSS, JS และ FixedColumns -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/fixedcolumns/4.0.1/css/fixedColumns.dataTables.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.11.5/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/fixedcolumns/4.0.1/js/dataTables.fixedColumns.min.js"></script>
</head>
<body>

<style>
    .table-responsive {
        overflow-x: auto;
    }

    .table th:first-child,
    .table td:first-child {
        position: sticky;
        left: 0;
        background-color: #ffffff;
        z-index: 10;
    }
</style>

<div class="card">
    <div class="card-header bg-success text-white">
        <i class="fa fa-bar-chart" aria-hidden="true"></i> ข้อมูลยอดขาย
        <?php echo "ประเภท ยางเล็ก " . " ชื่อ Sale " . $sale_name . " ปี " . $year; ?>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="salesTable" class="table table-bordered table-striped">
                <thead>
                <tr class="table-primary">
                    <th>ยี่ห้อ</th>
                    <?php for ($month = 1; $month <= $monthsInMonth; $month++) {
                        $month_eng_name = getMonthNameEng($month);
                        ?>
                        <th><?= $month_eng_name ?></th>
                    <?php } ?>
                    <th>ยอดรวม</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $dailySums = array_fill(1, $monthsInMonth, 0);
                $totalSum = 0;

                foreach ($data as $brand => $months) {
                    $sum = 0;
                    echo "<tr>";
                    echo "<td>{$brand}</td>";
                    for ($month = 1; $month <= $monthsInMonth; $month++) {
                        $value = isset($months[$month]) ? $months[$month] : 0;
                        echo "<td>{$value}</td>";
                        $sum += (float)$value;
                        $dailySums[$month] += (float)$value;
                    }
                    echo "<td>{$sum}</td>";
                    $totalSum += $sum;
                    echo "</tr>";
                }
                ?>
                <tr class="table-success">
                    <td><strong>ยอดรวมทุกยี่ห้อ</strong></td>
                    <?php
                    foreach ($dailySums as $dailySum) {
                        echo "<td>{$dailySum}</td>";
                    }
                    ?>
                    <td><strong><?= $totalSum ?></strong></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- เรียกใช้ DataTable -->
<script>
    $(document).ready(function() {
        $('#salesTable').DataTable({
            "paging": true,
            "searching": true,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "lengthChange": true,
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/Thai.json"
            }
        });
    });
</script>

</body>
</html>
