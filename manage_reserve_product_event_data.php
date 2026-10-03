<?php
session_start();
error_reporting(0);
ini_set('display_errors', 1);

include 'config/connect_db.php';
include('includes/Header.php');

$and_sale = "";
if ($_SESSION['account_type'] === "sale_user") {
    $and_sale = " AND SALE_NAME = '" . addslashes($_SESSION['first_name']) . "'";
}

$and_sale = "";
if ($_SESSION['account_type'] === "take") {
    $and_sale = " AND TAKE_NAME = '" . addslashes($_SESSION['first_name']) . "'";
}

$curr_date = date("d-m-Y");
$curr_year = date("Y");
$curr_year1 = $curr_year - 5;
$curr_year2 = $curr_year - 4;
$curr_year3 = $curr_year - 3;
$curr_year4 = $curr_year - 2;
$curr_year5 = $curr_year - 1;

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
    MAX(r.price_1) AS price_1,
    MAX(r.price_2) AS price_2,
    MAX(r.price_3) AS price_3,
    MAX(r.sale_price_1) AS sale_price_1,
    MAX(r.sale_price_2) AS sale_price_2,
    MAX(r.sale_price_3) AS sale_price_3,
    MAX(r.point) AS point,
    MAX(r.gift_voucher) AS gift_voucher,
    MAX(r.gift_voucher1) AS gift_voucher1,
    MAX(r.gift_voucher2) AS gift_voucher2,
    SUM(r.total_point) AS total_point,
    SUM(r.total_gift_voucher) AS total_gift_voucher_reserved,

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
    ((COALESCE(s.month6, 0) + COALESCE(s.month7, 0) + COALESCE(s.month8, 0)) - SUM(r.quantity_1 + r.quantity_2 + r.quantity_3)) AS sum_balance_quantity,

    (COALESCE(s.month6, 0) + COALESCE(s.month7, 0) + COALESCE(s.month8, 0)) * MAX(r.gift_voucher) AS total_gift_voucher_estimated,
    
    (COALESCE(s.month6, 0)) * MAX(r.point) AS total_point_month6,
    (COALESCE(s.month7, 0)) * MAX(r.point) AS total_point_month7,
    (COALESCE(s.month8, 0)) * MAX(r.point) AS total_point_month8,
    
    CASE 
        WHEN (COALESCE(s.month6, 0) + COALESCE(s.month7, 0) + COALESCE(s.month8, 0)) BETWEEN 60 AND 300 THEN 
            (COALESCE(s.month6, 0) + COALESCE(s.month7, 0) + COALESCE(s.month8, 0)) * MAX(r.gift_voucher)
        WHEN (COALESCE(s.month6, 0) + COALESCE(s.month7, 0) + COALESCE(s.month8, 0)) BETWEEN 301 AND 600 THEN 
            (COALESCE(s.month6, 0) + COALESCE(s.month7, 0) + COALESCE(s.month8, 0)) * MAX(r.gift_voucher1)
        WHEN (COALESCE(s.month6, 0) + COALESCE(s.month7, 0) + COALESCE(s.month8, 0)) > 600 THEN 
            (COALESCE(s.month6, 0) + COALESCE(s.month7, 0) + COALESCE(s.month8, 0)) * MAX(r.gift_voucher2)
        ELSE 0
    END AS total_gift_voucher_estimated

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
) s ON r.SKU_CODE = s.SKU_CODE AND r.CUSTOMER_CODE = s.AR_CODE AND r.BRAND = s.BRAND

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
) sy ON r.SKU_CODE = sy.SKU_CODE AND r.CUSTOMER_CODE = sy.AR_CODE AND r.BRAND = sy.BRAND

WHERE 1=1 {$and_sale}

GROUP BY 
    r.CUSTOMER_CODE,
    r.SKU_CODE,
    r.BRAND,
    s.month6, s.month7, s.month8,
    sy.year1, sy.year2, sy.year3, sy.year4, sy.year5, sy.current_year

ORDER BY r.SALE_NAME  , r.SKU_CAT_CODE , r.SKU_NAME
SQL;

/*
$txt = $sql;
$my_file = fopen("a_param.txt", "w") or die("Unable to open file!");
fwrite($my_file, $txt);
fclose($my_file);
*/

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
<div class="card">
    <div class="card-body">
        <div class="container-fluid mt-4">
            <div class="d-flex justify-content-between align-items-center">
                <h4 class="mb-0 text-primary fw-bold">รายการลงทะเบียนจองยาง</h4>
                <button class="btn btn-danger btn-sm" onclick="window.close();">ปิดหน้าต่าง</button>
            </div>
            <!-- Filter Row -->
            <div class="mb-3 d-flex flex-wrap align-items-end gap-3">

                <!-- SKU Category Filter -->
                <div>
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

                <div>
                    <label for="skuCodeFilter" class="form-label">กรองตาม SKU CODE:</label>
                    <select id="skuCodeFilter" class="form-select" style="max-width: 300px;">
                        <option value="">ทั้งหมด</option>
                        <?php
                        $skuCodes = [];
                        foreach ($rows as $row) {
                            $skuCodes[$row['SKU_CODE']] = $row['SKU_CODE'];
                        }
                        foreach ($skuCodes as $sku) {
                            echo "<option value='" . htmlspecialchars($sku) . "'>" . htmlspecialchars($sku) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label for="skuNameFilter" class="form-label">กรองตาม SKU NAME:</label>
                    <select id="skuNameFilter" class="form-select" style="max-width: 300px;">
                        <option value="">ทั้งหมด</option>
                        <?php
                        $skuNames = [];
                        foreach ($rows as $row) {
                            $skuNames[$row['SKU_NAME']] = $row['SKU_NAME'];
                        }
                        foreach ($skuNames as $sku_name) {
                            echo "<option value='" . htmlspecialchars($sku_name) . "'>" . htmlspecialchars($sku_name) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label for="brandFilter" class="form-label">กรองตาม ยี่ห้อ:</label>
                    <select id="brandFilter" class="form-select" style="max-width: 300px;">
                        <option value="">ทั้งหมด</option>
                        <?php
                        $Brands = [];
                        foreach ($rows as $row) {
                            $Brands[$row['BRAND']] = $row['BRAND'];
                        }
                        foreach ($Brands as $brand) {
                            echo "<option value='" . htmlspecialchars($brand) . "'>" . htmlspecialchars($brand) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label for="customerFilter" class="form-label">กรองตาม ลูกค้า:</label>
                    <select id="customerFilter" class="form-select" style="max-width: 300px;">
                        <option value="">ทั้งหมด</option>
                        <?php
                        $Customers = [];
                        foreach ($rows as $row) {
                            $Customers[$row['CUSTOMER_NAME']] = $row['CUSTOMER_NAME'];
                        }
                        foreach ($Customers as $cus) {
                            echo "<option value='" . htmlspecialchars($cus) . "'>" . htmlspecialchars($cus) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- SALE Name Filter -->
                <div>
                    <label for="saleNameFilter" class="form-label">กรองตามชื่อ SALE:</label>
                    <select id="saleNameFilter" class="form-select" style="max-width: 300px;">
                        <option value="">ทั้งหมด</option>
                        <?php
                        $saleNames = [];
                        foreach ($rows as $row) {
                            $saleNames[$row['SALE_NAME']] = $row['SALE_NAME'];
                        }
                        foreach ($saleNames as $sale) {
                            echo "<option value='" . htmlspecialchars($sale) . "'>" . htmlspecialchars($sale) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <div>
                    <label for="ProvinceFilter" class="form-label">กรองตาม จังหวัด:</label>
                    <select id="ProvinceFilter" class="form-select" style="max-width: 300px;">
                        <option value="">ทั้งหมด</option>
                        <?php
                        $provinces = [];
                        foreach ($rows as $row) {
                            $provinces[$row['PROVINCE']] = $row['PROVINCE'];
                        }
                        foreach ($provinces as $prov) {
                            echo "<option value='" . htmlspecialchars($prov) . "'>" . htmlspecialchars($prov) . "</option>";
                        }
                        ?>
                    </select>
                </div>

                <!-- Sort Dropdown -->
                <!--div class="mb-3 d-flex gap-3 align-items-end">
                    <div>
                        <label for="sortColumn1" class="form-label">เรียงลำดับหลัก:</label>
                        <select id="sortColumn1" class="form-select" style="max-width: 200px;">
                            <option value="">-- เลือกคอลัมน์ --</option>
                            <option value="1">CUSTOMER_NAME</option>
                            <option value="2">SALE_NAME</option>
                            <option value="4">PROVINCE</option>
                            <option value="5">SKU_CODE</option>
                            <option value="6">SKU_NAME</option>
                        </select>
                    </div>
                    <div>
                        <label for="sortColumn2" class="form-label">เรียงลำดับรอง:</label>
                        <select id="sortColumn2" class="form-select" style="max-width: 200px;">
                            <option value="">-- เลือกคอลัมน์ --</option>
                            <option value="1">CUSTOMER_NAME</option>
                            <option value="2">SALE_NAME</option>
                            <option value="4">PROVINCE</option>
                            <option value="5">SKU_CODE</option>
                            <option value="6">SKU_NAME</option>
                        </select>
                    </div>
                </div-->

                <div>
                    <label class="form-label d-block">&nbsp;</label>
                    <button id="resetFilters" class="btn btn-secondary">ล้างตัวกรองทั้งหมด</button>
                </div>

            </div>


            <div class="table-responsive">
                <table id="dataTable" class="table table-bordered table-striped" style="width:100%">
                    <thead class="table-light">
                    <tr>
                        <!--th>DOC ID</th--> <!-- Column ที่ 1 ถูกคอมเมนต์ไว้ -->
                        <th>วันที่</th> <!-- 1 -->
                        <th>ลูกค้า</th> <!-- 2 -->
                        <th>ชื่อ SALE</th> <!-- 3 -->
                        <th>เบอร์</th> <!-- 4 -->
                        <th>จังหวัด</th> <!-- 5 -->
                        <th>รหัสสินค้า</th> <!-- 6 -->
                        <th>ชื่อสินค้า</th> <!-- 7 -->
                        <th>ยี่ห้อ</th> <!-- 8 -->
                        <th>คะแนน/เส้น</th> <!-- 9 -->
                        <th>gift 60-300 เส้น</th> <!-- 10 -->
                        <th>gift 301-600 เส้น</th> <!-- 11 -->
                        <th>gift 601-1000 เส้น</th> <!-- 12 -->
                        <th>300 เส้น</th> <!-- 13 -->
                        <th>120 เส้น</th> <!-- 14 -->
                        <th>60 เส้น</th> <!-- 15 -->
                        <th>ยอดขาย <?= $curr_year1 ?></th> <!-- 16 -->
                        <th>ยอดขาย <?= $curr_year2 ?></th> <!-- 17 -->
                        <th>ยอดขาย <?= $curr_year3 ?></th> <!-- 18 -->
                        <th>ยอดขาย <?= $curr_year4 ?></th> <!-- 19 -->
                        <th>ยอดขาย <?= $curr_year5 ?></th> <!-- 20 -->
                        <th>ยอดขาย <?= $curr_year ?></th> <!-- 21 -->
                        <th>จอง มิ.ย.</th> <!-- 22 -->
                        <th>ขาย มิ.ย.</th> <!-- 23 -->
                        <th style='min-width: 120px; white-space: nowrap;'>ยอด ขาย - จอง มิ.ย.</th> <!-- 24 -->
                        <th>จอง ก.ค.</th> <!-- 25 -->
                        <th>ขาย ก.ค.</th> <!-- 26 -->
                        <th style='min-width: 120px; white-space: nowrap;'>ยอด ขาย - จอง ก.ค.</th> <!-- 27 -->
                        <th>จอง ส.ค.</th> <!-- 28 -->
                        <th>ขาย ส.ค.</th> <!-- 29 -->
                        <th style='min-width: 120px; white-space: nowrap;'>ยอด ขาย - จอง ส.ค.</th> <!-- 30 -->
                        <th>ยอดจองรวม</th> <!-- 31 -->
                        <th>ยอดขายรวม</th> <!-- 32 -->
                        <th style='min-width: 120px; white-space: nowrap;'>ยอด ขาย - จอง รวม</th> <!-- 33 -->
                        <th>บัตรของขวัญ รวม</th> <!-- 34 -->
                        <th>คะแนน มิ.ย.</th> <!-- 35 -->
                        <th>คะแนน ก.ค.</th> <!-- 36 -->
                        <th>คะแนน ส.ค.</th> <!-- 37 -->
                        <th>คะแนน รวม</th> <!-- 38 -->
                        <th>ประมาณการ 300 เส้น</th> <!-- 39 -->
                        <th>ประมาณการ 120 เส้น</th> <!-- 40 -->
                        <th>ประมาณการ 60 เส้น</th> <!-- 41 -->
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($rows as $row): ?>
                        <tr data-sku-cat="<?= htmlspecialchars($row['SKU_CAT_CODE']) ?>">
                            <!--td><?= htmlspecialchars($row['DOC_ID']) ?></td--> <!-- (ไม่แสดง) -->
                            <td><?= htmlspecialchars($row['DOC_DATE']) ?></td> <!-- 1 -->
                            <td><?= htmlspecialchars($row['CUSTOMER_NAME']) ?></td> <!-- 2 -->
                            <td><?= htmlspecialchars($row['SALE_NAME']) ?></td> <!-- 3 -->
                            <td><?= htmlspecialchars($row['PHONE']) ?></td> <!-- 4 -->
                            <td><?= htmlspecialchars($row['PROVINCE']) ?></td> <!-- 5 -->
                            <td><?= htmlspecialchars($row['SKU_CODE']) ?></td> <!-- 6 -->
                            <td><?= htmlspecialchars($row['SKU_NAME']) ?></td> <!-- 7 -->
                            <td><?= htmlspecialchars($row['BRAND']) ?></td> <!-- 8 -->
                            <td class="text-end"><?= number_format($row['point']) ?></td> <!-- 9 -->
                            <td class="text-end"><?= htmlspecialchars($row['gift_voucher']) ?></td> <!-- 10 -->
                            <td class="text-end"><?= htmlspecialchars($row['gift_voucher1']) ?></td> <!-- 11 -->
                            <td class="text-end"><?= htmlspecialchars($row['gift_voucher2']) ?></td> <!-- 12 -->
                            <td class="text-end"><?= number_format($row['price_1']) ?></td> <!-- 13 -->
                            <td class="text-end"><?= number_format($row['price_2']) ?></td> <!-- 14 -->
                            <td class="text-end"><?= number_format($row['price_3']) ?></td> <!-- 15 -->
                            <td class="text-end"><?= number_format($row['year1']) ?></td> <!-- 16 -->
                            <td class="text-end"><?= number_format($row['year2']) ?></td> <!-- 17 -->
                            <td class="text-end"><?= number_format($row['year3']) ?></td> <!-- 18 -->
                            <td class="text-end"><?= number_format($row['year4']) ?></td> <!-- 19 -->
                            <td class="text-end"><?= number_format($row['year5']) ?></td> <!-- 20 -->
                            <td class="text-end"><?= number_format($row['current_year']) ?></td> <!-- 21 -->
                            <td class="text-end"><?= number_format($row['quantity_1']) ?></td> <!-- 22 -->
                            <td class="text-end"><?= number_format($row['month6']) ?></td> <!-- 23 -->
                            <td class="text-end"><?= number_format($row['sum_balance_quantity_1']) ?></td> <!-- 24 -->
                            <td class="text-end"><?= number_format($row['quantity_2']) ?></td> <!-- 25 -->
                            <td class="text-end"><?= number_format($row['month7']) ?></td> <!-- 26 -->
                            <td class="text-end"><?= number_format($row['sum_balance_quantity_2']) ?></td> <!-- 27 -->
                            <td class="text-end"><?= number_format($row['quantity_3']) ?></td> <!-- 28 -->
                            <td class="text-end"><?= number_format($row['month8']) ?></td> <!-- 29 -->
                            <td class="text-end"><?= number_format($row['sum_balance_quantity_3']) ?></td> <!-- 30 -->
                            <td class="text-end"><?= number_format($row['sum_quantity_total']) ?></td> <!-- 31 -->
                            <td class="text-end"><?= number_format($row['sum_sale_all_month']) ?></td> <!-- 32 -->
                            <td class="text-end"><?= number_format($row['sum_balance_quantity']) ?></td> <!-- 33 -->
                            <td class="text-end"><?= number_format($row['total_gift_voucher_estimated']) ?></td>
                            <!-- 34 -->
                            <td class="text-end"><?= number_format($row['total_point_month6']) ?></td> <!-- 35 -->
                            <td class="text-end"><?= number_format($row['total_point_month7']) ?></td> <!-- 36 -->
                            <td class="text-end"><?= number_format($row['total_point_month8']) ?></td> <!-- 37 -->
                            <td class="text-end"><?= number_format($row['total_point_estimated']) ?></td> <!-- 38 -->
                            <td class="text-end"><?= number_format($row['sale_price_1']) ?></td> <!-- 39 -->
                            <td class="text-end"><?= number_format($row['sale_price_2']) ?></td> <!-- 40 -->
                            <td class="text-end"><?= number_format($row['sale_price_3']) ?></td> <!-- 41 -->
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                    <tfoot class="table-light">
                    <tr>
                        <th></th> <!-- 1 -->
                        <th></th> <!-- 2 -->
                        <th>รวม</th> <!-- 3 -->
                        <th></th> <!-- 4 -->
                        <th></th> <!-- 5 -->
                        <th></th> <!-- 6 -->
                        <th></th> <!-- 7 -->
                        <th></th> <!-- 8 -->
                        <th></th> <!-- 9 -->
                        <th></th> <!-- 10 -->
                        <th></th> <!-- 11 -->
                        <th></th> <!-- 12 -->
                        <th></th> <!-- 13 -->
                        <th></th> <!-- 14 -->
                        <th></th> <!-- 15 -->
                        <th></th> <!-- 16 -->
                        <th></th> <!-- 17 -->
                        <th></th> <!-- 18 -->
                        <th></th> <!-- 19 -->
                        <th></th> <!-- 20 -->
                        <th></th> <!-- 21 -->
                        <th></th> <!-- 22 -->
                        <th></th> <!-- 23 -->
                        <th></th> <!-- 24 -->
                        <th></th> <!-- 25 -->
                        <th></th> <!-- 26 -->
                        <th></th> <!-- 27 -->
                        <th></th> <!-- 28 -->
                        <th></th> <!-- 29 -->
                        <th></th> <!-- 30 -->
                        <th></th> <!-- 31 -->
                        <th></th> <!-- 32 -->
                        <th></th> <!-- 33 -->
                        <th></th> <!-- 34 -->
                        <th></th> <!-- 35 -->
                        <th></th> <!-- 36 -->
                        <th></th> <!-- 37 -->
                        <th></th> <!-- 38 -->
                        <th></th> <!-- 39 -->
                        <th></th> <!-- 40 -->
                        <th></th> <!-- 41 -->
                    </tr>
                    </tfoot>
                </table>
            </div>

        </div>
    </div>
</div>

<script src="vendor/jquery/jquery-3.7.0.min.js"></script>
<script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
<script src="vendor/datatables/v11/dataTables.buttons.min.js"></script>
<script src="vendor/datatables/v11/buttons.html5.min.js"></script>
<script src="vendor/datatables/v11/jszip.min.js"></script>

<!-- เพิ่ม CSS สำหรับ FixedColumns -->
<link href="https://cdn.datatables.net/fixedcolumns/4.3.0/css/fixedColumns.dataTables.min.css" rel="stylesheet"/>
<!-- เพิ่ม JS สำหรับ FixedColumns -->
<script src="https://cdn.datatables.net/fixedcolumns/4.3.0/js/dataTables.fixedColumns.min.js"></script>

<!-- CSS -->
<link rel="stylesheet" href="https://cdn.datatables.net/fixedheader/3.4.0/css/fixedHeader.dataTables.min.css">

<!-- JS -->
<script src="https://cdn.datatables.net/fixedheader/3.4.0/js/dataTables.fixedHeader.min.js"></script>

<!-- CSS for Buttons -->
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">

<!-- JS for DataTables Buttons -->
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

<!-- JSZip for Excel Export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>

<!-- pdfmake for PDF Export -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.68/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.68/vfs_fonts.js"></script>


<style>
    th, td {
        white-space: nowrap;
    }

    .table-responsive {
        overflow: auto;
        position: relative;
    }

    #reserveTable {
        width: 100% !important;
    }
</style>

<script>
    $(document).ready(function () {
        let table = $('#dataTable').DataTable({
            "ordering": true,  // ค่า default คือ true อยู่แล้ว
            lengthMenu: [[5, 10, 20, 50, 100], [5, 10, 20, 50, 100]],
            language: {
                search: 'ค้นหา',
                lengthMenu: 'แสดง _MENU_ รายการ  |  Click ที่ชื่อ Column เพื่อเรียงลำดับ',
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
            pageLength: 10,
            "order": [[2, 'asc']], // เรียงลำดับเริ่มต้นที่ column ชื่อ SALE_NAME (index 2)
            "dom": 'lBfrtip',
            // buttons: ['copyHtml5', 'excelHtml5', 'csvHtml5', 'pdfHtml5'],
            buttons: [
                'copyHtml5',
                {
                    extend: 'excelHtml5',
                    text: 'Export Excel',
                    exportOptions: {
                        columns: ':visible',
                        search: 'applied'
                    }
                },
                {
                    extend: 'csvHtml5',
                    text: 'Export CSV',
                    bom: true,
                    exportOptions: {
                        columns: ':visible',
                        search: 'applied'
                    }
                }
            ],
            scrollX: true,
            paging: true,
            fixedHeader: true, // ตรึง header
            fixedColumns: {
                leftColumns: 6
            },
            columnDefs: [
                {
                    targets: [15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37], // index ของคอลัมน์ตัวเลข
                    className: 'text-end'
                }
            ],
            footerCallback: function (row, data, start, end, display) {
                let api = this.api();

                // ฟังก์ชันช่วยแปลง string เป็น number
                let intVal = function (i) {
                    return typeof i === 'string' ?
                        i.replace(/[,]/g, '') * 1 :
                        typeof i === 'number' ?
                            i : 0;
                };

                // columnsToSum ตาม index ของคอลัมน์ที่ต้องการรวม (0-based)
                let columnsToSum = [15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37];

                columnsToSum.forEach(function (colIdx) {
                    // sum เฉพาะข้อมูลที่กรองแล้ว
                    let total = api
                        .column(colIdx, {filter: 'applied'})
                        .data()
                        .reduce(function (a, b) {
                            return intVal(a) + intVal(b);
                        }, 0);

                    // แสดงผลใน footer
                    $(api.column(colIdx).footer()).html(total.toLocaleString());
                });
            }
        });

        // ฟิลเตอร์ SKU_CAT_CODE ด้วย custom search
        $.fn.dataTable.ext.search.push(
            function (settings, data, dataIndex) {
                let selectedCat = $('#skuCatFilter').val();
                let rowSkuCat = table.row(dataIndex).node().getAttribute('data-sku-cat');
                if (!selectedCat) return true; // ถ้าไม่เลือกกรอง ให้แสดงทุกแถว
                return rowSkuCat === selectedCat;
            }
        );

        $('#skuCatFilter').on('change', function () {
            table.draw();
        });

        $('#saleNameFilter').on('change', function () {
            const val = $(this).val();
            table.column(2).search(val).draw(); // 👈
        });

        $('#skuCodeFilter').on('change', function () {
            const val = $(this).val();
            table.column(5).search(val).draw(); //
        });

        $('#skuNameFilter').on('change', function () {
            const val = $(this).val();
            table.column(6).search(val).draw(); //
        });

        $('#brandFilter').on('change', function () {
            const val = $(this).val();
            table.column(7).search(val).draw(); //
        });

        $('#customerFilter').on('change', function () {
            const val = $(this).val();
            table.column(1).search(val).draw(); //
        });

        $('#ProvinceFilter').on('change', function () {
            const val = $(this).val();
            table.column(4).search(val).draw(); //
        });

    });
</script>

<script>
    $('#resetFilters').on('click', function () {
        // เคลียร์ค่าจาก dropdown ทั้งหมด
        $('#skuCatFilter, #skuCodeFilter, #skuNameFilter, #brandFilter, #customerFilter, #saleNameFilter, #ProvinceFilter')
            .val('') // set value to ""
            .trigger('change'); // trigger event สำหรับบาง plugin หรือ select2

        // รีเซ็ตการค้นหาใน DataTable
        table.columns().search('').draw();
    });

</script>


</body>
</html>
