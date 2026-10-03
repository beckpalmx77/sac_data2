<?php
include 'config/connect_db.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);
include('includes/Header.php');

$curr_date = date("d-m-Y");

$curr_year = date("Y");
$curr_year1 = $curr_year - 5;
$curr_year2 = $curr_year - 4;
$curr_year3 = $curr_year - 3;
$curr_year4 = $curr_year - 2;
$curr_year5 = $curr_year - 1;

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
    WHERE DI_YEAR = {$curr_year} 
    GROUP BY AR_CODE, SKU_CODE, BRAND
) s ON r.SKU_CODE = s.SKU_CODE AND r.CUSTOMER_CODE = s.AR_CODE
LEFT JOIN (
    SELECT 
        AR_CODE, SKU_CODE, BRAND,
        SUM(CASE WHEN DI_YEAR = {$curr_year1} THEN TRD_QTY ELSE 0 END) AS year1,
        SUM(CASE WHEN DI_YEAR = {$curr_year2} THEN TRD_QTY ELSE 0 END) AS year2,
        SUM(CASE WHEN DI_YEAR = {$curr_year3} THEN TRD_QTY ELSE 0 END) AS year3,
        SUM(CASE WHEN DI_YEAR = {$curr_year4} THEN TRD_QTY ELSE 0 END) AS year4,
        SUM(CASE WHEN DI_YEAR = {$curr_year5} THEN TRD_QTY ELSE 0 END) AS year5,
        SUM(CASE WHEN DI_YEAR = {$curr_year} THEN TRD_QTY ELSE 0 END) AS current_year
    FROM ims_data_sale_sac_all
    WHERE DI_YEAR BETWEEN {$curr_year1} AND {$curr_year} 
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
    <title>Product Target Point</title>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>
</head>

<body>
<div class="container-fluid mt-4">
    <h4>รายการลงทะเบียนจองยาง</h4>

    <!-- Filter Dropdown -->
    <div class="mb-3">
        <label for="skuCatFilter" class="form-label">กรองตามหมวดหมู่สินค้า (SKU_CAT_CODE):</label>
        <select id="skuCatFilter" class="form-select" style="max-width: 300px;">
            <option value="">ทั้งหมด</option>
            <?php
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

    <!-- Table -->
    <div class="table-responsive">
        <table id="dataTable" class="table table-bordered table-striped" style="width:100%">
            <thead class="table-light">
            <tr>
                <!--th>DOC ID</th-->
                <th>วันที่</th>
                <th>ลูกค้า</th>
                <th>เบอร์</th>
                <th>จังหวัด</th>
                <th>รหัสสินค้า</th>
                <th>ชื่อสินค้า</th>
                <th>ยี่ห้อ</th>
                <th>ยอดขาย 2020</th>
                <th>ยอดขาย 2021</th>
                <th>ยอดขาย 2022</th>
                <th>ยอดขาย 2023</th>
                <th>ยอดขาย 2024</th>
                <th>ยอดขาย 2025</th>
                <th>จอง มิ.ย.</th>
                <th>ขาย มิ.ย.</th>
                <th>ยอด ขาย - จอง มิย.</th>
                <th>จอง ก.ค.</th>
                <th>ขาย ก.ค.</th>
                <th>ยอด ขาย - จอง ก.ค.</th>
                <th>จอง ส.ค.</th>
                <th>ขาย ส.ค.</th>
                <th>ยอด ขาย - จอง ส.ค.</th>
                <th>ยอดจองรวม</th>
                <th>ยอดขายรวม</th>
                <th>ยอด ขาย - จอง รวม</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($rows as $row): ?>
                <tr data-sku-cat="<?= htmlspecialchars($row['SKU_CAT_CODE']) ?>">
                    <!--td style='min-width: 200px; white-space: nowrap;'><?= htmlspecialchars($row['DOC_ID']) ?></td-->
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
                    <td class="text-end"><?= number_format($row['quantity_1']) ?></td>
                    <td class="text-end"><?= number_format($row['month6']) ?></td>
                    <td class="text-end"><?= number_format($row['sum_balance_quantity_1']) ?></td>
                    <td class="text-end"><?= number_format($row['quantity_2']) ?></td>
                    <td class="text-end"><?= number_format($row['month7']) ?></td>
                    <td class="text-end"><?= number_format($row['sum_balance_quantity_2']) ?></td>
                    <td class="text-end"><?= number_format($row['quantity_3']) ?></td>
                    <td class="text-end"><?= number_format($row['month8']) ?></td>
                    <td class="text-end"><?= number_format($row['sum_balance_quantity_3']) ?></td>
                    <td class="text-end"><?= number_format($row['sum_quantity_total']) ?></td>
                    <td class="text-end"><?= number_format($row['sum_sale_all_month']) ?></td>
                    <td class="text-end"><?= number_format($row['sum_balance_quantity']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
            <!-- เพิ่มส่วน tfoot ใต้ thead -->
            <tfoot>
            <tr>
                <th colspan="7" style="text-align:right">ยอดรวม:</th>
                <th class="text-end"></th> <!-- year1 -->
                <th class="text-end"></th> <!-- year2 -->
                <th class="text-end"></th> <!-- year3 -->
                <th class="text-end"></th> <!-- year4 -->
                <th class="text-end"></th> <!-- year5 -->
                <th class="text-end"></th> <!-- current_year -->
                <th class="text-end"></th> <!-- quantity_1 -->
                <th class="text-end"></th> <!-- month6 -->
                <th class="text-end"></th> <!-- sum_balance_quantity_1 -->
                <th class="text-end"></th> <!-- quantity_2 -->
                <th class="text-end"></th> <!-- month7 -->
                <th class="text-end"></th> <!-- sum_balance_quantity_2 -->
                <th class="text-end"></th> <!-- quantity_3 -->
                <th class="text-end"></th> <!-- month8 -->
                <th class="text-end"></th> <!-- sum_balance_quantity_3 -->
                <th class="text-end"></th> <!-- sum_quantity_total -->
                <th class="text-end"></th> <!-- sum_sale_all_month -->
                <th class="text-end"></th> <!-- sum_balance_quantity -->
            </tr>
            </tfoot>

        </table>
    </div>
</div>

<!-- Scripts -->
<script src="vendor/jquery/jquery.min.js"></script>
<script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>

<!--script>
    $(document).ready(function () {
        const table = $('#dataTable').DataTable({
            responsive: true,
            scrollX: true,
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
            }
        });

        // Dropdown filter
        $('#skuCatFilter').on('change', function () {
            const selected = this.value;
            table.rows().every(function () {
                const row = $(this.node());
                const skuCat = row.data('sku-cat');
                if (!selected || skuCat === selected) {
                    row.show();
                } else {
                    row.hide();
                }
            });
        });
    });
</script-->

<script>
    $(document).ready(function () {
        const table = $('#dataTable').DataTable({
            responsive: true,
            scrollX: true,
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
            // คำนวณยอดรวมใน footer ทุกครั้งที่เปลี่ยนหน้า หรือ กรองข้อมูล
            drawCallback: function () {
                let api = this.api();

                // รายการคอลัมน์ที่ต้องการคำนวณยอดรวม (index เริ่มจาก 0)
                const sumCols = [7,8,9,10,11,12,13,14,15,16,17,18,19,20,21,22,23,24,25];

                sumCols.forEach(function(colIdx) {
                    // รวมเฉพาะแถวที่แสดงอยู่หลังกรอง (visible rows)
                    let total = api
                        .column(colIdx, {filter:'applied'})
                        .data()
                        .reduce(function(a, b) {
                            // แปลงค่าที่ได้เป็นตัวเลข (เอาค่าที่เป็น string เช่น "1,000" มาลบ comma)
                            let x = typeof a === 'string' ? a.replace(/,/g, '') : a;
                            let y = typeof b === 'string' ? b.replace(/,/g, '') : b;
                            x = parseFloat(x) || 0;
                            y = parseFloat(y) || 0;
                            return x + y;
                        }, 0);

                    // อัปเดต footer ให้แสดงตัวเลขฟอร์แมตคั่นหลักพัน
                    $(api.column(colIdx).footer()).html(total.toLocaleString());
                });
            }
        });

        // ปรับ filter ให้ใช้ DataTables API แทนการซ่อนแถวเอง
        $('#skuCatFilter').on('change', function () {
            const selected = this.value;
            if (selected) {
                table.column(10).search('^' + selected + '$', true, false).draw(); // 10 = SKU_CAT_CODE column index?
                // *** แต่ในตารางเราไม่มีคอลัมน์ SKU_CAT_CODE แสดงในตาราง ***
                // ต้องเปลี่ยนเป็นคอลัมน์ที่เก็บ SKU_CAT_CODE หรือใช้ custom filter

            } else {
                table.column(10).search('').draw();
            }
        });

        // เนื่องจากตารางไม่มีคอลัมน์ SKU_CAT_CODE ที่แสดง
        // เราจะเขียน custom filter สำหรับ SKU_CAT_CODE โดยใช้ attribute data-sku-cat แทน
        $.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                const selected = $('#skuCatFilter').val();
                const skuCat = $(table.row(dataIndex).node()).attr('data-sku-cat');
                if (!selected || skuCat === selected) {
                    return true;
                }
                return false;
            }
        );

        // เมื่อเปลี่ยน dropdown ให้รีเฟรชตาราง (trigger redraw เพื่อเรียก filter ใหม่)
        $('#skuCatFilter').on('change', function () {
            table.draw();
        });
    });
</script>



</body>
</html>
