<?php
include('includes/Header.php');
include('config/connect_db.php'); // PDO $conn
include('util/month_util.php');

$DOC_ID = isset($_GET['DOC_ID']) ? trim($_GET['DOC_ID']) : '';
$MONTH = isset($_GET['MONTH']) ? trim($_GET['MONTH']) : '';
$YEAR = isset($_GET['YEAR']) ? trim($_GET['YEAR']) : '';

if (empty($DOC_ID)) {
    die("ไม่พบข้อมูล");
}

$curr_year = date("Y");
$prev_year1 = $curr_year - 1;
$prev_year2 = $curr_year - 2;
$prev_year3 = $curr_year - 3;
$prev_year4 = $curr_year - 4;
$prev_year5 = $curr_year - 5;

// Build the dynamic SELECT list for each year
$select_years = "";
for ($i = 0; $i <= 5; $i++) {
    $year = $curr_year - $i;
    $select_years .= "SUM(CASE WHEN s.DI_YEAR = {$year} THEN s.TRD_QTY ELSE 0 END) AS `sale_year_{$year}`, ";
}

$select_years = rtrim($select_years, ", ");

$sql = "
    SELECT
        h.DOC_ID,
        h.DOC_DATE,
        h.MONTH,
        h.YEAR,
        h.CUSTOMER_NAME,
        h.AMPHUR,
        h.PROVINCE,
        h.SKU_CODE,
        h.BRAND,
        h.TARGET,
        h.VOLUME,        
        h.TARGET * h.VOLUME AS TOTAL_VOLUME,
        h.seq_record,
        -- เพิ่มคอลัมน์ยอดขายเฉพาะเดือนที่ต้องการ (เดือน 8 ปี 2025)
        SUM(CASE WHEN s.DI_YEAR = 2025 AND s.DI_MONTH = 8 THEN s.TRD_QTY ELSE 0 END) AS `month8_2025_sale`,
        {$select_years}
    FROM v_ims_sale_logistic_plan_detail AS h
    LEFT JOIN ims_data_sale_sac_all AS s 
        ON h.SKU_CODE = s.SKU_CODE AND h.CUSTOMER_NAME = s.AR_NAME
    WHERE h.DOC_ID = :DOC_ID
    GROUP BY
        h.SKU_CODE,
        h.BRAND,
        h.CUSTOMER_NAME,
        h.AMPHUR,
        h.PROVINCE,
        h.TARGET,
        h.VOLUME
    ORDER BY
        h.CUSTOMER_NAME,
        h.SKU_CODE
";

$stmt = $conn->prepare($sql);
$stmt->execute(['DOC_ID' => $DOC_ID]);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

$header_data = !empty($rows) ? $rows[0] : null;

?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>รายการเป้าการขายและแผนการส่ง</title>
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="vendor/datatables/v11/jquery.dataTables.min.css" rel="stylesheet">
    <link href="vendor/datatables/v11/buttons.dataTables.min.css" rel="stylesheet">
    <!-- Include FixedColumns CSS -->
    <link href="https://cdn.datatables.net/fixedcolumns/4.3.0/css/fixedColumns.dataTables.min.css" rel="stylesheet">
    <style>
        th, td {
            white-space: nowrap;
        }

        .table-responsive {
            overflow: auto;
            position: relative;
        }

        #dataTable {
            width: 100% !important;
        }
    </style>
</head>
<body id="page-top">
<div id="wrapper">
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <div class="container-fluid" id="container-wrapper">
                <br>
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h4 mb-0 text-gray-800">รายการเป้าการขายและแผนการส่ง</h1>
                    <button class="btn btn-danger btn-sm" onclick="window.close();">
                        ปิดหน้าต่าง
                    </button>
                </div>

                <div class="card">
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-2">
                                <label>เลขที่เอกสาร</label>
                                <input type="text" class="form-control" id="doc_id"
                                       value="<?= htmlspecialchars($header_data['DOC_ID'] ?? ''); ?>" readonly>
                            </div>
                            <div class="col-md-2">
                                <label>เดือน</label>
                                <input type="text" class="form-control" id="MONTH"
                                       value="<?= htmlspecialchars($month_arr[$header_data['MONTH']] ?? ''); ?>"
                                       readonly>
                            </div>
                            <div class="col-md-2">
                                <label>ปี</label>
                                <input type="text" class="form-control" id="YEAR"
                                       value="<?= htmlspecialchars($header_data['YEAR'] ?? ''); ?>" readonly>
                            </div>
                            <div class="col-md-4">
                                <label>Sequence</label>
                                <input type="text" class="form-control" id="seq_record"
                                       value="<?= htmlspecialchars($header_data['seq_record'] ?? ''); ?>" readonly>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-bordered table-striped w-100 nowrap" id="dataTable">
                                <thead class="table-primary">
                                <tr>
                                    <th>ลูกค้า</th>
                                    <th>อำเภอ</th>
                                    <th>จังหวัด</th>
                                    <th>ยี่ห้อ</th>
                                    <th>รหัสสินค้า</th>
                                    <th>ยอดขาย - ส.ค. 2568</th>
                                    <th>ยอดขาย - <?= htmlspecialchars($prev_year5) ?></th>
                                    <th>ยอดขาย - <?= htmlspecialchars($prev_year4) ?></th>
                                    <th>ยอดขาย - <?= htmlspecialchars($prev_year3) ?></th>
                                    <th>ยอดขาย - <?= htmlspecialchars($prev_year2) ?></th>
                                    <th>ยอดขาย - <?= htmlspecialchars($prev_year1) ?></th>
                                    <th>ยอดขาย - <?= htmlspecialchars($curr_year) ?></th>
                                    <th>เฉลี่ย - <?= htmlspecialchars($prev_year5) ?></th>
                                    <th>เฉลี่ย - <?= htmlspecialchars($prev_year4) ?></th>
                                    <th>เฉลี่ย - <?= htmlspecialchars($prev_year3) ?></th>
                                    <th>เฉลี่ย - <?= htmlspecialchars($prev_year2) ?></th>
                                    <th>เฉลี่ย - <?= htmlspecialchars($prev_year1) ?></th>
                                    <th>เฉลี่ย - <?= htmlspecialchars($curr_year) ?></th>
                                    <th>เฉลี่ย - <?= htmlspecialchars($prev_year5) ?> - <?= htmlspecialchars($prev_year1) ?></th>
                                    <th>เป้าการขาย</th>
                                    <th>ปริมาตร/เส้น</th>
                                    <th>ปริมาตร รวม</th>
                                </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($rows as $r): ?>
                                    <?php
                                    // Calculate averages here to make the code cleaner
                                    $avg_year5 = $r["sale_year_{$prev_year5}"] / 12;
                                    $avg_year4 = $r["sale_year_{$prev_year4}"] / 12;
                                    $avg_year3 = $r["sale_year_{$prev_year3}"] / 12;
                                    $avg_year2 = $r["sale_year_{$prev_year2}"] / 12;
                                    $avg_year1 = $r["sale_year_{$prev_year1}"] / 12;
                                    $avg_curr_year = $r["sale_year_{$curr_year}"] / 12;

                                    // Calculate the 5-year average
                                    $total_sale_5yr = $r["sale_year_{$prev_year5}"] + $r["sale_year_{$prev_year4}"] + $r["sale_year_{$prev_year3}"] + $r["sale_year_{$prev_year2}"] + $r["sale_year_{$prev_year1}"];
                                    $avg_5yr = $total_sale_5yr / 60; // 5 years * 12 months

                                    ?>
                                    <tr>
                                        <td><?= htmlspecialchars($r['CUSTOMER_NAME']) ?></td>
                                        <td><?= htmlspecialchars($r['AMPHUR']) ?></td>
                                        <td><?= htmlspecialchars($r['PROVINCE']) ?></td>
                                        <td><?= htmlspecialchars($r['BRAND']) ?></td>
                                        <td><?= htmlspecialchars($r['SKU_CODE']) ?></td>
                                        <!-- แสดงยอดขายเฉพาะเดือน 8 ปี 2025 -->
                                        <td><?= number_format($r['month8_2025_sale'], 0) ?></td>
                                        <td><?= number_format($r["sale_year_{$prev_year5}"], 0) ?></td>
                                        <td><?= number_format($r["sale_year_{$prev_year4}"], 0) ?></td>
                                        <td><?= number_format($r["sale_year_{$prev_year3}"], 0) ?></td>
                                        <td><?= number_format($r["sale_year_{$prev_year2}"], 0) ?></td>
                                        <td><?= number_format($r["sale_year_{$prev_year1}"], 0) ?></td>
                                        <td><?= number_format($r["sale_year_{$curr_year}"], 0) ?></td>
                                        <td><?= number_format($avg_year5, 2) ?></td>
                                        <td><?= number_format($avg_year4, 2) ?></td>
                                        <td><?= number_format($avg_year3, 2) ?></td>
                                        <td><?= number_format($avg_year2, 2) ?></td>
                                        <td><?= number_format($avg_year1, 2) ?></td>
                                        <td><?= number_format($avg_curr_year, 2) ?></td>
                                        <td><?= number_format($avg_5yr, 2) ?></td>
                                        <td><?= number_format($r['TARGET'], 0) ?></td>
                                        <td><?= number_format($r['VOLUME'], 2) ?></td>
                                        <td><?= number_format($r['TOTAL_VOLUME'], 2) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        include('includes/Modal-Logout.php');
        include('includes/Footer.php');
        ?>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
<!-- Include FixedColumns JS -->
<script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js"></script>

<style>
    th, td {
        white-space: nowrap;
    }

    .table-responsive {
        overflow: auto;
        position: relative;
    }

    #dataTable {
        width: 100% !important;
    }
</style>

<script>
    $(document).ready(function () {
        $('#dataTable').DataTable({
            responsive: true,
            scrollX: true,
            paging: true,
            language: {
                search: 'ค้นหา',
                lengthMenu: 'แสดง _MENU_ รายการ',
                info: 'หน้าที่ _PAGE_ จาก _PAGES_',
                infoEmpty: 'ไม่มีข้อมูล',
                zeroRecords: "ไม่มีข้อมูลตามเงื่อนไข",
                infoFiltered: '(กรองจากทั้งหมด _MAX_ รายการ)',
                paginate: {
                    previous: 'ก่อนหน้า',
                    next: 'ถัดไป'
                }
            },
            // Configure FixedColumns to freeze the first 5 columns
            fixedColumns: {
                leftColumns: 5
            }
        });
    });
</script>

</body>
</html>
