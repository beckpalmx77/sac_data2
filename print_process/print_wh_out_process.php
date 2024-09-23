<?php
include('../config/connect_db.php');

// ดึงข้อมูลตามวันที่เริ่มต้นและสิ้นสุดที่ส่งมาจาก form
$doc_date_start = $_GET['doc_date_start'];
$doc_date_to = $_GET['doc_date_to'];
$car_no = $_GET['car_no_main'];

$where_cond = "";

if ($car_no!=='-') {
    $where_cond = " AND car_no = " . $car_no;
}

$stmt = $conn->prepare("SELECT * FROM v_wh_stock_movement_out WHERE doc_date BETWEEN :start_date AND :end_date " . $where_cond);
$stmt->execute(['start_date' => $doc_date_start, 'end_date' => $doc_date_to]);
$data = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="img/favicon.ico" type="image/x-icon">
    <link href="../img/logo/logo-01.png" rel="icon">
    <title>สงวนออโต้คาร์ | SANGUAN AUTO CAR</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            @page {
                size: A4 landscape;
                margin: 0.5cm;
            }

            body {
                -webkit-print-color-adjust: exact;
            }

            .table {
                font-size: 14px;
            }

            .table-responsive {
                overflow: visible !important;
            }

            /* ซ่อนปุ่มปิดหน้าจอเฉพาะการพิมพ์ */
            .no-print {
                display: none;
            }
        }

        .table-responsive {
            overflow-x: auto;
        }
    </style>
</head>

<body onload="window.print()"> <!-- เรียกคำสั่ง window.print() เมื่อหน้าเพจโหลด -->

<!--div class="container"-->
<h2>รายงานการตัดจ่ายสินค้าคงคลัง</h2>

<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>วันที่เอกสาร</th>
            <th>รหัสสินค้า</th>
            <th>ชื่อสินค้า</th>
            <th>จำนวน</th>
            <th>คลังต้นทาง</th>
            <th>สัปดาห์</th>
            <th>ตำแหน่ง</th>
            <th>เลขที่เอกสาร</th>
            <th>รถคันที่</th>
            <th>ชื่อ Sale/Take</th>
            <th>ชื่อลูกค้า</th>
            <th>คงเหลือ</th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($data as $row) : ?>
            <tr>
                <td><?= htmlspecialchars($row['doc_date'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['product_id'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['product_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['qty'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['wh_org'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['wh_week_id'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['location_org'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['doc_id'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['car_no'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['sale_take'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['customer_name'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['total_qty'] ?? '') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<!--/div-->

<script>
    // JavaScript event to adjust table style before printing
    window.onbeforeprint = function () {
        document.querySelector('.table').style.fontSize = '10px'; // ลดขนาดฟอนต์ก่อนพิมพ์
    };

    window.onafterprint = function () {
        document.querySelector('.table').style.fontSize = ''; // คืนค่าฟอนต์กลับหลังจากพิมพ์เสร็จ
    };
</script>