<?php
$servername = "localhost"; // หรือชื่อโฮสต์ของฐานข้อมูล
$username = "myadmin"; // ใส่ชื่อผู้ใช้ฐานข้อมูลของคุณ
$password = "myadmin"; // ใส่รหัสผ่านของคุณ
$dbname = "sac_data2"; // ใส่ชื่อฐานข้อมูลของคุณ
$port = 3307; // ตั้งค่าพอร์ตที่ใช้เชื่อมต่อ

try {
    // สร้างการเชื่อมต่อ PDO
    $conn = new PDO("mysql:host=$servername;port=$port;dbname=$dbname", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // คำสั่ง SQL
    $sql = "SELECT TRD_PROVINCE, DI_DAY,
        SUM(CAST(TRD_QTY AS DECIMAL(10, 2))) AS TRD_QTY
    FROM ims_data_sale_sac_all
    WHERE DI_YEAR = '2024' AND SKU_CAT = 'ยางเล็ก' AND DI_MONTH = '9' AND SALE_NAME = 'จิรกร (เตี้ยม)' 
    GROUP BY TRD_PROVINCE, DI_DAY 
    ORDER BY TRD_PROVINCE, DI_DAY";

    $stmt = $conn->prepare($sql);
    $stmt->execute();

    // เก็บข้อมูลในอาร์เรย์
    $data = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data[$row['TRD_PROVINCE']][$row['DI_DAY']] = $row['TRD_QTY'];
    }

    // แสดงหัวตารางสำหรับวันที่
    echo "<table border='1'>";
    echo "<tr><th>วันที่</th>";
    for ($day = 1; $day <= 30; $day++) {
        echo "<th>$day</th>";
    }
    echo "</tr>";

    // แสดงข้อมูลแต่ละจังหวัด
    foreach ($data as $province => $days) {
        echo "<tr><td>$province</td>";
        for ($day = 1; $day <= 30; $day++) {
            echo "<td>" . (isset($days[$day]) ? $days[$day] : '') . "</td>";
        }
        echo "</tr>";
    }
    echo "</table>";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// ปิดการเชื่อมต่อ
$conn = null;


