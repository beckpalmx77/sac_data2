<?php

include '../config/connect_db.php';

// รับค่าจาก AJAX
$DI_YEAR = $_POST['DI_YEAR'];
$DI_MONTH = $_POST['DI_MONTH'];
$SKU_CAT = $_POST['SKU_CAT'];
$SALE_NAME = $_POST['SALE_NAME'];

// ดึงข้อมูลจากฐานข้อมูล
$sql = "SELECT TRD_PROVINCE, DI_DAY,
               SUM(CAST(TRD_QTY AS DECIMAL(10, 2))) AS TRD_QTY,
               SUM(CAST(TRD_AMOUNT_PRICE AS DECIMAL(10, 2))) AS TRD_AMOUNT_PRICE
        FROM ims_data_sale_sac_all
        WHERE DI_YEAR = :DI_YEAR AND DI_MONTH = :DI_MONTH AND SKU_CAT = :SKU_CAT AND SALE_NAME = :SALE_NAME
        GROUP BY TRD_PROVINCE, DI_DAY
        ORDER BY TRD_PROVINCE, CAST(DI_DAY AS UNSIGNED)";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':DI_YEAR', $DI_YEAR);
$stmt->bindParam(':DI_MONTH', $DI_MONTH);
$stmt->bindParam(':SKU_CAT', $SKU_CAT);
$stmt->bindParam(':SALE_NAME', $SALE_NAME);
$stmt->execute();

// เก็บข้อมูลใน array 2 มิติแบบ pivot
$data = [];
$days = range(1, 31); // สมมติว่าเดือนมี 31 วัน

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $province = $row['TRD_PROVINCE'];
    $day = (int)$row['DI_DAY'];

    // ตรวจสอบว่า province นี้อยู่ใน array หรือไม่
    if (!isset($data[$province])) {
        $data[$province] = array_fill(1, 31, '-'); // เตรียมค่า default เป็น "-" สำหรับทุกวัน
    }

    // เก็บข้อมูลยอดขายลงในตำแหน่งวันที่ตรงกัน
    $data[$province][$day] = $row['TRD_AMOUNT_PRICE'];
}

// สร้างตารางหัวแถวแสดงวัน พร้อม ID 'salesTable'
echo "<div class='table-responsive'>";
echo "<table id='salesTable' class='table table-bordered table-hover'>";
echo "<thead class='table-dark'><tr><th>จังหวัด</th>";
foreach ($days as $day) {
    echo "<th>วัน $day</th>";
}
echo "</tr></thead>";
echo "<tbody>";

// แสดงข้อมูลที่ถูกจัดเรียงในแนวนอน
foreach ($data as $province => $sales) {
    echo "<tr>";
    echo "<td>" . $province . "</td>"; // แสดงจังหวัด
    foreach ($sales as $day => $amount) {
        echo "<td>" . $amount . "</td>"; // แสดงยอดขายประจำวัน
    }
    echo "</tr>";
}

echo "</tbody></table>";
echo "</div>";
