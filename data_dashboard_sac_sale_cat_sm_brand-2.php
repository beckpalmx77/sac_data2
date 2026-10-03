<?php
include("config/connect_db.php");

// 1. Logic การเลือกปี
$current_year = date("Y");
if (isset($_POST['year'])) {
    $year = $_POST['year'];
} else {
    $year = $current_year;
}

$sale_name = isset($_SESSION['first_name']) ? $_SESSION['first_name'] : '';
$monthsInMonth = 12;
$sale_name_with_wildcard = "%" . $sale_name . "%";

// --- ส่วนที่ 1: ดึงข้อมูล Actual (ยอดขายจริง) ---
$sql_actual = "SELECT SKU_CAT, BRAND, DI_MONTH, DI_YEAR,
        SUM(CAST(TRD_QTY AS DECIMAL(10, 2))) AS TRD_QTY
    FROM v_ims_data_sale_sac_all_sm_tire
    WHERE DI_YEAR = :DI_YEAR AND SALE_NAME LIKE :SALE_NAME
    GROUP BY SKU_CAT, BRAND, DI_MONTH
    ORDER BY BRAND, CAST(DI_MONTH AS UNSIGNED)";

$stmt_actual = $conn->prepare($sql_actual);
$stmt_actual->bindParam(':DI_YEAR', $year);
$stmt_actual->bindParam(':SALE_NAME', $sale_name_with_wildcard);
$stmt_actual->execute();

$data = [];
// สร้าง Array หลักจากยอดขายจริง
while ($row = $stmt_actual->fetch(PDO::FETCH_ASSOC)) {
    $sku_cat = $row['SKU_CAT'];
    $brand = $row['BRAND'];
    $month = (int)$row['DI_MONTH'];
    $qty = (float)$row['TRD_QTY'];

    // สร้างโครงสร้าง Array รอไว้ 12 เดือน (เริ่มด้วย 0)
    if (!isset($data[$sku_cat][$brand])) {
        $data[$sku_cat][$brand] = array_fill(1, $monthsInMonth, ['target' => 0, 'actual' => 0]);
    }
    // ใส่ค่า Actual
    $data[$sku_cat][$brand][$month]['actual'] = $qty;
}

// --- ส่วนที่ 2: ดึงข้อมูล Target (เป้าขาย) ---
// Optimization: ดึง Target ทั้งปีมาครั้งเดียว แทนการ Query ใน Loop (ช่วยให้เว็บโหลดเร็วขึ้นมาก)
// เราเลือกดึงเฉพาะ SKU_CAT และ BRAND ที่มีความเกี่ยวข้อง หรือดึงทั้งหมดในปีนั้นเลยก็ได้ (ในที่นี้ดึงทั้งหมดในปีนั้นเพื่อความรวดเร็ว)
$sql_target_query = "SELECT SKU_CAT, BRAND, DI_MONTH, target_qty
    FROM m_sale_target
    WHERE DI_YEAR = :DI_YEAR";

$stmt_target_all = $conn->prepare($sql_target_query);
$stmt_target_all->bindParam(':DI_YEAR', $year);
$stmt_target_all->execute();

$target_list = [];
while ($row = $stmt_target_all->fetch(PDO::FETCH_ASSOC)) {
    // ทำ Key เป็น SKU_CAT -> BRAND -> MONTH เพื่อให้ค้นหาง่าย
    $t_sku = $row['SKU_CAT'];
    $t_brand = $row['BRAND'];
    $t_month = (int)$row['DI_MONTH'];
    $t_qty = (float)$row['target_qty'];

    $target_list[$t_sku][$t_brand][$t_month] = $t_qty;
}

// --- ส่วนที่ 3: จับคู่ Target ใส่ใน $data ---
foreach ($data as $sku_cat => $brands) {
    foreach ($brands as $brand => $months) {
        foreach (range(1, $monthsInMonth) as $m) {
            // เช็คว่ามีค่า Target ในตัวแปร $target_list ที่เตรียมไว้ไหม
            if (isset($target_list[$sku_cat][$brand][$m])) {
                $data[$sku_cat][$brand][$m]['target'] = $target_list[$sku_cat][$brand][$m];
            } else {
                $data[$sku_cat][$brand][$m]['target'] = 0;
            }
        }
    }
}

// ฟังก์ชันชื่อเดือน (ป้องกัน Error กรณีไฟล์ include ไม่มี)
if (!function_exists('getMonthNameEng')) {
    function getMonthNameEng($monthNum) {
        $dateObj = DateTime::createFromFormat('!m', $monthNum);
        return $dateObj->format('F');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>เปรียบเทียบยอดขาย</title>
    <link rel="stylesheet" href="https://cdn.datatables.net/1.11.5/css/jquery.dataTables.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
</head>
<body>

<style>
    .table-responsive {
        overflow-x: auto;
    }
    .table thead th {
        text-align: center;
        vertical-align: middle;
        font-size: 0.85rem;
    }
    .table td {
        font-size: 0.85rem;
        text-align: right;
        vertical-align: middle;
    }
    .table td:first-child {
        text-align: left;
        font-weight: bold;
        position: sticky;
        left: 0;
        background-color: #f8f9fa;
        z-index: 1;
        border-right: 2px solid #dee2e6;
    }
    /* สีสำหรับ Diff */
    .text-pos { color: #28a745; font-weight: bold; } /* เขียว */
    .text-neg { color: #dc3545; font-weight: bold; } /* แดง */

    /* เส้นแบ่งเดือนให้ชัดเจน */
    .border-month-end {
        border-right: 2px solid #aaa !important;
    }
</style>

<div class="card">
    <div class="card-header bg-success text-white">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <i class="fa fa-bar-chart" aria-hidden="true"></i> ข้อมูลเปรียบเทียบยอดขาย (Target vs Actual)
                <?php echo "ประเภท ยางเล็ก " . " ชื่อ Sale " . htmlspecialchars($sale_name); ?>

                <form method="post" action="" style="display:inline-block; margin-left: 10px;">
                    <label for="year_select" class="mb-0">ปี: </label>
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
        </div>
    </div>

    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered table-striped table-sm">
                <thead>
                <tr class="table-primary">
                    <th style="min-width: 120px;">ยางเล็ก</th>
                    <?php for ($month = 1; $month <= $monthsInMonth; $month++) {
                        $month_eng_name = getMonthNameEng($month);
                        // ใส่ class border-month-end เพื่อตีเส้นคั่นระหว่างเดือน
                        echo "<th colspan='4' class='border-month-end'>{$month_eng_name}</th>";
                    } ?>
                </tr>
                <tr class="table-info">
                    <th>ยี่ห้อ</th>
                    <?php for ($month = 1; $month <= $monthsInMonth; $month++) { ?>
                        <th class="text-secondary" title="Target">Tar</th>
                        <th class="text-primary" title="Actual">Act</th>
                        <th title="Difference">Diff</th>
                        <th class="border-month-end" title="% Difference">%</th>
                    <?php } ?>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($data as $sku_cat => $brands) {
                    foreach ($brands as $brand => $months) {
                        echo "<tr>";
                        echo "<td>{$brand}</td>"; // แสดงยี่ห้อ

                        foreach ($months as $month => $values) {
                            $target = $values['target'];
                            $actual = $values['actual'];
                            $diff = $actual - $target;

                            // คำนวณ % Diff (ป้องกันหารด้วย 0)
                            $diffPercent = 0;
                            if ($target > 0) {
                                $diffPercent = ($diff / $target) * 100;
                            } elseif ($target == 0 && $actual > 0) {
                                $diffPercent = 100; // ไม่มีเป้าแต่มีขายถือว่า 100% หรือจะใส่เป็นค่าอื่นตาม Business Logic
                            }

                            // กำหนดสีตัวอักษร
                            $colorClass = ($diff >= 0) ? 'text-pos' : 'text-neg';

                            // จัด Style เส้นขอบขวาของคอลัมน์สุดท้ายในเดือน
                            $borderClass = "border-month-end";

                            echo "<td>" . number_format($target) . "</td>";
                            echo "<td>" . number_format($actual) . "</td>";
                            echo "<td class='{$colorClass}'>" . number_format($diff) . "</td>";
                            echo "<td class='{$colorClass} {$borderClass}'>" . number_format($diffPercent, 1) . "%</td>";
                        }
                        echo "</tr>";
                    }
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>