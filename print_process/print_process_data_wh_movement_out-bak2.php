<?php
include('../config/connect_db.php');

// รับค่าวันที่จากการส่ง POST
$doc_date_start = $_POST["doc_date_start"];
$doc_date_to = $_POST["doc_date_to"];

$myfile = fopen("wh1_param.txt", "w") or die("Unable to open file!");
fwrite($myfile, $doc_date_start);
fclose($myfile);

// แปลงรูปแบบวันที่
$start_date_formatted = DateTime::createFromFormat('d-m-Y', $doc_date_start)->format('Y-m-d');
$end_date_formatted = DateTime::createFromFormat('d-m-Y', $doc_date_to)->format('Y-m-d');

// SQL Query เพื่อดึงข้อมูล
$sql_get = "SELECT 
    vo.id, vo.doc_date, vo.doc_id, vo.line_no, vo.product_id, vo.product_name, vo.wh_org, vo.wh_week_id, vo.location_org,
    vo.sale_take, vo.customer_name, vo.car_no, vo.doc_user_id, vo.location_to, vo.qty, vb.total_qty, vo.create_by, vo.create_date 
FROM 
    v_wh_stock_movement_out vo
LEFT JOIN 
    v_wh_stock_balance vb 
ON 
    vb.product_id = vo.product_id 
    AND vb.wh = vo.wh_org 
    AND vb.wh_week_id = vo.wh_week_id 
    AND vb.location = vo.location_org 
WHERE 
    vo.doc_date BETWEEN '$doc_date_start' AND '$doc_date_to' 
ORDER BY 
    vo.doc_id";

// เตรียม query
$query = $conn->prepare($sql_get);
$query->execute();
$results = $query->fetchAll(PDO::FETCH_OBJ);

// แสดงข้อมูลในรูปแบบ HTML
if ($query->rowCount() >= 1) {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<thead>
            <tr>
                <th>วันที่</th>
                <th>รหัสสินค้า</th>
                <th>รายละเอียด</th>
                <th>จำนวน</th>
                <th>คลังปี</th>
                <th>สัปดาห์</th>
                <th>ตำแหน่ง</th>
                <th>เลขที่เอกสาร</th>
                <th>รถคันที่</th>
                <th>เทค</th>
                <th>Supplier/ลูกค้า</th>
                <th>ยอดคงเหลือ</th>
            </tr>
          </thead>";
    echo "<tbody>";

    // Loop ผ่านข้อมูลและสร้างแถว
    foreach ($results as $result) {
        echo "<tr>";
        echo "<td>" . $result->doc_date . "</td>";
        echo "<td>" . $result->product_id . "</td>";
        echo "<td>" . $result->product_name . "</td>";
        echo "<td>" . $result->qty . "</td>";
        echo "<td>" . $result->wh_org . "</td>";
        echo "<td>" . $result->wh_week_id . "</td>";
        echo "<td>" . $result->location_org . "</td>";
        echo "<td>" . $result->doc_id . "</td>";
        echo "<td>" . $result->car_no . "</td>";
        echo "<td>" . $result->sale_take . "</td>";
        echo "<td>" . $result->customer_name . "</td>";
        echo "<td>" . $result->total_qty . "</td>";
        echo "</tr>";
    }

    echo "</tbody></table>";
} else {
    echo "No data found for the given date range.";
}
?>
