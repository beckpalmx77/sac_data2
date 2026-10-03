<?php
include("config/connect_db.php");

// 1. ส่วนกำหนดปี (Logic): เช็คว่ามีการเลือกปีเข้ามาหรือไม่ ถ้าไม่มีให้ใช้ปีปัจจุบัน
$current_year = date("Y");
if (isset($_POST['year'])) {
    $year = $_POST['year'];
} else {
    $year = $current_year;
}

$month = "1";
$sale_name = isset($_SESSION['first_name']) ? $_SESSION['first_name'] : '';

// จำนวนเดือนในปี
$monthsInMonth = 12;

$sale_name_clean = str_replace(' ', '', $sale_name);
$sale_name_with_wildcard = "%" . $sale_name . "%";
$sale_name_clean_wildcard = "%" . $sale_name_clean . "%";

// ดึงชื่อเดือน
$month_name = "";
$sql_month = "SELECT * FROM ims_month WHERE month = :month";
$stmt_month = $conn->prepare($sql_month);
$stmt_month->bindParam(':month', $month);
$stmt_month->execute();
$MonthRecords = $stmt_month->fetchAll();

foreach ($MonthRecords as $row) {
    $month_name = $row["month_name"];
}

// Query ข้อมูลยอดขาย (ตาราง ML TIRE)
$sql = "SELECT BRAND, DI_MONTH,
        SUM(CAST(TRD_QTY AS DECIMAL(10, 2))) AS TRD_QTY,
        SUM(CAST(TRD_AMOUNT_PRICE AS DECIMAL(10, 2))) AS TRD_AMOUNT_PRICE
    FROM v_ims_data_sale_sac_all_ml_tire
    WHERE DI_YEAR = :DI_YEAR AND (SALE_NAME LIKE :SALE_NAME OR REPLACE(SALE_NAME, ' ', '') LIKE :SALE_NAME_CLEAN)
    GROUP BY BRAND, DI_MONTH
    ORDER BY BRAND, CAST(DI_MONTH AS UNSIGNED)";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':DI_YEAR', $year); // ใช้ปีที่เลือกจากตัวแปร $year
$stmt->bindParam(':SALE_NAME', $sale_name_with_wildcard);
$stmt->bindParam(':SALE_NAME_CLEAN', $sale_name_clean_wildcard);
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

// ฟังก์ชันแปลงเดือน
if (!function_exists('getMonthNameEng')) {
    function getMonthNameEng($monthNum) {
        $dateObj = DateTime::createFromFormat('!m', $monthNum);
        return $dateObj->format('F');
    }
}
?>
<style>
    .table-responsive {
        overflow-x: auto;
    }
    #salesTableML th:first-child,
    #salesTableML td:first-child {
        position: sticky;
        left: 0;
        background-color: #ffffff;
        z-index: 10;
        border-right: 1px solid #dee2e6;
    }
</style>

<form id="myform" name="myform" action="" method="post">
    <input type="hidden" id="SALE_NAME" name="SALE_NAME" value="<?php echo htmlspecialchars($sale_name); ?>">
    <input type="hidden" id="year" name="year" value="<?php echo htmlspecialchars($year); ?>">
</form>

<div class="card shadow-sm mb-4">
    <div class="card-header bg-success text-white">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <i class="fa fa-bar-chart" aria-hidden="true"></i> ข้อมูลยอดขาย
                <?php echo "ประเภท ยางกลาง-ใหญ่ | ชื่อ Sale: " . htmlspecialchars($sale_name); ?>

                <form method="post" action="" style="display:inline-block; margin-left: 10px;">
                    <label for="year_select" class="mb-0 text-white">ปี: </label>
                    <select name="year" id="year_select" class="form-control form-control-sm d-inline-block w-auto" onchange="this.form.submit()">
                        <?php
                        for ($i = 0; $i <= 7; $i++) {
                            $option_year = $current_year - $i;
                            $selected = ($option_year == $year) ? 'selected' : '';
                            echo "<option value='$option_year' $selected>$option_year</option>";
                        }
                        ?>
                    </select>
                </form>
            </div>

            <div>
                <button type="button" id="ChartButton2" class="btn btn-light text-primary btn-sm fw-bold">Click For Chart</button>
            </div>
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table id="salesTableML" class="table table-bordered table-striped w-100">
                <thead>
                <tr class="table-primary">
                    <th>ยี่ห้อ (ปี <?php echo $year; ?>)</th>
                    <?php for ($month = 1; $month <= $monthsInMonth; $month++) {
                        $month_eng_name = getMonthNameEng($month); ?>
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
                    echo "<td>" . htmlspecialchars($brand) . "</td>";
                    for ($month = 1; $month <= $monthsInMonth; $month++) {
                        $value = isset($months[$month]) ? $months[$month] : 0;
                        echo "<td>" . number_format($value, 2) . "</td>";
                        $sum += (float)$value;
                        $dailySums[$month] += (float)$value;
                    }
                    echo "<td>" . number_format($sum, 2) . "</td>";
                    $totalSum += $sum;
                    echo "</tr>";
                }
                ?>
                <tr class="table-success font-weight-bold">
                    <td><strong>ยอดรวมทุกยี่ห้อ</strong></td>
                    <?php foreach ($dailySums as $dailySum) {
                        echo "<td>" . number_format($dailySum, 2) . "</td>";
                    } ?>
                    <td><strong><?= number_format($totalSum, 2) ?></strong></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#salesTableML').DataTable({
            scrollX: true,
            paging: true,
            fixedColumns: {
                leftColumns: 1
            },
            ordering: true,
            language: {
                "url": "//cdn.datatables.net/plug-ins/1.11.5/i18n/Thai.json"
            }
        });
    });
</script>

<script>
    $('#ChartButton2').click(function() {
        document.forms['myform'].action = 'chart_sac_sale_product_by_month_ml_tires_brand_bar';
        document.forms['myform'].target = '_blank';
        document.forms['myform'].submit();
        return true;
    });
</script>