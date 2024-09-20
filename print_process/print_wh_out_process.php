<?php
include('../config/connect_db.php');

// ดึงข้อมูลตามวันที่เริ่มต้นและสิ้นสุดที่ส่งมาจาก form
$doc_date_start = $_GET['doc_date_start'];
$doc_date_to = $_GET['doc_date_to'];

$stmt = $conn->prepare("SELECT * FROM v_wh_stock_movement_out WHERE doc_date BETWEEN :start_date AND :end_date");
$stmt->execute(['start_date' => $doc_date_start, 'end_date' => $doc_date_to]);
$data = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Preview</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css">
    <style>
        @media print {
            @page {
                size: landscape; /* ตั้งค่าเป็นแนวนอน */
                margin: 1cm; /* กำหนดระยะขอบ */
            }
            body {
                -webkit-print-color-adjust: exact; /* เพื่อให้พิมพ์สีได้ */
            }
        }
    </style>
</head>
<body onload="window.print();">
<div class="container">
    <h2>รายงานการตัดจ่ายสินค้าคงคลัง</h2>
    <table class="table table-bordered">
        <thead>
        <tr>
            <th>วันที่เอกสาร</th>
            <th>รหัสสินค้า</th>
            <th>ชื่อสินค้า</th>
            <th>จำนวน</th>
            <th>คลังต้นทาง</th>
            <th>ทะเบียนรถ</th>
            <th>ชื่อลูกค้า</th>
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
                <td><?= htmlspecialchars($row['car_no'] ?? '') ?></td>
                <td><?= htmlspecialchars($row['customer_name'] ?? '') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
</body>
</html>