<?php

include 'config/connect_db.php';
// ส่วน header, session, connect_db.php ตามเดิม
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('includes/Header.php');

// Query
$sql = <<<SQL
SELECT
    MAX(r.DOC_ID) AS DOC_ID,
    MAX(r.DOC_DATE) AS DOC_DATE,
    r.CUSTOMER_CODE,
    MAX(r.CUSTOMER_NAME) AS CUSTOMER_NAME,
    MAX(r.PHONE) AS PHONE,
    MAX(r.PROVINCE) AS PROVINCE,
    MAX(r.SALE_NAME) AS SALE_NAME,
    r.SKU_CODE,
    MAX(r.SKU_NAME) AS SKU_NAME,
    MAX(r.SKU_CAT_CODE) AS SKU_CAT_CODE,
    MAX(r.SKU_CAT_NAME) AS SKU_CAT_NAME,
    r.BRAND,
    MAX(r.point) AS point,
    MAX(r.gift_voucher) AS gift_voucher,
    SUM(r.total_point) AS total_point,
    SUM(r.total_gift_voucher) AS total_gift_voucher,
    MAX(r.price_1) AS price_1,
    MAX(r.price_2) AS price_2,
    MAX(r.price_3) AS price_3,
    COALESCE(sy.year1, 0) AS year1,
    COALESCE(sy.year2, 0) AS year2,
    COALESCE(sy.year3, 0) AS year3,
    COALESCE(sy.year4, 0) AS year4,
    COALESCE(sy.year5, 0) AS year5,
    COALESCE(sy.current_year, 0) AS current_year,
    SUM(r.quantity_1) AS quantity_1,
    COALESCE(s.month6, 0) AS month6,
    (COALESCE(s.month6, 0) - SUM(r.quantity_1)) AS sum_balance_quantity_1,
    SUM(r.quantity_2) AS quantity_2,
    COALESCE(s.month7, 0) AS month7,
    (COALESCE(s.month7, 0) - SUM(r.quantity_2)) AS sum_balance_quantity_2,
    SUM(r.quantity_3) AS quantity_3,
    COALESCE(s.month8, 0) AS month8,
    (COALESCE(s.month8, 0) - SUM(r.quantity_3)) AS sum_balance_quantity_3,
    SUM(r.quantity_1 + r.quantity_2 + r.quantity_3) AS sum_quantity_total,
    (COALESCE(s.month6, 0) + COALESCE(s.month7, 0) + COALESCE(s.month8, 0)) AS sum_sale_all_month,
    ((COALESCE(s.month6, 0) + COALESCE(s.month7, 0) + COALESCE(s.month8, 0)) - SUM(r.quantity_1 + r.quantity_2 + r.quantity_3)) AS sum_balance_quantity
FROM ims_product_reserve_event r
LEFT JOIN (
    SELECT 
        AR_CODE, SKU_CODE, BRAND,
        SUM(CASE WHEN DI_MONTH = 6 THEN TRD_QTY ELSE 0 END) AS month6,
        SUM(CASE WHEN DI_MONTH = 7 THEN TRD_QTY ELSE 0 END) AS month7,
        SUM(CASE WHEN DI_MONTH = 8 THEN TRD_QTY ELSE 0 END) AS month8
    FROM ims_data_sale_sac_all
    WHERE DI_YEAR = '2025' 
    GROUP BY AR_CODE, SKU_CODE, BRAND
) s ON r.SKU_CODE = s.SKU_CODE AND r.CUSTOMER_CODE = s.AR_CODE
LEFT JOIN (
    SELECT 
        AR_CODE, SKU_CODE, BRAND,
        SUM(CASE WHEN DI_YEAR = 2020 THEN TRD_QTY ELSE 0 END) AS year1,
        SUM(CASE WHEN DI_YEAR = 2021 THEN TRD_QTY ELSE 0 END) AS year2,
        SUM(CASE WHEN DI_YEAR = 2022 THEN TRD_QTY ELSE 0 END) AS year3,
        SUM(CASE WHEN DI_YEAR = 2023 THEN TRD_QTY ELSE 0 END) AS year4,
        SUM(CASE WHEN DI_YEAR = 2024 THEN TRD_QTY ELSE 0 END) AS year5,
        SUM(CASE WHEN DI_YEAR = 2025 THEN TRD_QTY ELSE 0 END) AS current_year
    FROM ims_data_sale_sac_all
    WHERE DI_YEAR BETWEEN 2020 AND 2025 
    GROUP BY AR_CODE, SKU_CODE, BRAND
) sy ON r.SKU_CODE = sy.SKU_CODE AND r.CUSTOMER_CODE = sy.AR_CODE
GROUP BY r.CUSTOMER_CODE, r.SKU_CODE, r.BRAND,
         s.month6, s.month7, s.month8,
         sy.year1, sy.year2, sy.year3, sy.year4, sy.year5, sy.current_year
ORDER BY r.SKU_CODE
SQL;

$stmt = $conn->query($sql);
$rows = $stmt->fetchAll();

?>

<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Product Target Point</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>

    <!-- Bootstrap Select CSS -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">

    <!-- Alertify CSS -->
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/alertify.min.css"/>

</head>

<body id="page-top">
<div id="wrapper">

    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            <!-- Container Fluid-->
            <div class="container-fluid" id="container-wrapper">
                <br>
                <div class="d-sm-flex align-items-center justify-content-between mb-4">
                    <h1 class="h4 mb-0 text-gray-800">รายการลงทะเบียนจองยาง</h1>
                    <button class="btn btn-danger btn-sm" onclick="window.close();">
                        ปิดหน้าต่าง
                    </button>
                </div>

                <div class="mb-3">
                    <label for="skuCatFilter" class="form-label">กรองตามหมวดหมู่สินค้า (SKU_CAT_CODE):</label>
                    <select id="skuCatFilter" class="form-select" style="max-width: 300px;">
                        <option value="">ทั้งหมด</option>
                        <?php
                        // ดึงรายการ SKU_CAT_CODE ที่ไม่ซ้ำ
                        $skuCats = [];
                        foreach ($rows as $row) {
                            $skuCats[$row['SKU_CAT_CODE']] = $row['SKU_CAT_NAME'];
                        }
                        foreach ($skuCats as $catCode => $catName) {
                            echo "<option value='" . htmlspecialchars($catCode) . "'>" . htmlspecialchars($catCode . " - " . $catName) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div class="table-responsive table-wrapper">
                    <table id="dataTable" class="table table-bordered table-striped table-hover" style="width:100%">
                        <thead class="table-light">
                        <tr>
                            <th>DOC ID</th>
                            <th>วันที่</th>
                            <th>ลูกค้า</th>
                            <th>เบอร์</th>
                            <th>จังหวัด</th>
                            <th>รหัสสินค้า</th>
                            <th>ชื่อสินค้า</th>
                            <th>แบรนด์</th>
                            <th>2020</th>
                            <th>2021</th>
                            <th>2022</th>
                            <th>2023</th>
                            <th>2024</th>
                            <th>2025</th>
                            <th>จอง มิ.ย.</th>
                            <th>Balance เดือน 6</th>
                            <th>QTY เดือน 7</th>
                            <th>Balance เดือน 7</th>
                            <th>QTY เดือน 8</th>
                            <th>Balance เดือน 8</th>
                            <th>รวม 3 เดือน</th>
                            <th>รวม</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($rows as $row): ?>
                            <tr>
                                <td style='min-width: 200px; white-space: nowrap;'><?= htmlspecialchars($row['DOC_ID']) ?></td>
                                <td style='min-width: 200px; white-space: nowrap;'><?= htmlspecialchars($row['DOC_DATE']) ?></td>
                                <td style='min-width: 200px; white-space: nowrap;'><?= htmlspecialchars($row['CUSTOMER_NAME']) ?></td>
                                <td><?= htmlspecialchars($row['PHONE']) ?></td>
                                <td><?= htmlspecialchars($row['PROVINCE']) ?></td>
                                <td style='min-width: 200px; white-space: nowrap;'><?= htmlspecialchars($row['SKU_CODE']) ?></td>
                                <td style='min-width: 200px; white-space: nowrap;'><?= htmlspecialchars($row['SKU_NAME']) ?></td>
                                <td><?= htmlspecialchars($row['BRAND']) ?></td>

                                <td class="text-end"><?= number_format($row['year1']) ?></td>
                                <td class="text-end"><?= number_format($row['year2']) ?></td>
                                <td class="text-end"><?= number_format($row['year3']) ?></td>
                                <td class="text-end"><?= number_format($row['year4']) ?></td>
                                <td class="text-end"><?= number_format($row['year5']) ?></td>
                                <td class="text-end"><?= number_format($row['current_year']) ?></td>

                                <td class="text-end"><?= number_format($row['month6']) ?></td>
                                <td class="text-end"><?= number_format($row['sum_balance_quantity_1']) ?></td>
                                <td class="text-end"><?= number_format($row['month7']) ?></td>
                                <td class="text-end"><?= number_format($row['sum_balance_quantity_2']) ?></td>
                                <td class="text-end"><?= number_format($row['month8']) ?></td>
                                <td class="text-end"><?= number_format($row['sum_balance_quantity_3']) ?></td>
                                <td class="text-end"><?= number_format($row['sum_sale_all_month']) ?></td>
                                <td class="text-end"><?= number_format($row['sum_balance_quantity']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

            </div>
            <!-- /.container-fluid -->
        </div>
        <!-- End of Main Content -->

        <?php include('includes/Modal-Logout.php'); ?>
        <?php include('includes/Footer.php'); ?>

    </div>
    <!-- End of Content Wrapper -->

</div>
<!-- End of Page Wrapper -->

<a class="scroll-to-top rounded" href="#page-top">
    <i class="fas fa-angle-up"></i>
</a>

<!-- jQuery -->
<script src="vendor/jquery/jquery.min.js"></script>

<!-- Bootstrap Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<!-- DataTables JS -->
<script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
<script src="vendor/datatables/v11/dataTables.buttons.min.js"></script>

<!-- jQuery Easing -->
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>

<!-- Custom Admin JS -->
<script src="js/myadmin.min.js"></script>

<!-- Bootstrap Select JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>

<!-- Alertify JS -->
<script src="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>

<style>
    /* เพิ่ม scroll แนวนอนสำหรับตาราง */
    .table-wrapper {
        overflow-x: auto;
        width: 100%;
    }

    /* input ตัวเลขจัดชิดขวา */
    .input-3-digit {
        min-width: 80px;
        text-align: right;
    }
</style>

<!--script>
    $(document).ready(function () {
        $('#dataTable').DataTable({
            scrollX: true,  // เปิด scroll แนวนอน
            lengthMenu: [[5, 10, 20, 50, 100], [5, 10, 20, 50, 100]],
            language: {
                search: 'ค้นหา',
                lengthMenu: 'แสดง _MENU_ รายการ',
                info: 'หน้าที่ _PAGE_ จาก _PAGES_',
                infoEmpty: 'ไม่มีข้อมูล',
                zeroRecords: "ไม่มีข้อมูลตามเงื่อนไข",
                infoFiltered: '(กรองข้อมูลจากทั้งหมด _MAX_ รายการ)',
                paginate: {
                    previous: 'ก่อนหน้า',
                    next: 'ต่อไป',
                    last: 'สุดท้าย'
                }
            },
            columnDefs: [{
                targets: [8, 9, 10, 11, 12, 13, 14, 15, 16], // คอลัมน์ตัวเลข disable sort ได้ (รวม 9-16)
                orderable: false
            }]
        });
    });
</script-->

<script>
    $(document).ready(function () {
        let table = $('#dataTable').DataTable({
            scrollX: true,
            lengthMenu: [[5, 10, 20, 50, 100], [5, 10, 20, 50, 100]],
            initComplete: function () {
                $('#skuCatFilter').on('change', function () {
                    table.column(6).search(this.value).draw(); // filter ที่ column SKU_CAT_CODE (ซ่อนไว้ ให้เพิ่มได้ถ้าจำเป็น)
                });
            },
            footerCallback: function (row, data, start, end, display) {
                let api = this.api();

                function intVal(i) {
                    return typeof i === 'string' ?
                        i.replace(/[\$,]/g, '') * 1 :
                        typeof i === 'number' ?
                            i : 0;
                }

                // คอลัมน์ตัวเลขที่ต้องการรวม
                let cols = [8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21];

                cols.forEach(function (colIdx) {
                    let total = api
                        .column(colIdx, {search: 'applied'})
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);
                    $(api.column(colIdx).footer()).html(total.toLocaleString());
                });
            },
            language: {
                search: 'ค้นหา',
                lengthMenu: 'แสดง _MENU_ รายการ',
                info: 'หน้าที่ _PAGE_ จาก _PAGES_',
                infoEmpty: 'ไม่มีข้อมูล',
                zeroRecords: "ไม่มีข้อมูลตามเงื่อนไข",
                infoFiltered: '(กรองข้อมูลจากทั้งหมด _MAX_ รายการ)',
                paginate: {
                    previous: 'ก่อนหน้า',
                    next: 'ต่อไป',
                    last: 'สุดท้าย'
                }
            }
        });
    });

</script>

</body>
</html>
