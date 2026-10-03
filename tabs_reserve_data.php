<?php

include 'config/connect_db.php';

$sql = "
    SELECT
        r.CUSTOMER_CODE,
        r.CUSTOMER_NAME,
        r.SKU_CODE,
        r.SKU_NAME,
        r.SKU_CAT_CODE,
        r.SKU_CAT_NAME,
        r.BRAND,
        COALESCE(s.month1, 0) AS month1,
        COALESCE(s.month2, 0) AS month2,
        COALESCE(s.month3, 0) AS month3,
        COALESCE(s.month4, 0) AS month4,
        COALESCE(s.month5, 0) AS month5,
        COALESCE(s.month6, 0) AS month6,
        COALESCE(s.month7, 0) AS month7,
        COALESCE(s.month8, 0) AS month8,
        COALESCE(s.month9, 0) AS month9,
        COALESCE(s.month10, 0) AS month10,
        COALESCE(s.month11, 0) AS month11,
        COALESCE(s.month12, 0) AS month12,
        COALESCE(SUM(r.quantity_1), 0) AS total_quantity_1,
        COALESCE(SUM(r.quantity_2), 0) AS total_quantity_2,
        COALESCE(SUM(r.quantity_3), 0) AS total_quantity_3
    FROM
        ims_product_reserve_event r
    LEFT JOIN (
        SELECT 
            AR_CODE,
            AR_NAME,
            SKU_CODE,
            SKU_NAME,
            BRAND,
            SUM(CASE WHEN DI_MONTH = 1 THEN TRD_QTY ELSE 0 END) AS month1,
            SUM(CASE WHEN DI_MONTH = 2 THEN TRD_QTY ELSE 0 END) AS month2,
            SUM(CASE WHEN DI_MONTH = 3 THEN TRD_QTY ELSE 0 END) AS month3,
            SUM(CASE WHEN DI_MONTH = 4 THEN TRD_QTY ELSE 0 END) AS month4,
            SUM(CASE WHEN DI_MONTH = 5 THEN TRD_QTY ELSE 0 END) AS month5,
            SUM(CASE WHEN DI_MONTH = 6 THEN TRD_QTY ELSE 0 END) AS month6,
            SUM(CASE WHEN DI_MONTH = 7 THEN TRD_QTY ELSE 0 END) AS month7,
            SUM(CASE WHEN DI_MONTH = 8 THEN TRD_QTY ELSE 0 END) AS month8,
            SUM(CASE WHEN DI_MONTH = 9 THEN TRD_QTY ELSE 0 END) AS month9,
            SUM(CASE WHEN DI_MONTH = 10 THEN TRD_QTY ELSE 0 END) AS month10,
            SUM(CASE WHEN DI_MONTH = 11 THEN TRD_QTY ELSE 0 END) AS month11,
            SUM(CASE WHEN DI_MONTH = 12 THEN TRD_QTY ELSE 0 END) AS month12
        FROM ims_data_sale_sac_all
        WHERE DI_YEAR = '2025' AND AR_CODE = 'SAC.0000272'
        GROUP BY AR_CODE, AR_NAME, SKU_CODE, SKU_NAME, BRAND
    ) s ON r.SKU_CODE = s.SKU_CODE AND r.CUSTOMER_CODE = s.AR_CODE
    WHERE r.CUSTOMER_CODE = 'SAC.0000272'
    GROUP BY
        r.CUSTOMER_CODE,
        r.CUSTOMER_NAME,
        r.SKU_CODE,
        r.SKU_NAME,
        r.SKU_CAT_CODE,
        r.SKU_CAT_NAME,
        r.BRAND,
        s.month1,
        s.month2,
        s.month3,
        s.month4,
        s.month5,
        s.month6,
        s.month7,
        s.month8,
        s.month9,
        s.month10,
        s.month11,
        s.month12
    ORDER BY r.SKU_CODE
";

// รันคำสั่ง SQL
$stmt = $conn->query($sql);
$results = $stmt->fetchAll();

// หมวดหมู่ตาม SKU_CAT_CODE
$categories = [
    'G1' => 'ยางรถกระบะเล็กและรถตู้',
    'G2' => 'ยางรถเก๋งเล็กและรถเก๋งกลาง',
    'G3' => 'ยางรถซิ่ง',
    'G4' => 'ยางรถกระบะสูงและยางรถ SUV',
    'G5' => 'ยาง ATLAS'
];

// สมมุติว่าคุณ fetch ข้อมูลจากฐานข้อมูลมาเก็บไว้ใน $results
$results = []; // <- ตรงนี้ควรเป็นผลลัพธ์จากการ query

// จำแนกข้อมูลตาม SKU_CAT_CODE
$data_by_category = [];
foreach ($results as $row) {
    $cat_code = $row['SKU_CAT_CODE'];
    $data_by_category[$cat_code][] = $row;
}
?>

<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>Product Target Point</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="vendor/datatables/v11/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="vendor/datatables/v11/buttons.dataTables.min.css"/>
    <!-- Bootstrap Select -->
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
    <!-- JavaScript -->
    <script src="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/alertify.min.js"></script>

    <!-- CSS -->
    <link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.14.0/build/css/alertify.min.css"/>

</head>

<!-- สร้าง Tab ด้วย Bootstrap -->
<ul class="nav nav-tabs" id="skuTab" role="tablist">
    <?php $i = 0;
    foreach ($categories as $cat_code => $cat_name): ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?php if ($i == 0) echo 'active'; ?>" id="tab-<?= $cat_code ?>" data-bs-toggle="tab"
                    data-bs-target="#content-<?= $cat_code ?>" type="button" role="tab">
                <?= $cat_name ?>
            </button>
        </li>
        <?php $i++; endforeach; ?>
</ul>

<!-- เนื้อหาแต่ละ Tab -->
<div class="tab-content mt-3" id="skuTabContent">
    <?php $i = 0;
    foreach ($categories as $cat_code => $cat_name): ?>
        <div class="tab-pane fade <?php if ($i == 0) echo 'show active'; ?>" id="content-<?= $cat_code ?>"
             role="tabpanel">
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-sm">
                    <thead class="table-primary">
                    <tr>
                        <th>SKU CODE</th>
                        <th>SKU NAME</th>
                        <th>BRAND</th>
                        <th>Month 6</th>
                        <th>Month 7</th>
                        <th>Month 8</th>
                        <th>Total Q1</th>
                        <th>Total Q2</th>
                        <th>Total Q3</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if (!empty($data_by_category[$cat_code])): ?>
                        <?php foreach ($data_by_category[$cat_code] as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['SKU_CODE']) ?></td>
                                <td><?= htmlspecialchars($row['SKU_NAME']) ?></td>
                                <td><?= htmlspecialchars($row['BRAND']) ?></td>
                                <td><?= $row['month6'] ?></td>
                                <td><?= $row['month7'] ?></td>
                                <td><?= $row['month8'] ?></td>
                                <td><?= $row['total_quantity_1'] ?></td>
                                <td><?= $row['total_quantity_2'] ?></td>
                                <td><?= $row['total_quantity_3'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="10" class="text-center">ไม่มีข้อมูล</td>
                        </tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php $i++; endforeach; ?>
</div>

<script src="vendor/jquery/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="vendor/datatables/v11/jquery.dataTables.min.js"></script>
<script src="vendor/datatables/v11/dataTables.buttons.min.js"></script>
<script src="vendor/jquery-easing/jquery.easing.min.js"></script>
<script src="js/myadmin.min.js"></script>

<!-- Bootstrap Select -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>

<!-- CSS -->
<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/alertify.min.css"/>
<!-- Theme (optional) -->
<link rel="stylesheet" href="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/css/themes/bootstrap.min.css"/>

<!-- JS -->
<script src="//cdn.jsdelivr.net/npm/alertifyjs@1.13.1/build/alertify.min.js"></script>

<style>
    .table-wrapper {
        overflow-x: auto;
        width: 100%;
    }
</style>

<style>
    .input-3-digit {
        min-width: 80px;
        text-align: right; /* เพื่อให้ตัวเลขชิดขวา */
    }
</style>

