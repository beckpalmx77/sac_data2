<?php
// เชื่อมต่อฐานข้อมูล
include("../config/connect_db.php");

$date = date("d/m/Y");
$total = 0;
$html_rows = "";

$sql_daily = "SELECT SLMN_NAME, sum(CAST(TRD_G_KEYIN AS DECIMAL(10,2))) as TRD_G_KEYIN
              FROM ims_product_sale_sac 
              WHERE DI_DATE = '" . $date . "' 
              GROUP BY SLMN_NAME
              ORDER BY SLMN_NAME";

$statement = $conn->query($sql_daily);
$results = $statement->fetchAll(PDO::FETCH_ASSOC);

if (count($results) > 0) {
    foreach ($results as $row) {
        $amount = $row['TRD_G_KEYIN'];
        $total += $amount;

        $html_rows .= '<tr>';
        $html_rows .= '<td>' . htmlentities($row['SLMN_NAME']) . '</td>';
        $html_rows .= '<td><p class="number">' . number_format($amount, 2) . '</p></td>';
        $html_rows .= '</tr>';
    }
} else {
    $html_rows = '<tr><td colspan="2" class="text-center">ไม่พบข้อมูลยอดขายวันนี้</td></tr>';
}

$total_text = "ยอดขายรวม วันที่ " . $date . " = " . number_format($total, 2) . " บาท";

// ส่งค่ากลับเป็น JSON เพื่อให้ JS แยกเอาไปใช้งานได้ง่าย
echo json_encode(array(
    "html" => $html_rows,
    "total_text" => $total_text
));