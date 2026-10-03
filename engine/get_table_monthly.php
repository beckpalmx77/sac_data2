<?php
include("../config/connect_db.php");

$month_name_th = array(
    "1" => "มกราคม", "2" => "กุมภาพันธ์", "3" => "มีนาคม", "4" => "เมษายน",
    "5" => "พฤษภาคม", "6" => "มิถุนายน", "7" => "กรกฎาคม", "8" => "สิงหาคม",
    "9" => "กันยายน", "10" => "ตุลาคม", "11" => "พฤศจิกายน", "12" => "ธันวาคม"
);
$month_text = $month_name_th[date("n")] . " " . date("Y");
$sale_point = 15000000;
$sale_point_text = '10,500,000 บาท';

$total = 0;
$html_rows = "";

$sql_monthly = "SELECT SLMN_NAME, sum(CAST(TRD_G_KEYIN AS DECIMAL(10,2))) as TRD_G_KEYIN
                FROM ims_product_sale_sac 
                WHERE DI_MONTH = '" . date("n") . "'
                AND DI_YEAR = '" . date("Y") . "'                                                      
                GROUP BY SLMN_NAME
                ORDER BY SLMN_NAME";

$statement = $conn->query($sql_monthly);
$results = $statement->fetchAll(PDO::FETCH_ASSOC);

if (count($results) > 0) {
    foreach ($results as $row) {
        $amount = $row['TRD_G_KEYIN'];
        $total += $amount;

        // คำนวณเปอร์เซ็นต์
        $percent_sale = ($sale_point > 0) ? ($amount / $sale_point) * 100 : 0;
        $total_remain = $sale_point - $amount;
        $percent_remain = ($sale_point > 0) ? ($total_remain / $sale_point) * 100 : 0;

        $width_style = "style='width: " . number_format($percent_sale, 0) . "%'";

        $html_rows .= '<tr>';
        $html_rows .= '<td>' . htmlentities($row['SLMN_NAME']) . '</td>';
        $html_rows .= '<td>';
        $html_rows .= '  <p class="number">' . number_format($amount, 2) . '</p>';
        $html_rows .= '  <div class="progress mb-2">';
        $html_rows .= '    <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" ' . $width_style . ' aria-valuenow="'.$percent_sale.'" aria-valuemin="0" aria-valuemax="100">' . number_format($percent_sale, 2) . '%</div>';
        $html_rows .= '  </div>';
        $html_rows .= '  <small class="d-block text-right">คิดเป็น ' . number_format($percent_sale, 2) . '% จากเป้า</small>';
        $html_rows .= '  <small class="d-block text-right text-danger">ขาดอีก ' . number_format($total_remain, 2) . ' (' . number_format($percent_remain, 2) . '%)</small>';
        $html_rows .= '</td>';
        $html_rows .= '</tr>';
    }
} else {
    $html_rows = '<tr><td colspan="2" class="text-center">ไม่พบข้อมูลยอดขายเดือนนี้</td></tr>';
}

$total_text = "ยอดขายรวม เดือน " . $month_text . " = " . number_format($total, 2) . " บาท";

echo json_encode(array(
    "html" => $html_rows,
    "total_text" => $total_text
));